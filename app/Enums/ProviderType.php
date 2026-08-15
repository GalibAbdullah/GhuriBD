<?php

namespace App\Enums;

enum ProviderType: string
{
    case RESORT_OWNER = 'Resort Owner';
    case TOUR_OPERATOR = 'Tour Operator';
    case TOUR_GUIDE = 'Tour Guide';

    public static function values(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }
}