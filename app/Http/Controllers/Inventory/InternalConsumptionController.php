<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InternalConsumption;
use App\Models\Inventory\StoreLocation;
use App\Models\Inventory\Product;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\StockTransaction;
use App\Services\Inventory\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternalConsumptionController extends Controller
{
    public function index(Request $request)
    {
        $consumptions = InternalConsumption::with('location')
                            ->when($request->status, fn($q) => $q->where('status', $request->status))
                            ->when($request->from, fn($q) => $q->whereDate('consumption_date', '>=', $request->from))
                            ->when($request->to, fn($q) => $q->whereDate('consumption_date', '<=', $request->to))
                            ->latest()
                            ->paginate(20);

        return view('inventory.consumptions.index', compact('consumptions'));
    }

    public function create()
    {
        $locations = StoreLocation::active()->get();
        $products  = Product::with('category')->get();

        return view('inventory.consumptions.create', compact('locations', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'consumption_date'   => 'required|date',
            'location_id'        => 'required|exists:inventory_store_locations,id',
            'purpose'            => 'required|string|max:255',
            'reference_note'     => 'nullable|string|max:255',
            'note'               => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:inventory_products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.note'       => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);

            $consumption = InternalConsumption::create([
                'consumption_date' => $request->consumption_date,
                'location_id'      => $request->location_id,
                'purpose'          => $request->purpose,
                'reference_note'   => $request->reference_note,
                'total_amount'     => $totalAmount,
                'status'           => 'draft',
                'note'             => $request->note,
                'created_by'       => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $consumption->items()->create([
                    'product_id'  => $item['product_id'],
                    'unit'        => $product->unit,
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'note'        => $item['note'] ?? null,
                ]);
            }
        });

        return redirect()->route('inventory.consumptions.index')
                         ->with('success', 'Consumption created successfully.');
    }

    public function show(InternalConsumption $consumption)
    {
        $consumption->load('location', 'items.product', 'createdBy');

        return view('inventory.consumptions.show', compact('consumption'));
    }

    public function confirm(InternalConsumption $consumption)
    {
        if (!$consumption->isDraft()) {
            return back()->with('error', 'Only draft consumptions can be confirmed.');
        }

        // Stock check
        foreach ($consumption->items as $item) {
            if ($item->product->stock_quantity < $item->quantity) {
                return back()->with('error', "Insufficient stock for: {$item->product->name}");
            }
        }

        DB::transaction(function () use ($consumption) {
            $consumption->update(['status' => 'confirmed']);

            foreach ($consumption->items as $item) {
                $item->product->decrement('stock_quantity', $item->quantity);

                LocationStock::where('product_id', $item->product_id)
                             ->where('location_id', $consumption->location_id)
                             ->decrement('quantity', $item->quantity);

                // Stock transaction
                StockTransaction::create([
                    'product_id'     => $item->product_id,
                    'location_id'    => $consumption->location_id,
                    'type'           => 'out',
                    'reason'         => 'consumption',
                    'reference_type' => 'consumption',
                    'reference_id'   => $consumption->id,
                    'quantity'       => $item->quantity,
                    'note'           => $consumption->purpose . ' — ' . $consumption->consumption_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            // Sync to Expense ledger via accounting service.
            app(AccountingService::class)->createConsumptionExpense($consumption);
        });

        return redirect()->route('inventory.consumptions.show', $consumption)
                         ->with('success', 'Consumption confirmed. Stock updated.');
    }

    public function void(Request $request, InternalConsumption $consumption)
    {
        $request->validate([
            'void_reason' => 'required|string|max:500',
        ]);

        if (!$consumption->isConfirmed()) {
            return back()->with('error', 'Only confirmed consumptions can be voided.');
        }

        DB::transaction(function () use ($request, $consumption) {
            $consumption->update([
                'is_void'     => true,
                'void_reason' => $request->void_reason,
                'void_by'     => auth()->id(),
                'void_at'     => now(),
            ]);

            foreach ($consumption->items as $item) {
                $item->product->increment('stock_quantity', $item->quantity);

                LocationStock::where('product_id', $item->product_id)
                             ->where('location_id', $consumption->location_id)
                             ->increment('quantity', $item->quantity);

                // Stock transaction reverse
                StockTransaction::create([
                    'product_id'     => $item->product_id,
                    'location_id'    => $consumption->location_id,
                    'type'           => 'in',
                    'reason'         => 'adjustment',
                    'reference_type' => 'consumption',
                    'reference_id'   => $consumption->id,
                    'quantity'       => $item->quantity,
                    'note'           => 'Consumption Void: ' . $consumption->consumption_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            // Accounting → Expense void
            app(AccountingService::class)->voidConsumptionExpense($consumption);
        });

        return redirect()->route('inventory.consumptions.show', $consumption)
                         ->with('success', 'Consumption voided. Stock restored.');
    }

    public function destroy(InternalConsumption $consumption)
    {
        if (!$consumption->isDraft()) {
            return back()->with('error', 'Only draft consumptions can be deleted.');
        }

        $consumption->items()->delete();
        $consumption->delete();

        return redirect()->route('inventory.consumptions.index')
                         ->with('success', 'Consumption deleted successfully.');
    }
}
