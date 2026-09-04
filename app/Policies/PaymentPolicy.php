<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * A payment attempt is visible to — and actionable by — the Traveler who
     * owns the booking it belongs to. Ownership only: the checkout page and
     * callback stay reachable to show (or safely replay) a resolved payment,
     * with the controller enforcing the "still payable" checks.
     */
    public function view(User $user, Payment $payment): bool
    {
        return $user->id === $payment->booking->user_id;
    }
}
