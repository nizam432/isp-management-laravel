<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StockTransfer;
use App\Models\Inventory\StoreLocation;
use App\Models\Inventory\Product;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $transfers = StockTransfer::with('fromLocation', 'toLocation')
                        ->when($request->status, fn($q) => $q->where('status', $request->status))
                        ->latest()
                        ->paginate(20);

        return view('inventory.transfers.index', compact('transfers'));
    }

    public function create()
    {
        $locations = StoreLocation::active()->get();
        $products  = Product::with('category')->inStock()->get();

        return view('inventory.transfers.create', compact('locations', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_location_id'   => 'required|exists:inventory_store_locations,id|different:to_location_id',
            'to_location_id'     => 'required|exists:inventory_store_locations,id',
            'transfer_date'      => 'required|date',
            'note'               => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:inventory_products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request) {
            $transfer = StockTransfer::create([
                'from_location_id' => $request->from_location_id,
                'to_location_id'   => $request->to_location_id,
                'transfer_date'    => $request->transfer_date,
                'status'           => 'draft',
                'note'             => $request->note,
                'created_by'       => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                ]);
            }
        });

        return redirect()->route('inventory.transfers.index')
                         ->with('success', 'Transfer created successfully.');
    }

    public function show(StockTransfer $transfer)
    {
        $transfer->load('fromLocation', 'toLocation', 'items.product');

        return view('inventory.transfers.show', compact('transfer'));
    }

    public function confirm(StockTransfer $transfer)
    {
        if (!$transfer->isDraft()) {
            return back()->with('error', 'Only draft transfers can be confirmed.');
        }

        // Stock check
        foreach ($transfer->items as $item) {
            $fromStock = $item->product->stockAtLocation($transfer->from_location_id);
            if ($fromStock < $item->quantity) {
                return back()->with('error', "Insufficient stock at source location for: {$item->product->name}");
            }
        }

        DB::transaction(function () use ($transfer) {
            $transfer->update(['status' => 'confirmed']);

            foreach ($transfer->items as $item) {
                LocationStock::where('product_id', $item->product_id)
                             ->where('location_id', $transfer->from_location_id)
                             ->decrement('quantity', $item->quantity);

                LocationStock::updateOrCreate(
                    ['product_id' => $item->product_id, 'location_id' => $transfer->to_location_id],
                    ['quantity'   => DB::raw('quantity + ' . $item->quantity)]
                );

                // Stock transaction — out (from)
                StockTransaction::create([
                    'product_id'       => $item->product_id,
                    'location_id'      => $transfer->from_location_id,
                    'from_location_id' => $transfer->from_location_id,
                    'to_location_id'   => $transfer->to_location_id,
                    'type'             => 'out',
                    'reason'           => 'transfer',
                    'reference_type'   => 'transfer',
                    'reference_id'     => $transfer->id,
                    'quantity'         => $item->quantity,
                    'note'             => 'Transfer: ' . $transfer->transfer_no,
                    'created_by'       => auth()->id(),
                ]);

                // Stock transaction — in (to)
                StockTransaction::create([
                    'product_id'       => $item->product_id,
                    'location_id'      => $transfer->to_location_id,
                    'from_location_id' => $transfer->from_location_id,
                    'to_location_id'   => $transfer->to_location_id,
                    'type'             => 'in',
                    'reason'           => 'transfer',
                    'reference_type'   => 'transfer',
                    'reference_id'     => $transfer->id,
                    'quantity'         => $item->quantity,
                    'note'             => 'Transfer: ' . $transfer->transfer_no,
                    'created_by'       => auth()->id(),
                ]);
            }
        });

        return redirect()->route('inventory.transfers.show', $transfer)
                         ->with('success', 'Transfer confirmed. Stock moved.');
    }

    public function cancel(StockTransfer $transfer)
    {
        if (!$transfer->isDraft()) {
            return back()->with('error', 'Only draft transfers can be cancelled.');
        }

        $transfer->update(['status' => 'cancelled']);

        return back()->with('success', 'Transfer cancelled successfully.');
    }
}
