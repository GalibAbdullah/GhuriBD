<?php

namespace App\Enums;

use App\Models\User;

enum UserRole: string
{
    case TRAVELER = 'Traveler';
    case TRAVEL_PARTNER = 'Travel Partner';
    case ADMIN = 'Admin';

    public static function registrationOptions(): array
    {
        return [self::TRAVELER, self::TRAVEL_PARTNER];
    }

    public static function registrationValues(): array
    {
        return array_map(static fn (self $role): string => $role->value, self::registrationOptions());
    }

    public function routeName(): string
    {
        return match ($this) {
            self::TRAVELER => 'traveler',
            self::TRAVEL_PARTNER => 'partner',
            self::ADMIN => 'admin',
        };
    }

    public static function redirectTo(User $user): string
    {
        foreach ([self::ADMIN, self::TRAVEL_PARTNER, self::TRAVELER] as $role) {
            if ($user->hasRole($role->value)) {
                return '/'.$role->routeName();
            }
        }

        return '/'.self::TRAVELER->routeName();
    }
}