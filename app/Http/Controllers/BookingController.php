<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Payments\PaymentGateway;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookingController extends Controller
{
    private const SCOPES = ['upcoming', 'past', 'all'];

    public function index(Request $request): View
    {
        $scope = in_array($request->query('scope'), self::SCOPES, true)
            ? $request->query('scope')
            : 'upcoming';

        $today = GuideAvailability::today()->toDateString();

        $bookings = Booking::query()
            ->where('traveler_id', $request->user()->id)
            ->with('bookable', 'latestPayment')
            ->when($scope !== 'all', function (Builder $query) use ($scope, $today): void {
                // Filtered at the DB level (before pagination) so page counts stay
                // correct — a per-row PHP filter after paginate() would silently
                // drop items from a page without adjusting its reported total.
                $query->whereHasMorph('bookable', [GuideAvailability::class], function (Builder $q) use ($scope, $today): void {
                    $scope === 'past'
                        ? $q->where('available_date', '<', $today)
                        : $q->where('available_date', '>=', $today);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('bookings.index', [
            'bookings' => $bookings,
            'scope' => $scope,
        ]);
    }

    public function create(GuideAvailability $availability): View
    {
        if (! $availability->isBookable()) {
            abort(403, 'This slot is no longer open for booking.');
        }

        return view('bookings.create', [
            'availability' => $availability,
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $availability = $request->availability();
        $partySize = (int) $request->validated('party_size');

        $booking = Booking::create([
            'traveler_id' => $request->user()->id,
            'bookable_type' => GuideAvailability::class,
            'bookable_id' => $availability->id,
            'party_size' => $partySize,
            'unit_price' => $availability->price,
            'total_price' => bcmul((string) $availability->price, (string) $partySize, 2),
            'status' => BookingStatus::PENDING_PAYMENT->value,
        ]);

        return redirect()->route('bookings.checkout', $booking);
    }

    public function show(Booking $booking): View
    {
        Gate::authorize('view', $booking);

        return view('bookings.show', [
            'booking' => $booking->load('bookable', 'payments'),
        ]);
    }

    public function checkout(Booking $booking, PaymentGateway $gateway): RedirectResponse
    {
        Gate::authorize('pay', $booking);

        $session = $gateway->initiate($booking);

        return redirect($session->redirectUrl);
    }

    public function cancel(Request $request, Booking $booking, PaymentGateway $gateway): RedirectResponse
    {
        Gate::authorize('cancel', $booking);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $wasConfirmed = $booking->isConfirmed();

        DB::transaction(function () use ($booking, $gateway, $wasConfirmed, $validated): void {
            if ($wasConfirmed) {
                $bookable = $booking->bookable()->lockForUpdate()->first();

                if ($bookable !== null) {
                    $bookable->decrement('booked_count', $booking->party_size);
                }

                $succeededPayment = $booking->payments()
                    ->where('status', PaymentStatus::SUCCEEDED->value)
                    ->latest()
                    ->first();

                if ($succeededPayment !== null && $gateway->refund($succeededPayment)) {
                    $succeededPayment->update(['status' => PaymentStatus::REFUNDED->value]);
                }
            }

            $booking->update([
                'status' => BookingStatus::CANCELLED->value,
                'cancellation_reason' => $validated['reason'] ?? null,
                'cancelled_at' => now(),
            ]);
        });

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', $wasConfirmed
                ? 'Booking cancelled. Any payment made will be refunded.'
                : 'Booking cancelled.');
    }
}
