<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\PurchaseItem;
use App\Models\Inventory\Vendor;
use App\Models\Inventory\StoreLocation;
use App\Models\Inventory\Product;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\VendorLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::with('vendor', 'location')
                        ->when($request->vendor_id, fn($q) => $q->where('vendor_id', $request->vendor_id))
                        ->when($request->status, fn($q) => $q->where('status', $request->status))
                        ->when($request->from, fn($q) => $q->whereDate('purchase_date', '>=', $request->from))
                        ->when($request->to, fn($q) => $q->whereDate('purchase_date', '<=', $request->to))
                        ->latest()
                        ->paginate(20);

        $vendors = Vendor::active()->get();

        return view('inventory.purchases.index', compact('purchases', 'vendors'));
    }

    public function create()
    {
        $vendors   = Vendor::active()->get();
        $locations = StoreLocation::active()->get();
        $products  = Product::with('category')->get();

        return view('inventory.purchases.create', compact('vendors', 'locations', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id'        => 'required|exists:inventory_vendors,id',
            'location_id'      => 'required|exists:inventory_store_locations,id',
            'purchase_date'    => 'required|date',
            'invoice_no'       => 'nullable|string|max:255',
            'discount'         => 'nullable|numeric|min:0',
            'tax'              => 'nullable|numeric|min:0',
            'note'             => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:inventory_products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $discount     = $request->discount ?? 0;
            $tax          = $request->tax ?? 0;
            $totalAmount  = $subtotal - $discount + $tax;

            $purchase = Purchase::create([
                'vendor_id'      => $request->vendor_id,
                'location_id'    => $request->location_id,
                'purchase_date'  => $request->purchase_date,
                'invoice_no'     => $request->invoice_no,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total_amount'   => $totalAmount,
                'paid_amount'    => 0,
                'due_amount'     => $totalAmount,
                'payment_status' => 'unpaid',
                'status'         => 'draft',
                'note'           => $request->note,
                'created_by'     => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $purchase->items()->create([
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        });

        return redirect()->route('inventory.purchases.index')
                         ->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('vendor', 'location', 'items.product', 'payments', 'returns');

        return view('inventory.purchases.show', compact('purchase'));
    }

    // Draft → Received (Stock বাড়বে)
    public function receive(Purchase $purchase)
    {
        if (!$purchase->isDraft()) {
            return back()->with('error', 'Only draft purchases can be received.');
        }

        DB::transaction(function () use ($purchase) {
            $purchase->update(['status' => 'received']);

            foreach ($purchase->items as $item) {
                // Product stock বাড়াও
                $item->product->increment('stock_quantity', $item->quantity);

                // Product এর last purchase price update করো
                $item->product->update(['purchase_price' => $item->unit_price]);

                // Location stock বাড়াও
                LocationStock::updateOrCreate(
                    ['product_id' => $item->product_id, 'location_id' => $purchase->location_id],
                    ['quantity'   => DB::raw('quantity + ' . $item->quantity)]
                );

                // Stock transaction record করো
                StockTransaction::create([
                    'product_id'     => $item->product_id,
                    'location_id'    => $purchase->location_id,
                    'type'           => 'in',
                    'reason'         => 'purchase',
                    'reference_type' => 'purchase',
                    'reference_id'   => $purchase->id,
                    'quantity'       => $item->quantity,
                    'note'           => 'Purchase Received: ' . $purchase->purchase_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            // Vendor Ledger এ credit করো
            $lastBalance = $purchase->vendor->ledger()->latest('id')->value('balance') ?? 0;
            VendorLedger::create([
                'vendor_id'    => $purchase->vendor_id,
                'date'         => $purchase->purchase_date,
                'type'         => 'purchase',
                'reference_id' => $purchase->id,
                'debit'        => 0,
                'credit'       => $purchase->total_amount,
                'balance'      => $lastBalance + $purchase->total_amount,
                'note'         => 'Purchase: ' . $purchase->purchase_no,
                'created_by'   => auth()->id(),
            ]);
        });

        return redirect()->route('inventory.purchases.show', $purchase)
                         ->with('success', 'Purchase received successfully. Stock updated.');
    }

    // Draft → Cancelled
    public function cancel(Purchase $purchase)
    {
        if ($purchase->cannotCancel()) {
            return back()->with('error', 'Received purchases cannot be cancelled.');
        }

        $purchase->update(['status' => 'cancelled']);

        return redirect()->route('inventory.purchases.show', $purchase)
                         ->with('success', 'Purchase cancelled successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        if (!$purchase->isDraft()) {
            return back()->with('error', 'Only draft purchases can be deleted.');
        }

        $purchase->items()->delete();
        $purchase->delete();

        return redirect()->route('inventory.purchases.index')
                         ->with('success', 'Purchase deleted successfully.');
    }
}
