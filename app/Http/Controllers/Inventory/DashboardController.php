<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Product;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\Sale;
use App\Models\Inventory\InternalConsumption;
use App\Models\Inventory\StockTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_products'     => Product::count(),
            'low_stock_products' => Product::lowStock()->count(),
            'out_of_stock'       => Product::where('stock_quantity', 0)->count(),

            'monthly_purchase'   => Purchase::received()
                                        ->whereMonth('purchase_date', now()->month)
                                        ->whereYear('purchase_date', now()->year)
                                        ->sum('total_amount'),
            'purchase_due'       => Purchase::received()->where('due_amount', '>', 0)->sum('due_amount'),

            'monthly_sale'       => Sale::confirmed()
                                        ->whereMonth('sale_date', now()->month)
                                        ->whereYear('sale_date', now()->year)
                                        ->sum('total_amount'),
            'sale_due'           => Sale::confirmed()->where('due_amount', '>', 0)->sum('due_amount'),

            'low_stock_list'     => Product::with('category')->lowStock()->take(10)->get(),

            'recent_transactions' => StockTransaction::with(['product', 'location'])
                                        ->latest()
                                        ->take(10)
                                        ->get(),
        ];

        return view('inventory.dashboard.index', $data);
    }
}
