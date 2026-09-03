<?php

// ════════════════════════════════════════════════════════════════
// routes/super-admin.php
// Super Admin (Central) Portal — নিজস্ব guard (super_admin), main 'auth'
// middleware group এর সম্পূর্ণ বাইরে, ঠিক reseller.php/client.php এর মতো।
// এই ফাইলটা routes/web.php এর শেষে require করতে হবে।
// ════════════════════════════════════════════════════════════════

use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->name('super-admin.')->group(function () {

    // ── Guest routes (শুধু login) ──────────────────────
    Route::middleware('guest:super_admin')->group(function () {
        Route::get('/login',  [SuperAdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [SuperAdminAuthController::class, 'login'])->name('login.post');
    });

    // ── Authenticated routes ───────────────────────────
    Route::middleware('auth:super_admin')->group(function () {

        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('logout');

        // NOTE: dashboard route already exists (SuperAdmin\TenantController@dashboard,
        // at URI 'super-admin' → name 'super-admin.dashboard'). Removed the
        // placeholder that was here — it was duplicate-naming the real route.
        // Full panel (tenants, permissions, plans, roles, sms/payment gateways)
        // already exists — this file only needed to add the missing login/logout.
    });

});