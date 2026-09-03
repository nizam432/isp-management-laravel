<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventory\DashboardController;
use App\Http\Controllers\Inventory\ProductCategoryController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\StoreLocationController;
use App\Http\Controllers\Inventory\VendorController;
use App\Http\Controllers\Inventory\VendorContactController;
use App\Http\Controllers\Inventory\VendorDocumentController;
use App\Http\Controllers\Inventory\VendorLedgerController;
use App\Http\Controllers\Inventory\PurchaseController;
use App\Http\Controllers\Inventory\PurchasePaymentController;
use App\Http\Controllers\Inventory\PurchaseReturnController;
use App\Http\Controllers\Inventory\SaleController;
use App\Http\Controllers\Inventory\SalePaymentController;
use App\Http\Controllers\Inventory\SaleReturnController;
use App\Http\Controllers\Inventory\InternalConsumptionController;
use App\Http\Controllers\Inventory\StockTransferController;
use App\Http\Controllers\Inventory\StockTransactionController;
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Inventory\ClientDeviceAssignmentController;
use App\Http\Controllers\Inventory\ClientLedgerController;
use App\Http\Controllers\Inventory\ReportController;

Route::prefix('inventory')
    ->name('inventory.')
    ->middleware(['auth'])
    ->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Product Categories ────────────────────────────────────────
    Route::resource('categories', ProductCategoryController::class);

    // ── Products ──────────────────────────────────────────────────
    Route::resource('products', ProductController::class);

    // ── Store Locations ───────────────────────────────────────────
    Route::resource('locations', StoreLocationController::class);
    Route::post('locations/{location}/toggle', [StoreLocationController::class, 'toggle'])
         ->name('locations.toggle');

    // ── Vendors ───────────────────────────────────────────────────
    Route::resource('vendors', VendorController::class);
    Route::get('vendors/{vendor}/ledger', [VendorController::class, 'ledger'])
         ->name('vendors.ledger');
    Route::post('vendors/{vendor}/contacts', [VendorContactController::class, 'store'])
         ->name('vendors.contacts.store');
    Route::put('vendors/{vendor}/contacts/{contact}', [VendorContactController::class, 'update'])
         ->name('vendors.contacts.update');
    Route::delete('vendors/{vendor}/contacts/{contact}', [VendorContactController::class, 'destroy'])
         ->name('vendors.contacts.destroy');
    Route::post('vendors/{vendor}/documents', [VendorDocumentController::class, 'store'])
         ->name('vendors.documents.store');
    Route::delete('vendors/{vendor}/documents/{document}', [VendorDocumentController::class, 'destroy'])
         ->name('vendors.documents.destroy');

    // ── Purchases ─────────────────────────────────────────────────
    Route::get('purchases/export-xlsx', [PurchaseController::class, 'exportXlsx'])
         ->name('purchases.export-xlsx');
    Route::get('purchases/export-pdf', [PurchaseController::class, 'exportPdf'])
         ->name('purchases.export-pdf');
         // Static route আগে
Route::get('purchase-returns/purchase/{purchase}/items', [PurchaseReturnController::class, 'purchaseItems'])
     ->name('purchase-returns.purchase-items');

    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::get('purchases/{purchase}/detail', [PurchaseController::class, 'detail'])
         ->name('purchases.detail');
    Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive'])
         ->name('purchases.receive');
    Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])
         ->name('purchases.cancel');
    Route::post('purchases/{purchase}/payment', [PurchasePaymentController::class, 'store'])
         ->name('purchases.payment.store');
    Route::post('purchases/{purchase}/payment/{payment}/void', [PurchasePaymentController::class, 'void'])
         ->name('purchases.payment.void');

    // ── Purchase Returns ──────────────────────────────────────────
    Route::resource('purchase-returns', PurchaseReturnController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('purchase-returns/{purchaseReturn}/approve', [PurchaseReturnController::class, 'approve'])
         ->name('purchase-returns.approve');
    Route::post('purchase-returns/{purchaseReturn}/cancel', [PurchaseReturnController::class, 'cancel'])
         ->name('purchase-returns.cancel');
        Route::get('purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])
     ->name('purchase-returns.show');
    // ── Sales ─────────────────────────────────────────────────────
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('sales/{sale}/confirm', [SaleController::class, 'confirm'])
         ->name('sales.confirm');
    Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])
         ->name('sales.cancel');
    Route::post('sales/{sale}/payment', [SalePaymentController::class, 'store'])
         ->name('sales.payment.store');
    Route::post('sales/{sale}/payment/{payment}/void', [SalePaymentController::class, 'void'])
         ->name('sales.payment.void');

    // ── Sale Returns ──────────────────────────────────────────────
    Route::get('sale-returns/sale/{sale}/items', [SaleReturnController::class, 'saleItems'])
         ->name('sale-returns.sale-items');
    Route::resource('sale-returns', SaleReturnController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('sale-returns/{saleReturn}/approve', [SaleReturnController::class, 'approve'])
         ->name('sale-returns.approve');
    Route::post('sale-returns/{saleReturn}/cancel', [SaleReturnController::class, 'cancel'])
         ->name('sale-returns.cancel');
         Route::get('sale-returns/{saleReturn}', [SaleReturnController::class, 'show'])
     ->name('sale-returns.show');

    // ── Internal Consumptions ─────────────────────────────────────
    Route::resource('consumptions', InternalConsumptionController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('consumptions/{consumption}/confirm', [InternalConsumptionController::class, 'confirm'])
         ->name('consumptions.confirm');
    Route::post('consumptions/{consumption}/void', [InternalConsumptionController::class, 'void'])
         ->name('consumptions.void');

    // ── Stock Transfers ───────────────────────────────────────────
    Route::resource('transfers', StockTransferController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('transfers/{transfer}/confirm', [StockTransferController::class, 'confirm'])
         ->name('transfers.confirm');
    Route::post('transfers/{transfer}/cancel', [StockTransferController::class, 'cancel'])
         ->name('transfers.cancel');

    // ── Stock ─────────────────────────────────────────────────────
    Route::get('stock', [StockTransactionController::class, 'index'])->name('stock.index');
    Route::get('stock/transactions', [StockTransactionController::class, 'index'])->name('stock.transactions');
    Route::get('stock/adjustment', [StockAdjustmentController::class, 'index'])->name('stock.adjustment');
    Route::post('stock/adjustment', [StockAdjustmentController::class, 'store'])->name('stock.adjustment.store');

    // ── Client Device Assignments ─────────────────────────────────
    Route::resource('assignments', ClientDeviceAssignmentController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('assignments/{assignment}/return', [ClientDeviceAssignmentController::class, 'return'])
         ->name('assignments.return');

    // ── Client Ledger ─────────────────────────────────────────────
    Route::get('client-ledger', [ClientLedgerController::class, 'index'])->name('client-ledger.index');
    Route::get('client-ledger/{customer}', [ClientLedgerController::class, 'show'])->name('client-ledger.show');

    // ── Reports ───────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('stock',         [ReportController::class, 'stock'])->name('stock');
        Route::get('purchase',      [ReportController::class, 'purchase'])->name('purchase');
        Route::get('sale',          [ReportController::class, 'sale'])->name('sale');
        Route::get('consumption',   [ReportController::class, 'consumption'])->name('consumption');
        Route::get('profit-loss',   [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('low-stock',     [ReportController::class, 'lowStock'])->name('low-stock');
        Route::get('vendor-ledger', [ReportController::class, 'vendorLedger'])->name('vendor-ledger');
        Route::get('client-ledger', [ReportController::class, 'clientLedger'])->name('client-ledger');
    });
});