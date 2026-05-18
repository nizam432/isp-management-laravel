<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;

// ─────────────────────────────────────────────
// Public Routes (Login page)
// ─────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// Auth Routes (Laravel Breeze)
require __DIR__ . '/auth.php';

// ─────────────────────────────────────────────
// Protected Routes (Login required)
// ─────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Customers ──────────────────────────────
    Route::resource('customers', CustomerController::class);
    Route::patch('customers/{customer}/status', [CustomerController::class, 'updateStatus'])
         ->name('customers.status');

    // ── Packages ───────────────────────────────
    Route::resource('packages', PackageController::class);
    Route::patch('packages/{package}/toggle', [PackageController::class, 'toggleStatus'])
         ->name('packages.toggle');

    // ── Invoices ───────────────────────────────
    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
         ->name('invoices.pdf');
    Route::post('invoices/bulk-generate', [InvoiceController::class, 'bulkGenerate'])
         ->name('invoices.bulk-generate');

    // ── Payments ───────────────────────────────
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // ── Tickets ────────────────────────────────
    Route::resource('tickets', TicketController::class)->except(['edit']);
    Route::post('tickets/{ticket}/reply', [TicketController::class, 'reply'])
         ->name('tickets.reply');

    // ── Agents ─────────────────────────────────
    Route::resource('agents', AgentController::class)->except(['edit']);
    Route::post('agents/{agent}/pay-commission', [AgentController::class, 'payCommission'])
         ->name('agents.pay-commission');

    // ── MikroTik ───────────────────────────────
    Route::prefix('mikrotik')->name('mikrotik.')->group(function () {

        // CRUD (পুরনো — blade এ ব্যবহার হচ্ছে)
        Route::get('/',                     [MikrotikController::class, 'index'])->name('index');
        Route::post('/',                    [MikrotikController::class, 'store'])->name('store');
        Route::put('{mikrotikRouter}',      [MikrotikController::class, 'update'])->name('update');
        Route::delete('{mikrotikRouter}',   [MikrotikController::class, 'destroy'])->name('destroy');
        Route::post('{mikrotikRouter}/pool',[MikrotikController::class, 'addPool'])->name('pool.store');

        // Bulk operations
        Route::post('bulk-suspend',         [MikrotikController::class, 'bulkSuspend'])->name('bulk.suspend');
        Route::post('sync-all',             [MikrotikController::class, 'syncAll'])->name('sync.all');

        // Router-level AJAX endpoints
        Route::get('{router}/status',          [MikrotikController::class, 'routerStatus'])->name('router.status');
        Route::get('{router}/pppoe-users',     [MikrotikController::class, 'pppoeUsers'])->name('pppoe.users');
        Route::get('{router}/active-sessions', [MikrotikController::class, 'activeSessions'])->name('active.sessions');
        Route::get('{router}/queues',          [MikrotikController::class, 'queues'])->name('queues');
        Route::get('{router}/profiles',        [MikrotikController::class, 'profiles'])->name('profiles');
    });

    // Customer-level MikroTik operations
    Route::prefix('customers/{customer}/mikrotik')->name('customers.mikrotik.')->group(function () {
        Route::get('session',         [MikrotikController::class, 'customerSession'])->name('session');
        Route::post('provision',      [MikrotikController::class, 'provisionCustomer'])->name('provision');
        Route::post('suspend',        [MikrotikController::class, 'suspendCustomer'])->name('suspend');
        Route::post('restore',        [MikrotikController::class, 'restoreCustomer'])->name('restore');
        Route::post('kick',           [MikrotikController::class, 'kickCustomer'])->name('kick');
        Route::post('change-package', [MikrotikController::class, 'changePackage'])->name('change-package');
        Route::delete('/',            [MikrotikController::class, 'removeCustomer'])->name('remove');
    });

    // ── Inventory ──────────────────────────────
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/',                              [InventoryController::class, 'index'])->name('index');
        Route::post('/',                             [InventoryController::class, 'store'])->name('store');
        Route::put('{inventoryItem}',                [InventoryController::class, 'update'])->name('update');
        Route::delete('{inventoryItem}',             [InventoryController::class, 'destroy'])->name('destroy');
        Route::post('{inventoryItem}/stock-in',      [InventoryController::class, 'stockIn'])->name('stock-in');
        Route::post('{inventoryItem}/stock-out',     [InventoryController::class, 'stockOut'])->name('stock-out');
    });

    // ── Reports ────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('revenue',           [ReportController::class, 'revenue'])->name('revenue');
        Route::get('due',               [ReportController::class, 'due'])->name('due');
        Route::get('customers',         [ReportController::class, 'customers'])->name('customers');
        Route::get('export/{type}/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
    });

});