<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Sale;
use App\Models\Inventory\SaleReturn;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\ClientLedger;
use App\Services\Inventory\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = SaleReturn::with('sale', 'client')
                    ->when($request->status, fn($q) => $q->where('status', $request->status))
                    ->latest()
                    ->paginate(20);

        return view('inventory.sale-returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $sale = Sale::with('items.product', 'client')
                    ->findOrFail($request->sale_id);

        if (!$sale->isConfirmed()) {
            return back()->with('error', 'Can only return confirmed sales.');
        }

        $locations = \App\Models\Inventory\StoreLocation::active()->get();

        return view('inventory.sale-returns.create', compact('sale', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id'              => 'required|exists:inventory_sales,id',
            'location_id'          => 'required|exists:inventory_store_locations,id',
            'return_date'          => 'required|date',
            'reason'               => 'required|string',
            'refund_type'          => 'required|in:cash,adjust,none',
            'note'                 => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:inventory_sale_items,id',
            'items.*.product_id'   => 'required|exists:inventory_products,id',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        $sale = Sale::findOrFail($request->sale_id);

        DB::transaction(function () use ($request, $sale) {
            $totalAmount = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);

            $return = SaleReturn::create([
                'sale_id'      => $sale->id,
                'client_id'    => $sale->client_id,
                'location_id'  => $request->location_id,
                'return_date'  => $request->return_date,
                'total_amount' => $totalAmount,
                'reason'       => $request->reason,
                'refund_type'  => $request->refund_type,
                'status'       => 'draft',
                'note'         => $request->note,
                'created_by'   => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $return->items()->create([
                    'sale_item_id' => $item['sale_item_id'],
                    'product_id'   => $item['product_id'],
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'total_price'  => $item['quantity'] * $item['unit_price'],
                ]);
            }
        });

        return redirect()->route('inventory.sale-returns.index')
                         ->with('success', 'Sale return created successfully.');
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load('sale', 'client', 'location', 'items.product');

        return view('inventory.sale-returns.show', compact('saleReturn'));
    }

    // Draft → Approved (Stock বাড়বে)
    public function approve(SaleReturn $saleReturn)
    {
        if (!$saleReturn->isDraft()) {
            return back()->with('error', 'Only draft returns can be approved.');
        }

        DB::transaction(function () use ($saleReturn) {
            $saleReturn->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
            ]);

            foreach ($saleReturn->items as $item) {
                // Stock বাড়াও
                $item->product->increment('stock_quantity', $item->quantity);

                // Location stock বাড়াও
                LocationStock::updateOrCreate(
                    ['product_id' => $item->product_id, 'location_id' => $saleReturn->location_id],
                    ['quantity'   => DB::raw('quantity + ' . $item->quantity)]
                );

                // Stock transaction
                StockTransaction::create([
                    'product_id'     => $item->product_id,
                    'location_id'    => $saleReturn->location_id,
                    'type'           => 'in',
                    'reason'         => 'return',
                    'reference_type' => 'sale_return',
                    'reference_id'   => $saleReturn->id,
                    'quantity'       => $item->quantity,
                    'note'           => 'Sale Return: ' . $saleReturn->return_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            // Client Ledger adjust
            if ($saleReturn->client_id) {
                $lastBalance = ClientLedger::lastBalance($saleReturn->client_id);
                ClientLedger::create([
                    'client_id'    => $saleReturn->client_id,
                    'date'         => $saleReturn->return_date,
                    'type'         => 'return',
                    'reference_id' => $saleReturn->id,
                    'debit'        => $saleReturn->total_amount,
                    'credit'       => 0,
                    'balance'      => $lastBalance - $saleReturn->total_amount,
                    'note'         => 'Sale Return: ' . $saleReturn->return_no,
                    'created_by'   => auth()->id(),
                ]);
            }

            // Accounting → Income minus
            app(AccountingService::class)->createSaleReturnIncome($saleReturn);
        });

        return redirect()->route('inventory.sale-returns.show', $saleReturn)
                         ->with('success', 'Return approved. Stock updated.');
    }

    public function cancel(SaleReturn $saleReturn)
    {
        if (!$saleReturn->isDraft()) {
            return back()->with('error', 'Only draft returns can be cancelled.');
        }

        $saleReturn->update(['status' => 'cancelled']);

        return back()->with('success', 'Return cancelled successfully.');
    }
}
