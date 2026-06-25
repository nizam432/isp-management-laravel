<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Sale;
use App\Models\Inventory\StoreLocation;
use App\Models\Inventory\Product;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\ClientLedger;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::with('client', 'location')
                    ->when($request->status, fn($q) => $q->where('status', $request->status))
                    ->when($request->from, fn($q) => $q->whereDate('sale_date', '>=', $request->from))
                    ->when($request->to, fn($q) => $q->whereDate('sale_date', '<=', $request->to))
                    ->when($request->search, fn($q) => $q->where('sale_no', 'like', '%' . $request->search . '%')
                                                          ->orWhere('invoice_no', 'like', '%' . $request->search . '%'))
                    ->latest()
                    ->paginate(20);

        return view('inventory.sales.index', compact('sales'));
    }

    public function create()
    {
        $locations = StoreLocation::active()->get();
        $products  = Product::with('category')->inStock()->get();
        $clients   = Customer::active()->get();

        return view('inventory.sales.create', compact('locations', 'products', 'clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'          => 'nullable|exists:customers,id',
            'walk_in_name'       => 'nullable|string|max:255',
            'location_id'        => 'required|exists:inventory_store_locations,id',
            'sale_date'          => 'required|date',
            'discount'           => 'nullable|numeric|min:0',
            'tax'                => 'nullable|numeric|min:0',
            'sale_type'          => 'required|in:cash,credit',
            'note'               => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:inventory_products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount'   => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $itemDiscount = $item['discount'] ?? 0;
                $subtotal += ($item['quantity'] * $item['unit_price']) - $itemDiscount;
            }

            $discount    = $request->discount ?? 0;
            $tax         = $request->tax ?? 0;
            $totalAmount = $subtotal - $discount + $tax;

            $sale = Sale::create([
                'client_id'      => $request->client_id,
                'walk_in_name'   => $request->walk_in_name,
                'location_id'    => $request->location_id,
                'sale_date'      => $request->sale_date,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total_amount'   => $totalAmount,
                'paid_amount'    => 0,
                'due_amount'     => $totalAmount,
                'payment_status' => 'unpaid',
                'sale_type'      => $request->sale_type,
                'status'         => 'draft',
                'note'           => $request->note,
                'created_by'     => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $product      = Product::find($item['product_id']);
                $itemDiscount = $item['discount'] ?? 0;
                $totalPrice   = ($item['quantity'] * $item['unit_price']) - $itemDiscount;
                $profit       = $totalPrice - ($item['quantity'] * ($product->purchase_price ?? 0));

                $sale->items()->create([
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['unit_price'],
                    'discount'       => $itemDiscount,
                    'purchase_price' => $product->purchase_price ?? 0,
                    'total_price'    => $totalPrice,
                    'profit'         => $profit,
                ]);
            }
        });

        return redirect()->route('inventory.sales.index')
                         ->with('success', 'Sale created successfully.');
    }

    public function show(Sale $sale)
    {
        $sale->load('client', 'location', 'items.product', 'payments', 'returns');

        return view('inventory.sales.show', compact('sale'));
    }

    // Draft → Confirmed (Stock কমবে)
    public function confirm(Sale $sale)
    {
        if (!$sale->isDraft()) {
            return back()->with('error', 'Only draft sales can be confirmed.');
        }

        // Stock check
        foreach ($sale->items as $item) {
            if ($item->product->stock_quantity < $item->quantity) {
                return back()->with('error', "Insufficient stock for: {$item->product->name}");
            }
        }

        DB::transaction(function () use ($sale) {
            $sale->update(['status' => 'confirmed']);

            foreach ($sale->items as $item) {
                // Stock কমাও
                $item->product->decrement('stock_quantity', $item->quantity);

                // Location stock কমাও
                LocationStock::where('product_id', $item->product_id)
                             ->where('location_id', $sale->location_id)
                             ->decrement('quantity', $item->quantity);

                // Stock transaction
                StockTransaction::create([
                    'product_id'     => $item->product_id,
                    'location_id'    => $sale->location_id,
                    'type'           => 'out',
                    'reason'         => 'sale',
                    'reference_type' => 'sale',
                    'reference_id'   => $sale->id,
                    'quantity'       => $item->quantity,
                    'note'           => 'Sale: ' . $sale->sale_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            // Client Ledger এ credit করো (due আছে মানে client এর কাছে বাকি)
            if ($sale->client_id) {
                $lastBalance = ClientLedger::lastBalance($sale->client_id);
                ClientLedger::create([
                    'client_id'    => $sale->client_id,
                    'date'         => $sale->sale_date,
                    'type'         => 'sale',
                    'reference_id' => $sale->id,
                    'debit'        => 0,
                    'credit'       => $sale->total_amount,
                    'balance'      => $lastBalance + $sale->total_amount,
                    'note'         => 'Sale: ' . $sale->sale_no,
                    'created_by'   => auth()->id(),
                ]);
            }
        });

        return redirect()->route('inventory.sales.show', $sale)
                         ->with('success', 'Sale confirmed. Stock updated.');
    }

    // Draft → Cancelled
    public function cancel(Sale $sale)
    {
        if (!$sale->canCancel()) {
            return back()->with('error', 'Confirmed sales cannot be cancelled. Process a return instead.');
        }

        $sale->update(['status' => 'cancelled']);

        return redirect()->route('inventory.sales.show', $sale)
                         ->with('success', 'Sale cancelled successfully.');
    }

    public function destroy(Sale $sale)
    {
        if (!$sale->isDraft()) {
            return back()->with('error', 'Only draft sales can be deleted.');
        }

        $sale->items()->delete();
        $sale->delete();

        return redirect()->route('inventory.sales.index')
                         ->with('success', 'Sale deleted successfully.');
    }
}
