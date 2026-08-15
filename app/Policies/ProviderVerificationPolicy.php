<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ProviderVerification;
use App\Models\User;

class ProviderVerificationPolicy
{
    /**
     * Travel Partners can view their own verification requests.
     */
    public function view(User $user, ProviderVerification $verification): bool
    {
        return $user->id === $verification->user_id || $user->hasRole(UserRole::ADMIN->value);
    }

    /**
     * Travel Partners can create a new verification request.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVEL_PARTNER->value);
    }

    /**
     * Travel Partners can update their own pending verification requests.
     */
    public function update(User $user, ProviderVerification $verification): bool
    {
        return $user->id === $verification->user_id && $verification->isPending();
    }

    /**
     * Only Admins can review (approve/reject) verification requests.
     */
    public function review(User $user, ProviderVerification $verification): bool
    {
        // Admins cannot approve themselves
        if ($user->id === $verification->user_id) {
            return false;
        }

        return $user->hasRole(UserRole::ADMIN->value) && $verification->isPending();
    }

    /**
     * Travel Partners can delete their own pending requests.
     */
    public function delete(User $user, ProviderVerification $verification): bool
    {
        return $user->id === $verification->user_id && $verification->isPending();
    }
}