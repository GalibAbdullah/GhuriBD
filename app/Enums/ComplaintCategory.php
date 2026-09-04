<?php

namespace App\Enums;

enum ComplaintCategory: string
{
    case BOOKING = 'Booking';
    case PAYMENT = 'Payment';
    case PROVIDER_SERVICE = 'Provider Service';
    case OTHER = 'Other';

    public static function values(): array
    {
        return array_map(static fn (self $category): string => $category->value, self::cases());
    }
}
