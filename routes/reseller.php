<?php

// ════════════════════════════════════════════════════════════════
// routes/reseller.php
// MAC Reseller Portal — নিজস্ব guard (mac_reseller), main 'auth'
// middleware group এর সম্পূর্ণ বাইরে, ঠিক client.php এর মতো।
// এই ফাইলটা routes/web.php এর শেষে require করা হয়েছে।
// ════════════════════════════════════════════════════════════════

use App\Http\Controllers\Reseller\ResellerAuthController;
use App\Http\Controllers\Reseller\ResellerDashboardController;
use App\Http\Controllers\Reseller\ResellerPlaceholderController;
use App\Http\Controllers\Reseller\ResellerClientController;
use App\Http\Controllers\Reseller\ResellerBillingController;
use App\Http\Controllers\Reseller\ResellerFundHistoryController;
use App\Http\Controllers\Reseller\ResellerConfigurationController;
use App\Http\Controllers\Reseller\ResellerEmployeeController;
use App\Http\Controllers\Reseller\ResellerMikrotikClientController;
use App\Http\Controllers\Reseller\ResellerMonitoringController;
use App\Http\Controllers\Reseller\ResellerSupportController;
use App\Http\Controllers\Reseller\ResellerSmsController;
use App\Http\Controllers\Reseller\ResellerReportController;
use App\Http\Controllers\Reseller\ResellerTutorialController;
use Illuminate\Support\Facades\Route;

Route::prefix('reseller')->name('reseller.')->group(function () {

    // ── Guest routes (শুধু login) ──────────────────────
    Route::middleware('guest:mac_reseller')->group(function () {
        Route::get('/login',  [ResellerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [ResellerAuthController::class, 'login'])->name('login.submit');
    });

    // ── Authenticated routes ───────────────────────────
    Route::middleware(['auth:mac_reseller', 'reseller.active'])->group(function () {

        Route::post('/logout', [ResellerAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [ResellerDashboardController::class, 'index'])->name('dashboard');

        // ── প্রতিটা menu — admin এর checkbox (allowed_menus) অনুযায়ী access ──
        Route::middleware('reseller.menu:CONFIGURATION')->prefix('configuration')->name('configuration.')->group(function () {
            Route::get('/',          [ResellerConfigurationController::class, 'index'])->name('index');
            Route::put('/',          [ResellerConfigurationController::class, 'update'])->name('update');
            Route::put('/password',  [ResellerConfigurationController::class, 'updatePassword'])->name('password');
        });

        Route::middleware('reseller.menu:MIKROTIK CLIENT')->prefix('mikrotik-client')->name('mikrotik-client.')->group(function () {
            Route::get('/',                    [ResellerMikrotikClientController::class, 'index'])     ->name('index');
            Route::post('/{client}/disconnect', [ResellerMikrotikClientController::class, 'disconnect'])->name('disconnect');
        });

        Route::middleware('reseller.menu:EMPLOYEES')->prefix('employees')->name('employees.')->group(function () {
            Route::get('/',            [ResellerEmployeeController::class, 'index'])  ->name('index');
            Route::post('/',           [ResellerEmployeeController::class, 'store'])  ->name('store');
            Route::get('/{employee}',  [ResellerEmployeeController::class, 'edit'])   ->name('edit');
            Route::put('/{employee}',  [ResellerEmployeeController::class, 'update']) ->name('update');
            Route::delete('/{employee}', [ResellerEmployeeController::class, 'destroy'])->name('destroy');
            Route::post('/{employee}/toggle', [ResellerEmployeeController::class, 'toggle'])->name('toggle');
        });

        // ── CLIENT menu — এখন real controller ব্যবহার করছে ──
        Route::middleware('reseller.menu:CLIENT')->prefix('client')->name('client.')->group(function () {
            Route::get('/',         [ResellerClientController::class, 'index'])->name('index');
            Route::get('/{client}', [ResellerClientController::class, 'show'])->name('show');
        });

        Route::middleware('reseller.menu:BILLING')->prefix('billing')->name('billing.')->group(function () {
            Route::get('/',          [ResellerBillingController::class, 'index'])->name('index');
            Route::get('/{invoice}', [ResellerBillingController::class, 'show'])->name('show');
        });

        Route::middleware('reseller.menu:MONITORING')->prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/', [ResellerMonitoringController::class, 'index'])->name('index');
        });

        Route::middleware('reseller.menu:CLIENT SUPPORT')->prefix('client-support')->name('client-support.')->group(function () {
            Route::get('/',               [ResellerSupportController::class, 'index']) ->name('index');
            Route::get('/{ticket}',       [ResellerSupportController::class, 'show'])  ->name('show');
            Route::post('/{ticket}/reply',[ResellerSupportController::class, 'reply']) ->name('reply');
            Route::post('/{ticket}/close',[ResellerSupportController::class, 'close']) ->name('close');
        });

        Route::middleware('reseller.menu:SMS SERVICE')->prefix('sms-service')->name('sms-service.')->group(function () {
            Route::get('/',     [ResellerSmsController::class, 'index'])->name('index');
            Route::post('/send',[ResellerSmsController::class, 'send']) ->name('send');
        });

        Route::middleware('reseller.menu:REPORT')->prefix('report')->name('report.')->group(function () {
            Route::get('/', [ResellerReportController::class, 'index'])->name('index');
        });

        Route::middleware('reseller.menu:FUND HISTORY')->prefix('fund-history')->name('fund-history.')->group(function () {
            Route::get('/', [ResellerFundHistoryController::class, 'index'])->name('index');
        });

        Route::middleware('reseller.menu:TUTORIALS')->prefix('tutorials')->name('tutorials.')->group(function () {
            Route::get('/', [ResellerTutorialController::class, 'index'])->name('index');
        });

    });

});
