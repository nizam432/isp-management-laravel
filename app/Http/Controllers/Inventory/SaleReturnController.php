<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Sale;
use App\Models\Inventory\SaleReturn;
use App\Models\Inventory\SaleReturnItem;
use App\Models\Inventory\Product;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\ClientLedger;
use App\Services\Inventory\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    // =========================================================================
    // INDEX
    // =========================================================================
    public function index(Request $request)
    {
        $returns = SaleReturn::with('sale', 'client', 'location')
            ->when($request->search, fn($q) =>
                $q->where('return_no', 'like', '%' . $request->search . '%')
                  ->orWhereHas('sale', fn($s) => $s->where('invoice_no', 'like', '%' . $request->search . '%')))
            ->when($request->from, fn($q) => $q->whereDate('return_date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('return_date', '<=', $request->to))
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.sale-returns.index', compact('returns'));
    }

    // =========================================================================
    // CREATE
    // =========================================================================
    public function create(Request $request)
    {
        $sale = null;
        if ($request->sale_id) {
            $sale = Sale::with('items.product')->find($request->sale_id);
        }

        $sales = Sale::where('status', 'confirmed')
            ->orderByDesc('id')
            ->get(['id', 'invoice_no', 'sale_no', 'client_id', 'walk_in_name']);

        return view('inventory.sale-returns.create', compact('sale', 'sales'));
    }

    // ── AJAX — Get sale items for return form ────────────────────
    public function saleItems(Sale $sale)
    {
        $sale->load('items.product');

        return response()->json([
            'success' => true,
            'sale' => [
                'id'            => $sale->id,
                'invoice_no'    => $sale->invoice_no,
                'customer_name' => $sale->customer_name,
                'location_id'   => $sale->location_id,
                'client_id'     => $sale->client_id,
                'paid_amount'   => (float) $sale->paid_amount,
                'total_amount'  => (float) $sale->total_amount,
            ],
            'items' => $sale->items->map(function ($item) {
                $alreadyReturned = SaleReturnItem::where('sale_item_id', $item->id)
                    ->whereHas('saleReturn', fn($q) => $q->where('status', '!=', 'cancelled'))
                    ->sum('quantity');

                return [
                    'sale_item_id'   => $item->id,
                    'product_id'     => $item->product_id,
                    'product_name'   => $item->product->name ?? '—',
                    'unit'           => $item->product->unit ?? '',
                    'quantity'       => $item->quantity,
                    'unit_price'     => $item->unit_price,
                    'already_returned' => (float) $alreadyReturned,
                    'returnable_qty' => max(0, $item->quantity - $alreadyReturned),
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
            'sale_id'              => 'required|exists:inventory_sales,id',
            'return_date'          => 'required|date',
            'reason'               => 'nullable|string|max:500',
            'refund_type'          => 'required|in:cash,adjust,none',
            'note'                 => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:inventory_sale_items,id',
            'items.*.quantity'     => 'required|numeric|min:0.01',
        ]);

        $sale = Sale::with('items')->findOrFail($request->sale_id);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $itemsData   = [];

            foreach ($request->items as $reqItem) {
                $saleItem = $sale->items->firstWhere('id', $reqItem['sale_item_id']);
                if (! $saleItem) continue;

                $alreadyReturned = SaleReturnItem::where('sale_item_id', $saleItem->id)
                    ->whereHas('saleReturn', fn($q) => $q->where('status', '!=', 'cancelled'))
                    ->sum('quantity');

                $maxReturnable = $saleItem->quantity - $alreadyReturned;
                $returnQty     = min((float) $reqItem['quantity'], $maxReturnable);

                if ($returnQty <= 0) continue;

                $itemTotal = $returnQty * $saleItem->unit_price;
                $totalAmount += $itemTotal;

                $itemsData[] = [
                    'sale_item_id' => $saleItem->id,
                    'product_id'   => $saleItem->product_id,
                    'quantity'     => $returnQty,
                    'unit_price'   => $saleItem->unit_price,
                    'total_price'  => $itemTotal,
                ];
            }

            if (empty($itemsData)) {
                return back()->withInput()->with('error', 'No valid items to return.');
            }

            $return = SaleReturn::create([
                'sale_id'      => $sale->id,
                'client_id'    => $sale->client_id,
                'location_id'  => $sale->location_id,
                'return_date'  => $request->return_date,
                'total_amount' => $totalAmount,
                'reason'       => $request->reason ?: 'Not specified',
                'refund_type'  => $request->refund_type,
                'status'       => 'approved', // সরাসরি approved — draft নেই
                'note'         => $request->note,
                'created_by'   => auth()->id(),
                'approved_by'  => auth()->id(),
            ]);

            foreach ($itemsData as $item) {
                $return->items()->create($item);

                $product = Product::find($item['product_id']);
                $product->increment('stock_quantity', $item['quantity']);

                LocationStock::where('product_id', $item['product_id'])
                             ->where('location_id', $sale->location_id)
                             ->increment('quantity', $item['quantity']);

                StockTransaction::create([
                    'product_id'     => $item['product_id'],
                    'location_id'    => $sale->location_id,
                    'type'           => 'in',
                    'reason'         => 'sale_return',
                    'reference_type' => 'sale_return',
                    'reference_id'   => $return->id,
                    'quantity'       => $item['quantity'],
                    'note'           => 'Return: ' . $return->return_no . ' (Sale: ' . $sale->sale_no . ')',
                    'created_by'     => auth()->id(),
                ]);
            }

            $newTotal = max(0, $sale->total_amount - $totalAmount);
            $paid     = (float) $sale->paid_amount;

            if ($paid > $newTotal) {
                // Customer overpaid; excess becomes a refund due.
                $newDue    = 0;
                $refundDue = $paid - $newTotal;
            } else {
                $newDue    = $newTotal - $paid;
                $refundDue = 0;
            }

            $sale->update([
                'total_amount' => $newTotal,
                'due_amount'   => $newDue,
                'refund_due'   => $refundDue,
            ]);

            // ── Client Ledger reverse ─────────────────────────────
            if ($sale->client_id) {
                $lastBalance = ClientLedger::lastBalance($sale->client_id);
                ClientLedger::create([
                    'client_id'    => $sale->client_id,
                    'date'         => $request->return_date,
                    'type'         => 'return',
                    'reference_id' => $return->id,
                    'debit'        => 0,
                    'credit'       => -$totalAmount,
                    'balance'      => $lastBalance - $totalAmount,
                    'note'         => 'Sale Return: ' . $return->return_no,
                    'created_by'   => auth()->id(),
                ]);
            }

            // ── Accounting → Income negative entry (product_return) ─
            app(AccountingService::class)->createSaleReturnIncome($return);

            DB::commit();

            return redirect()->route('inventory.sale-returns.show', $return)
                ->with('success', "Return {$return->return_no} created. Stock & Income updated.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // SHOW
    // =========================================================================
    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load('sale', 'client', 'location', 'items.product', 'createdBy', 'approvedBy');
        return view('inventory.sale-returns.show', ['return' => $saleReturn]);
    }
}
