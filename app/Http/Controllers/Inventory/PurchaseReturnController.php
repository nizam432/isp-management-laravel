<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\PurchaseReturn;
use App\Models\Inventory\PurchaseReturnItem;
use App\Models\Inventory\Product;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\VendorLedger;
use App\Services\Inventory\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    // =========================================================================
    // INDEX
    // =========================================================================
    public function index(Request $request)
    {
        $returns = PurchaseReturn::with('purchase', 'vendor', 'location')
            ->when($request->search, fn($q) =>
                $q->where('return_no', 'like', '%' . $request->search . '%')
                  ->orWhereHas('purchase', fn($p) => $p->where('purchase_no', 'like', '%' . $request->search . '%')))
            ->when($request->from, fn($q) => $q->whereDate('return_date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('return_date', '<=', $request->to))
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.purchase-returns.index', compact('returns'));
    }

    // =========================================================================
    // CREATE
    // =========================================================================
    public function create(Request $request)
    {
        $purchase = null;
        if ($request->purchase_id) {
            $purchase = Purchase::with('items.product')->find($request->purchase_id);
        }

        $purchases = Purchase::where('status', 'received')
            ->orderByDesc('id')
            ->get(['id', 'purchase_no', 'vendor_id']);

        return view('inventory.purchase-returns.create', compact('purchase', 'purchases'));
    }

    // ── AJAX — Get purchase items for return form ─────────────────
    public function purchaseItems(Purchase $purchase)
    {
        $purchase->load('items.product');

        return response()->json([
            'success' => true,
            'purchase' => [
                'id'           => $purchase->id,
                'purchase_no'  => $purchase->purchase_no,
                'vendor_name'  => $purchase->vendor->name ?? '—',
                'location_id'  => $purchase->location_id,
                'vendor_id'    => $purchase->vendor_id,
                'paid_amount'  => (float) $purchase->paid_amount,
                'total_amount' => (float) $purchase->total_amount,
            ],
            'items' => $purchase->items->map(function ($item) {
                $alreadyReturned = PurchaseReturnItem::where('purchase_item_id', $item->id)
                    ->whereHas('purchaseReturn', fn($q) => $q->where('status', '!=', 'cancelled'))
                    ->sum('quantity');

                return [
                    'purchase_item_id' => $item->id,
                    'product_id'       => $item->product_id,
                    'product_name'     => $item->product->name ?? '—',
                    'unit'             => $item->product->unit ?? '',
                    'quantity'         => $item->quantity,
                    'unit_price'       => $item->unit_price,
                    'already_returned' => (float) $alreadyReturned,
                    'returnable_qty'   => max(0, $item->quantity - $alreadyReturned),
                ];
            }),
        ]);
    }

    // =========================================================================
    // STORE
    // =========================================================================
    public function store(Request $request)
    {
        $request->validate([
            'purchase_id'              => 'required|exists:inventory_purchases,id',
            'return_date'              => 'required|date',
            'reason'                   => 'nullable|string|max:500',
            'note'                     => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:inventory_purchase_items,id',
            'items.*.quantity'         => 'required|numeric|min:0.01',
        ]);

        $purchase = Purchase::with('items')->findOrFail($request->purchase_id);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $itemsData   = [];

            foreach ($request->items as $reqItem) {
                $purchaseItem = $purchase->items->firstWhere('id', $reqItem['purchase_item_id']);
                if (! $purchaseItem) continue;

                $alreadyReturned = PurchaseReturnItem::where('purchase_item_id', $purchaseItem->id)
                    ->whereHas('purchaseReturn', fn($q) => $q->where('status', '!=', 'cancelled'))
                    ->sum('quantity');

                $maxReturnable = $purchaseItem->quantity - $alreadyReturned;
                $returnQty     = min((float) $reqItem['quantity'], $maxReturnable);

                if ($returnQty <= 0) continue;

                $itemTotal = $returnQty * $purchaseItem->unit_price;
                $totalAmount += $itemTotal;

                $itemsData[] = [
                    'purchase_item_id' => $purchaseItem->id,
                    'product_id'       => $purchaseItem->product_id,
                    'quantity'         => $returnQty,
                    'unit_price'       => $purchaseItem->unit_price,
                    'total_price'      => $itemTotal,
                ];
            }

            if (empty($itemsData)) {
                return back()->withInput()->with('error', 'No valid items to return.');
            }

            // ── Stock check — return করার মতো stock আছে কিনা ──────
            foreach ($itemsData as $item) {
                $product = Product::find($item['product_id']);
                if ($product->stock_quantity < $item['quantity']) {
                    return back()->withInput()->with('error',
                        "Insufficient stock to return: {$product->name} (Available: {$product->stock_quantity})");
                }
            }

            $return = PurchaseReturn::create([
                'purchase_id'  => $purchase->id,
                'vendor_id'    => $purchase->vendor_id,
                'location_id'  => $purchase->location_id,
                'return_date'  => $request->return_date,
                'total_amount' => $totalAmount,
                'reason'       => $request->reason ?: 'Not specified',
                'status'       => 'approved', // সরাসরি approved — draft নেই
                'note'         => $request->note,
                'created_by'   => auth()->id(),
                'approved_by'  => auth()->id(),
            ]);

            foreach ($itemsData as $item) {
                $return->items()->create($item);

                // ── Stock কমবে (vendor কে ফেরত যাচ্ছে) ───────────
                $product = Product::find($item['product_id']);
                $product->decrement('stock_quantity', $item['quantity']);

                LocationStock::where('product_id', $item['product_id'])
                             ->where('location_id', $purchase->location_id)
                             ->decrement('quantity', $item['quantity']);

                StockTransaction::create([
                    'product_id'     => $item['product_id'],
                    'location_id'    => $purchase->location_id,
                    'type'           => 'out',
                    'reason'         => 'purchase_return',
                    'reference_type' => 'purchase_return',
                    'reference_id'   => $return->id,
                    'quantity'       => $item['quantity'],
                    'note'           => 'Return: ' . $return->return_no . ' (Purchase: ' . $purchase->purchase_no . ')',
                    'created_by'     => auth()->id(),
                ]);
            }

            // ── Purchase total কমাও, due/refund সঠিকভাবে recalculate ──
            $newTotal = max(0, $purchase->total_amount - $totalAmount);
            $paid     = (float) $purchase->paid_amount;

            if ($paid > $newTotal) {
                $newDue    = 0;
                $refundDue = $paid - $newTotal;
            } else {
                $newDue    = $newTotal - $paid;
                $refundDue = 0;
            }

            $purchase->update([
                'total_amount' => $newTotal,
                'due_amount'   => $newDue,
                'refund_due'   => $refundDue,
            ]);

            // ── Vendor Ledger reverse ─────────────────────────────
            $lastBalance = $purchase->vendor->ledger()->latest('id')->value('balance') ?? 0;
            VendorLedger::create([
                'vendor_id'    => $purchase->vendor_id,
                'date'         => $request->return_date,
                'type'         => 'return',
                'reference_id' => $return->id,
                'debit'        => 0,
                'credit'       => -$totalAmount,
                'balance'      => $lastBalance - $totalAmount,
                'note'         => 'Purchase Return: ' . $return->return_no,
                'created_by'   => auth()->id(),
            ]);

            // ── Accounting → Expense negative entry (purchase_return) ─
            app(AccountingService::class)->createPurchaseReturnExpense($return);

            DB::commit();

            return redirect()->route('inventory.purchase-returns.show', $return)
                ->with('success', "Return {$return->return_no} created. Stock & Expense updated.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // SHOW
    // =========================================================================
    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load('purchase', 'vendor', 'location', 'items.product', 'createdBy', 'approvedBy');
        return view('inventory.purchase-returns.show', ['return' => $purchaseReturn]);
    }
}
