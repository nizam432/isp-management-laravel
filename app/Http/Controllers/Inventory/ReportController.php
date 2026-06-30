<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductCategory;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\Sale;
use App\Models\Inventory\SaleItem;
use App\Models\Inventory\InternalConsumption;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\Vendor;
use App\Models\Inventory\ClientLedger;
use App\Models\Customer;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Current Stock Report
    public function stock(Request $request)
    {
        $products = Product::with('category', 'locationStocks.location')
                        ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
                        ->when($request->low_stock, fn($q) => $q->lowStock())
                        ->get();

        $categories = ProductCategory::orderBy('name')->get();

        return view('inventory.reports.stock', compact('products', 'categories'));
    }

    // Purchase Report
    public function purchase(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        $purchases = Purchase::with('vendor', 'location')
                        ->when($request->vendor_id, fn($q) => $q->where('vendor_id', $request->vendor_id))
                        ->when($request->status, fn($q) => $q->where('status', $request->status))
                        ->when($request->from, fn($q) => $q->whereDate('purchase_date', '>=', $request->from))
                        ->when($request->to, fn($q) => $q->whereDate('purchase_date', '<=', $request->to))
                        ->latest('purchase_date')
                        ->get();

        $summary = [
            'total_amount' => $purchases->sum('total_amount'),
            'total_paid'   => $purchases->sum('paid_amount'),
            'total_due'    => $purchases->sum('due_amount'),
        ];

        $vendors = Vendor::active()->get();

        return view('inventory.reports.purchase', compact('purchases', 'summary', 'vendors'));
    }

    // Sale Report
    public function sale(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        $sales = Sale::with('client', 'location')
                    ->when($request->status, fn($q) => $q->where('status', $request->status))
                    ->when($request->from, fn($q) => $q->whereDate('sale_date', '>=', $request->from))
                    ->when($request->to, fn($q) => $q->whereDate('sale_date', '<=', $request->to))
                    ->latest('sale_date')
                    ->get();

        $summary = [
            'total_amount' => $sales->sum('total_amount'),
            'total_paid'   => $sales->sum('paid_amount'),
            'total_due'    => $sales->sum('due_amount'),
        ];

        return view('inventory.reports.sale', compact('sales', 'summary'));
    }

    // Consumption Report
    public function consumption(Request $request)
    {
        $consumptions = InternalConsumption::with('location')
                            ->when($request->from, fn($q) => $q->whereDate('consumption_date', '>=', $request->from))
                            ->when($request->to, fn($q) => $q->whereDate('consumption_date', '<=', $request->to))
                            ->where('status', 'confirmed')
                            ->where('is_void', false)
                            ->latest('consumption_date')
                            ->get();

        $summary = [
            'total_amount'    => $consumptions->sum('total_amount'),
            'by_purpose'      => $consumptions->groupBy('purpose')->map(fn($g) => $g->sum('total_amount')),
        ];

        return view('inventory.reports.consumption', compact('consumptions', 'summary'));
    }

    // Profit & Loss Report
    public function profitLoss(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        // Sale Revenue
        $totalRevenue = Sale::confirmed()
                            ->whereBetween('sale_date', [$from, $to])
                            ->sum('total_amount');

        // COGS (Cost of Goods Sold)
        $totalCogs = SaleItem::whereHas('sale', fn($q) => $q->confirmed()->whereBetween('sale_date', [$from, $to]))
                            ->selectRaw('SUM(purchase_price * quantity) as cogs')
                            ->value('cogs') ?? 0;

        // Gross Profit
        $grossProfit = $totalRevenue - $totalCogs;

        // Consumption Expense
        $consumptionExpense = InternalConsumption::confirmed()
                                ->whereBetween('consumption_date', [$from, $to])
                                ->sum('total_amount');

        // Net Profit
        $netProfit = $grossProfit - $consumptionExpense;

        $data = compact(
            'from', 'to',
            'totalRevenue', 'totalCogs', 'grossProfit',
            'consumptionExpense', 'netProfit'
        );

        return view('inventory.reports.profit-loss', $data);
    }

    // Low Stock Report
    public function lowStock()
    {
        $products = Product::with('category', 'locationStocks.location')
                        ->lowStock()
                        ->get();

        return view('inventory.reports.low-stock', compact('products'));
    }

    // Vendor Ledger Report
    public function vendorLedger(Request $request)
    {
        $vendor = Vendor::findOrFail($request->vendor_id);

        $ledger = $vendor->ledger()
                    ->when($request->from, fn($q) => $q->whereDate('date', '>=', $request->from))
                    ->when($request->to, fn($q) => $q->whereDate('date', '<=', $request->to))
                    ->orderBy('date')
                    ->get();

        $vendors = Vendor::all();

        return view('inventory.reports.vendor-ledger', compact('vendor', 'ledger', 'vendors'));
    }

    // Client Ledger Report
    public function clientLedger(Request $request)
    {
        $clients = Customer::orderBy('name')->get();

        if (!$request->client_id) {
            return view('inventory.reports.client-ledger', compact('clients'));
        }

        $client = Customer::findOrFail($request->client_id);

        $ledger = ClientLedger::where('client_id', $client->id)
                    ->when($request->from, fn($q) => $q->whereDate('date', '>=', $request->from))
                    ->when($request->to, fn($q) => $q->whereDate('date', '<=', $request->to))
                    ->orderBy('date')
                    ->get();

        return view('inventory.reports.client-ledger', compact('client', 'ledger', 'clients'));
    }
}
