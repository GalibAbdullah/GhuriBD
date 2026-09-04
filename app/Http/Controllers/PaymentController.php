<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Payments\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Entry point from the booking flow: start (or resume) a payment attempt
     * for a booking and hand the traveler to the gateway's checkout page.
     */
    public function checkout(Booking $booking, PaymentGateway $gateway): RedirectResponse
    {
        Gate::authorize('view', $booking);

        if ($booking->payment_status !== PaymentStatus::PENDING->value) {
            return redirect()
                ->route('traveler.bookings.show', $booking)
                ->with('status', 'This booking has already been paid.');
        }

        if ($booking->booking_status === BookingStatus::CANCELLED->value) {
            return redirect()
                ->route('traveler.bookings.show', $booking)
                ->withErrors(['payment' => 'This booking has been cancelled.']);
        }

        // Reuse an unresolved attempt so a traveler who navigated away and
        // came back doesn't accumulate dangling Payment rows.
        $payment = $booking->payments()
            ->where('status', PaymentStatus::PENDING->value)
            ->latest()
            ->first();

        $redirectUrl = $payment
            ? route('payments.show', $payment)
            : $gateway->initiate($booking)->redirectUrl;

        return redirect()->to($redirectUrl);
    }

    /**
     * The mock gateway's in-app "checkout page" — approve or decline in place
     * of a real redirect to an external processor.
     */
    public function show(Payment $payment): View|RedirectResponse
    {
        Gate::authorize('view', $payment);

        if (! $payment->isPending()) {
            return redirect()
                ->route('traveler.bookings.show', $payment->booking)
                ->with('status', 'This payment has already been processed.');
        }

        return view('payments.checkout', [
            'payment' => $payment->load('booking'),
        ]);
    }

    /**
     * Handle the gateway callback for a payment attempt: confirm the booking
     * on success, record the failure otherwise.
     */
    public function callback(Request $request, Payment $payment, PaymentGateway $gateway): RedirectResponse
    {
        Gate::authorize('view', $payment);

        $booking = $payment->booking;

        // A replayed or double-submitted callback must not be processed twice —
        // the first resolution already decided this payment's fate.
        if (! $payment->isPending() || $booking->payment_status !== PaymentStatus::PENDING->value) {
            return redirect()
                ->route('traveler.bookings.show', $booking)
                ->with('status', 'This payment has already been processed.');
        }

        if ($booking->booking_status === BookingStatus::CANCELLED->value) {
            return redirect()
                ->route('traveler.bookings.show', $booking)
                ->withErrors(['payment' => 'This booking has been cancelled.']);
        }

        $result = $gateway->handleCallback($payment, $request);

        if (! $result->success) {
            $payment->update([
                'status' => PaymentStatus::FAILED->value,
                'failure_reason' => $result->failureReason,
                'gateway_payload' => $result->rawPayload,
            ]);

            return redirect()
                ->route('traveler.bookings.show', $booking)
                ->withErrors(['payment' => $result->failureReason ?? 'The payment was declined.']);
        }

        $outcome = DB::transaction(function () use ($payment, $booking, $gateway): string {
            // Room capacity can legitimately have shifted while the traveler
            // was at checkout (another traveler paid first). Re-check under a
            // lock before confirming.
            if ($booking->room_id !== null) {
                $overlapping = Booking::query()
                    ->overlappingForRoom(
                        $booking->room_id,
                        $booking->check_in_date->toDateString(),
                        $booking->check_out_date->toDateString(),
                    )
                    ->where('id', '!=', $booking->id)
                    ->lockForUpdate()
                    ->count();

                $availableRooms = $booking->room()->value('available_rooms') ?? 0;

                if ($overlapping >= $availableRooms) {
                    // The gateway genuinely captured the charge, so it is
                    // marked paid-then-refunded — never "failed", which would
                    // misreport a charge that did go through.
                    $payment->update(['status' => PaymentStatus::PAID->value, 'paid_at' => now()]);
                    $gateway->refund($payment);
                    $payment->update(['status' => PaymentStatus::REFUNDED->value]);

                    $booking->update([
                        'booking_status' => BookingStatus::CANCELLED->value,
                        'payment_status' => PaymentStatus::REFUNDED->value,
                    ]);

                    return 'lost_race';
                }
            }

            $payment->update([
                'status' => PaymentStatus::PAID->value,
                'paid_at' => now(),
            ]);

            $booking->update([
                'booking_status' => BookingStatus::CONFIRMED->value,
                'payment_status' => PaymentStatus::PAID->value,
            ]);

            return 'confirmed';
        });

        if ($outcome === 'lost_race') {
            return redirect()
                ->route('traveler.bookings.show', $booking)
                ->withErrors(['payment' => 'Sorry — this room was booked by someone else moments before your payment completed. You have been refunded.']);
        }

        return redirect()
            ->route('traveler.bookings.show', $booking)
            ->with('status', 'Payment successful. Your booking is confirmed.');
    }
}
