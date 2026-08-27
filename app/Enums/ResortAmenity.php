<?php

namespace App\Enums;

enum ResortAmenity: string
{
    case WIFI = 'WiFi';
    case SWIMMING_POOL = 'Swimming Pool';
    case PARKING = 'Parking';
    case RESTAURANT = 'Restaurant';
    case AIR_CONDITIONING = 'Air Conditioning';
    case BREAKFAST = 'Breakfast';
    case SEA_VIEW = 'Sea View';
    case MOUNTAIN_VIEW = 'Mountain View';
    case GYM = 'Gym';
    case FAMILY_FRIENDLY = 'Family Friendly';

    public static function values(): array
    {
        return array_map(static fn (self $amenity): string => $amenity->value, self::cases());
    }
}
