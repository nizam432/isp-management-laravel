<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\Product;
use App\Models\Inventory\StoreLocation;
use Illuminate\Http\Request;

class StockTransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = StockTransaction::with('product.category', 'location', 'createdBy')
                            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
                            ->when($request->location_id, fn($q) => $q->where('location_id', $request->location_id))
                            ->when($request->type, fn($q) => $q->where('type', $request->type))
                            ->when($request->reason, fn($q) => $q->where('reason', $request->reason))
                            ->when($request->from, fn($q) => $q->whereDate('created_at', '>=', $request->from))
                            ->when($request->to, fn($q) => $q->whereDate('created_at', '<=', $request->to))
                            ->latest()
                            ->paginate(30);

        $products  = Product::all();
        $locations = StoreLocation::all();

        return view('inventory.stock.transactions', compact('transactions', 'products', 'locations'));
    }
}
