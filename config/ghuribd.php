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

];