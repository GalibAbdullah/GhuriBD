<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'Pending';
    case CONFIRMED = 'Confirmed';
    case CANCELLED = 'Cancelled';
    case COMPLETED = 'Completed';

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }
}
