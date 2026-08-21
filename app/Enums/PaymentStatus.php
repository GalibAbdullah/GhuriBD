<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case INITIATED = 'Initiated';
    case SUCCEEDED = 'Succeeded';
    case FAILED = 'Failed';
    case REFUNDED = 'Refunded';

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    public function isInitiated(): bool
    {
        return $this === self::INITIATED;
    }

    public function isSucceeded(): bool
    {
        return $this === self::SUCCEEDED;
    }

    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }

    public function isRefunded(): bool
    {
        return $this === self::REFUNDED;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::INITIATED => 'badge badge-warning',
            self::SUCCEEDED => 'badge badge-success',
            self::FAILED => 'badge badge-error',
            self::REFUNDED => 'badge badge-neutral',
        };
    }
}
