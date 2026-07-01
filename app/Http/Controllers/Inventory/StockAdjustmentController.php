<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\Product;
use App\Models\Inventory\StoreLocation;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with('product', 'location', 'createdBy')
                            ->latest()
                            ->paginate(20);

        $products  = Product::all();
        $locations = StoreLocation::active()->get();

        return view('inventory.stock.adjustment', compact('adjustments', 'products', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'      => 'required|exists:inventory_products,id',
            'location_id'     => 'required|exists:inventory_store_locations,id',
            'adjustment_date' => 'required|date',
            'type'            => 'required|in:add,subtract',
            'quantity'        => 'required|numeric|min:0.01',
            'reason'          => 'required|string|max:500',
            'note'            => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($request->type === 'subtract' && $product->stock_quantity < $request->quantity) {
            return back()->with('error', 'Insufficient stock for this adjustment.');
        }

        DB::transaction(function () use ($request, $product) {
            StockAdjustment::create([
                ...$request->only(['product_id', 'location_id', 'adjustment_date', 'type', 'quantity', 'reason', 'note']),
                'created_by' => auth()->id(),
            ]);

            // Stock update
            if ($request->type === 'add') {
                $product->increment('stock_quantity', $request->quantity);

                LocationStock::updateOrCreate(
                    ['product_id' => $request->product_id, 'location_id' => $request->location_id],
                    ['quantity'   => DB::raw('quantity + ' . $request->quantity)]
                );
            } else {
                $product->decrement('stock_quantity', $request->quantity);

                LocationStock::where('product_id', $request->product_id)
                             ->where('location_id', $request->location_id)
                             ->decrement('quantity', $request->quantity);
            }

            // Stock transaction
            StockTransaction::create([
                'product_id'     => $request->product_id,
                'location_id'    => $request->location_id,
                'type'           => $request->type === 'add' ? 'in' : 'out',
                'reason'         => 'adjustment',
                'reference_type' => 'adjustment',
                'quantity'       => $request->quantity,
                'note'           => 'Adjustment: ' . $request->reason,
                'created_by'     => auth()->id(),
            ]);
        });

        return back()->with('success', 'Stock adjusted successfully.');
    }
}
