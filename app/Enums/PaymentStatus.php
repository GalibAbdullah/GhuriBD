<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'Pending';
    case PAID = 'Paid';
    case FAILED = 'Failed';
    case REFUNDED = 'Refunded';

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }

    public function isRefunded(): bool
    {
        return $this === self::REFUNDED;
    }
}
