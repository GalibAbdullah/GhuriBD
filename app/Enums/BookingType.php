<?php

namespace App\Enums;

enum BookingType: string
{
    case RESORT = 'resort';
    case PACKAGE = 'package';
    case COMBINED = 'combined';
    case GUIDE = 'guide';

    public static function values(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }
}
