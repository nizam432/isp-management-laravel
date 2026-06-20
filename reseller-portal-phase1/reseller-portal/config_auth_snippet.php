<?php
// ════════════════════════════════════════════════════════════════
// config/auth.php
// নিচের অংশগুলো existing 'guards' এবং 'providers' array এ মার্জ করুন
// ════════════════════════════════════════════════════════════════

// 'guards' array এর ভেতরে যোগ করুন (web, api এর পাশে):
'mac_reseller' => [
    'driver'   => 'session',
    'provider' => 'mac_resellers',
],

// 'providers' array এর ভেতরে যোগ করুন (users এর পাশে):
'mac_resellers' => [
    'driver' => 'eloquent',
    'model'  => App\Models\MacReseller::class,
],

// 'passwords' array এ যোগ করুন (যদি reseller password reset লাগে, ভবিষ্যতে):
'mac_resellers' => [
    'provider' => 'mac_resellers',
    'table'    => 'mac_reseller_password_reset_tokens',
    'expire'   => 60,
    'throttle' => 60,
],
