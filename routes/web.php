<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Reports\BillCollectionReportController;
use App\Http\Controllers\Reports\IncomeExpenseReportController;
use App\Http\Controllers\Reports\CustomerReportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\TenantSmsController;
use App\Http\Controllers\MyResellerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\SuperAdmin\PlanController as SuperAdminPlanController;
use App\Http\Controllers\SuperAdmin\SmsGatewayController as SuperAdminSmsGatewayController;
use App\Http\Controllers\SuperAdmin\PermissionController as SuperAdminPermissionController;
use App\Http\Controllers\SuperAdmin\RoleController as SuperAdminRoleController;
use App\Http\Controllers\RoleController;
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
use App\Http\Controllers\SupportCategoryController;
use App\Http\Controllers\ClientSupportController;
use App\Http\Controllers\SupportHistoryController;
use App\Http\Controllers\BandwidthBuy\BandwidthProviderController;
use App\Http\Controllers\BandwidthBuy\BandwidthServiceController;
use App\Http\Controllers\BandwidthBuy\BandwidthPurchaseController;
use App\Http\Controllers\BandwidthBuy\BandwidthReportController;
use App\Http\Controllers\BandwidthSale\BwsCustomerController;
use App\Http\Controllers\BandwidthSale\BwsInvoiceController;
use App\Http\Controllers\SuperAdmin\PaymentGatewayController as SuperAdminPGController;
use App\Http\Controllers\Settings\PaymentGatewaySettingController;
use App\Http\Controllers\Client\OnlinePaymentController;

// ─────────────────────────────────────────────
// Public Routes
// ─────────────────────────────────────────────

Route::get('/', fn() => redirect()->route('login'));

Route::get('language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

require __DIR__ . '/auth.php';

// ─────────────────────────────────────────────
// Protected Routes (Login required)
// ─────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // ── Notifications ──────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                  [NotificationController::class, 'index'])         ->name('index');
        Route::get('/unread-count',      [NotificationController::class, 'unreadCount'])    ->name('unread-count');
        Route::post('/{id}/read',        [NotificationController::class, 'markAsRead'])     ->name('read');
        Route::post('/read-all',         [NotificationController::class, 'markAllAsRead'])  ->name('read-all');
    });


    // ── Dashboard ──────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('can:dashboard.view');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats')->middleware('can:dashboard.view');

    // ── Customers ──────────────────────────────
    // ⚠️ Static/AJAX routes MUST come before resource() — otherwise {customer} catches them
    Route::get ('customers/sub-zones',                    [CustomerController::class, 'getSubZones'])           ->name('customers.sub-zones')->middleware('can:customer.view');
    Route::get ('customers/package-price',                [CustomerController::class, 'getPackagePrice'])        ->name('customers.package-price')->middleware('can:customer.view');
    Route::post('customers/quick-add/zone',               [CustomerController::class, 'quickAddZone'])           ->name('customers.quick-add.zone')->middleware('can:customer.create');
    Route::post('customers/quick-add/connection-type',    [CustomerController::class, 'quickAddConnectionType']) ->name('customers.quick-add.connection-type')->middleware('can:customer.create');
    Route::post('customers/quick-add/client-type',        [CustomerController::class, 'quickAddClientType'])     ->name('customers.quick-add.client-type')->middleware('can:customer.create');
    Route::post('customers/quick-add/protocol-type',      [CustomerController::class, 'quickAddProtocolType'])   ->name('customers.quick-add.protocol-type')->middleware('can:customer.create');

    Route::resource('customers', CustomerController::class)->middleware([
        'index'   => 'can:customer.view',
        'show'    => 'can:customer.view',
        'create'  => 'can:customer.create',
        'store'   => 'can:customer.create',
        'edit'    => 'can:customer.edit',
        'update'  => 'can:customer.edit',
        'destroy' => 'can:customer.delete',
    ]);
    Route::patch('customers/{customer}/status',       [CustomerController::class, 'updateStatus']) ->name('customers.status')->middleware('can:customer.suspend');
    Route::get  ('customers/{customer}/mikrotik-info',[CustomerController::class, 'mikrotikInfo'])->name('customers.mikrotik-info')->middleware('can:customer.view');

    // ── Packages ───────────────────────────────
    Route::get ('packages/sync',  [PackageController::class, 'syncPreview'])->name('packages.sync.preview')->middleware('can:package.mikrotik.sync');
    Route::post('packages/sync', [PackageController::class, 'syncStore'])  ->name('packages.sync.store')  ->middleware('can:package.mikrotik.sync');
    Route::get('packages/mikrotik-profiles', [PackageController::class, 'mikrotikProfilesByProtocol'])->name('packages.mikrotik-profiles')->middleware('can:package.view');
    Route::resource('packages', PackageController::class)->middleware([
        'index'   => 'can:package.view',
        'show'    => 'can:package.view',
        'create'  => 'can:package.create',
        'store'   => 'can:package.create',
        'edit'    => 'can:package.edit',
        'update'  => 'can:package.edit',
        'destroy' => 'can:package.delete',
    ]);
    Route::patch('packages/{package}/toggle', [PackageController::class, 'toggleStatus'])->name('packages.toggle')->middleware('can:package.edit');

    // ── Invoices ───────────────────────────────
    // Bulk routes must be before resource routes
    Route::post('invoices/bulk-generate',    [InvoiceController::class, 'bulkGenerate'])->name('invoices.bulk-generate')->middleware('can:invoice.bulk');
    Route::post('invoices/bulk-delete',      [InvoiceController::class, 'bulkDelete'])->name('invoices.bulk-delete')->middleware('can:invoice.delete');
    Route::get('invoices/bulk-xlsx',         [InvoiceController::class, 'bulkXlsx'])->name('invoices.bulk-xlsx')->middleware('can:invoice.view');
    Route::get('invoices/bulk-pdf',          [InvoiceController::class, 'bulkPdf'])->name('invoices.bulk-pdf')->middleware('can:invoice.view');
    Route::post('invoices/bulk-sms',         [InvoiceController::class, 'bulkSms'])->name('invoices.bulk-sms')->middleware('can:sms.send');

    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update'])->middleware([
        'index'   => 'can:invoice.view',
        'show'    => 'can:invoice.view',
        'create'  => 'can:invoice.create',
        'store'   => 'can:invoice.create',
        'destroy' => 'can:invoice.delete',
    ]);
    Route::get('invoices/{invoice}/pdf',     [InvoiceController::class, 'pdf'])->name('invoices.pdf')->middleware('can:invoice.view');
    Route::get('invoices/{invoice}/receipt', [InvoiceController::class, 'receipt'])->name('invoices.receipt');

    // ── Payments ───────────────────────────────
    Route::get('payments',                          [PaymentController::class, 'index'])->name('payments.index')->middleware('can:payment.view');
    Route::post('payments/invoice/{invoice}',       [PaymentController::class, 'payInvoice'])->name('payments.pay-invoice')->middleware('can:payment.collect');
    Route::get('payments/collect',                  [PaymentController::class, 'collectPage'])->name('payments.collect')->middleware('can:payment.collect');
    Route::post('payments/collect',                 [PaymentController::class, 'collectStore'])->name('payments.collect-store')->middleware('can:payment.collect');
    Route::get('payments/customer-due/{customer}',  [PaymentController::class, 'customerDue'])->name('payments.customer-due')->middleware('can:payment.view');
    Route::post('payments/{payment}/void',          [PaymentController::class, 'void'])->name('payments.void')->middleware('can:payment.void');


    // ── Support & Ticketing ────────────────────

    // Support Categories (AJAX)
    Route::prefix('support-categories')->name('support-categories.')->group(function () {
        Route::get('/',                              [SupportCategoryController::class, 'index'])  ->name('index')->middleware('can:support.category.view');
        Route::post('/',                             [SupportCategoryController::class, 'store'])  ->name('store')->middleware('can:support.category.create');
        Route::get('/{supportCategory}/edit',        [SupportCategoryController::class, 'edit'])   ->name('edit');
        Route::put('/{supportCategory}',             [SupportCategoryController::class, 'update']) ->name('update');
        Route::delete('/{supportCategory}',          [SupportCategoryController::class, 'destroy'])->name('destroy');
    });

    // Client Support (Tickets) (AJAX)
    Route::prefix('client-support')->name('client-support.')->group(function () {
        Route::get('/',                              [ClientSupportController::class, 'index'])        ->name('index')->middleware('can:support.client.view');
        Route::get('/customer-info',                 [ClientSupportController::class, 'customerInfo']) ->name('customer-info');
        Route::post('/',                             [ClientSupportController::class, 'store'])        ->name('store')->middleware('can:support.client.create');
        Route::get('/{ticket}/edit',                 [ClientSupportController::class, 'edit'])         ->name('edit');
        Route::put('/{ticket}',                      [ClientSupportController::class, 'update'])       ->name('update');
        Route::delete('/{ticket}',                   [ClientSupportController::class, 'destroy'])      ->name('destroy');
        Route::get('/{ticket}/chat',                 [ClientSupportController::class, 'chat'])         ->name('chat');
        Route::post('/{ticket}/chat',                [ClientSupportController::class, 'chatReply'])    ->name('chat.reply');
        Route::get('/{ticket}/chat/messages',        [ClientSupportController::class, 'chatMessages']) ->name('chat.messages');
        Route::post('/{ticket}/solve',               [ClientSupportController::class, 'solve'])        ->name('solve');
        Route::get('/{ticket}/mikrotik-status',      [ClientSupportController::class, 'mikrotikStatus'])->name('mikrotik-status');
        Route::post('/{ticket}/reassign',            [ClientSupportController::class, 'reassign'])     ->name('reassign');
        Route::get('/departments/{department}/employees', [ClientSupportController::class, 'getEmployees'])->name('employees');
    });

    // Support History
    Route::prefix('support-history')->name('support-history.')->group(function () {
        Route::get('/',        [SupportHistoryController::class, 'index'])     ->name('index')->middleware('can:support.history.view');
        Route::get('/pdf',     [SupportHistoryController::class, 'exportPdf']) ->name('pdf');
        Route::get('/csv',     [SupportHistoryController::class, 'exportCsv']) ->name('csv');
    });

    // ── Agents ─────────────────────────────────
    Route::resource('agents', AgentController::class)->except(['edit'])->middleware([
        'index'   => 'can:agent.view',
        'show'    => 'can:agent.view',
        'create'  => 'can:agent.create',
        'store'   => 'can:agent.create',
        'update'  => 'can:agent.edit',
        'destroy' => 'can:agent.delete',
    ]);
    Route::post('agents/{agent}/pay-commission', [AgentController::class, 'payCommission'])->name('agents.pay-commission')->middleware('can:agent.edit');

    // ── MikroTik ───────────────────────────────
    Route::prefix('mikrotik')->name('mikrotik.')->middleware('can:mikrotik.view')->group(function () {
        Route::get('/',                        [MikrotikController::class, 'index'])->name('index');
        Route::post('/',                       [MikrotikController::class, 'store'])->name('store')->middleware('can:mikrotik.create');
        Route::get('active-sessions',          [MikrotikController::class, 'activeSessionsPage'])->name('active-sessions.page')->middleware('can:mikrotik.session.view');
        Route::post('kick-by-username',        [MikrotikController::class, 'kickByUsername'])->name('kick-by-username')->middleware('can:mikrotik.sync');
        Route::post('bulk-suspend',            [MikrotikController::class, 'bulkSuspend'])->name('bulk.suspend')->middleware('can:mikrotik.sync');
        Route::post('sync-all',                [MikrotikController::class, 'syncAll'])->name('sync.all');
        Route::get('sync-status',              [MikrotikController::class, 'syncStatus'])->name('sync.status');
        Route::put('pool/{pool}',              [MikrotikController::class, 'updatePool'])->name('pool.update');
        Route::delete('pool/{pool}',           [MikrotikController::class, 'destroyPool'])->name('pool.destroy');
        Route::put('{mikrotikRouter}',         [MikrotikController::class, 'update'])->name('update')->middleware('can:mikrotik.edit');
        Route::delete('{mikrotikRouter}',      [MikrotikController::class, 'destroy'])->name('destroy')->middleware('can:mikrotik.delete');
        Route::post('{mikrotikRouter}/pool',   [MikrotikController::class, 'addPool'])->name('pool.store');
        Route::get('{router}/status',          [MikrotikController::class, 'routerStatus'])->name('router.status');
        Route::get('{router}/pppoe-users',     [MikrotikController::class, 'pppoeUsers'])->name('pppoe.users');
        Route::get('{router}/active-sessions', [MikrotikController::class, 'activeSessions'])->name('active.sessions');
        Route::get('{router}/queues',          [MikrotikController::class, 'queues'])->name('queues');
        Route::get('{router}/profiles',        [MikrotikController::class, 'profiles'])->name('profiles');
    });

    // ── OLT Management ─────────────────────────────────────────────
    Route::prefix('olt')->name('olt.')->middleware('can:olt.view')->group(function () {
        // static routes আগে
        Route::get ('users',         [OltController::class, 'users'])     ->name('users');
        Route::get ('users/data',    [OltController::class, 'usersData']) ->name('users.data');
        Route::post('sync-all',      [OltController::class, 'syncAll'])   ->name('sync-all');
        // CRUD
        Route::get   ('/',           [OltController::class, 'index'])     ->name('index');
        Route::post  ('/',           [OltController::class, 'store'])     ->name('store')->middleware('can:olt.create');
        Route::get   ('/{olt}',      [OltController::class, 'show'])      ->name('show');
        Route::put   ('/{olt}',      [OltController::class, 'update'])    ->name('update')->middleware('can:olt.edit');
        Route::delete('/{olt}',      [OltController::class, 'destroy'])   ->name('destroy')->middleware('can:olt.delete');
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
        Route::get('/',                 [ImportController::class, 'index'])->name('index')->middleware('can:customer.import.view');
        Route::any('mikrotik/preview',  [ImportController::class, 'mikrotikPreview'])->name('mikrotik.preview')->middleware('can:mikrotik.import.customer');
        Route::post('mikrotik/execute', [ImportController::class, 'mikrotikImport'])->name('mikrotik.execute')->middleware('can:mikrotik.import.customer');
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

        // ── Sales ──────────────────────────────
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/',                    [\App\Http\Controllers\Inventory\SaleController::class, 'index'])      ->name('index');
            Route::get('/create',              [\App\Http\Controllers\Inventory\SaleController::class, 'create'])     ->name('create');
            Route::post('/',                   [\App\Http\Controllers\Inventory\SaleController::class, 'store'])      ->name('store');
            Route::get('/export-xlsx',         [\App\Http\Controllers\Inventory\SaleController::class, 'exportXlsx']) ->name('export-xlsx');
            Route::get('/export-pdf',          [\App\Http\Controllers\Inventory\SaleController::class, 'exportPdf'])  ->name('export-pdf');
            Route::get('/{sale}/detail',       [\App\Http\Controllers\Inventory\SaleController::class, 'detail'])     ->name('detail');
            Route::get('/{sale}/invoice-pdf',  [\App\Http\Controllers\Inventory\SaleController::class, 'invoicePdf']) ->name('invoice-pdf');
            Route::post('/{sale}/payment',     [\App\Http\Controllers\Inventory\SalePaymentController::class, 'store'])->name('payment.store');
            Route::post('/{sale}/payment/{payment}/void', [\App\Http\Controllers\Inventory\SalePaymentController::class, 'void'])->name('payment.void');
            Route::get('/{sale}',              [\App\Http\Controllers\Inventory\SaleController::class, 'show'])       ->name('show');
            Route::get('/{sale}/edit',         [\App\Http\Controllers\Inventory\SaleController::class, 'edit'])       ->name('edit');
            Route::put('/{sale}',              [\App\Http\Controllers\Inventory\SaleController::class, 'update'])     ->name('update');
            Route::post('/{sale}/void',        [\App\Http\Controllers\Inventory\SaleController::class, 'void'])       ->name('void');
            Route::delete('/{sale}',           [\App\Http\Controllers\Inventory\SaleController::class, 'destroy'])    ->name('destroy');
        });

        // ── Sale Returns ───────────────────────
        Route::prefix('sale-returns')->name('sale-returns.')->group(function () {
            Route::get('/',                       [\App\Http\Controllers\Inventory\SaleReturnController::class, 'index'])     ->name('index');
            Route::get('/create',                 [\App\Http\Controllers\Inventory\SaleReturnController::class, 'create'])    ->name('create');
            Route::post('/',                      [\App\Http\Controllers\Inventory\SaleReturnController::class, 'store'])     ->name('store');
            Route::get('/sale/{sale}/items',      [\App\Http\Controllers\Inventory\SaleReturnController::class, 'saleItems']) ->name('sale-items');
            Route::get('/{saleReturn}',           [\App\Http\Controllers\Inventory\SaleReturnController::class, 'show'])      ->name('show');
        });

        // ── Purchases ──────────────────────────
        Route::prefix('purchases')->name('purchases.')->group(function () {
            Route::get('/',                    [\App\Http\Controllers\Inventory\PurchaseController::class, 'index'])      ->name('index');
            Route::get('/create',              [\App\Http\Controllers\Inventory\PurchaseController::class, 'create'])     ->name('create');
            Route::post('/',                   [\App\Http\Controllers\Inventory\PurchaseController::class, 'store'])      ->name('store');
            Route::get('/export-xlsx',         [\App\Http\Controllers\Inventory\PurchaseController::class, 'exportXlsx']) ->name('export-xlsx');
            Route::get('/export-pdf',          [\App\Http\Controllers\Inventory\PurchaseController::class, 'exportPdf'])  ->name('export-pdf');
            Route::get('/{purchase}/detail',   [\App\Http\Controllers\Inventory\PurchaseController::class, 'detail'])     ->name('detail');
            Route::post('/{purchase}/payment', [\App\Http\Controllers\Inventory\PurchasePaymentController::class, 'store'])->name('payment.store');
            Route::post('/{purchase}/payment/{payment}/void', [\App\Http\Controllers\Inventory\PurchasePaymentController::class, 'void'])->name('payment.void');
            Route::get('/{purchase}',          [\App\Http\Controllers\Inventory\PurchaseController::class, 'show'])       ->name('show');
            Route::get('/{purchase}/edit',     [\App\Http\Controllers\Inventory\PurchaseController::class, 'edit'])       ->name('edit');
            Route::put('/{purchase}',          [\App\Http\Controllers\Inventory\PurchaseController::class, 'update'])     ->name('update');
            Route::post('/{purchase}/cancel',  [\App\Http\Controllers\Inventory\PurchaseController::class, 'cancel'])     ->name('cancel');
            Route::delete('/{purchase}',       [\App\Http\Controllers\Inventory\PurchaseController::class, 'destroy'])    ->name('destroy');
        });

        // ── Purchase Returns ───────────────────
        Route::prefix('purchase-returns')->name('purchase-returns.')->group(function () {
            Route::get('/',                       [\App\Http\Controllers\Inventory\PurchaseReturnController::class, 'index'])         ->name('index');
            Route::get('/create',                 [\App\Http\Controllers\Inventory\PurchaseReturnController::class, 'create'])        ->name('create');
            Route::post('/',                      [\App\Http\Controllers\Inventory\PurchaseReturnController::class, 'store'])         ->name('store');
            Route::get('/purchase/{purchase}/items', [\App\Http\Controllers\Inventory\PurchaseReturnController::class, 'purchaseItems'])->name('purchase-items');
            Route::get('/{purchaseReturn}',       [\App\Http\Controllers\Inventory\PurchaseReturnController::class, 'show'])           ->name('show');
        });
    });

    // ── Reports ────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('revenue',           [ReportController::class, 'revenue'])->name('revenue')->middleware('can:report.revenue.view');
        Route::get('due',               [ReportController::class, 'due'])->name('due')->middleware('can:report.revenue.view');
        Route::get('customers',         [ReportController::class, 'customers'])->name('customers')->middleware('can:report.collection.view');
        Route::get('export/{type}/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf')->middleware('can:report.revenue.view');
    });

    // ── SMS ────────────────────────────────────
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/',                          [SmsController::class, 'index'])->name('index')->middleware('can:sms.view');
        Route::post('gateway/{gateway}/toggle',  [SmsController::class, 'toggleGateway'])->name('gateway.toggle')->middleware('can:sms.gateway.manage');
        Route::post('gateway/{gateway}/config',  [SmsController::class, 'updateConfig'])->name('gateway.config')->middleware('can:sms.gateway.manage');
        Route::post('test',                      [SmsController::class, 'sendTest'])->name('test')->middleware('can:sms.send');
        Route::post('bulk',                      [SmsController::class, 'sendBulk'])->name('bulk')->middleware('can:sms.send');
        Route::delete('logs',                    [SmsController::class, 'clearLogs'])->name('logs.clear');
        Route::get('reports',                    [App\Http\Controllers\SmsReportController::class, 'index'])->name('reports')->middleware('can:sms.report.view');
        Route::get('reports/details',            [App\Http\Controllers\SmsReportController::class, 'details'])->name('reports.details');
        Route::get('templates',                  [App\Http\Controllers\SmsTemplateController::class, 'index'])->name('templates.index')->middleware('can:sms.template.view');
        Route::post('templates',                 [App\Http\Controllers\SmsTemplateController::class, 'store'])->name('templates.store')->middleware('can:sms.template.create');
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

    // ── User Management (ISP Admin) ────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',               [UserController::class, 'index'])   ->name('index');
        Route::get('/create',         [UserController::class, 'create'])  ->name('create');
        Route::post('/',              [UserController::class, 'store'])   ->name('store');
        Route::get('/{user}/edit',    [UserController::class, 'edit'])    ->name('edit');
        Route::put('/{user}',         [UserController::class, 'update'])  ->name('update');
        Route::post('/{user}/toggle', [UserController::class, 'toggle'])  ->name('toggle');
        Route::delete('/{user}',      [UserController::class, 'destroy']) ->name('destroy');
    });

    // ── Role Management (ISP Admin) ────────────
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/',            [RoleController::class, 'index'])  ->name('index');
        Route::get('/create',      [RoleController::class, 'create']) ->name('create');
        Route::post('/',           [RoleController::class, 'store'])  ->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])   ->name('edit');
        Route::put('/{role}',      [RoleController::class, 'update']) ->name('update');
        Route::delete('/{role}',   [RoleController::class, 'destroy'])->name('destroy');
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
        Route::get('/dashboard/stats', [SuperAdminTenantController::class, 'dashboardStats'])->name('dashboard.stats');

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

        // Payment Gateways
        Route::prefix('payment-gateways')->name('payment-gateways.')->group(function () {
            Route::get ('/',                 [SuperAdminPGController::class, 'index'])  ->name('index');
            Route::post('/{gateway}/toggle', [SuperAdminPGController::class, 'toggle']) ->name('toggle');
        });

        // Permissions
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/',                [SuperAdminPermissionController::class, 'index'])  ->name('index');
            Route::post('/',               [SuperAdminPermissionController::class, 'store'])  ->name('store');
            Route::put('/{permission}',    [SuperAdminPermissionController::class, 'update']) ->name('update');
            Route::delete('/{permission}', [SuperAdminPermissionController::class, 'destroy'])->name('destroy');
        });

        // isp-admin role permission management
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/isp-admin',  [SuperAdminRoleController::class, 'ispAdminPermissions'])      ->name('isp-admin');
            Route::put('/isp-admin',  [SuperAdminRoleController::class, 'updateIspAdminPermissions'])->name('isp-admin.update');
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

        // Box
        Route::get   ('box',              [App\Http\Controllers\Settings\BoxController::class, 'index'])      ->name('box.index');
        Route::get   ('box/sub-zones',    [App\Http\Controllers\Settings\BoxController::class, 'getSubZones']) ->name('box.sub-zones');
        Route::post  ('box',              [App\Http\Controllers\Settings\BoxController::class, 'store'])      ->name('box.store');
        Route::get   ('box/{box}/edit',   [App\Http\Controllers\Settings\BoxController::class, 'edit'])       ->name('box.edit');
        Route::put   ('box/{box}',        [App\Http\Controllers\Settings\BoxController::class, 'update'])     ->name('box.update');
        Route::delete('box/{box}',        [App\Http\Controllers\Settings\BoxController::class, 'destroy'])    ->name('box.destroy');
        Route::post  ('box/{box}/toggle', [App\Http\Controllers\Settings\BoxController::class, 'toggle'])     ->name('box.toggle');

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

        // ── Payment Gateways (ISP Admin credentials) ───────────
        Route::prefix('payment-gateways')->name('payment-gateway.')->group(function () {
            Route::get ('/{slug}/config', [PaymentGatewaySettingController::class, 'config']) ->name('config');
            Route::post('/{slug}/save',   [PaymentGatewaySettingController::class, 'save'])   ->name('save');
            Route::post('/{slug}/toggle', [PaymentGatewaySettingController::class, 'toggle']) ->name('toggle');
        });

    }); // end settings

    // ── HR ─────────────────────────────────────
    Route::resource('employees', EmployeeController::class)->middleware([
        'index'   => 'can:hr.employee.view',
        'show'    => 'can:hr.employee.view',
        'create'  => 'can:hr.employee.create',
        'store'   => 'can:hr.employee.create',
        'edit'    => 'can:hr.employee.edit',
        'update'  => 'can:hr.employee.edit',
        'destroy' => 'can:hr.employee.delete',
    ]);
    Route::resource('departments', DepartmentController::class)->middleware([
        'index'   => 'can:hr.department.view',
        'store'   => 'can:hr.department.create',
        'update'  => 'can:hr.department.edit',
        'destroy' => 'can:hr.department.delete',
    ]);
    Route::resource('positions', PositionController::class)->middleware([
        'index'   => 'can:hr.position.view',
        'store'   => 'can:hr.position.create',
        'update'  => 'can:hr.position.edit',
        'destroy' => 'can:hr.position.delete',
    ]);
    Route::resource('salary-heads', SalaryHeadController::class)->middleware([
        'index'   => 'can:hr.salary.head.view',
        'store'   => 'can:hr.salary.head.create',
        'update'  => 'can:hr.salary.head.edit',
        'destroy' => 'can:hr.salary.head.delete',
    ]);

    Route::delete('employees/documents/{document}', [EmployeeController::class, 'destroyDocument'])->name('employees.documents.destroy')->middleware('can:hr.employee.edit');
    Route::get('departments/{department}/positions', [EmployeeController::class, 'getPositions'])->name('departments.positions')->middleware('can:hr.department.view');
    Route::post('employees/{employee}/resign-terminate', [EmployeeController::class, 'resignTerminate'])->name('employees.resign-terminate')->middleware('can:hr.employee.edit');

    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/',                          [PayrollController::class, 'index'])          ->name('index')->middleware('can:hr.payroll.view');
        Route::get('/generate',                  [PayrollController::class, 'generate'])       ->name('generate')->middleware('can:hr.payroll.manage');
        Route::post('/',                         [PayrollController::class, 'store'])          ->name('store')->middleware('can:hr.payroll.manage');
        Route::post('/bulk-delete',              [PayrollController::class, 'bulkDelete'])     ->name('bulk-delete');
        Route::get('/export-xlsx',               [PayrollController::class, 'exportXlsx'])    ->name('export-xlsx');
        Route::get('/export-pdf',                [PayrollController::class, 'exportPdf'])     ->name('export-pdf');
        Route::post('/payment/{payment}/void',   [PayrollController::class, 'voidPayment'])   ->name('payment.void');
        Route::get('/{payroll}',                 [PayrollController::class, 'show'])          ->name('show');
        Route::get('/{payroll}/edit',            [PayrollController::class, 'edit'])          ->name('edit');
        Route::put('/{payroll}',                 [PayrollController::class, 'update'])        ->name('update');
        Route::post('/{payroll}/pay',            [PayrollController::class, 'pay'])           ->name('pay');
        Route::delete('/{payroll}',              [PayrollController::class, 'destroy'])       ->name('destroy');
        Route::get('/{payroll}/payslip',         [PayrollController::class, 'payslip'])       ->name('payslip');
        Route::get('/{payroll}/payslip-pdf',     [PayrollController::class, 'payslipPdf'])    ->name('payslip-pdf');
        Route::get('/{payroll}/payment-history', [PayrollController::class, 'paymentHistory'])->name('payment-history');
    });

    Route::prefix('leave')->name('leave.')->group(function () {
        Route::get('/',                 [LeaveController::class, 'index'])->name('index')->middleware('can:hr.leave.view');
        Route::get('/create',           [LeaveController::class, 'create'])->name('create')->middleware('can:hr.leave.create');
        Route::post('/',                [LeaveController::class, 'store'])->name('store')->middleware('can:hr.leave.create');
        Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve')->middleware('can:hr.leave.approve');
        Route::post('/{leave}/reject',  [LeaveController::class, 'reject'])->name('reject')->middleware('can:hr.leave.approve');
        Route::get('/types',            [LeaveController::class, 'types'])->name('types')->middleware('can:hr.leave.type.view');
        Route::post('/types',           [LeaveController::class, 'storeType'])->name('types.store')->middleware('can:hr.leave.type.create');
        Route::put('/types/{type}',     [LeaveController::class, 'updateType'])->name('types.update');
        Route::delete('/types/{type}',  [LeaveController::class, 'destroyType'])->name('types.destroy');
    });

    Route::prefix('salary-advance')->name('salary-advance.')->group(function () {
        Route::get('/',                  [SalaryAdvanceController::class, 'index'])->name('index')->middleware('can:hr.salary.advance.view');
        Route::post('/',                 [SalaryAdvanceController::class, 'store'])->name('store')->middleware('can:hr.salary.advance.create');
        Route::post('/{advance}/deduct', [SalaryAdvanceController::class, 'deduct'])->name('deduct')->middleware('can:hr.salary.advance.approve');
    });

    // ── Financial Module ───────────────────────
    Route::prefix('expenses')->name('expenses.')->group(function () {

        // P&L Report — static routes BEFORE resource-style {expense} routes
        Route::get('reports/profit-loss',     [ExpenseController::class, 'profitLoss'])    ->name('profit-loss')->middleware('can:accounting.report.view');
        Route::get('reports/profit-loss/pdf', [ExpenseController::class, 'profitLossPdf']) ->name('profit-loss.pdf');
        Route::get('api/chart-data',          [ExpenseController::class, 'chartData'])     ->name('chart-data');
        Route::get('export/xlsx',             [ExpenseController::class, 'exportXlsx'])    ->name('export-xlsx');
        Route::get('export/pdf',              [ExpenseController::class, 'exportPdf'])     ->name('export-pdf');

        // CRUD
        Route::get('/',               [ExpenseController::class, 'index'])  ->name('index')->middleware('can:accounting.expense.view');
        Route::get('/create',         [ExpenseController::class, 'create']) ->name('create')->middleware('can:accounting.expense.create');
        Route::post('/',              [ExpenseController::class, 'store'])  ->name('store')->middleware('can:accounting.expense.create');
        Route::get('/{expense}',      [ExpenseController::class, 'show'])   ->name('show');
        Route::get('/{expense}/edit-data', [ExpenseController::class, 'editData']) ->name('edit-data');
        Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])   ->name('edit');
        Route::put('/{expense}',      [ExpenseController::class, 'update']) ->name('update')->middleware('can:accounting.expense.edit');

        // Void & hard delete
        Route::post('/{expense}/void', [ExpenseController::class, 'void'])    ->name('void')->middleware('can:accounting.expense.void');
        Route::delete('/{expense}',    [ExpenseController::class, 'destroy']) ->name('destroy')->middleware('can:accounting.expense.delete');
    });

    Route::prefix('expense-categories')->name('expense-categories.')->group(function () {
        Route::get('/',                    [ExpenseController::class, 'categoriesIndex'])  ->name('index')->middleware('can:accounting.expense.category.view');
        Route::post('/',                   [ExpenseController::class, 'categoryStore'])    ->name('store')->middleware('can:accounting.expense.category.create');
        Route::post('/quick-add',          [ExpenseController::class, 'quickAddCategory']) ->name('quick-add');
        Route::put('/{expenseCategory}',   [ExpenseController::class, 'categoryUpdate'])   ->name('update');
        Route::delete('/{expenseCategory}',[ExpenseController::class, 'categoryDestroy'])  ->name('destroy');
    });

    // ── Income Module ────────────────────────
    Route::prefix('incomes')->name('incomes.')->group(function () {

        // Static routes BEFORE {income} to avoid route collision
        Route::get('/',                   [IncomeController::class, 'index'])           ->name('index')->middleware('can:accounting.income.view');
        Route::post('/',                  [IncomeController::class, 'store'])           ->name('store')->middleware('can:accounting.income.create');
        Route::get('/{income}/edit-data', [IncomeController::class, 'editData'])        ->name('edit-data');
        Route::get('/{income}',           [IncomeController::class, 'show'])            ->name('show');
        Route::put('/{income}',           [IncomeController::class, 'update'])          ->name('update')->middleware('can:accounting.income.edit');
        Route::post('/{income}/void',     [IncomeController::class, 'void'])            ->name('void')->middleware('can:accounting.income.void');
        Route::delete('/{income}',        [IncomeController::class, 'destroy'])         ->name('destroy')->middleware('can:accounting.income.delete');
    });

    // ── Accounting Quick Add Categories ────────────
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::post('income-categories/quick-add',  [IncomeController::class,  'quickAddCategory']) ->name('income-categories.quick-add');
        Route::post('expense-categories/quick-add', [ExpenseController::class, 'quickAddCategory']) ->name('expense-categories.quick-add');
    });

    // ── Accounting Dashboard ───────────────────────
    Route::get('accounting/dashboard', [AccountingController::class, 'dashboard'])->name('accounting.dashboard')->middleware('can:accounting.view');

    // ── Income Categories ──────────────────────────
    Route::prefix('income-categories')->name('income-categories.')->group(function () {
        Route::get('/',                     [IncomeController::class, 'categoriesIndex'])  ->name('index')->middleware('can:accounting.income.category.view');
        Route::post('/',                    [IncomeController::class, 'categoryStore'])    ->name('store')->middleware('can:accounting.income.category.create');
        Route::put('/{incomeCategory}',     [IncomeController::class, 'categoryUpdate'])   ->name('update');
        Route::delete('/{incomeCategory}',  [IncomeController::class, 'categoryDestroy'])  ->name('destroy');
    });

    // ── Bandwidth Buy Module ───────────────────────
    Route::prefix('bandwidth-buy')->name('bandwidth-buy.')->middleware('can:isp-admin')->group(function () {

        // Provider
        Route::prefix('provider')->name('provider.')->group(function () {
            Route::get('/',                    [BandwidthProviderController::class, 'index'])  ->name('index');
            Route::get('/create',              [BandwidthProviderController::class, 'create']) ->name('create');
            Route::post('/',                   [BandwidthProviderController::class, 'store'])  ->name('store');
            Route::get('/{provider}/edit',     [BandwidthProviderController::class, 'edit'])   ->name('edit');
            Route::put('/{provider}',          [BandwidthProviderController::class, 'update']) ->name('update');
            Route::delete('/{provider}',       [BandwidthProviderController::class, 'destroy'])->name('destroy');
        });

        // Service
        Route::prefix('service')->name('service.')->group(function () {
            Route::get('/',                    [BandwidthServiceController::class, 'index'])  ->name('index');
            Route::get('/create',              [BandwidthServiceController::class, 'create']) ->name('create');
            Route::post('/',                   [BandwidthServiceController::class, 'store'])  ->name('store');
            Route::get('/{service}/edit',      [BandwidthServiceController::class, 'edit'])   ->name('edit');
            Route::put('/{service}',           [BandwidthServiceController::class, 'update']) ->name('update');
        });

        // Purchase Bill
        Route::prefix('purchase')->name('purchase.')->group(function () {
            Route::get('/',                              [BandwidthPurchaseController::class, 'index'])                ->name('index');
            Route::get('/create',                        [BandwidthPurchaseController::class, 'create'])               ->name('create');
            Route::post('/',                             [BandwidthPurchaseController::class, 'store'])                ->name('store');
            Route::get('/export-xlsx',                   [BandwidthPurchaseController::class, 'exportXlsx'])           ->name('export-xlsx');
            Route::get('/export-pdf',                    [BandwidthPurchaseController::class, 'exportPdf'])            ->name('export-pdf');
            Route::get('/payment-history',               [BandwidthPurchaseController::class, 'allPaymentHistory'])    ->name('all-payment-history');
            Route::get('/payment-history/xlsx',          [BandwidthPurchaseController::class, 'allPaymentHistoryXlsx'])->name('all-payment-history.xlsx');
            Route::get('/payment-history/pdf',           [BandwidthPurchaseController::class, 'allPaymentHistoryPdf']) ->name('all-payment-history.pdf');
            Route::post('/payment/{payment}/void',       [BandwidthPurchaseController::class, 'voidPayment'])          ->name('payment.void');
            Route::get('/payment/{payment}/detail',      [BandwidthPurchaseController::class, 'paymentDetail'])        ->name('payment.detail');
            Route::get('/{purchase}',                    [BandwidthPurchaseController::class, 'show'])                 ->name('show');
            Route::get('/{purchase}/edit',               [BandwidthPurchaseController::class, 'edit'])                 ->name('edit');
            Route::put('/{purchase}',                    [BandwidthPurchaseController::class, 'update'])               ->name('update');
            Route::post('/{purchase}/void',              [BandwidthPurchaseController::class, 'void'])                 ->name('void');
            Route::delete('/{purchase}',                 [BandwidthPurchaseController::class, 'destroy'])              ->name('destroy');
            Route::post('/{purchase}/pay',               [BandwidthPurchaseController::class, 'pay'])                  ->name('pay');
            Route::get('/{purchase}/payment-history',    [BandwidthPurchaseController::class, 'paymentHistory'])       ->name('payment-history');
        });

        // Purchase Report
        Route::get('report', [BandwidthReportController::class, 'index'])->name('report');
        Route::get('report/datatables', [BandwidthReportController::class, 'datatables'])->name('report.datatables');

    }); // end bandwidth-buy

    // ── Bandwidth Sale Module ──────────────────
    Route::prefix('bandwidth-sale')->name('bandwidth-sale.')->middleware('can:isp-admin')->group(function () {

        // Dashboard — redirect to customers index for now
        Route::get('dashboard', fn() => redirect()->route('bandwidth-sale.customers.index'))->name('dashboard');

        // ── Customers ──────────────────────────
        Route::get ('customers/data',         [BwsCustomerController::class, 'data'])    ->name('customers.data');
        Route::get ('customers',              [BwsCustomerController::class, 'index'])   ->name('customers.index');
        Route::post('customers',              [BwsCustomerController::class, 'store'])   ->name('customers.store');
        Route::get ('customers/{customer}',   [BwsCustomerController::class, 'show'])    ->name('customers.show');
        Route::put ('customers/{customer}',   [BwsCustomerController::class, 'update'])  ->name('customers.update');
        Route::delete('customers/{customer}', [BwsCustomerController::class, 'destroy']) ->name('customers.destroy');

        // ── Invoices — static routes BEFORE {bwsInvoice} ──
        Route::get('invoices/export-pdf',                  [BwsInvoiceController::class, 'exportPdf'])      ->name('invoices.export-pdf');
        Route::get('invoices/export-xlsx',                 [BwsInvoiceController::class, 'exportXlsx'])     ->name('invoices.export-xlsx');
        Route::get('invoices/next-no',                     [BwsInvoiceController::class, 'nextNo'])         ->name('invoices.next-no');
        Route::get('invoices/due-for-customer/{customer}', [BwsInvoiceController::class, 'dueForCustomer']) ->name('invoices.due-for-customer');

        Route::get   ('invoices',                    [BwsInvoiceController::class, 'index'])   ->name('invoices.index');
        Route::get   ('invoices/create',             [BwsInvoiceController::class, 'create'])  ->name('invoices.create');
        Route::post  ('invoices',                    [BwsInvoiceController::class, 'store'])   ->name('invoices.store');
        Route::get   ('invoices/{bwsInvoice}',       [BwsInvoiceController::class, 'show'])    ->name('invoices.show');
        Route::get   ('invoices/{bwsInvoice}/edit',  [BwsInvoiceController::class, 'edit'])    ->name('invoices.edit');
        Route::put   ('invoices/{bwsInvoice}',       [BwsInvoiceController::class, 'update'])  ->name('invoices.update');
        Route::delete('invoices/{bwsInvoice}',       [BwsInvoiceController::class, 'destroy']) ->name('invoices.destroy');
        Route::get   ('invoices/{bwsInvoice}/pdf',   [BwsInvoiceController::class, 'pdf'])     ->name('invoices.pdf');

        // ── Bill Receive ───────────────────────
        Route::get ('invoices/{bwsInvoice}/receive', [BwsInvoiceController::class, 'receiveData'])  ->name('invoices.receive-data');
        Route::post('invoices/{bwsInvoice}/receive', [BwsInvoiceController::class, 'receiveStore']) ->name('invoices.receive');

        // ── Payment Void ───────────────────────
        Route::post('payments/delete-selected',     [BwsInvoiceController::class, 'deleteSelected'])  ->name('payments.delete-selected');
        Route::post('payments/approve-selected',    [BwsInvoiceController::class, 'approveSelected']) ->name('payments.approve-selected');
        Route::post('payments/{payment}/void',      [BwsInvoiceController::class, 'voidPayment'])     ->name('payments.void');

        // ── Daily Bill ─────────────────────────
        Route::get('daily-bill',          [BwsInvoiceController::class, 'dailyBill'])            ->name('daily-bill.index');
        Route::get('daily-bill/xlsx',     [BwsInvoiceController::class, 'dailyBillExportXlsx'])  ->name('daily-bill.xlsx');
        Route::get('daily-bill/pdf',      [BwsInvoiceController::class, 'dailyBillExportPdf'])   ->name('daily-bill.pdf');

        // ── Recurring Invoice ──────────────────
        Route::get   ('recurring',                   [BwsInvoiceController::class, 'recurringIndex'])  ->name('recurring.index');
        Route::get   ('recurring/create',            [BwsInvoiceController::class, 'recurringCreate']) ->name('recurring.create');
        Route::post  ('recurring',                   [BwsInvoiceController::class, 'recurringStore'])  ->name('recurring.store');
        Route::get   ('recurring/{bwsInvoice}/edit', [BwsInvoiceController::class, 'recurringEdit'])   ->name('recurring.edit');
        Route::put   ('recurring/{bwsInvoice}',      [BwsInvoiceController::class, 'recurringUpdate']) ->name('recurring.update');
        Route::delete('recurring/{bwsInvoice}',      [BwsInvoiceController::class, 'recurringDestroy'])->name('recurring.destroy');

    }); // end bandwidth-sale

    // ── Bill / Collection Reports (Tier 1) ──────
    Route::prefix('reports/bill')->name('reports.bill.')->group(function () {
        Route::get('renewal',             [BillCollectionReportController::class, 'renewal'])->name('renewal')->middleware('can:report.revenue.view');
        Route::get('aging-due',           [BillCollectionReportController::class, 'agingDue'])->name('aging-due')->middleware('can:report.revenue.view');
        Route::get('daily-collection',    [BillCollectionReportController::class, 'dailyCollection'])->name('daily-collection')->middleware('can:report.collection.view');
        Route::get('package-revenue',     [BillCollectionReportController::class, 'packageRevenue'])->name('package-revenue')->middleware('can:report.revenue.view');
        Route::get('receive-history',     [BillCollectionReportController::class, 'receiveHistory'])->name('receive-history')->middleware('can:report.collection.view');
        Route::get('receive-history/pdf', [BillCollectionReportController::class, 'exportReceiveHistoryPdf'])->name('receive-history.pdf')->middleware('can:report.collection.view');
        Route::get('receive-history/csv', [BillCollectionReportController::class, 'exportReceiveHistoryCsv'])->name('receive-history.csv')->middleware('can:report.collection.view');
        Route::get('monthly-billing',     [BillCollectionReportController::class, 'monthlyBilling'])->name('monthly-billing')->middleware('can:report.revenue.view');
        Route::get('monthly-billing/pdf', [BillCollectionReportController::class, 'exportMonthlyBillingPdf'])->name('monthly-billing.pdf')->middleware('can:report.revenue.view');
        Route::get('monthly-billing/csv', [BillCollectionReportController::class, 'exportMonthlyBillingCsv'])->name('monthly-billing.csv')->middleware('can:report.revenue.view');

        // ── Income Report ──
        Route::get('income',      [IncomeExpenseReportController::class, 'incomeReport'])->name('income')->middleware('can:report.revenue.view');
        Route::get('income/pdf',  [IncomeExpenseReportController::class, 'exportIncomePdf'])->name('income.pdf')->middleware('can:report.revenue.view');
        Route::get('income/xlsx', [IncomeExpenseReportController::class, 'exportIncomeXlsx'])->name('income.xlsx')->middleware('can:report.revenue.view');

        // ── Expense Report ──
        Route::get('expense',      [IncomeExpenseReportController::class, 'expenseReport'])->name('expense')->middleware('can:report.revenue.view');
        Route::get('expense/pdf',  [IncomeExpenseReportController::class, 'exportExpensePdf'])->name('expense.pdf')->middleware('can:report.revenue.view');
        Route::get('expense/xlsx', [IncomeExpenseReportController::class, 'exportExpenseXlsx'])->name('expense.xlsx')->middleware('can:report.revenue.view');

        // ── Customer Report ──
        Route::get('customer',      [CustomerReportController::class, 'customerReport'])->name('customer')->middleware('can:report.revenue.view');
        Route::get('customer/pdf',  [CustomerReportController::class, 'exportCustomerPdf'])->name('customer.pdf')->middleware('can:report.revenue.view');
        Route::get('customer/xlsx', [CustomerReportController::class, 'exportCustomerXlsx'])->name('customer.xlsx')->middleware('can:report.revenue.view');

        // ── POP Wise Clients ──
        Route::get('pop-wise',      [CustomerReportController::class, 'popWiseClients'])->name('pop-wise')->middleware('can:report.revenue.view');
        Route::get('pop-wise/pdf',  [CustomerReportController::class, 'exportPopWisePdf'])->name('pop-wise.pdf')->middleware('can:report.revenue.view');
        Route::get('pop-wise/xlsx', [CustomerReportController::class, 'exportPopWiseXlsx'])->name('pop-wise.xlsx')->middleware('can:report.revenue.view');

        // ── Income & Discount Report ──
        Route::get('income-discount',      [BillCollectionReportController::class, 'incomeDiscount'])->name('income-discount')->middleware('can:report.revenue.view');
        Route::get('income-discount/pdf',  [BillCollectionReportController::class, 'exportIncomeDiscountPdf'])->name('income-discount.pdf')->middleware('can:report.revenue.view');
        Route::get('income-discount/xlsx', [BillCollectionReportController::class, 'exportIncomeDiscountXlsx'])->name('income-discount.xlsx')->middleware('can:report.revenue.view');

        // ── Profit & Loss Report ──
        Route::get('profit',      [BillCollectionReportController::class, 'profitReport'])->name('profit')->middleware('can:report.revenue.view');
        Route::get('profit/pdf',  [BillCollectionReportController::class, 'exportProfitPdf'])->name('profit.pdf')->middleware('can:report.revenue.view');
        Route::get('profit/xlsx', [BillCollectionReportController::class, 'exportProfitXlsx'])->name('profit.xlsx')->middleware('can:report.revenue.view');
    });

}); // end auth
// ─────────────────────────────────────────────
// Client Portal Routes (নিজস্ব guard — auth এর বাইরে)
// ─────────────────────────────────────────────
require __DIR__ . '/client.php';
require __DIR__.'/inventory.php';

// ─────────────────────────────────────────────
// Payment Gateway Callbacks — No Auth, No CSRF
// (Gateway redirect করে এখানে — session নেই)
// ─────────────────────────────────────────────
Route::prefix('client/payment')->group(function () {

    // SSLCommerz & AmarPay — POST success/fail/cancel
    Route::post('{gateway}/success', [OnlinePaymentController::class, 'success'])->name('client.payment.success');
    Route::post('{gateway}/fail',    [OnlinePaymentController::class, 'fail'])   ->name('client.payment.fail');
    Route::post('{gateway}/cancel',  [OnlinePaymentController::class, 'cancel']) ->name('client.payment.cancel');

    // Razorpay & Stripe cancel — GET
    Route::get('{gateway}/cancel',   [OnlinePaymentController::class, 'cancel']);

    // Stripe success — GET (has ?session_id=)
    Route::get('{gateway}/success',  [OnlinePaymentController::class, 'success']);

    // bKash & Nagad & Razorpay — GET/POST callback
    Route::get ('{gateway}/callback', [OnlinePaymentController::class, 'callback'])->name('client.payment.callback');
    Route::post('{gateway}/callback', [OnlinePaymentController::class, 'callback']);

    // IPN — server-to-server (SSLCommerz, AmarPay)
    Route::post('{gateway}/ipn', [OnlinePaymentController::class, 'ipn'])
         ->name('client.payment.ipn')
         ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

    // Stripe Webhook
    Route::post('stripe/webhook', [OnlinePaymentController::class, 'stripeWebhook'])
         ->name('client.payment.stripe-webhook')
         ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

    // Payment success page — no auth needed (SSLCommerz redirects here after payment)
    Route::get('success-page/{ref}', [OnlinePaymentController::class, 'successPage'])
         ->name('client.payment.success-page');
});
