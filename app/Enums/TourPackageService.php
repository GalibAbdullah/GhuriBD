<?php

namespace App\Enums;

enum TourPackageService: string
{
    case TRANSPORT = 'Transport';
    case HOTEL = 'Hotel';
    case MEALS = 'Meals';
    case GUIDE = 'Guide';
    case ENTRY_TICKETS = 'Entry Tickets';
    case PHOTOGRAPHY = 'Photography';

    public static function values(): array
    {
        return array_map(static fn (self $service): string => $service->value, self::cases());
    }
}
