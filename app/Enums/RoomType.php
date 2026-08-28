<?php

namespace App\Enums;

enum RoomType: string
{
    case STANDARD = 'Standard Room';
    case DELUXE = 'Deluxe Room';
    case PREMIUM = 'Premium Room';
    case FAMILY = 'Family Room';
    case COUPLE_SUITE = 'Couple Suite';

    public static function values(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }
}
