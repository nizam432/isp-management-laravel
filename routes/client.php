<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\ClientPortalController;
use App\Http\Controllers\Client\OnlinePaymentController;

/*
|--------------------------------------------------------------------------
| Client Portal Routes
|--------------------------------------------------------------------------
| এই ফাইলটি web.php তে include করতে হবে:
|   require __DIR__ . '/client.php';
|
| অথবা RouteServiceProvider এ register করতে হবে।
|--------------------------------------------------------------------------
*/

Route::prefix('client')->name('client.')->group(function () {

    // ── Public: Login / Logout ──────────────────────────────
    Route::get ('login',  [ClientPortalController::class, 'loginForm'])->name('login');
    Route::post('login',  [ClientPortalController::class, 'login']);
    Route::post('logout', [ClientPortalController::class, 'logout'])->name('logout');

    // ── Protected: Customer must be logged in ───────────────
    Route::middleware('client.auth')->group(function () {

        // Dashboard
        Route::get('dashboard', [ClientPortalController::class, 'dashboard'])->name('dashboard');

        // Invoices
        Route::get('invoices',          [ClientPortalController::class, 'invoices'])->name('invoices');
        Route::get('invoices/{invoice}', [ClientPortalController::class, 'invoiceShow'])->name('invoices.show');

        // Support Tickets
        Route::get ('tickets',                    [ClientPortalController::class, 'tickets'])->name('tickets');
        Route::post('tickets',                    [ClientPortalController::class, 'ticketStore'])->name('tickets.store');
        Route::get ('tickets/{ticket}',           [ClientPortalController::class, 'ticketShow'])->name('tickets.show');
        Route::post('tickets/{ticket}/reply',     [ClientPortalController::class, 'ticketReply'])->name('tickets.reply');

        // Live Traffic
        Route::get ('live-traffic',  [ClientPortalController::class, 'liveTraffic'])->name('live-traffic');
        Route::get ('session-data',  [ClientPortalController::class, 'sessionData'])->name('session-data');

        // Packages
        Route::get('packages', [ClientPortalController::class, 'packages'])->name('packages');

        // Profile & Password
        Route::get ('profile',          [ClientPortalController::class, 'profile'])->name('profile');
        Route::post('change-password',  [ClientPortalController::class, 'changePassword'])->name('password.change');

        // ── Online Payment ──────────────────────────────────
        Route::get ('pay/done/{ref}',         [OnlinePaymentController::class, 'successPage'])->name('payment.success-page');
        Route::get ('pay/{invoice}/select',   [OnlinePaymentController::class, 'selectGateway'])->name('payment.select');
        Route::post('pay/{invoice}/initiate', [OnlinePaymentController::class, 'initiate'])    ->name('payment.initiate');

        // ── Invoice PDF ─────────────────────────────────────
        Route::get ('invoices/{invoice}/pdf', [ClientPortalController::class, 'invoicePdf'])->name('invoice.pdf');
    });
});
