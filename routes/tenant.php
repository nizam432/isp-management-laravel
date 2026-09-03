<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SmsTemplateMappingController;
use App\Http\Controllers\TenantSmsController;
use App\Http\Controllers\MyResellerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
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
use App\Http\Controllers\Settings\PaymentGatewaySettingController;

// ═══════════════════════════════════════════════════════════
// routes/tenant.php — TENANT (ISP) DOMAIN ONLY
// ═══════════════════════════════════════════════════════════
// Everything here used to live directly in routes/web.php's big
// Route::middleware(['auth'])->group() — which had NO domain-based tenancy
// middleware at all. That meant visiting any tenant subdomain
// (e.g. demo.innovativeitbd.com) still queried the CENTRAL database,
// since Laravel never identified/switched to that tenant's own database.
//
// This file wraps the exact same routes with InitializeTenancyByDomain +
// PreventAccessFromCentralDomains, so each ISP's subdomain now correctly
// resolves to its own (pool-assigned) database.
//
// NOTE on inventory: the old web.php had TWO separate, overlapping sets of
// inventory routes — one defined inline (basic stock in/out + sales/
// purchases) and another via a full `require __DIR__.'/inventory.php'`
// (categories, products, vendors, purchases, sales, stock, reports —
// more complete). Both tried to register the same route names (e.g.
// inventory.purchases.index), which is exactly what caused the
// "Another route has already been assigned name [inventory.purchase-returns.show]"
// error seen earlier when running `route:cache`. Keeping ONLY the separate,
// more complete inventory.php here; the old inline inventory block has been
// removed entirely to avoid the duplicate.
// ═══════════════════════════════════════════════════════════

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // ── Root — redirect to this tenant's own login ──
    Route::get('/', fn() => redirect()->route('login'));

    // ── Tenant-Aware Storage File Serving ──
    // The old static `public/storage` symlink (or the empty leftover folder,
    // in this case) can only ever point at ONE tenant's files — it doesn't
    // follow Stancl Tenancy's per-request storage_path() switching. This
    // route lives inside the tenancy-initialized group above, so
    // Storage::disk('public') here always resolves against whichever
    // tenant's domain the request actually came in on. No `auth` middleware
    // on purpose — uploaded photos/logos need to be viewable without login
    // (e.g. on the login page itself).
    Route::get('/storage/{path}', function (string $path) {
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
        $mimeType = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type'  => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    })->where('path', '.*')->name('tenant.storage.show');

    // ── Login / Register / Password (Breeze) ──
    // Needs tenancy already initialized above, so it checks the RIGHT
    // tenant's `users` table.
    require __DIR__ . '/auth.php';
    require __DIR__ . '/reseller.php';

    // ── Profile ── (ProfileController exists with show()/update()/updatePassword(),
    // but had no routes registered at all — this is what caused /profile to 404).
    // Named 'profile.edit' (not 'profile.show') to match the standard Breeze
    // navigation partial convention, which links to route('profile.edit').
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.edit');
        Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
        // named differently from auth.php's own 'password.update' (Breeze's
        // built-in change-password route) to avoid a duplicate route name
        Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
              
    // ─────────────────────────────────────────────
    // Protected Routes (Login required, per-tenant)
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
        Route::prefix('support-categories')->name('support-categories.')->group(function () {
            Route::get('/',                              [SupportCategoryController::class, 'index'])  ->name('index')->middleware('can:support.category.view');
            Route::post('/',                             [SupportCategoryController::class, 'store'])  ->name('store')->middleware('can:support.category.create');
            Route::get('/{supportCategory}/edit',        [SupportCategoryController::class, 'edit'])   ->name('edit');
            Route::put('/{supportCategory}',             [SupportCategoryController::class, 'update']) ->name('update');
            Route::delete('/{supportCategory}',          [SupportCategoryController::class, 'destroy'])->name('destroy');
        });

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

        Route::prefix('support-history')->name('support-history.')->group(function () {
            Route::get('/',        [SupportHistoryController::class, 'index'])     ->name('index')->middleware('can:support.history.view');
            Route::get('/pdf',     [SupportHistoryController::class, 'exportPdf']) ->name('pdf');
            Route::get('/csv',     [SupportHistoryController::class, 'exportCsv']) ->name('csv');
        });

        // ── Agents ─────────────────────────────────
        Route::resource('agents', AgentController::class)->middleware([
            'index'   => 'can:agent.view',
            'show'    => 'can:agent.view',
            'create'  => 'can:agent.create',
            'store'   => 'can:agent.create',
            'edit'    => 'can:agent.edit',
            'update'  => 'can:agent.edit',
            'destroy' => 'can:agent.delete',
        ]);
        Route::post('agents/{agent}/toggle',         [AgentController::class, 'toggle'])        ->name('agents.toggle')        ->middleware('can:agent.edit');
        Route::post('agents/{agent}/pay-commission', [AgentController::class, 'payCommission']) ->name('agents.pay-commission') ->middleware('can:agent.edit');

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
            Route::get ('users',         [OltController::class, 'users'])     ->name('users');
            Route::get ('users/data',    [OltController::class, 'usersData']) ->name('users.data');
            Route::post('sync-all',      [OltController::class, 'syncAll'])   ->name('sync-all');
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
            Route::post('templates/mapping', [SmsTemplateMappingController::class, 'update'])->name('mapping.update')->middleware('can:sms.template.create');
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

        // ── Settings ───────────────────────────────
        Route::middleware(['auth', 'can:isp-admin'])->prefix('settings')->name('settings.')->group(function () {

            Route::get('general', [App\Http\Controllers\Settings\SettingController::class, 'index'])->name('general');
            Route::put('general', [App\Http\Controllers\Settings\SettingController::class, 'update'])->name('update');

            Route::get   ('zones',        [ZoneController::class, 'index'])  ->name('zones.index');
            Route::get   ('zones/data',   [ZoneController::class, 'data'])   ->name('zones.data');
            Route::post  ('zones',        [ZoneController::class, 'store'])  ->name('zones.store');
            Route::put   ('zones/{zone}', [ZoneController::class, 'update']) ->name('zones.update');
            Route::delete('zones/{zone}', [ZoneController::class, 'destroy'])->name('zones.destroy');

            Route::get   ('sub-zones',           [SubZoneController::class, 'index'])  ->name('sub-zones.index');
            Route::get   ('sub-zones/data',      [SubZoneController::class, 'data'])   ->name('sub-zones.data');
            Route::post  ('sub-zones',           [SubZoneController::class, 'store'])  ->name('sub-zones.store');
            Route::put   ('sub-zones/{subZone}', [SubZoneController::class, 'update']) ->name('sub-zones.update');
            Route::delete('sub-zones/{subZone}', [SubZoneController::class, 'destroy'])->name('sub-zones.destroy');

            Route::get   ('box',              [App\Http\Controllers\Settings\BoxController::class, 'index'])      ->name('box.index');
            Route::get   ('box/sub-zones',    [App\Http\Controllers\Settings\BoxController::class, 'getSubZones']) ->name('box.sub-zones');
            Route::post  ('box',              [App\Http\Controllers\Settings\BoxController::class, 'store'])      ->name('box.store');
            Route::get   ('box/{box}/edit',   [App\Http\Controllers\Settings\BoxController::class, 'edit'])       ->name('box.edit');
            Route::put   ('box/{box}',        [App\Http\Controllers\Settings\BoxController::class, 'update'])     ->name('box.update');
            Route::delete('box/{box}',        [App\Http\Controllers\Settings\BoxController::class, 'destroy'])    ->name('box.destroy');
            Route::post  ('box/{box}/toggle', [App\Http\Controllers\Settings\BoxController::class, 'toggle'])     ->name('box.toggle');

            Route::get   ('connection-types',                         [ConnectionTypeController::class, 'index'])  ->name('connection-types.index');
            Route::get   ('connection-types/data',                    [ConnectionTypeController::class, 'data'])   ->name('connection-types.data');
            Route::post  ('connection-types',                         [ConnectionTypeController::class, 'store'])  ->name('connection-types.store');
            Route::put   ('connection-types/{connectionType}',        [ConnectionTypeController::class, 'update']) ->name('connection-types.update');
            Route::post  ('connection-types/{connectionType}/toggle', [ConnectionTypeController::class, 'toggle']) ->name('connection-types.toggle');
            Route::delete('connection-types/{connectionType}',        [ConnectionTypeController::class, 'destroy'])->name('connection-types.destroy');

            Route::get   ('client-types',                    [ClientTypeController::class, 'index'])  ->name('client-types.index');
            Route::get   ('client-types/data',               [ClientTypeController::class, 'data'])   ->name('client-types.data');
            Route::post  ('client-types',                    [ClientTypeController::class, 'store'])  ->name('client-types.store');
            Route::put   ('client-types/{clientType}',       [ClientTypeController::class, 'update']) ->name('client-types.update');
            Route::post  ('client-types/{clientType}/toggle',[ClientTypeController::class, 'toggle']) ->name('client-types.toggle');
            Route::delete('client-types/{clientType}',       [ClientTypeController::class, 'destroy'])->name('client-types.destroy');

            Route::get   ('protocol-types',                        [ProtocolTypeController::class, 'index'])  ->name('protocol-types.index');
            Route::get   ('protocol-types/data',                   [ProtocolTypeController::class, 'data'])   ->name('protocol-types.data');
            Route::post  ('protocol-types',                        [ProtocolTypeController::class, 'store'])  ->name('protocol-types.store');
            Route::put   ('protocol-types/{protocolType}',         [ProtocolTypeController::class, 'update']) ->name('protocol-types.update');
            Route::post  ('protocol-types/{protocolType}/toggle',  [ProtocolTypeController::class, 'toggle']) ->name('protocol-types.toggle');
            Route::delete('protocol-types/{protocolType}',         [ProtocolTypeController::class, 'destroy'])->name('protocol-types.destroy');

            Route::get   ('olt-types',                  [OltTypeController::class, 'index'])  ->name('olt-types.index');
            Route::get   ('olt-types/data',             [OltTypeController::class, 'data'])   ->name('olt-types.data');
            Route::post  ('olt-types',                  [OltTypeController::class, 'store'])  ->name('olt-types.store');
            Route::put   ('olt-types/{oltType}',        [OltTypeController::class, 'update']) ->name('olt-types.update');
            Route::post  ('olt-types/{oltType}/toggle', [OltTypeController::class, 'toggle']) ->name('olt-types.toggle');
            Route::delete('olt-types/{oltType}',        [OltTypeController::class, 'destroy'])->name('olt-types.destroy');

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
            Route::get('reports/profit-loss',     [ExpenseController::class, 'profitLoss'])    ->name('profit-loss')->middleware('can:accounting.report.view');
            Route::get('reports/profit-loss/pdf', [ExpenseController::class, 'profitLossPdf']) ->name('profit-loss.pdf');
            Route::get('api/chart-data',          [ExpenseController::class, 'chartData'])     ->name('chart-data');
            Route::get('export/xlsx',             [ExpenseController::class, 'exportXlsx'])    ->name('export-xlsx');
            Route::get('export/pdf',              [ExpenseController::class, 'exportPdf'])     ->name('export-pdf');

            Route::get('/',               [ExpenseController::class, 'index'])  ->name('index')->middleware('can:accounting.expense.view');
            Route::get('/create',         [ExpenseController::class, 'create']) ->name('create')->middleware('can:accounting.expense.create');
            Route::post('/',              [ExpenseController::class, 'store'])  ->name('store')->middleware('can:accounting.expense.create');
            Route::get('/{expense}',      [ExpenseController::class, 'show'])   ->name('show');
            Route::get('/{expense}/edit-data', [ExpenseController::class, 'editData']) ->name('edit-data');
            Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])   ->name('edit');
            Route::put('/{expense}',      [ExpenseController::class, 'update']) ->name('update')->middleware('can:accounting.expense.edit');
            Route::get('/{expenseS}/edit-data', [ExpenseController::class, 'editData'])->name('expenses.edit-data');
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
            // Static/export routes BEFORE {income} to avoid route collision
            Route::get('/export/xlsx',        [IncomeController::class, 'exportXlsx'])      ->name('export.xlsx')->middleware('can:accounting.income.view');
            Route::get('/export/pdf',         [IncomeController::class, 'exportPdf'])       ->name('export.pdf')->middleware('can:accounting.income.view');

            Route::get('/',                   [IncomeController::class, 'index'])           ->name('index')->middleware('can:accounting.income.view');
            Route::post('/',                  [IncomeController::class, 'store'])           ->name('store')->middleware('can:accounting.income.create');
            Route::get('/{income}/edit-data', [IncomeController::class, 'editData'])        ->name('edit-data');
            Route::get('/{income}',           [IncomeController::class, 'show'])            ->name('show');
            Route::put('/{income}',           [IncomeController::class, 'update'])          ->name('update')->middleware('can:accounting.income.edit');
            Route::post('/{income}/void',     [IncomeController::class, 'void'])            ->name('void')->middleware('can:accounting.income.void');
            Route::delete('/{income}',        [IncomeController::class, 'destroy'])         ->name('destroy')->middleware('can:accounting.income.delete');
        });

        Route::prefix('accounting')->name('accounting.')->group(function () {
            Route::post('income-categories/quick-add',  [IncomeController::class,  'quickAddCategory']) ->name('income-categories.quick-add');
            Route::post('expense-categories/quick-add', [ExpenseController::class, 'quickAddCategory']) ->name('expense-categories.quick-add');
        });

        Route::get('accounting/dashboard', [AccountingController::class, 'dashboard'])->name('accounting.dashboard')->middleware('can:accounting.view');

        Route::prefix('income-categories')->name('income-categories.')->group(function () {
            Route::get('/',                     [IncomeController::class, 'categoriesIndex'])  ->name('index')->middleware('can:accounting.income.category.view');
            Route::post('/',                    [IncomeController::class, 'categoryStore'])    ->name('store')->middleware('can:accounting.income.category.create');
            Route::put('/{incomeCategory}',     [IncomeController::class, 'categoryUpdate'])   ->name('update');
            Route::delete('/{incomeCategory}',  [IncomeController::class, 'categoryDestroy'])  ->name('destroy');
        });

        // ── Bandwidth Buy Module ───────────────────────
        Route::prefix('bandwidth-buy')->name('bandwidth-buy.')->middleware('can:isp-admin')->group(function () {

            Route::prefix('provider')->name('provider.')->group(function () {
                Route::get('/',                    [BandwidthProviderController::class, 'index'])  ->name('index');
                Route::get('/create',              [BandwidthProviderController::class, 'create']) ->name('create');
                Route::post('/',                   [BandwidthProviderController::class, 'store'])  ->name('store');
                Route::get('/{provider}/edit',     [BandwidthProviderController::class, 'edit'])   ->name('edit');
                Route::put('/{provider}',          [BandwidthProviderController::class, 'update']) ->name('update');
                Route::delete('/{provider}',       [BandwidthProviderController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('service')->name('service.')->group(function () {
                Route::get('/',                    [BandwidthServiceController::class, 'index'])  ->name('index');
                Route::get('/create',              [BandwidthServiceController::class, 'create']) ->name('create');
                Route::post('/',                   [BandwidthServiceController::class, 'store'])  ->name('store');
                Route::get('/{service}/edit',      [BandwidthServiceController::class, 'edit'])   ->name('edit');
                Route::put('/{service}',           [BandwidthServiceController::class, 'update']) ->name('update');
            });

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

            Route::get('report', [BandwidthReportController::class, 'index'])->name('report');
            Route::get('report/datatables', [BandwidthReportController::class, 'datatables'])->name('report.datatables');

        }); // end bandwidth-buy

        // ── Bandwidth Sale Module ──────────────────
        Route::prefix('bandwidth-sale')->name('bandwidth-sale.')->middleware('can:isp-admin')->group(function () {

            Route::get('dashboard', fn() => redirect()->route('bandwidth-sale.customers.index'))->name('dashboard');

            Route::get ('customers/data',         [BwsCustomerController::class, 'data'])    ->name('customers.data');
            Route::get ('customers',              [BwsCustomerController::class, 'index'])   ->name('customers.index');
            Route::post('customers',              [BwsCustomerController::class, 'store'])   ->name('customers.store');
            Route::get ('customers/{customer}',   [BwsCustomerController::class, 'show'])    ->name('customers.show');
            Route::put ('customers/{customer}',   [BwsCustomerController::class, 'update'])  ->name('customers.update');
            Route::delete('customers/{customer}', [BwsCustomerController::class, 'destroy']) ->name('customers.destroy');

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

            Route::get ('invoices/{bwsInvoice}/receive', [BwsInvoiceController::class, 'receiveData'])  ->name('invoices.receive-data');
            Route::post('invoices/{bwsInvoice}/receive', [BwsInvoiceController::class, 'receiveStore']) ->name('invoices.receive');

            Route::post('payments/delete-selected',     [BwsInvoiceController::class, 'deleteSelected'])  ->name('payments.delete-selected');
            Route::post('payments/approve-selected',    [BwsInvoiceController::class, 'approveSelected']) ->name('payments.approve-selected');
            Route::post('payments/{payment}/void',      [BwsInvoiceController::class, 'voidPayment'])     ->name('payments.void');

            Route::get('daily-bill',          [BwsInvoiceController::class, 'dailyBill'])            ->name('daily-bill.index');
            Route::get('daily-bill/xlsx',     [BwsInvoiceController::class, 'dailyBillExportXlsx'])  ->name('daily-bill.xlsx');
            Route::get('daily-bill/pdf',      [BwsInvoiceController::class, 'dailyBillExportPdf'])   ->name('daily-bill.pdf');

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

            Route::get('income',      [IncomeExpenseReportController::class, 'incomeReport'])->name('income')->middleware('can:report.revenue.view');
            Route::get('income/pdf',  [IncomeExpenseReportController::class, 'exportIncomePdf'])->name('income.pdf')->middleware('can:report.revenue.view');
            Route::get('income/xlsx', [IncomeExpenseReportController::class, 'exportIncomeXlsx'])->name('income.xlsx')->middleware('can:report.revenue.view');

            Route::get('expense',      [IncomeExpenseReportController::class, 'expenseReport'])->name('expense')->middleware('can:report.revenue.view');
            Route::get('expense/pdf',  [IncomeExpenseReportController::class, 'exportExpensePdf'])->name('expense.pdf')->middleware('can:report.revenue.view');
            Route::get('expense/xlsx', [IncomeExpenseReportController::class, 'exportExpenseXlsx'])->name('expense.xlsx')->middleware('can:report.revenue.view');

            Route::get('customer',      [CustomerReportController::class, 'customerReport'])->name('customer')->middleware('can:report.revenue.view');
            Route::get('customer/pdf',  [CustomerReportController::class, 'exportCustomerPdf'])->name('customer.pdf')->middleware('can:report.revenue.view');
            Route::get('customer/xlsx', [CustomerReportController::class, 'exportCustomerXlsx'])->name('customer.xlsx')->middleware('can:report.revenue.view');

            Route::get('pop-wise',      [CustomerReportController::class, 'popWiseClients'])->name('pop-wise')->middleware('can:report.revenue.view');
            Route::get('pop-wise/pdf',  [CustomerReportController::class, 'exportPopWisePdf'])->name('pop-wise.pdf')->middleware('can:report.revenue.view');
            Route::get('pop-wise/xlsx', [CustomerReportController::class, 'exportPopWiseXlsx'])->name('pop-wise.xlsx')->middleware('can:report.revenue.view');

            Route::get('income-discount',      [BillCollectionReportController::class, 'incomeDiscount'])->name('income-discount')->middleware('can:report.revenue.view');
            Route::get('income-discount/pdf',  [BillCollectionReportController::class, 'exportIncomeDiscountPdf'])->name('income-discount.pdf')->middleware('can:report.revenue.view');
            Route::get('income-discount/xlsx', [BillCollectionReportController::class, 'exportIncomeDiscountXlsx'])->name('income-discount.xlsx')->middleware('can:report.revenue.view');

            Route::get('profit',      [BillCollectionReportController::class, 'profitReport'])->name('profit')->middleware('can:report.revenue.view');
            Route::get('profit/pdf',  [BillCollectionReportController::class, 'exportProfitPdf'])->name('profit.pdf')->middleware('can:report.revenue.view');
            Route::get('profit/xlsx', [BillCollectionReportController::class, 'exportProfitXlsx'])->name('profit.xlsx')->middleware('can:report.revenue.view');
        });


// ── MAC Reseller Management (ISP Admin) ───────────
// NOTE: this is the ISP-admin's own management of their MAC Resellers,
// separate from routes/reseller.php (which is the MAC Reseller's OWN
// portal login). Views already exist at resources/views/mac-reseller/.
Route::prefix('mac-reseller')->name('mac-reseller.')->middleware('can:isp-admin')->group(function () {

    // ── Main MAC Reseller CRUD (views expect 'mac-reseller.list.*') ──
    Route::prefix('list')->name('list.')->group(function () {
        Route::post('quick-add-zone',    [\App\Http\Controllers\MacReseller\MacResellerController::class, 'quickAddZone'])    ->name('quick-add-zone');
        Route::get ('get-upazilas',      [\App\Http\Controllers\MacReseller\MacResellerController::class, 'getUpazilas'])     ->name('get-upazilas');
        Route::post('quick-add-upazila', [\App\Http\Controllers\MacReseller\MacResellerController::class, 'quickAddUpazila']) ->name('quick-add-upazila');

        Route::get   ('/',                              [\App\Http\Controllers\MacReseller\MacResellerController::class, 'index'])  ->name('index');
        Route::get   ('/create',                        [\App\Http\Controllers\MacReseller\MacResellerController::class, 'create']) ->name('create');
        Route::post  ('/',                              [\App\Http\Controllers\MacReseller\MacResellerController::class, 'store'])  ->name('store');
        Route::get   ('/{macReseller}/edit',             [\App\Http\Controllers\MacReseller\MacResellerController::class, 'edit'])   ->name('edit');
        Route::put   ('/{macReseller}',                  [\App\Http\Controllers\MacReseller\MacResellerController::class, 'update']) ->name('update');
        Route::post  ('/{macReseller}/toggle-client',    [\App\Http\Controllers\MacReseller\MacResellerController::class, 'toggleClientEnabled']) ->name('toggle-client');
        Route::post  ('/{macReseller}/toggle-fund-start',[\App\Http\Controllers\MacReseller\MacResellerController::class, 'toggleFundStart'])     ->name('toggle-fund-start');
        Route::post  ('/{macReseller}/toggle-locked',    [\App\Http\Controllers\MacReseller\MacResellerController::class, 'toggleLocked'])         ->name('toggle-locked');
        Route::post  ('/{macReseller}/change-password',  [\App\Http\Controllers\MacReseller\MacResellerController::class, 'changePassword'])       ->name('change-password');
        Route::get   ('/{macReseller}/login-as',         [\App\Http\Controllers\MacReseller\MacResellerController::class, 'loginAs'])              ->name('login-as');
        
    
   });

    // ── Funding ─────────────────────────────────
    Route::prefix('funding')->name('funding.')->group(function () {
        Route::get ('/',                     [\App\Http\Controllers\MacReseller\MacResellerFundingController::class, 'index'])            ->name('index');
        Route::post('/',                     [\App\Http\Controllers\MacReseller\MacResellerFundingController::class, 'store'])            ->name('store');
        Route::post('/{funding}/paid',       [\App\Http\Controllers\MacReseller\MacResellerFundingController::class, 'markPaid'])          ->name('mark-paid');
        Route::post('/{funding}/refund',     [\App\Http\Controllers\MacReseller\MacResellerFundingController::class, 'refund'])            ->name('refund');
        Route::post('/{funding}/toggle-restrict', [\App\Http\Controllers\MacReseller\MacResellerFundingController::class, 'toggleRestrict'])->name('toggle-restrict');
        Route::post('/bulk-toggle-restrict', [\App\Http\Controllers\MacReseller\MacResellerFundingController::class, 'bulkToggleRestrict']) ->name('bulk-toggle-restrict');
        Route::get ('/history',              [\App\Http\Controllers\MacReseller\MacResellerFundingController::class, 'history'])           ->name('history');
        Route::get ('/download-pdf',         [\App\Http\Controllers\MacReseller\MacResellerFundingController::class, 'downloadPdf'])       ->name('download-pdf');
        Route::get ('/download-excel',       [\App\Http\Controllers\MacReseller\MacResellerFundingController::class, 'downloadExcel'])     ->name('download-excel');
        Route::post('/payment/{payment}/void', [\App\Http\Controllers\MacReseller\MacResellerFundingController::class, 'voidPayment'])->name('payment.void');
    });

    // ── Notices ─────────────────────────────────
    Route::prefix('notice')->name('notice.')->group(function () {
        Route::get   ('/',              [\App\Http\Controllers\MacReseller\MacResellerNoticeController::class, 'index'])  ->name('index');
        Route::post  ('/',              [\App\Http\Controllers\MacReseller\MacResellerNoticeController::class, 'store'])  ->name('store');
        Route::get   ('/{notice}/edit', [\App\Http\Controllers\MacReseller\MacResellerNoticeController::class, 'edit'])   ->name('edit');
        Route::put   ('/{notice}',      [\App\Http\Controllers\MacReseller\MacResellerNoticeController::class, 'update']) ->name('update');
        Route::delete('/{notice}',      [\App\Http\Controllers\MacReseller\MacResellerNoticeController::class, 'destroy'])->name('destroy');
        Route::post  ('/{notice}/toggle',[\App\Http\Controllers\MacReseller\MacResellerNoticeController::class, 'toggle']) ->name('toggle');
    });

    // ── Packages ────────────────────────────────
    Route::prefix('package')->name('package.')->group(function () {
        Route::get   ('/',               [\App\Http\Controllers\MacReseller\MacResellerPackageController::class, 'index'])  ->name('index');
        Route::post  ('/',               [\App\Http\Controllers\MacReseller\MacResellerPackageController::class, 'store'])  ->name('store');
        Route::get   ('/{package}/edit', [\App\Http\Controllers\MacReseller\MacResellerPackageController::class, 'edit'])   ->name('edit');
        Route::put   ('/{package}',      [\App\Http\Controllers\MacReseller\MacResellerPackageController::class, 'update']) ->name('update');
        Route::delete('/{package}',      [\App\Http\Controllers\MacReseller\MacResellerPackageController::class, 'destroy'])->name('destroy');
    });

    // ── Payment Gateway (Wallet) ────────────────
    Route::get('pgw', [\App\Http\Controllers\MacReseller\MacResellerPgwController::class, 'index'])->name('pgw.index');

    // ── Settlement ──────────────────────────────
    Route::prefix('settlement')->name('settlement.')->group(function () {
        Route::get ('/',                          [\App\Http\Controllers\MacReseller\MacResellerSettlementController::class, 'index'])            ->name('index');
        Route::post('/{macReseller}/settle',      [\App\Http\Controllers\MacReseller\MacResellerSettlementController::class, 'settle'])            ->name('settle');
        Route::get ('/pgw-transactions',          [\App\Http\Controllers\MacReseller\MacResellerSettlementController::class, 'pgwTransactions'])   ->name('pgw-transactions');
        Route::get ('/history',                   [\App\Http\Controllers\MacReseller\MacResellerSettlementController::class, 'settlementHistory']) ->name('history');
    });

    // ── Tariff ──────────────────────────────────
    Route::prefix('tariff')->name('tariff.')->group(function () {
        Route::get   ('/',                       [\App\Http\Controllers\MacReseller\MacResellerTariffController::class, 'index'])       ->name('index');
        Route::post  ('/',                       [\App\Http\Controllers\MacReseller\MacResellerTariffController::class, 'store'])       ->name('store');
        Route::get   ('/{tariff}',                [\App\Http\Controllers\MacReseller\MacResellerTariffController::class, 'show'])        ->name('show');
        Route::put   ('/{tariff}',                [\App\Http\Controllers\MacReseller\MacResellerTariffController::class, 'update'])      ->name('update');
        Route::put   ('/line/{line}',             [\App\Http\Controllers\MacReseller\MacResellerTariffController::class, 'updateLine'])  ->name('line.update');
        Route::delete('/line/{line}',             [\App\Http\Controllers\MacReseller\MacResellerTariffController::class, 'destroyLine']) ->name('line.destroy');
        Route::delete('/{tariff}',                [\App\Http\Controllers\MacReseller\MacResellerTariffController::class, 'destroy'])     ->name('destroy');
        Route::post  ('/{tariff}/toggle',         [\App\Http\Controllers\MacReseller\MacResellerTariffController::class, 'toggle'])       ->name('toggle');
        Route::post  ('/{tariff}/sync-mikrotik',  [\App\Http\Controllers\MacReseller\MacResellerTariffController::class, 'syncMikrotik'])  ->name('sync-mikrotik');
    });

    // ── Tutorials ───────────────────────────────
    Route::prefix('tutorial')->name('tutorial.')->group(function () {
        Route::get   ('/',                [\App\Http\Controllers\MacReseller\MacResellerTutorialController::class, 'index'])  ->name('index');
        Route::post  ('/',                [\App\Http\Controllers\MacReseller\MacResellerTutorialController::class, 'store'])  ->name('store');
        Route::get   ('/{tutorial}/edit', [\App\Http\Controllers\MacReseller\MacResellerTutorialController::class, 'edit'])   ->name('edit');
        Route::put   ('/{tutorial}',      [\App\Http\Controllers\MacReseller\MacResellerTutorialController::class, 'update']) ->name('update');
        Route::delete('/{tutorial}',      [\App\Http\Controllers\MacReseller\MacResellerTutorialController::class, 'destroy'])->name('destroy');
        Route::post  ('/{tutorial}/toggle',[\App\Http\Controllers\MacReseller\MacResellerTutorialController::class, 'toggle']) ->name('toggle');
    });
    
    }); // end mac-reseller


    }); // end auth

    // ── Inventory (own internal 'auth' middleware — kept as the SOLE
    // inventory route definition; the old inline duplicate has been removed) ──
    require __DIR__ . '/client.php';
    require __DIR__ . '/inventory.php';

}); // end tenancy group