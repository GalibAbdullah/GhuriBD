<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Payments\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * The mock gateway's in-app "checkout page" — approve or decline in place
     * of a real redirect to an external processor.
     */
    public function mockShow(Request $request, Payment $payment): View|RedirectResponse
    {
        $this->authorizeOwner($request, $payment);

        if (! $payment->isInitiated()) {
            return redirect()
                ->route('bookings.show', $payment->booking)
                ->with('status', 'This payment has already been processed.');
        }

        return view('payments.mock', [
            'payment' => $payment->load('booking.bookable'),
        ]);
    }

    public function mockCallback(Request $request, Payment $payment, PaymentGateway $gateway): RedirectResponse
    {
        $booking = $payment->booking;

        $this->authorizeOwner($request, $payment);

        // A replayed or double-submitted callback must not be processed twice —
        // the first resolution already decided this payment's fate.
        if (! $payment->isInitiated()) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('status', 'This payment has already been processed.');
        }

        $result = $gateway->handleCallback($payment, $request);

        if (! $result->success) {
            $payment->update([
                'status' => PaymentStatus::FAILED->value,
                'failure_reason' => $result->failureReason,
                'gateway_payload' => $result->rawPayload,
            ]);

            return redirect()
                ->route('bookings.show', $booking)
                ->withErrors(['payment' => $result->failureReason ?? 'The payment was declined.']);
        }

        $outcome = DB::transaction(function () use ($payment, $booking, $gateway): string {
            // Capacity is authoritative here, not at booking creation — it can
            // legitimately have shifted (another traveler paid first, or the
            // guide edited the slot) in the time it took to reach checkout.
            $bookable = $booking->bookable()->lockForUpdate()->first();

            if ($bookable === null || $bookable->remainingCapacity() < $booking->party_size) {
                // The gateway genuinely captured the charge, so it is marked
                // succeeded-then-refunded — never "failed", which would
                // misreport a charge that did in fact go through.
                $payment->update(['status' => PaymentStatus::SUCCEEDED->value, 'paid_at' => now()]);
                $gateway->refund($payment);
                $payment->update(['status' => PaymentStatus::REFUNDED->value]);

                $booking->update([
                    'status' => BookingStatus::CANCELLED->value,
                    'cancellation_reason' => 'Slot filled before payment completed.',
                    'cancelled_at' => now(),
                ]);

                return 'lost_race';
            }

            $bookable->increment('booked_count', $booking->party_size);

            $payment->update([
                'status' => PaymentStatus::SUCCEEDED->value,
                'paid_at' => now(),
            ]);

            $booking->update(['status' => BookingStatus::CONFIRMED->value]);

            return 'confirmed';
        });

        if ($outcome === 'lost_race') {
            return redirect()
                ->route('bookings.show', $booking)
                ->withErrors(['payment' => 'Sorry — this slot was booked by someone else moments before your payment completed. You have not been charged.']);
        }

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', 'Payment successful. Your booking is confirmed.');
    }

    /**
     * Ownership only — deliberately not the "pay" ability, since that also
     * requires the booking to still be payable. A resolved payment's own
     * booking is naturally no longer payable, and this endpoint must still be
     * reachable by its owner to see (or safely replay) that resolution.
     */
    private function authorizeOwner(Request $request, Payment $payment): void
    {
        abort_unless($request->user()?->id === $payment->booking->traveler_id, 403);
    }
}
