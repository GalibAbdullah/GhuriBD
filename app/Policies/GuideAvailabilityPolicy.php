<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\GuideAvailability;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GuideAvailabilityPolicy
{
    /**
     * Only a Travel Partner whose Tour Guide verification has been approved may
     * open the calendar. Partners verified as Resort Owners or Tour Operators
     * are legitimately verified but are not guides.
     */
    public function viewAny(User $user): Response
    {
        if (! $user->hasRole(UserRole::TRAVEL_PARTNER->value)) {
            return Response::denyWithStatus(403, 'Only Travel Partners can manage guide availability.');
        }

        if (! $user->isVerifiedTourGuide()) {
            return Response::denyWithStatus(403, 'Your Tour Guide verification must be approved before you can manage availability.');
        }

        return Response::allow();
    }

    public function create(User $user): Response
    {
        return $this->viewAny($user);
    }

    public function view(User $user, GuideAvailability $availability): Response
    {
        return $user->id === $availability->user_id
            ? $this->viewAny($user)
            : Response::denyWithStatus(403);
    }

    /**
     * A slot is editable only by its owner, and only while it carries no
     * bookings and has not already finished.
     */
    public function update(User $user, GuideAvailability $availability): Response
    {
        if ($user->id !== $availability->user_id) {
            return Response::denyWithStatus(403);
        }

        if ($availability->hasBookings()) {
            return Response::denyWithStatus(403, 'This slot already has bookings and can no longer be changed.');
        }

        if ($availability->hasEnded()) {
            return Response::denyWithStatus(403, 'This slot has already passed and can no longer be changed.');
        }

        return $this->viewAny($user);
    }

    public function delete(User $user, GuideAvailability $availability): Response
    {
        return $this->update($user, $availability);
    }
}
