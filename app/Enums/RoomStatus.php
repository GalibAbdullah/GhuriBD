<?php

namespace App\Enums;

enum RoomStatus: string
{
    case AVAILABLE = 'Available';
    case UNAVAILABLE = 'Unavailable';

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    public function isAvailable(): bool
    {
        return $this === self::AVAILABLE;
    }
}
