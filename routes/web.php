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
use App\Http\Controllers\OltController;
use App\Http\Controllers\Settings\OltTypeController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\DepartmentController;
use App\Http\Controllers\HR\PositionController;
use App\Http\Controllers\HR\SalaryHeadController;
use App\Http\Controllers\HR\PayrollController;
use App\Http\Controllers\HR\LeaveController;
use App\Http\Controllers\HR\SalaryAdvanceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\AccountingController;

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
    // ⚠️ Static/AJAX routes MUST come before resource() — otherwise {customer} catches them
    Route::get ('customers/sub-zones',                    [CustomerController::class, 'getSubZones'])           ->name('customers.sub-zones');
    Route::get ('customers/package-price',                [CustomerController::class, 'getPackagePrice'])        ->name('customers.package-price');
    Route::post('customers/quick-add/zone',               [CustomerController::class, 'quickAddZone'])           ->name('customers.quick-add.zone');
    Route::post('customers/quick-add/connection-type',    [CustomerController::class, 'quickAddConnectionType']) ->name('customers.quick-add.connection-type');
    Route::post('customers/quick-add/client-type',        [CustomerController::class, 'quickAddClientType'])     ->name('customers.quick-add.client-type');
    Route::post('customers/quick-add/protocol-type',      [CustomerController::class, 'quickAddProtocolType'])   ->name('customers.quick-add.protocol-type');

    Route::resource('customers', CustomerController::class);
    Route::patch('customers/{customer}/status', [CustomerController::class, 'updateStatus'])->name('customers.status');
    Route::get('customers/{customer}/mikrotik-info', [CustomerController::class, 'mikrotikInfo'])->name('customers.mikrotik-info');

    // ── Packages ───────────────────────────────
    Route::get('packages/sync',  [PackageController::class, 'syncPreview'])->name('packages.sync.preview');
    Route::post('packages/sync', [PackageController::class, 'syncStore'])->name('packages.sync.store');
    Route::resource('packages', PackageController::class);
    Route::patch('packages/{package}/toggle', [PackageController::class, 'toggleStatus'])->name('packages.toggle');

    // ── Invoices ───────────────────────────────
    // Bulk routes must be before resource routes
    Route::post('invoices/bulk-generate',    [InvoiceController::class, 'bulkGenerate'])->name('invoices.bulk-generate');
    Route::post('invoices/bulk-delete',      [InvoiceController::class, 'bulkDelete'])->name('invoices.bulk-delete');
    Route::get('invoices/bulk-xlsx',         [InvoiceController::class, 'bulkXlsx'])->name('invoices.bulk-xlsx');
    Route::get('invoices/bulk-pdf',          [InvoiceController::class, 'bulkPdf'])->name('invoices.bulk-pdf');
    Route::post('invoices/bulk-sms',         [InvoiceController::class, 'bulkSms'])->name('invoices.bulk-sms');

    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
    Route::get('invoices/{invoice}/pdf',     [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('invoices/{invoice}/receipt', [InvoiceController::class, 'receipt'])->name('invoices.receipt');

    // ── Payments ───────────────────────────────
    Route::get('payments',                          [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/invoice/{invoice}',       [PaymentController::class, 'payInvoice'])->name('payments.pay-invoice');
    Route::get('payments/collect',                  [PaymentController::class, 'collectPage'])->name('payments.collect');
    Route::post('payments/collect',                 [PaymentController::class, 'collectStore'])->name('payments.collect-store');
    Route::get('payments/customer-due/{customer}',  [PaymentController::class, 'customerDue'])->name('payments.customer-due');
    Route::post('payments/{payment}/void',          [PaymentController::class, 'void'])->name('payments.void');

    // ── Tickets ────────────────────────────────
    Route::resource('tickets', TicketController::class)->except(['edit']);
    Route::post('tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');

    // ── Agents ─────────────────────────────────
    Route::resource('agents', AgentController::class)->except(['edit']);
    Route::post('agents/{agent}/pay-commission', [AgentController::class, 'payCommission'])->name('agents.pay-commission');

    // ── MikroTik ───────────────────────────────
    Route::prefix('mikrotik')->name('mikrotik.')->group(function () {
        Route::get('/',                        [MikrotikController::class, 'index'])->name('index');
        Route::post('/',                       [MikrotikController::class, 'store'])->name('store');
        Route::get('active-sessions',          [MikrotikController::class, 'activeSessionsPage'])->name('active-sessions.page');
        Route::post('kick-by-username',        [MikrotikController::class, 'kickByUsername'])->name('kick-by-username');
        Route::post('bulk-suspend',            [MikrotikController::class, 'bulkSuspend'])->name('bulk.suspend');
        Route::post('sync-all',                [MikrotikController::class, 'syncAll'])->name('sync.all');
        Route::put('pool/{pool}',              [MikrotikController::class, 'updatePool'])->name('pool.update');
        Route::delete('pool/{pool}',           [MikrotikController::class, 'destroyPool'])->name('pool.destroy');
        Route::put('{mikrotikRouter}',         [MikrotikController::class, 'update'])->name('update');
        Route::delete('{mikrotikRouter}',      [MikrotikController::class, 'destroy'])->name('destroy');
        Route::post('{mikrotikRouter}/pool',   [MikrotikController::class, 'addPool'])->name('pool.store');
        Route::get('{router}/status',          [MikrotikController::class, 'routerStatus'])->name('router.status');
        Route::get('{router}/pppoe-users',     [MikrotikController::class, 'pppoeUsers'])->name('pppoe.users');
        Route::get('{router}/active-sessions', [MikrotikController::class, 'activeSessions'])->name('active.sessions');
        Route::get('{router}/queues',          [MikrotikController::class, 'queues'])->name('queues');
        Route::get('{router}/profiles',        [MikrotikController::class, 'profiles'])->name('profiles');
    });

    // ── OLT Management ─────────────────────────────────────────────
    Route::prefix('olt')->name('olt.')->group(function () {
        // static routes আগে
        Route::get ('users',         [OltController::class, 'users'])     ->name('users');
        Route::get ('users/data',    [OltController::class, 'usersData']) ->name('users.data');
        Route::post('sync-all',      [OltController::class, 'syncAll'])   ->name('sync-all');
        // CRUD
        Route::get   ('/',           [OltController::class, 'index'])     ->name('index');
        Route::post  ('/',           [OltController::class, 'store'])     ->name('store');
        Route::get   ('/{olt}',      [OltController::class, 'show'])      ->name('show');
        Route::put   ('/{olt}',      [OltController::class, 'update'])    ->name('update');
        Route::delete('/{olt}',      [OltController::class, 'destroy'])   ->name('destroy');
        Route::post  ('/{olt}/sync', [OltController::class, 'sync'])      ->name('sync');
    });
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
        Route::any('mikrotik/preview',  [ImportController::class, 'mikrotikPreview'])->name('mikrotik.preview');
        Route::post('mikrotik/execute', [ImportController::class, 'mikrotikImport'])->name('mikrotik.execute');
        Route::post('csv/preview',      [ImportController::class, 'csvPreview'])->name('csv.preview');
        Route::post('csv/execute',      [ImportController::class, 'csvImport'])->name('csv.execute');
        Route::get('csv/template',      [ImportController::class, 'downloadTemplate'])->name('csv.template');
        Route::post('mikrotik/single',  [ImportController::class, 'mikrotikSingleImport'])->name('mikrotik.single');
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

    // ── SMS ────────────────────────────────────
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/',                          [SmsController::class, 'index'])->name('index');
        Route::post('gateway/{gateway}/toggle',  [SmsController::class, 'toggleGateway'])->name('gateway.toggle');
        Route::post('gateway/{gateway}/config',  [SmsController::class, 'updateConfig'])->name('gateway.config');
        Route::post('test',                      [SmsController::class, 'sendTest'])->name('test');
        Route::post('bulk',                      [SmsController::class, 'sendBulk'])->name('bulk');
        Route::delete('logs',                    [SmsController::class, 'clearLogs'])->name('logs.clear');
        Route::get('reports',                    [App\Http\Controllers\SmsReportController::class, 'index'])->name('reports');
        Route::get('reports/details',            [App\Http\Controllers\SmsReportController::class, 'details'])->name('reports.details');
        Route::get('templates',                  [App\Http\Controllers\SmsTemplateController::class, 'index'])->name('templates.index');
        Route::post('templates',                 [App\Http\Controllers\SmsTemplateController::class, 'store'])->name('templates.store');
        Route::put('templates/{smsTemplate}',    [App\Http\Controllers\SmsTemplateController::class, 'update'])->name('templates.update');
        Route::delete('templates/{smsTemplate}', [App\Http\Controllers\SmsTemplateController::class, 'destroy'])->name('templates.destroy');
        Route::post('templates/{smsTemplate}/toggle', [App\Http\Controllers\SmsTemplateController::class, 'toggle'])->name('templates.toggle');
    });

    // ── SMS Settings (ISP Admin — tenant gateway config) ──
    Route::prefix('sms/settings')->name('sms.tenant.')->group(function () {
        Route::get('/',               [TenantSmsController::class, 'index'])->name('index');
        Route::post('/{slug}/save',   [TenantSmsController::class, 'save'])->name('save');
        Route::post('/{slug}/toggle', [TenantSmsController::class, 'toggle'])->name('toggle');
    });

    // ── My Resellers (Master Reseller only) ────
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

        Route::get('/', [SuperAdminTenantController::class, 'dashboard'])->name('dashboard');

        // Tenants
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

        // SMS Gateways
        Route::prefix('sms')->name('sms.')->group(function () {
            Route::get('/',                  [SuperAdminSmsGatewayController::class, 'index'])->name('index');
            Route::post('/{gateway}/toggle', [SuperAdminSmsGatewayController::class, 'toggle'])->name('toggle');
        });

    }); // end super-admin

    // ── Settings ───────────────────────────────
    Route::middleware(['auth', 'can:isp-admin'])->prefix('settings')->name('settings.')->group(function () {

        Route::get('general', [App\Http\Controllers\Settings\SettingController::class, 'index'])->name('general');
        Route::put('general', [App\Http\Controllers\Settings\SettingController::class, 'update'])->name('update');

        // Zones
        Route::get   ('zones',        [ZoneController::class, 'index'])  ->name('zones.index');
        Route::get   ('zones/data',   [ZoneController::class, 'data'])   ->name('zones.data');
        Route::post  ('zones',        [ZoneController::class, 'store'])  ->name('zones.store');
        Route::put   ('zones/{zone}', [ZoneController::class, 'update']) ->name('zones.update');
        Route::delete('zones/{zone}', [ZoneController::class, 'destroy'])->name('zones.destroy');

        // Sub Zones
        Route::get   ('sub-zones',           [SubZoneController::class, 'index'])  ->name('sub-zones.index');
        Route::get   ('sub-zones/data',      [SubZoneController::class, 'data'])   ->name('sub-zones.data');
        Route::post  ('sub-zones',           [SubZoneController::class, 'store'])  ->name('sub-zones.store');
        Route::put   ('sub-zones/{subZone}', [SubZoneController::class, 'update']) ->name('sub-zones.update');
        Route::delete('sub-zones/{subZone}', [SubZoneController::class, 'destroy'])->name('sub-zones.destroy');

        // Connection Types
        Route::get   ('connection-types',                         [ConnectionTypeController::class, 'index'])  ->name('connection-types.index');
        Route::get   ('connection-types/data',                    [ConnectionTypeController::class, 'data'])   ->name('connection-types.data');
        Route::post  ('connection-types',                         [ConnectionTypeController::class, 'store'])  ->name('connection-types.store');
        Route::put   ('connection-types/{connectionType}',        [ConnectionTypeController::class, 'update']) ->name('connection-types.update');
        Route::post  ('connection-types/{connectionType}/toggle', [ConnectionTypeController::class, 'toggle']) ->name('connection-types.toggle');
        Route::delete('connection-types/{connectionType}',        [ConnectionTypeController::class, 'destroy'])->name('connection-types.destroy');

        // Client Types
        Route::get   ('client-types',                    [ClientTypeController::class, 'index'])  ->name('client-types.index');
        Route::get   ('client-types/data',               [ClientTypeController::class, 'data'])   ->name('client-types.data');
        Route::post  ('client-types',                    [ClientTypeController::class, 'store'])  ->name('client-types.store');
        Route::put   ('client-types/{clientType}',       [ClientTypeController::class, 'update']) ->name('client-types.update');
        Route::post  ('client-types/{clientType}/toggle',[ClientTypeController::class, 'toggle']) ->name('client-types.toggle');
        Route::delete('client-types/{clientType}',       [ClientTypeController::class, 'destroy'])->name('client-types.destroy');

        // Protocol Types
        Route::get   ('protocol-types',                        [ProtocolTypeController::class, 'index'])  ->name('protocol-types.index');
        Route::get   ('protocol-types/data',                   [ProtocolTypeController::class, 'data'])   ->name('protocol-types.data');
        Route::post  ('protocol-types',                        [ProtocolTypeController::class, 'store'])  ->name('protocol-types.store');
        Route::put   ('protocol-types/{protocolType}',         [ProtocolTypeController::class, 'update']) ->name('protocol-types.update');
        Route::post  ('protocol-types/{protocolType}/toggle',  [ProtocolTypeController::class, 'toggle']) ->name('protocol-types.toggle');
        Route::delete('protocol-types/{protocolType}',         [ProtocolTypeController::class, 'destroy'])->name('protocol-types.destroy');

        // OLT Types
        Route::get   ('olt-types',                  [OltTypeController::class, 'index'])  ->name('olt-types.index');
        Route::get   ('olt-types/data',             [OltTypeController::class, 'data'])   ->name('olt-types.data');
        Route::post  ('olt-types',                  [OltTypeController::class, 'store'])  ->name('olt-types.store');
        Route::put   ('olt-types/{oltType}',        [OltTypeController::class, 'update']) ->name('olt-types.update');
        Route::post  ('olt-types/{oltType}/toggle', [OltTypeController::class, 'toggle']) ->name('olt-types.toggle');
        Route::delete('olt-types/{oltType}',        [OltTypeController::class, 'destroy'])->name('olt-types.destroy');

    }); // end settings

    // ── HR ─────────────────────────────────────
    Route::resource('employees', EmployeeController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('positions', PositionController::class);
    Route::resource('salary-heads', SalaryHeadController::class);

    Route::delete('employees/documents/{document}', [EmployeeController::class, 'destroyDocument'])->name('employees.documents.destroy');
    Route::get('departments/{department}/positions', [EmployeeController::class, 'getPositions'])->name('departments.positions');
    Route::post('employees/{employee}/resign-terminate', [EmployeeController::class, 'resignTerminate'])->name('employees.resign-terminate');

    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/',                  [PayrollController::class, 'index'])->name('index');
        Route::get('/generate',          [PayrollController::class, 'generate'])->name('generate');
        Route::post('/',                 [PayrollController::class, 'store'])->name('store');
        Route::get('/{payroll}',         [PayrollController::class, 'show'])->name('show');
        Route::post('/{payroll}/pay',    [PayrollController::class, 'pay'])->name('pay');
        Route::get('/{payroll}/payslip', [PayrollController::class, 'payslip'])->name('payslip');
    });

    Route::prefix('leave')->name('leave.')->group(function () {
        Route::get('/',                 [LeaveController::class, 'index'])->name('index');
        Route::get('/create',           [LeaveController::class, 'create'])->name('create');
        Route::post('/',                [LeaveController::class, 'store'])->name('store');
        Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
        Route::post('/{leave}/reject',  [LeaveController::class, 'reject'])->name('reject');
        Route::get('/types',            [LeaveController::class, 'types'])->name('types');
        Route::post('/types',           [LeaveController::class, 'storeType'])->name('types.store');
        Route::put('/types/{type}',     [LeaveController::class, 'updateType'])->name('types.update');
        Route::delete('/types/{type}',  [LeaveController::class, 'destroyType'])->name('types.destroy');
    });

    Route::prefix('salary-advance')->name('salary-advance.')->group(function () {
        Route::get('/',                  [SalaryAdvanceController::class, 'index'])->name('index');
        Route::post('/',                 [SalaryAdvanceController::class, 'store'])->name('store');
        Route::post('/{advance}/deduct', [SalaryAdvanceController::class, 'deduct'])->name('deduct');
    });

    // ── Financial Module ───────────────────────
    Route::prefix('expenses')->name('expenses.')->group(function () {

        // P&L Report — static routes BEFORE resource-style {expense} routes
        Route::get('reports/profit-loss',     [ExpenseController::class, 'profitLoss'])    ->name('profit-loss');
        Route::get('reports/profit-loss/pdf', [ExpenseController::class, 'profitLossPdf']) ->name('profit-loss.pdf');
        Route::get('api/chart-data',          [ExpenseController::class, 'chartData'])     ->name('chart-data');

        // CRUD
        Route::get('/',               [ExpenseController::class, 'index'])  ->name('index');
        Route::get('/create',         [ExpenseController::class, 'create']) ->name('create');
        Route::post('/',              [ExpenseController::class, 'store'])  ->name('store');
        Route::get('/{expense}',      [ExpenseController::class, 'show'])   ->name('show');
        Route::get('/{expense}/edit-data', [ExpenseController::class, 'editData']) ->name('edit-data');
        Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])   ->name('edit');
        Route::put('/{expense}',      [ExpenseController::class, 'update']) ->name('update');

        // Void & hard delete
        Route::post('/{expense}/void', [ExpenseController::class, 'void'])    ->name('void');
        Route::delete('/{expense}',    [ExpenseController::class, 'destroy']) ->name('destroy');
    });

    Route::prefix('expense-categories')->name('expense-categories.')->group(function () {
        Route::get('/',                    [ExpenseController::class, 'categoriesIndex'])  ->name('index');
        Route::post('/',                   [ExpenseController::class, 'categoryStore'])    ->name('store');
        Route::post('/quick-add',          [ExpenseController::class, 'quickAddCategory']) ->name('quick-add');
        Route::put('/{expenseCategory}',   [ExpenseController::class, 'categoryUpdate'])   ->name('update');
        Route::delete('/{expenseCategory}',[ExpenseController::class, 'categoryDestroy'])  ->name('destroy');
    });

    // ── Income Module ────────────────────────
    Route::prefix('incomes')->name('incomes.')->group(function () {

        // Static routes BEFORE {income} to avoid route collision
        Route::get('/',                   [IncomeController::class, 'index'])           ->name('index');
        Route::post('/',                  [IncomeController::class, 'store'])           ->name('store');
        Route::get('/{income}/edit-data', [IncomeController::class, 'editData'])        ->name('edit-data');
        Route::get('/{income}',           [IncomeController::class, 'show'])            ->name('show');
        Route::put('/{income}',           [IncomeController::class, 'update'])          ->name('update');
        Route::post('/{income}/void',     [IncomeController::class, 'void'])            ->name('void');
        Route::delete('/{income}',        [IncomeController::class, 'destroy'])         ->name('destroy');
    });

    // ── Accounting Quick Add Categories ────────────
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::post('income-categories/quick-add',  [IncomeController::class,  'quickAddCategory']) ->name('income-categories.quick-add');
        Route::post('expense-categories/quick-add', [ExpenseController::class, 'quickAddCategory']) ->name('expense-categories.quick-add');
    });

    // ── Accounting Dashboard ───────────────────────
    Route::get('accounting/dashboard', [AccountingController::class, 'dashboard'])->name('accounting.dashboard');

    // ── Income Categories ──────────────────────────
    Route::prefix('income-categories')->name('income-categories.')->group(function () {
        Route::get('/',                     [IncomeController::class, 'categoriesIndex'])  ->name('index');
        Route::post('/',                    [IncomeController::class, 'categoryStore'])    ->name('store');
        Route::put('/{incomeCategory}',     [IncomeController::class, 'categoryUpdate'])   ->name('update');
        Route::delete('/{incomeCategory}',  [IncomeController::class, 'categoryDestroy'])  ->name('destroy');
    });

}); // end auth