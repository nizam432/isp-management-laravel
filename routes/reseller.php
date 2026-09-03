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
use App\Http\Controllers\Reseller\ResellerPaymentController;
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
        Route::get('/back-to-admin', [\App\Http\Controllers\Reseller\ResellerImpersonationController::class, 'backToAdmin'])->name('back-to-admin');

        // ── প্রতিটা menu — admin এর checkbox (allowed_menus) অনুযায়ী access ──
        Route::middleware('reseller.menu:CONFIGURATION')->prefix('configuration')->name('configuration.')->group(function () {
            Route::get('/',          [ResellerConfigurationController::class, 'index'])->name('index');
            Route::put('/',          [ResellerConfigurationController::class, 'update'])->name('update');
            Route::put('/password',  [ResellerConfigurationController::class, 'updatePassword'])->name('password');

            // ── Zone (reseller's own scoped zones) ──
            Route::prefix('zone')->name('zone.')->group(function () {
                Route::get('/',            [\App\Http\Controllers\Reseller\ResellerZoneController::class, 'index'])  ->name('index');
                Route::post('/',           [\App\Http\Controllers\Reseller\ResellerZoneController::class, 'store'])  ->name('store');
                Route::put('/{zone}',      [\App\Http\Controllers\Reseller\ResellerZoneController::class, 'update']) ->name('update');
                Route::post('/{zone}/toggle', [\App\Http\Controllers\Reseller\ResellerZoneController::class, 'toggle'])->name('toggle');
                Route::delete('/{zone}',   [\App\Http\Controllers\Reseller\ResellerZoneController::class, 'destroy'])->name('destroy');
            });

            // ── Sub Zone (reseller's own scoped sub-zones, each tied to a Zone) ──
            Route::prefix('subzone')->name('subzone.')->group(function () {
                Route::get('/',               [\App\Http\Controllers\Reseller\ResellerSubZoneController::class, 'index'])  ->name('index');
                Route::post('/',              [\App\Http\Controllers\Reseller\ResellerSubZoneController::class, 'store'])  ->name('store');
                Route::put('/{subzone}',      [\App\Http\Controllers\Reseller\ResellerSubZoneController::class, 'update']) ->name('update');
                Route::post('/{subzone}/toggle', [\App\Http\Controllers\Reseller\ResellerSubZoneController::class, 'toggle'])->name('toggle');
                Route::delete('/{subzone}',   [\App\Http\Controllers\Reseller\ResellerSubZoneController::class, 'destroy'])->name('destroy');
            });

            // ── Department (reseller's own scoped departments) ──
            Route::prefix('department')->name('department.')->group(function () {
                Route::get('/',                  [\App\Http\Controllers\Reseller\ResellerDepartmentController::class, 'index'])  ->name('index');
                Route::post('/',                 [\App\Http\Controllers\Reseller\ResellerDepartmentController::class, 'store'])  ->name('store');
                Route::put('/{department}',      [\App\Http\Controllers\Reseller\ResellerDepartmentController::class, 'update']) ->name('update');
                Route::post('/{department}/toggle', [\App\Http\Controllers\Reseller\ResellerDepartmentController::class, 'toggle'])->name('toggle');
                Route::delete('/{department}',   [\App\Http\Controllers\Reseller\ResellerDepartmentController::class, 'destroy'])->name('destroy');
            });

            // ── Designation (reseller's own scoped designations) ──
            Route::prefix('designation')->name('designation.')->group(function () {
                Route::get('/',                    [\App\Http\Controllers\Reseller\ResellerDesignationController::class, 'index'])  ->name('index');
                Route::post('/',                   [\App\Http\Controllers\Reseller\ResellerDesignationController::class, 'store'])  ->name('store');
                Route::put('/{designation}',       [\App\Http\Controllers\Reseller\ResellerDesignationController::class, 'update']) ->name('update');
                Route::post('/{designation}/toggle',[\App\Http\Controllers\Reseller\ResellerDesignationController::class, 'toggle'])->name('toggle');
                Route::delete('/{designation}',    [\App\Http\Controllers\Reseller\ResellerDesignationController::class, 'destroy'])->name('destroy');
            });

            // ── Box (reseller's own scoped boxes) ──
            Route::prefix('box')->name('box.')->group(function () {
                Route::get('/',            [\App\Http\Controllers\Reseller\ResellerBoxController::class, 'index'])  ->name('index');
                Route::post('/',           [\App\Http\Controllers\Reseller\ResellerBoxController::class, 'store'])  ->name('store');
                Route::put('/{box}',       [\App\Http\Controllers\Reseller\ResellerBoxController::class, 'update']) ->name('update');
                Route::post('/{box}/toggle',[\App\Http\Controllers\Reseller\ResellerBoxController::class, 'toggle'])->name('toggle');
                Route::delete('/{box}',    [\App\Http\Controllers\Reseller\ResellerBoxController::class, 'destroy'])->name('destroy');
            });

            // ── Package (read-only list from reseller's assigned Tariff; only Selling Rate is editable) ──
            Route::prefix('package')->name('package.')->group(function () {
                Route::get('/',                             [\App\Http\Controllers\Reseller\ResellerPackageController::class, 'index'])              ->name('index');
                Route::put('/{package}/selling-rate',       [\App\Http\Controllers\Reseller\ResellerPackageController::class, 'updateSellingRate']) ->name('selling-rate');
            });
        });

        Route::middleware('reseller.menu:MIKROTIK CLIENT')->prefix('mikrotik-client')->name('mikrotik-client.')->group(function () {
            Route::get('/',                    [ResellerMikrotikClientController::class, 'index'])     ->name('index');
            Route::post('/{client}/disconnect', [ResellerMikrotikClientController::class, 'disconnect'])->name('disconnect');
            // ── placeholder sub-page (build controller method later) ──
            Route::get('/bulk-import',          [\App\Http\Controllers\Reseller\ResellerBulkImportController::class, 'index'])    ->name('bulk-import');
            Route::post('/bulk-import/preview', [\App\Http\Controllers\Reseller\ResellerBulkImportController::class, 'preview'])  ->name('bulk-import.preview');
            Route::post('/bulk-import/execute', [\App\Http\Controllers\Reseller\ResellerBulkImportController::class, 'import'])   ->name('bulk-import.execute');
            Route::get('/bulk-import/template', [\App\Http\Controllers\Reseller\ResellerBulkImportController::class, 'downloadTemplate'])->name('bulk-import.template');
        });

        Route::middleware('reseller.menu:EMPLOYEES')->prefix('employees')->name('employees.')->group(function () {
            Route::get('/',            [ResellerEmployeeController::class, 'index'])  ->name('index');
            Route::post('/',           [ResellerEmployeeController::class, 'store'])  ->name('store');
            Route::get('/{employee}',  [ResellerEmployeeController::class, 'edit'])   ->name('edit');
            Route::put('/{employee}',  [ResellerEmployeeController::class, 'update']) ->name('update');
            Route::delete('/{employee}', [ResellerEmployeeController::class, 'destroy'])->name('destroy');
            Route::post('/{employee}/toggle', [ResellerEmployeeController::class, 'toggle'])->name('toggle');
            // ── placeholder sub-pages (build controller methods later) ──
            Route::view('/create', 'reseller.placeholder', ['pageTitle' => 'Add Employee'])->name('create');
            Route::view('/salary', 'reseller.placeholder', ['pageTitle' => 'Salary'])->name('salary');
        });

        // ── CLIENT menu — এখন real controller ব্যবহার করছে ──
        Route::middleware('reseller.menu:CLIENT')->prefix('client')->name('client.')->group(function () {
            Route::get('/',         [ResellerClientController::class, 'index'])->name('index');

            // ── static routes must come BEFORE /{client} or "create" etc. would be
            //    swallowed as a route-model-binding id and 404 ──
            Route::get('/create',  [ResellerClientController::class, 'create'])->name('create');
            Route::post('/',       [ResellerClientController::class, 'store']) ->name('store');

            Route::post('/quick-add/zone',        [ResellerClientController::class, 'quickAddZone'])       ->name('quick-add.zone');
            Route::post('/quick-add/client-type', [ResellerClientController::class, 'quickAddClientType']) ->name('quick-add.client-type');

            Route::get('/{client}/mikrotik-info',    [ResellerClientController::class, 'mikrotikInfo'])   ->name('mikrotik-info');
            Route::post('/{client}/mikrotik-suspend',[ResellerClientController::class, 'mikrotikSuspend']) ->name('mikrotik-suspend');
            Route::post('/{client}/mikrotik-enable', [ResellerClientController::class, 'mikrotikEnable'])  ->name('mikrotik-enable');

            // ── placeholder sub-pages (build controller methods later) ──
            Route::view('/left',           'reseller.placeholder', ['pageTitle' => 'Left Client'])       ->name('left');
            Route::view('/scheduler',      'reseller.placeholder', ['pageTitle' => 'Scheduler'])         ->name('scheduler');
            Route::view('/change-request', 'reseller.placeholder', ['pageTitle' => 'Change Request'])    ->name('change-request');
            Route::view('/portal-manage',  'reseller.placeholder', ['pageTitle' => 'Portal Manage'])     ->name('portal-manage');

            Route::get('/{client}', [ResellerClientController::class, 'show'])->name('show');
            Route::get('/{client}/edit', [ResellerClientController::class, 'edit'])->name('edit');
            Route::put('/{client}', [ResellerClientController::class, 'update'])->name('update');
            Route::patch('/{client}/status', [ResellerClientController::class, 'updateStatus'])->name('status');
        });

        Route::middleware('reseller.menu:BILLING')->prefix('billing')->name('billing.')->group(function () {
            Route::get('/',        [ResellerBillingController::class, 'index'])->name('index');

            // ── static routes must come BEFORE /{invoice} or they'd be
            //    swallowed as a route-model-binding id and 404 ──
            Route::get('/create',  [ResellerBillingController::class, 'create'])->name('create');
            Route::post('/',       [ResellerBillingController::class, 'store']) ->name('store');
            Route::get('/customer-due/{customer}', [ResellerBillingController::class, 'customerDue'])   ->name('customer-due');
            Route::post('/bulk-generate',          [ResellerBillingController::class, 'bulkGenerate'])  ->name('bulk-generate');
            Route::post('/bulk-delete',            [ResellerBillingController::class, 'bulkDelete'])    ->name('bulk-delete');

            // ── placeholder sub-page (build controller method later) ──
            Route::view('/daily',  'reseller.placeholder', ['pageTitle' => 'Daily Bill Collection'])->name('daily');

            Route::get('/{invoice}/pdf', [ResellerBillingController::class, 'pdf']) ->name('pdf');
            Route::get('/{invoice}', [ResellerBillingController::class, 'show'])->name('show');
        });

        Route::middleware('reseller.menu:BILLING')->prefix('payment')->name('payment.')->group(function () {
            Route::get('/',                  [ResellerPaymentController::class, 'index'])       ->name('index');
            Route::get('/collect',           [ResellerPaymentController::class, 'collectPage']) ->name('collect');
            Route::post('/collect',          [ResellerPaymentController::class, 'collectStore']) ->name('collect.store');
            Route::get('/customer/{customer}/due', [ResellerPaymentController::class, 'customerDue'])->name('customer-due');
            Route::post('/{payment}/void',   [ResellerPaymentController::class, 'void'])         ->name('void');
        });

        Route::middleware('reseller.menu:MONITORING')->prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/', [ResellerMonitoringController::class, 'index'])->name('index');
        });

        Route::middleware('reseller.menu:CLIENT SUPPORT')->prefix('client-support')->name('client-support.')->group(function () {
            Route::get('/',                [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'index'])        ->name('index');
            Route::get('/customer-info',   [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'customerInfo']) ->name('customer-info');
            Route::post('/',               [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'store'])        ->name('store');
            Route::get('/{ticket}/edit',   [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'edit'])         ->name('edit');
            Route::put('/{ticket}',        [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'update'])       ->name('update');
            Route::delete('/{ticket}',     [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'destroy'])      ->name('destroy');
            Route::get('/{ticket}/chat',   [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'chat'])         ->name('chat');
            Route::post('/{ticket}/chat/reply',    [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'chatReply'])   ->name('chat.reply');
            Route::get('/{ticket}/chat/messages',  [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'chatMessages']) ->name('chat.messages');
            Route::get('/{ticket}/mikrotik-status', [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'mikrotikStatus'])->name('mikrotik-status');
            Route::post('/{ticket}/solve',    [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'solve'])    ->name('solve');
            Route::post('/{ticket}/reassign', [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'reassign']) ->name('reassign');
            Route::get('/departments/{department}/employees', [\App\Http\Controllers\Reseller\ResellerClientSupportController::class, 'getEmployees'])->name('departments.employees');

            // ── Problem Category ──
            Route::prefix('category')->name('category.')->group(function () {
                Route::get('/',                     [\App\Http\Controllers\Reseller\ResellerSupportCategoryController::class, 'index'])  ->name('index');
                Route::post('/',                    [\App\Http\Controllers\Reseller\ResellerSupportCategoryController::class, 'store'])  ->name('store');
                Route::get('/{supportCategory}/edit',[\App\Http\Controllers\Reseller\ResellerSupportCategoryController::class, 'edit'])   ->name('edit');
                Route::put('/{supportCategory}',    [\App\Http\Controllers\Reseller\ResellerSupportCategoryController::class, 'update']) ->name('update');
                Route::delete('/{supportCategory}', [\App\Http\Controllers\Reseller\ResellerSupportCategoryController::class, 'destroy'])->name('destroy');
            });

            // ── Monthly Complain List — not yet built, still placeholder ──
            Route::get('/history',      [\App\Http\Controllers\Reseller\ResellerSupportHistoryController::class, 'index'])    ->name('monthly');
            Route::get('/history/pdf',  [\App\Http\Controllers\Reseller\ResellerSupportHistoryController::class, 'exportPdf'])->name('history.pdf');
            Route::get('/history/csv',  [\App\Http\Controllers\Reseller\ResellerSupportHistoryController::class, 'exportCsv'])->name('history.csv');
        });

        Route::middleware('reseller.menu:SMS SERVICE')->prefix('sms-service')->name('sms-service.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Reseller\ResellerSmsController::class, 'index'])->name('index');

            // ── Gateway Settings ──
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/',                [\App\Http\Controllers\Reseller\ResellerSmsSettingController::class, 'index']) ->name('index');
                Route::post('/{slug}/save',    [\App\Http\Controllers\Reseller\ResellerSmsSettingController::class, 'save'])   ->name('save');
                Route::post('/{slug}/toggle',  [\App\Http\Controllers\Reseller\ResellerSmsSettingController::class, 'toggle']) ->name('toggle');
            });

            // ── Templates ──
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/',             [\App\Http\Controllers\Reseller\ResellerSmsTemplateController::class, 'index'])  ->name('index');
                Route::post('/',            [\App\Http\Controllers\Reseller\ResellerSmsTemplateController::class, 'store'])  ->name('store');
                Route::put('/{template}',   [\App\Http\Controllers\Reseller\ResellerSmsTemplateController::class, 'update']) ->name('update');
                Route::delete('/{template}',[\App\Http\Controllers\Reseller\ResellerSmsTemplateController::class, 'destroy'])->name('destroy');
                Route::post('/{template}/toggle', [\App\Http\Controllers\Reseller\ResellerSmsTemplateController::class, 'toggle'])->name('toggle');
            });
            Route::put('/mapping', [\App\Http\Controllers\Reseller\ResellerSmsTemplateMappingController::class, 'update'])->name('mapping.update');

            // ── Send SMS ──
            Route::get('/send',      [\App\Http\Controllers\Reseller\ResellerSmsController::class, 'sendPage']) ->name('send');
            Route::post('/send/test',[\App\Http\Controllers\Reseller\ResellerSmsController::class, 'sendTest']) ->name('send.test');
            Route::post('/send/bulk',[\App\Http\Controllers\Reseller\ResellerSmsController::class, 'sendBulk']) ->name('send.bulk');

            // ── SMS Reports ──
            Route::get('/reports', [\App\Http\Controllers\Reseller\ResellerSmsReportController::class, 'index'])->name('reports');
        });

        Route::middleware('reseller.menu:REPORT')->prefix('report')->name('report.')->group(function () {
            Route::get('/', [ResellerReportController::class, 'index'])->name('index');
            Route::get('/btrc',            [ResellerReportController::class, 'btrc'])          ->name('btrc');
            Route::get('/status-history',  [ResellerReportController::class, 'statusHistory'])  ->name('status-history');
            Route::get('/bill-collection', [ResellerReportController::class, 'billCollection']) ->name('bill-collection');
            Route::get('/messages',        [ResellerReportController::class, 'messages'])       ->name('messages');
        });

        Route::middleware('reseller.menu:FUND HISTORY')->prefix('fund-history')->name('fund-history.')->group(function () {
            Route::get('/', [ResellerFundHistoryController::class, 'index'])->name('index');
            // ── placeholder sub-pages (build controller methods later) ──
            Route::view('/debit',  'reseller.placeholder', ['pageTitle' => 'Debit History']) ->name('debit');
            Route::view('/credit', 'reseller.placeholder', ['pageTitle' => 'Credit History'])->name('credit');
        });

        Route::prefix('hr')->name('hr.')->group(function () {
            Route::prefix('department')->name('department.')->group(function () {
                Route::get('/',             [\App\Http\Controllers\Reseller\ResellerHrDepartmentController::class, 'index'])  ->name('index');
                Route::post('/',            [\App\Http\Controllers\Reseller\ResellerHrDepartmentController::class, 'store'])  ->name('store');
                Route::put('/{department}', [\App\Http\Controllers\Reseller\ResellerHrDepartmentController::class, 'update']) ->name('update');
                Route::post('/{department}/toggle', [\App\Http\Controllers\Reseller\ResellerHrDepartmentController::class, 'toggle'])->name('toggle');
                Route::delete('/{department}', [\App\Http\Controllers\Reseller\ResellerHrDepartmentController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('position')->name('position.')->group(function () {
                Route::get('/',           [\App\Http\Controllers\Reseller\ResellerHrPositionController::class, 'index'])  ->name('index');
                Route::post('/',          [\App\Http\Controllers\Reseller\ResellerHrPositionController::class, 'store'])  ->name('store');
                Route::put('/{position}', [\App\Http\Controllers\Reseller\ResellerHrPositionController::class, 'update']) ->name('update');
                Route::post('/{position}/toggle', [\App\Http\Controllers\Reseller\ResellerHrPositionController::class, 'toggle'])->name('toggle');
                Route::delete('/{position}', [\App\Http\Controllers\Reseller\ResellerHrPositionController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('salary-head')->name('salary-head.')->group(function () {
                Route::get('/',             [\App\Http\Controllers\Reseller\ResellerHrSalaryHeadController::class, 'index'])  ->name('index');
                Route::post('/',            [\App\Http\Controllers\Reseller\ResellerHrSalaryHeadController::class, 'store'])  ->name('store');
                Route::put('/{salaryHead}', [\App\Http\Controllers\Reseller\ResellerHrSalaryHeadController::class, 'update']) ->name('update');
                Route::post('/{salaryHead}/toggle', [\App\Http\Controllers\Reseller\ResellerHrSalaryHeadController::class, 'toggle'])->name('toggle');
                Route::delete('/{salaryHead}', [\App\Http\Controllers\Reseller\ResellerHrSalaryHeadController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('employee')->name('employee.')->group(function () {
                Route::get('/',       [\App\Http\Controllers\Reseller\ResellerHrEmployeeController::class, 'index'])  ->name('index');
                Route::get('/create', [\App\Http\Controllers\Reseller\ResellerHrEmployeeController::class, 'create']) ->name('create');
                Route::post('/',      [\App\Http\Controllers\Reseller\ResellerHrEmployeeController::class, 'store'])  ->name('store');
                Route::get('/{employee}/edit', [\App\Http\Controllers\Reseller\ResellerHrEmployeeController::class, 'edit'])  ->name('edit');
                Route::put('/{employee}',      [\App\Http\Controllers\Reseller\ResellerHrEmployeeController::class, 'update'])->name('update');
            });

            Route::prefix('payroll')->name('payroll.')->group(function () {
                Route::get('/',            [\App\Http\Controllers\Reseller\ResellerPayrollController::class, 'index'])  ->name('index');
                Route::get('/create',      [\App\Http\Controllers\Reseller\ResellerPayrollController::class, 'create']) ->name('create');
                Route::post('/',           [\App\Http\Controllers\Reseller\ResellerPayrollController::class, 'store'])  ->name('store');
                Route::get('/{payroll}',   [\App\Http\Controllers\Reseller\ResellerPayrollController::class, 'show'])   ->name('show');
                Route::get('/{payroll}/edit', [\App\Http\Controllers\Reseller\ResellerPayrollController::class, 'edit'])  ->name('edit');
                Route::put('/{payroll}',      [\App\Http\Controllers\Reseller\ResellerPayrollController::class, 'update'])->name('update');
                Route::post('/{payroll}/pay', [\App\Http\Controllers\Reseller\ResellerPayrollController::class, 'pay'])->name('pay');
                Route::post('/{payroll}/payment/{payment}/void', [\App\Http\Controllers\Reseller\ResellerPayrollController::class, 'voidPayment'])->name('payment.void');
            });

            Route::prefix('salary-advance')->name('salary-advance.')->group(function () {
                Route::get('/',       [\App\Http\Controllers\Reseller\ResellerSalaryAdvanceController::class, 'index'])  ->name('index');
                Route::get('/create', [\App\Http\Controllers\Reseller\ResellerSalaryAdvanceController::class, 'create']) ->name('create');
                Route::post('/',      [\App\Http\Controllers\Reseller\ResellerSalaryAdvanceController::class, 'store'])  ->name('store');
            });

            Route::prefix('leave-type')->name('leave-type.')->group(function () {
                Route::get('/',             [\App\Http\Controllers\Reseller\ResellerHrLeaveTypeController::class, 'index'])  ->name('index');
                Route::post('/',            [\App\Http\Controllers\Reseller\ResellerHrLeaveTypeController::class, 'store'])  ->name('store');
                Route::put('/{leaveType}',  [\App\Http\Controllers\Reseller\ResellerHrLeaveTypeController::class, 'update']) ->name('update');
                Route::post('/{leaveType}/toggle', [\App\Http\Controllers\Reseller\ResellerHrLeaveTypeController::class, 'toggle'])->name('toggle');
                Route::delete('/{leaveType}', [\App\Http\Controllers\Reseller\ResellerHrLeaveTypeController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('leave-application')->name('leave-application.')->group(function () {
                Route::get('/',         [\App\Http\Controllers\Reseller\ResellerLeaveApplicationController::class, 'index'])  ->name('index');
                Route::get('/create',   [\App\Http\Controllers\Reseller\ResellerLeaveApplicationController::class, 'create']) ->name('create');
                Route::post('/',        [\App\Http\Controllers\Reseller\ResellerLeaveApplicationController::class, 'store'])  ->name('store');
                Route::get('/balance',  [\App\Http\Controllers\Reseller\ResellerLeaveApplicationController::class, 'balance'])->name('balance');
                Route::post('/{leave}/approve', [\App\Http\Controllers\Reseller\ResellerLeaveApplicationController::class, 'approve'])->name('approve');
                Route::post('/{leave}/reject',  [\App\Http\Controllers\Reseller\ResellerLeaveApplicationController::class, 'reject']) ->name('reject');
            });
        });

        Route::middleware('reseller.menu:TUTORIALS')->prefix('tutorials')->name('tutorials.')->group(function () {
            Route::get('/', [ResellerTutorialController::class, 'index'])->name('index');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Reseller\ResellerSettingController::class, 'index']) ->name('index');
            Route::put('/', [\App\Http\Controllers\Reseller\ResellerSettingController::class, 'update'])->name('update');

            Route::post('/payment-gateways/{slug}/save',   [\App\Http\Controllers\Reseller\ResellerPaymentGatewayController::class, 'save'])  ->name('payment-gateways.save');
            Route::post('/payment-gateways/{slug}/toggle', [\App\Http\Controllers\Reseller\ResellerPaymentGatewayController::class, 'toggle'])->name('payment-gateways.toggle');
        });

    });

});