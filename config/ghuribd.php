<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Account
    |--------------------------------------------------------------------------
    |
    | Credentials used by the AdminSeeder to create the initial
    | administrator account. Override these in your .env file.
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'GhuriBD Admin'),
        'email' => env('ADMIN_EMAIL', 'admin@ghuribd.test'),
        'password' => env('ADMIN_PASSWORD', 'Admin123!'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Operating Timezone
    |--------------------------------------------------------------------------
    |
    | Calendar dates are business dates in Bangladesh, not UTC dates. A guide
    | opening a slot at 01:00 in Dhaka is still on "today" locally while UTC has
    | not yet rolled over, so every past/future date check resolves "today"
    | through this timezone rather than the app default.
    |
    */

    'timezone' => env('GHURIBD_TIMEZONE', 'Asia/Dhaka'),

    'currency' => [
        'code' => 'BDT',
        'symbol' => '৳',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guide Availability Rules
    |--------------------------------------------------------------------------
    */

    'availability' => [
        // How far ahead a guide may publish availability.
        'max_advance_days' => 365,

        // Guards against fat-fingered slots and against a single slot swallowing
        // the whole day so that no other slot can be added.
        'min_duration_minutes' => 30,
        'max_duration_minutes' => 1440,

        // Travelers per slot.
        'min_capacity' => 1,
        'max_capacity' => 50,

        // Per-slot price in BDT.
        'max_price' => 1000000,

        // Longest span a single bulk publish may cover.
        'max_bulk_range_days' => 90,
    ],

];