<?php

use App\Models\User;
use App\Models\Customer;

return [

    'defaults' => [
        'guard'     => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        // Admin / Staff guard (existing — হাত দেওয়া হয়নি)
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        // Client Portal guard (নতুন)
        'customer' => [
            'driver'   => 'session',
            'provider' => 'customers',
        ],
    ],

    'providers' => [
        // Existing admin users
        'users' => [
            'driver' => 'eloquent',
            'model'  => env('AUTH_MODEL', User::class),
        ],

        // Client portal customers
        // customer_code দিয়ে login হবে (getAuthIdentifierName = 'customer_code')
        'customers' => [
            'driver' => 'eloquent',
            'model'  => Customer::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire'   => 60,
            'throttle' => 60,
        ],

        'customers' => [
            'provider' => 'customers',
            'table'    => 'customer_password_resets',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
