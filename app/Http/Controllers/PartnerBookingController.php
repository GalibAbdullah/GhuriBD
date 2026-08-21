<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\GuideAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PartnerBookingController extends Controller
{
    /**
     * Read-only: which travelers have booked this guide's slots. Guides do not
     * cancel or refund bookings on a traveler's behalf in this release.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', GuideAvailability::class);

        $bookings = Booking::query()
            ->whereHasMorph('bookable', [GuideAvailability::class], function ($query) use ($request): void {
                $query->where('user_id', $request->user()->id);
            })
            ->with('bookable', 'traveler', 'latestPayment')
            ->latest()
            ->paginate(15);

        return view('partner.bookings.index', [
            'bookings' => $bookings,
        ]);
    }
}
