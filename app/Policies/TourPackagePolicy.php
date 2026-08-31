<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\TourPackage;
use App\Models\User;

class TourPackagePolicy
{
    /**
     * Travel Partners can list their own packages; Admins can list all of them;
     * Travelers can browse the public (active) listing.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVEL_PARTNER->value)
            || $user->hasRole(UserRole::ADMIN->value)
            || $user->hasRole(UserRole::TRAVELER->value);
    }

    /**
     * Travel Partners can view their own packages; Admins can view any package;
     * Travelers can view any package that is currently active.
     */
    public function view(User $user, TourPackage $tourPackage): bool
    {
        return $user->id === $tourPackage->user_id
            || $user->hasRole(UserRole::ADMIN->value)
            || ($user->hasRole(UserRole::TRAVELER->value) && $tourPackage->isActive());
    }

    /**
     * Only verified Travel Partners can create a tour package.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVEL_PARTNER->value) && $user->isVerifiedProvider();
    }

    /**
     * Travel Partners can update only their own packages.
     */
    public function update(User $user, TourPackage $tourPackage): bool
    {
        return $user->id === $tourPackage->user_id;
    }

    /**
     * Travel Partners can delete only their own packages.
     */
    public function delete(User $user, TourPackage $tourPackage): bool
    {
        return $user->id === $tourPackage->user_id;
    }
}
