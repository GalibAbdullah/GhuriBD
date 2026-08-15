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
            self::TRAVELER => 'traveler.dashboard',
            self::TRAVEL_PARTNER => 'partner.dashboard',
            self::ADMIN => 'admin.dashboard',
        };
    }

    public static function redirectTo(User $user): string
    {
        foreach ([self::ADMIN, self::TRAVEL_PARTNER, self::TRAVELER] as $role) {
            if ($user->hasRole($role->value)) {
                return '/'.collect(explode('.', $role->routeName()))->first();
            }
        }

        // No role assigned — send to profile instead of a 403 on a role-gated dashboard.
        return '/profile';
    }
}