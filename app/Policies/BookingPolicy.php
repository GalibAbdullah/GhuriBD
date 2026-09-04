<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    /**
     * Every role may open a bookings list — the controller scopes the query
     * per role (own bookings, own services' bookings, or everything).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVELER->value)
            || $user->hasRole(UserRole::TRAVEL_PARTNER->value)
            || $user->hasRole(UserRole::ADMIN->value);
    }

    /**
     * Travelers may view only their own bookings; Travel Partners may view a
     * booking only if it touches a resort or tour package they own; Admins
     * may view any booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        if ($user->hasRole(UserRole::ADMIN->value)) {
            return true;
        }

        if ($user->hasRole(UserRole::TRAVELER->value)) {
            return $user->id === $booking->user_id;
        }

        if ($user->hasRole(UserRole::TRAVEL_PARTNER->value)) {
            return $booking->resort?->user_id === $user->id
                || $booking->tourPackage?->user_id === $user->id;
        }

        return false;
    }

    /**
     * Only Travelers create bookings — Travel Partners cannot book on behalf
     * of a traveler.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVELER->value);
    }

    /**
     * A booking may be cancelled only by the Traveler who owns it, and only
     * while it is still cancellable (not already cancelled/completed, and
     * its date hasn't passed).
     */
    public function cancel(User $user, Booking $booking): Response
    {
        if ($user->id !== $booking->user_id) {
            return Response::denyWithStatus(403);
        }

        if (! $booking->isCancellable()) {
            return Response::denyWithStatus(403, 'This booking can no longer be cancelled.');
        }

        return Response::allow();
    }

    /**
     * Only the Travel Partner who owns the booked resort/package may mark a
     * booking Completed, and only once it's Confirmed.
     */
    public function complete(User $user, Booking $booking): Response
    {
        $ownsService = $booking->resort?->user_id === $user->id
            || $booking->tourPackage?->user_id === $user->id;

        if (! $ownsService) {
            return Response::denyWithStatus(403);
        }

        if (! $booking->isConfirmed()) {
            return Response::denyWithStatus(403, 'Only confirmed bookings can be marked as completed.');
        }

        return Response::allow();
    }
}
