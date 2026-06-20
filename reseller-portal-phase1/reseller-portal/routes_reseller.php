<?php

// ════════════════════════════════════════════════════════════════
// RESELLER PORTAL ROUTES
// web.php এর একদম শেষে (auth middleware group এর বাইরে) যোগ করুন
// কারণ এটার নিজস্ব আলাদা guard আছে
// ════════════════════════════════════════════════════════════════

use App\Http\Controllers\Reseller\ResellerAuthController;
use App\Http\Controllers\Reseller\ResellerDashboardController;
use App\Http\Controllers\Reseller\ResellerPlaceholderController;

Route::prefix('reseller')->name('reseller.')->group(function () {

    // ── Guest routes (login) ──────────────────────────
    Route::middleware('guest:mac_reseller')->group(function () {
        Route::get('/login',  [ResellerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [ResellerAuthController::class, 'login'])->name('login.submit');
    });

    // ── Authenticated routes ──────────────────────────
    Route::middleware(['auth:mac_reseller', 'reseller.active'])->group(function () {

        Route::post('/logout', [ResellerAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [ResellerDashboardController::class, 'index'])->name('dashboard');

        // ── প্রতিটা menu — admin এর checkbox (allowed_menus) অনুযায়ী access ──
        Route::middleware('reseller.menu:CONFIGURATION')->group(function () {
            Route::get('/configuration', [ResellerPlaceholderController::class, 'show'])->name('configuration')->defaults('menu', 'configuration');
        });

        Route::middleware('reseller.menu:MIKROTIK CLIENT')->group(function () {
            Route::get('/mikrotik-client', [ResellerPlaceholderController::class, 'show'])->name('mikrotik-client')->defaults('menu', 'mikrotik-client');
        });

        Route::middleware('reseller.menu:EMPLOYEES')->group(function () {
            Route::get('/employees', [ResellerPlaceholderController::class, 'show'])->name('employees')->defaults('menu', 'employees');
        });

        Route::middleware('reseller.menu:CLIENT')->group(function () {
            Route::get('/client', [ResellerPlaceholderController::class, 'show'])->name('client')->defaults('menu', 'client');
        });

        Route::middleware('reseller.menu:BILLING')->group(function () {
            Route::get('/billing', [ResellerPlaceholderController::class, 'show'])->name('billing')->defaults('menu', 'billing');
        });

        Route::middleware('reseller.menu:MONITORING')->group(function () {
            Route::get('/monitoring', [ResellerPlaceholderController::class, 'show'])->name('monitoring')->defaults('menu', 'monitoring');
        });

        Route::middleware('reseller.menu:CLIENT SUPPORT')->group(function () {
            Route::get('/client-support', [ResellerPlaceholderController::class, 'show'])->name('client-support')->defaults('menu', 'client-support');
        });

        Route::middleware('reseller.menu:SMS SERVICE')->group(function () {
            Route::get('/sms-service', [ResellerPlaceholderController::class, 'show'])->name('sms-service')->defaults('menu', 'sms-service');
        });

        Route::middleware('reseller.menu:REPORT')->group(function () {
            Route::get('/report', [ResellerPlaceholderController::class, 'show'])->name('report')->defaults('menu', 'report');
        });

        Route::middleware('reseller.menu:FUND HISTORY')->group(function () {
            Route::get('/fund-history', [ResellerPlaceholderController::class, 'show'])->name('fund-history')->defaults('menu', 'fund-history');
        });

        Route::middleware('reseller.menu:TUTORIALS')->group(function () {
            Route::get('/tutorials', [ResellerPlaceholderController::class, 'show'])->name('tutorials')->defaults('menu', 'tutorials');
        });

    });

});
