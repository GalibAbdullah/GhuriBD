<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Wishlist;

class WishlistPolicy
{
    /**
     * Only Travelers use the wishlist. Admins get read-only access.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVELER->value) || $user->hasRole(UserRole::ADMIN->value);
    }

    /**
     * A Traveler may view only their own wishlist items.
     */
    public function view(User $user, Wishlist $wishlist): bool
    {
        if ($user->hasRole(UserRole::ADMIN->value)) {
            return true;
        }

        return $user->id === $wishlist->user_id;
    }

    /**
     * Only Travelers can add to a wishlist.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVELER->value);
    }

    /**
     * A Traveler may remove only their own wishlist items.
     */
    public function delete(User $user, Wishlist $wishlist): bool
    {
        return $user->id === $wishlist->user_id;
    }
}
