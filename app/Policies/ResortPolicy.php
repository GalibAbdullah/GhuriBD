<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Resort;
use App\Models\User;

class ResortPolicy
{
    /**
     * Travel Partners can list their own resorts; Admins can list all of them.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVEL_PARTNER->value) || $user->hasRole(UserRole::ADMIN->value);
    }

    /**
     * Travel Partners can view their own resorts; Admins can view any resort.
     */
    public function view(User $user, Resort $resort): bool
    {
        return $user->id === $resort->user_id || $user->hasRole(UserRole::ADMIN->value);
    }

    /**
     * Only verified Travel Partners can create a resort listing.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVEL_PARTNER->value) && $user->isVerifiedProvider();
    }

    /**
     * Travel Partners can update only their own resorts.
     */
    public function update(User $user, Resort $resort): bool
    {
        return $user->id === $resort->user_id;
    }

    /**
     * Travel Partners can delete only their own resorts.
     */
    public function delete(User $user, Resort $resort): bool
    {
        return $user->id === $resort->user_id;
    }
}
