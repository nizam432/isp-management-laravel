<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\ClientPortalController;

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
        Route::get ('tickets',       [ClientPortalController::class, 'tickets'])->name('tickets');
        Route::post('tickets',       [ClientPortalController::class, 'ticketStore'])->name('tickets.store');

        // Profile & Password
        Route::get ('profile',          [ClientPortalController::class, 'profile'])->name('profile');
        Route::post('change-password',  [ClientPortalController::class, 'changePassword'])->name('password.change');
    });
});
