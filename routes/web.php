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
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\TenantSmsController;
use App\Http\Controllers\MyResellerController;
use App\Http\Controllers\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\SuperAdmin\PlanController as SuperAdminPlanController;
use App\Http\Controllers\SuperAdmin\SmsGatewayController as SuperAdminSmsGatewayController;
use App\Http\Controllers\Settings\ZoneController;
use App\Http\Controllers\Settings\SubZoneController;
use App\Http\Controllers\Settings\ConnectionTypeController;
use App\Http\Controllers\Settings\ClientTypeController;
use App\Http\Controllers\Settings\ProtocolTypeController;
// ─────────────────────────────────────────────
// Public Routes
// ─────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

require __DIR__ . '/auth.php';

// ─────────────────────────────────────────────
// Protected Routes (Login required)
// ─────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // ── Dashboard ──────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Customers ──────────────────────────────
    Route::resource('customers', CustomerController::class);
    Route::patch('customers/{customer}/status', [CustomerController::class, 'updateStatus'])
         ->name('customers.status');

    // ── Packages ───────────────────────────────
Route::get('packages/sync',  [PackageController::class, 'syncPreview'])->name('packages.sync.preview');
Route::post('packages/sync', [PackageController::class, 'syncStore'])->name('packages.sync.store');

Route::resource('packages', PackageController::class);
Route::patch('packages/{package}/toggle', [PackageController::class, 'toggleStatus'])->name('packages.toggle');
    // ── Invoices ───────────────────────────────
    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
         ->name('invoices.pdf');
    Route::post('invoices/bulk-generate', [InvoiceController::class, 'bulkGenerate'])
         ->name('invoices.bulk-generate');

    // ── Payments ───────────────────────────────
    Route::get('payments',              [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments',             [PaymentController::class, 'store'])->name('payments.store');
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
        Route::get('/',                          [MikrotikController::class, 'index'])->name('index');
        Route::post('/',                         [MikrotikController::class, 'store'])->name('store');
        Route::get('active-sessions',            [MikrotikController::class, 'activeSessionsPage'])->name('active-sessions.page'); // ← এটা আগে
        Route::post('kick-by-username',          [MikrotikController::class, 'kickByUsername'])->name('kick-by-username');
        Route::post('bulk-suspend',              [MikrotikController::class, 'bulkSuspend'])->name('bulk.suspend');
        Route::post('sync-all',                  [MikrotikController::class, 'syncAll'])->name('sync.all');
        Route::put('pool/{pool}',                [MikrotikController::class, 'updatePool'])->name('pool.update');
        Route::delete('pool/{pool}',             [MikrotikController::class, 'destroyPool'])->name('pool.destroy');
        Route::put('{mikrotikRouter}',           [MikrotikController::class, 'update'])->name('update');
        Route::delete('{mikrotikRouter}',        [MikrotikController::class, 'destroy'])->name('destroy');
        Route::post('{mikrotikRouter}/pool',     [MikrotikController::class, 'addPool'])->name('pool.store');
        Route::get('{router}/status',            [MikrotikController::class, 'routerStatus'])->name('router.status');
        Route::get('{router}/pppoe-users',       [MikrotikController::class, 'pppoeUsers'])->name('pppoe.users');
        Route::get('{router}/active-sessions',   [MikrotikController::class, 'activeSessions'])->name('active.sessions');
        Route::get('{router}/queues',            [MikrotikController::class, 'queues'])->name('queues');
        Route::get('{router}/profiles',          [MikrotikController::class, 'profiles'])->name('profiles');
    });
    // ── Customer MikroTik ──────────────────────
    Route::prefix('customers/{customer}/mikrotik')->name('customers.mikrotik.')->group(function () {
        Route::get('session',         [MikrotikController::class, 'customerSession'])->name('session');
        Route::post('provision',      [MikrotikController::class, 'provisionCustomer'])->name('provision');
        Route::post('suspend',        [MikrotikController::class, 'suspendCustomer'])->name('suspend');
        Route::post('restore',        [MikrotikController::class, 'restoreCustomer'])->name('restore');
        Route::post('kick',           [MikrotikController::class, 'kickCustomer'])->name('kick');
        Route::post('change-package', [MikrotikController::class, 'changePackage'])->name('change-package');
        Route::delete('/',            [MikrotikController::class, 'removeCustomer'])->name('remove');
    });

    // ── Import ─────────────────────────────────
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/',                 [ImportController::class, 'index'])->name('index');
        Route::any('mikrotik/preview', [ImportController::class, 'mikrotikPreview'])->name('mikrotik.preview');
        Route::post('mikrotik/execute', [ImportController::class, 'mikrotikImport'])->name('mikrotik.execute');
        Route::post('csv/preview',      [ImportController::class, 'csvPreview'])->name('csv.preview');
        Route::post('csv/execute',      [ImportController::class, 'csvImport'])->name('csv.execute');
        Route::get('csv/template',      [ImportController::class, 'downloadTemplate'])->name('csv.template');
        Route::post('mikrotik/single', [ImportController::class, 'mikrotikSingleImport'])->name('mikrotik.single');   
   });

    // ── Inventory ──────────────────────────────
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/',                          [InventoryController::class, 'index'])->name('index');
        Route::post('/',                         [InventoryController::class, 'store'])->name('store');
        Route::put('{inventoryItem}',            [InventoryController::class, 'update'])->name('update');
        Route::delete('{inventoryItem}',         [InventoryController::class, 'destroy'])->name('destroy');
        Route::post('{inventoryItem}/stock-in',  [InventoryController::class, 'stockIn'])->name('stock-in');
        Route::post('{inventoryItem}/stock-out', [InventoryController::class, 'stockOut'])->name('stock-out');
    });

    // ── Reports ────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('revenue',           [ReportController::class, 'revenue'])->name('revenue');
        Route::get('due',               [ReportController::class, 'due'])->name('due');
        Route::get('customers',         [ReportController::class, 'customers'])->name('customers');
        Route::get('export/{type}/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
    });

    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/',                         [SmsController::class, 'index'])->name('index');
        Route::post('gateway/{gateway}/toggle', [SmsController::class, 'toggleGateway'])->name('gateway.toggle');
        Route::post('gateway/{gateway}/config', [SmsController::class, 'updateConfig'])->name('gateway.config');
        Route::post('test',                     [SmsController::class, 'sendTest'])->name('test');
        Route::post('bulk',                     [SmsController::class, 'sendBulk'])->name('bulk');
        Route::delete('logs',                   [SmsController::class, 'clearLogs'])->name('logs.clear');
        Route::get('reports',                   [App\Http\Controllers\SmsReportController::class, 'index'])->name('reports');
        Route::get('reports/details',           [App\Http\Controllers\SmsReportController::class, 'details'])->name('reports.details');
        Route::get('templates',              [App\Http\Controllers\SmsTemplateController::class, 'index'])->name('templates.index');
        Route::post('templates',             [App\Http\Controllers\SmsTemplateController::class, 'store'])->name('templates.store');
        Route::put('templates/{smsTemplate}',[App\Http\Controllers\SmsTemplateController::class, 'update'])->name('templates.update');
        Route::delete('templates/{smsTemplate}',[App\Http\Controllers\SmsTemplateController::class, 'destroy'])->name('templates.destroy');
        Route::post('templates/{smsTemplate}/toggle',[App\Http\Controllers\SmsTemplateController::class, 'toggle'])->name('templates.toggle');
        
    });

    // ── SMS Settings (ISP Admin — tenant gateway config) ──
    Route::prefix('sms/settings')->name('sms.tenant.')->group(function () {
        Route::get('/',               [TenantSmsController::class, 'index'])->name('index');
        Route::post('/{slug}/save',   [TenantSmsController::class, 'save'])->name('save');
        Route::post('/{slug}/toggle', [TenantSmsController::class, 'toggle'])->name('toggle');
    });

    // ── My Resellers (Master Reseller only) ───
    Route::prefix('my-resellers')->name('my-resellers.')->middleware(['can:create-reseller'])->group(function () {
        Route::get('/',             [MyResellerController::class, 'index'])->name('index');
        Route::get('/create',       [MyResellerController::class, 'create'])->name('create');
        Route::post('/',            [MyResellerController::class, 'store'])->name('store');
        Route::get('/{id}/edit',    [MyResellerController::class, 'edit'])->name('edit');
        Route::put('/{id}',         [MyResellerController::class, 'update'])->name('update');
        Route::post('/{id}/toggle', [MyResellerController::class, 'toggle'])->name('toggle');
    });

    // ── Super Admin ────────────────────────────
    Route::prefix('super-admin')->name('super-admin.')->middleware(['superadmin'])->group(function () {

        // Dashboard
        Route::get('/', [SuperAdminTenantController::class, 'dashboard'])->name('dashboard');

        // Tenants (ISP Management)
        Route::prefix('tenants')->name('tenants.')->group(function () {
            Route::get('/',             [SuperAdminTenantController::class, 'index'])->name('index');
            Route::get('/create',       [SuperAdminTenantController::class, 'create'])->name('create');
            Route::post('/',            [SuperAdminTenantController::class, 'store'])->name('store');
            Route::get('/{id}',         [SuperAdminTenantController::class, 'show'])->name('show');
            Route::get('/{id}/edit',    [SuperAdminTenantController::class, 'edit'])->name('edit');
            Route::put('/{id}',         [SuperAdminTenantController::class, 'update'])->name('update');
            Route::post('/{id}/toggle', [SuperAdminTenantController::class, 'toggle'])->name('toggle');
            Route::post('/{id}/plan',   [SuperAdminTenantController::class, 'changePlan'])->name('change-plan');
        });

        // Plans
        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/',               [SuperAdminPlanController::class, 'index'])->name('index');
            Route::post('/',              [SuperAdminPlanController::class, 'store'])->name('store');
            Route::put('/{plan}',         [SuperAdminPlanController::class, 'update'])->name('update');
            Route::post('/{plan}/toggle', [SuperAdminPlanController::class, 'toggle'])->name('toggle');
        });

        // SMS Gateways (enable/disable for ISPs)
        Route::prefix('sms')->name('sms.')->group(function () {
            Route::get('/',                 [SuperAdminSmsGatewayController::class, 'index'])->name('index');
            Route::post('/{gateway}/toggle',[SuperAdminSmsGatewayController::class, 'toggle'])->name('toggle');
        });
        
            
            }); // end super-admin
            
    Route::middleware(['auth', 'can:isp-admin'])->prefix('settings')->name('settings.')->group(function () {

        // ── Zones ──────────────────────────────────────────────
        Route::get   ('zones',         [ZoneController::class, 'index'])   ->name('zones.index');
        Route::get   ('zones/data',    [ZoneController::class, 'data'])    ->name('zones.data');
        Route::post  ('zones',         [ZoneController::class, 'store'])   ->name('zones.store');
        Route::put   ('zones/{zone}',  [ZoneController::class, 'update'])  ->name('zones.update');
        Route::delete('zones/{zone}',  [ZoneController::class, 'destroy']) ->name('zones.destroy');

        // ── Sub Zones ───────────────────────────────────────────
        Route::get   ('sub-zones',           [SubZoneController::class, 'index'])   ->name('sub-zones.index');
        Route::get   ('sub-zones/data',      [SubZoneController::class, 'data'])    ->name('sub-zones.data');
        Route::post  ('sub-zones',           [SubZoneController::class, 'store'])   ->name('sub-zones.store');
        Route::put   ('sub-zones/{subZone}', [SubZoneController::class, 'update'])  ->name('sub-zones.update');
        Route::delete('sub-zones/{subZone}', [SubZoneController::class, 'destroy']) ->name('sub-zones.destroy');

        // ── Connection Types ──────────────────────────────────── ← এগুলো যোগ করুন
        Route::get   ('connection-types',                        [ConnectionTypeController::class, 'index'])   ->name('connection-types.index');
        Route::get   ('connection-types/data',                   [ConnectionTypeController::class, 'data'])    ->name('connection-types.data');
        Route::post  ('connection-types',                        [ConnectionTypeController::class, 'store'])   ->name('connection-types.store');
        Route::put   ('connection-types/{connectionType}',       [ConnectionTypeController::class, 'update'])  ->name('connection-types.update');
        Route::post  ('connection-types/{connectionType}/toggle',[ConnectionTypeController::class, 'toggle'])  ->name('connection-types.toggle');
        Route::delete('connection-types/{connectionType}',       [ConnectionTypeController::class, 'destroy']) ->name('connection-types.destroy');

        // ── Client Types ──────────────────────────────────────── ← এগুলো যোগ করুন
        Route::get   ('client-types',               [ClientTypeController::class, 'index'])   ->name('client-types.index');
        Route::get   ('client-types/data',          [ClientTypeController::class, 'data'])    ->name('client-types.data');
        Route::post  ('client-types',               [ClientTypeController::class, 'store'])   ->name('client-types.store');
        Route::put   ('client-types/{clientType}',  [ClientTypeController::class, 'update'])  ->name('client-types.update');
        Route::post  ('client-types/{clientType}/toggle', [ClientTypeController::class, 'toggle'])  ->name('client-types.toggle');
        Route::delete('client-types/{clientType}',  [ClientTypeController::class, 'destroy']) ->name('client-types.destroy');
        
        // ── protocol Types ──────────────────────────────────────── ← এগুলো যোগ করুন
        Route::get   ('protocol-types',                        [ProtocolTypeController::class, 'index'])   ->name('protocol-types.index');
        Route::get   ('protocol-types/data',                   [ProtocolTypeController::class, 'data'])    ->name('protocol-types.data');
        Route::post  ('protocol-types',                        [ProtocolTypeController::class, 'store'])   ->name('protocol-types.store');
        Route::put   ('protocol-types/{protocolType}',         [ProtocolTypeController::class, 'update'])  ->name('protocol-types.update');
        Route::post  ('protocol-types/{protocolType}/toggle',  [ProtocolTypeController::class, 'toggle'])  ->name('protocol-types.toggle');
        Route::delete('protocol-types/{protocolType}',         [ProtocolTypeController::class, 'destroy']) ->name('protocol-types.destroy');
    });

    

}); // end auth