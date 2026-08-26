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

    /*
    |--------------------------------------------------------------------------
    | Booking Rules
    |--------------------------------------------------------------------------
    */

    'booking' => [
        // Travelers per booking (a solo traveler or their whole party).
        'min_party_size' => 1,
        'max_party_size' => 20,

        // A confirmed booking can only be cancelled this many hours before the
        // slot starts — a guide who shows up to an empty tour an hour before
        // start time cannot recover that lost morning.
        'cancellation_window_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    |
    | "mock" simulates a gateway in-app (approve/decline) with no external
    | credentials, so the booking-and-payment flow is fully testable before a
    | real provider (e.g. SSLCommerz) is wired in. Swapping providers later is
    | one binding change in AppServiceProvider, not a rewrite of this config.
    |
    */

    'payment' => [
        'gateway' => env('PAYMENT_GATEWAY', 'mock'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Tour Planner
    |--------------------------------------------------------------------------
    |
    | "rule_based" generates itineraries with a deterministic budget/interest
    | allocator — no external API key or per-request cost, and fully testable.
    | Swapping in a real LLM later is one binding change in AppServiceProvider,
    | behind the same TourPlanner interface, not a rewrite of the feature.
    |
    */

    'tour_planner' => [
        'engine' => env('TOUR_PLANNER_ENGINE', 'rule_based'),

        'min_days' => 1,
        'max_days' => 14,

        'min_budget' => 1000,
        'max_budget' => 5000000,

        // Reserved off the top for transport/food/misc before splitting the
        // rest across days as each day's activity budget.
        'logistics_reserve_percent' => 20,
    ],

];
