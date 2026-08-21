<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    public function create(User $user): Response
    {
        return $user->hasRole(UserRole::TRAVELER->value)
            ? Response::allow()
            : Response::denyWithStatus(403, 'Only Travelers can make bookings.');
    }

    /**
     * The traveler who booked, or the guide being booked, may view it — a
     * guide reasonably needs to see who is coming to their own tour.
     */
    public function view(User $user, Booking $booking): Response
    {
        if ($user->id === $booking->traveler_id) {
            return Response::allow();
        }

        if ($booking->bookable instanceof GuideAvailability && $user->id === $booking->bookable->user_id) {
            return Response::allow();
        }

        return Response::denyWithStatus(403);
    }

    public function pay(User $user, Booking $booking): Response
    {
        if ($user->id !== $booking->traveler_id) {
            return Response::denyWithStatus(403);
        }

        return $booking->canBePaid()
            ? Response::allow()
            : Response::denyWithStatus(403, 'This booking can no longer be paid for.');
    }

    public function cancel(User $user, Booking $booking): Response
    {
        if ($user->id !== $booking->traveler_id) {
            return Response::denyWithStatus(403);
        }

        return $booking->canBeCancelled()
            ? Response::allow()
            : Response::denyWithStatus(403, 'This booking can no longer be cancelled.');
    }
}
