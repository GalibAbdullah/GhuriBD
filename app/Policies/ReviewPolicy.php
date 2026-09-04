<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{
    /**
     * Travel Partners view reviews for their own resorts/packages; Admins
     * view every review.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVEL_PARTNER->value) || $user->hasRole(UserRole::ADMIN->value);
    }

    /**
     * A booking may be reviewed only by the Traveler who owns it, only once
     * it's Completed, and only once.
     */
    public function create(User $user, Booking $booking): Response
    {
        if ($user->id !== $booking->user_id) {
            return Response::denyWithStatus(403);
        }

        if (! $booking->isCompleted()) {
            return Response::denyWithStatus(403, 'Only completed bookings can be reviewed.');
        }

        if ($booking->review()->exists()) {
            return Response::denyWithStatus(403, 'This booking has already been reviewed.');
        }

        return Response::allow();
    }

    /**
     * Only the Travel Partner who owns the reviewed resort/package may reply
     * — never the traveler's rating or text.
     */
    public function reply(User $user, Review $review): bool
    {
        return $review->resort?->user_id === $user->id
            || $review->tourPackage?->user_id === $user->id;
    }

    /**
     * Only Admins may delete inappropriate reviews.
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->hasRole(UserRole::ADMIN->value);
    }
}
