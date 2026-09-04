<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    /**
     * Every authenticated role may open the complaints list — the
     * controller scopes it to "mine" for Travelers/Partners and "all" for
     * Admins.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * A complaint is visible to the user who filed it and to Admins.
     */
    public function view(User $user, Complaint $complaint): bool
    {
        return $user->id === $complaint->user_id || $user->hasRole(UserRole::ADMIN->value);
    }

    /**
     * Travelers and Travel Partners file complaints; Admins resolve them
     * rather than filing their own.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVELER->value) || $user->hasRole(UserRole::TRAVEL_PARTNER->value);
    }

    /**
     * Only Admins respond to / change the status of a complaint.
     */
    public function respond(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN->value);
    }
}
