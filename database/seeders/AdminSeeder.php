<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public const ADMIN_NAME = 'GhuriBD Admin';

    public const ADMIN_EMAIL = 'admin@ghuribd.test';

    public const ADMIN_PASSWORD = 'Admin123!';

    public function run(): void
    {
        Role::findOrCreate(UserRole::ADMIN->value, 'web');

        $admin = User::updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => self::ADMIN_NAME,
                'password' => self::ADMIN_PASSWORD,
            ]
        );

        $admin->syncRoles(UserRole::ADMIN->value);
    }
}