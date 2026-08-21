<?php

namespace App\Payments;

use App\Models\Payment;

/**
 * What a gateway hands back immediately after initiate(): where to send the
 * traveler next, and the Payment row tracking that attempt.
 */
final class PaymentSession
{
    public function __construct(
        public readonly Payment $payment,
        public readonly string $redirectUrl,
    ) {}
}
