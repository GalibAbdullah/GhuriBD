<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Resort;
use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    /**
     * Travel Partners can list rooms of their own resorts; Admins can list any resort's rooms;
     * Travelers can browse rooms of an active resort.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVEL_PARTNER->value)
            || $user->hasRole(UserRole::ADMIN->value)
            || $user->hasRole(UserRole::TRAVELER->value);
    }

    /**
     * Travel Partners can view rooms of their own resorts; Admins can view any room;
     * Travelers can view rooms belonging to a currently active resort.
     */
    public function view(User $user, Room $room): bool
    {
        return $user->id === $room->resort->user_id
            || $user->hasRole(UserRole::ADMIN->value)
            || ($user->hasRole(UserRole::TRAVELER->value) && $room->resort->isActive());
    }

    /**
     * Only the resort's owning, verified Travel Partner can add rooms to it.
     */
    public function create(User $user, Resort $resort): bool
    {
        return $user->id === $resort->user_id
            && $user->hasRole(UserRole::TRAVEL_PARTNER->value)
            && $user->isVerifiedProvider();
    }

    /**
     * Travel Partners can update only rooms belonging to their own resorts.
     */
    public function update(User $user, Room $room): bool
    {
        return $user->id === $room->resort->user_id;
    }

    /**
     * Travel Partners can delete only rooms belonging to their own resorts.
     */
    public function delete(User $user, Room $room): bool
    {
        return $user->id === $room->resort->user_id;
    }
}
