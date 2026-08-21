<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING_PAYMENT = 'Pending Payment';
    case CONFIRMED = 'Confirmed';
    case CANCELLED = 'Cancelled';
    case COMPLETED = 'Completed';

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    public function isPendingPayment(): bool
    {
        return $this === self::PENDING_PAYMENT;
    }

    public function isConfirmed(): bool
    {
        return $this === self::CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Bookings in these states hold no seat and settle no money — safe to
     * disregard when computing remaining capacity or revenue.
     */
    public function isTerminalWithoutCapacity(): bool
    {
        return $this === self::CANCELLED;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'badge badge-warning',
            self::CONFIRMED => 'badge badge-success',
            self::CANCELLED => 'badge badge-neutral',
            self::COMPLETED => 'badge badge-info',
        };
    }
}
