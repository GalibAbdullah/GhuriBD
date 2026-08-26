<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case AVAILABLE = 'Available';
    case BLOCKED = 'Blocked';
    case BOOKED = 'Booked';

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    /**
     * Statuses a guide may set directly. "Booked" is owned by the booking
     * system and must never be assignable from the availability forms.
     */
    public static function guideAssignable(): array
    {
        return [self::AVAILABLE, self::BLOCKED];
    }

    public static function guideAssignableValues(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::guideAssignable());
    }

    public function isAvailable(): bool
    {
        return $this === self::AVAILABLE;
    }

    public function isBlocked(): bool
    {
        return $this === self::BLOCKED;
    }

    public function isBooked(): bool
    {
        return $this === self::BOOKED;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::AVAILABLE => 'badge badge-success',
            self::BLOCKED => 'badge badge-neutral',
            self::BOOKED => 'badge badge-info',
        };
    }
}
