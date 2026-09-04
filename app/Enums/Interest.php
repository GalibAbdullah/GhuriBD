<?php

namespace App\Enums;

enum Interest: string
{
    case NATURE = 'Nature & Scenery';
    case HERITAGE_CULTURE = 'Heritage & Culture';
    case FOOD = 'Food & Cuisine';
    case ADVENTURE = 'Adventure';
    case WILDLIFE = 'Wildlife';
    case BEACH = 'Beach & Coast';
    case RELIGIOUS_SITES = 'Religious Sites';
    case SHOPPING = 'Shopping';
    case PHOTOGRAPHY = 'Photography';

    public static function values(): array
    {
        return array_map(static fn (self $interest): string => $interest->value, self::cases());
    }
}
