<?php

namespace App\Enums;

enum RoomAmenity: string
{
    case AC = 'AC';
    case WIFI = 'WiFi';
    case TV = 'TV';
    case BALCONY = 'Balcony';
    case MINI_FRIDGE = 'Mini Fridge';
    case BREAKFAST = 'Breakfast';
    case ATTACHED_BATHROOM = 'Attached Bathroom';
    case HOT_WATER = 'Hot Water';
    case SEA_VIEW = 'Sea View';
    case MOUNTAIN_VIEW = 'Mountain View';

    public static function values(): array
    {
        return array_map(static fn (self $amenity): string => $amenity->value, self::cases());
    }
}
