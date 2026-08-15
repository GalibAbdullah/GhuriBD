<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

// Create the Travel Partner role if it doesn't exist
Role::findOrCreate(UserRole::TRAVEL_PARTNER->value, 'web');

// Create or update the travel partner user
$partner = User::updateOrCreate(
    ['email' => 'partner@ghuribd.test'],
    [
        'name' => 'Travel Partner',
        'password' => Hash::make('Partner123!'),
    ]
);

$partner->syncRoles(UserRole::TRAVEL_PARTNER->value);

echo 'Travel Partner created: ' . $partner->email . ' / Partner123!' . PHP_EOL;