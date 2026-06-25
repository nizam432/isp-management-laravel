<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\PurchaseReturn;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\VendorLedger;
use App\Services\Inventory\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = PurchaseReturn::with('vendor', 'purchase')
                    ->when($request->status, fn($q) => $q->where('status', $request->status))
                    ->latest()
                    ->paginate(20);

        return view('inventory.purchase-returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $purchase = Purchase::with('items.product', 'vendor')
                        ->findOrFail($request->purchase_id);

        if (!$purchase->isReceived()) {
            return back()->with('error', 'Can only return received purchases.');
        }

        $locations = \App\Models\Inventory\StoreLocation::active()->get();

        return view('inventory.purchase-returns.create', compact('purchase', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id'          => 'required|exists:inventory_purchases,id',
            'location_id'          => 'required|exists:inventory_store_locations,id',
            'return_date'          => 'required|date',
            'reason'               => 'required|string',
            'note'                 => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:inventory_purchase_items,id',
            'items.*.product_id'       => 'required|exists:inventory_products,id',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.unit_price'       => 'required|numeric|min:0',
        ]);

        $purchase = Purchase::findOrFail($request->purchase_id);

        DB::transaction(function () use ($request, $purchase) {
            $totalAmount = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);

            $return = PurchaseReturn::create([
                'purchase_id'  => $purchase->id,
                'vendor_id'    => $purchase->vendor_id,
                'location_id'  => $request->location_id,
                'return_date'  => $request->return_date,
                'total_amount' => $totalAmount,
                'reason'       => $request->reason,
                'status'       => 'draft',
                'note'         => $request->note,
                'created_by'   => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $return->items()->create([
                    'purchase_item_id' => $item['purchase_item_id'],
                    'product_id'       => $item['product_id'],
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'total_price'      => $item['quantity'] * $item['unit_price'],
                ]);
            }
        });

        return redirect()->route('inventory.purchase-returns.index')
                         ->with('success', 'Purchase return created successfully.');
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load('purchase', 'vendor', 'location', 'items.product');

        return view('inventory.purchase-returns.show', compact('purchaseReturn'));
    }

    // Draft → Approved (Stock কমবে)
    public function approve(PurchaseReturn $purchaseReturn)
    {
        if (!$purchaseReturn->isDraft()) {
            return back()->with('error', 'Only draft returns can be approved.');
        }

        DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
            ]);

            foreach ($purchaseReturn->items as $item) {
                // Stock কমাও
                $item->product->decrement('stock_quantity', $item->quantity);

                // Location stock কমাও
                LocationStock::where('product_id', $item->product_id)
                             ->where('location_id', $purchaseReturn->location_id)
                             ->decrement('quantity', $item->quantity);

                // Stock transaction
                StockTransaction::create([
                    'product_id'     => $item->product_id,
                    'location_id'    => $purchaseReturn->location_id,
                    'type'           => 'out',
                    'reason'         => 'return',
                    'reference_type' => 'purchase_return',
                    'reference_id'   => $purchaseReturn->id,
                    'quantity'       => $item->quantity,
                    'note'           => 'Purchase Return: ' . $purchaseReturn->return_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            // Vendor Ledger adjust
            $lastBalance = $purchaseReturn->vendor->ledger()->latest('id')->value('balance') ?? 0;
            VendorLedger::create([
                'vendor_id'    => $purchaseReturn->vendor_id,
                'date'         => $purchaseReturn->return_date,
                'type'         => 'return',
                'reference_id' => $purchaseReturn->id,
                'debit'        => $purchaseReturn->total_amount,
                'credit'       => 0,
                'balance'      => $lastBalance - $purchaseReturn->total_amount,
                'note'         => 'Purchase Return: ' . $purchaseReturn->return_no,
                'created_by'   => auth()->id(),
            ]);

            // Accounting → Expense minus
            app(AccountingService::class)->createPurchaseReturnExpense($purchaseReturn);
        });

        return redirect()->route('inventory.purchase-returns.show', $purchaseReturn)
                         ->with('success', 'Return approved. Stock updated.');
    }

    public function cancel(PurchaseReturn $purchaseReturn)
    {
        if (!$purchaseReturn->isDraft()) {
            return back()->with('error', 'Only draft returns can be cancelled.');
        }

        $purchaseReturn->update(['status' => 'cancelled']);

        return back()->with('success', 'Return cancelled successfully.');
    }
}
