<?php

use App\Models\User;
use App\Models\Customer;
use App\Models\MacReseller;
use App\Models\SuperAdmin;

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

        // Client Portal guard (existing — হাত দেওয়া হয়নি)
        'customer' => [
            'driver'   => 'session',
            'provider' => 'customers',
        ],

        // MAC Reseller Portal guard (নতুন)
        'mac_reseller' => [
            'driver'   => 'session',
            'provider' => 'mac_resellers',
        ],

        // Super Admin (Central) guard (নতুন)
        'super_admin' => [
            'driver'   => 'session',
            'provider' => 'super_admins',
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

        // MAC Reseller portal (নতুন)
        // username দিয়ে login হবে (username() override করা আছে Model এ)
        'mac_resellers' => [
            'driver' => 'eloquent',
            'model'  => MacReseller::class,
        ],

        // Super Admin (Central) — email দিয়ে login হবে
        'super_admins' => [
            'driver' => 'eloquent',
            'model'  => SuperAdmin::class,
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

        'mac_resellers' => [
            'provider' => 'mac_resellers',
            'table'    => 'mac_reseller_password_resets',
            'expire'   => 60,
            'throttle' => 60,
        ],

        'super_admins' => [
            'provider' => 'super_admins',
            'table'    => 'super_admin_password_resets',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
