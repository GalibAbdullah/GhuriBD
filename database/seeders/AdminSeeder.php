<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate(UserRole::ADMIN->value, 'web');

        $admin = User::updateOrCreate(
            ['email' => config('ghuribd.admin.email')],
            [
                'name' => config('ghuribd.admin.name'),
                'password' => Hash::make(config('ghuribd.admin.password')),
            ]
        );

        $admin->syncRoles(UserRole::ADMIN->value);
    }
}