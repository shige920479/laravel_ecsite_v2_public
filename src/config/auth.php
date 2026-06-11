<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'web_admin' => [
            'driver' => 'session',
            'provider' => 'admin',
        ],
        'web_owner' => [
            'driver' => 'session',
            'provider' => 'owners',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
        'admin' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\Admin::class),
        ],
        'owners' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\Owner::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],



    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
