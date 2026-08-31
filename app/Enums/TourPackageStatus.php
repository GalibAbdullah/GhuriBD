<?php

namespace App\Enums;

enum TourPackageStatus: string
{
    case ACTIVE = 'Active';
    case INACTIVE = 'Inactive';

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
