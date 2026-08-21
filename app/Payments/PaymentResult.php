<?php

namespace App\Payments;

/**
 * The outcome of a gateway callback, independent of what triggered it (a
 * redirect back, a webhook, or — for the mock gateway — a same-app form post).
 */
final class PaymentResult
{
    private function __construct(
        public readonly bool $success,
        public readonly string $gatewayReference,
        public readonly ?string $failureReason,
        public readonly array $rawPayload,
    ) {}

    public static function success(string $gatewayReference, array $rawPayload = []): self
    {
        return new self(true, $gatewayReference, null, $rawPayload);
    }

    public static function failure(string $gatewayReference, string $reason, array $rawPayload = []): self
    {
        return new self(false, $gatewayReference, $reason, $rawPayload);
    }
}
