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
                        ->orderByDesc('purchase_date')
                        ->orderByDesc('id')
                        ->paginate(20)
                        ->withQueryString();

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

    // ── STORE — সরাসরি Received, stock তখনই বাড়বে ─────────────────
    public function store(Request $request)
    {
        $request->validate([
            'vendor_id'           => 'required|exists:inventory_vendors,id',
            'location_id'         => 'required|exists:inventory_store_locations,id',
            'purchase_date'       => 'required|date',
            'invoice_no'          => 'nullable|string|max:255',
            'discount'            => 'nullable|numeric|min:0',
            'tax'                 => 'nullable|numeric|min:0',
            'note'                => 'nullable|string',
            'paid_amount'         => 'nullable|numeric|min:0',
            'payment_method'      => 'nullable|in:cash,bank,mobile_banking',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:inventory_products,id',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $discount    = $request->discount ?? 0;
            $tax         = $request->tax ?? 0;
            $totalAmount = $subtotal - $discount + $tax;
            $paidAmount  = min((float) ($request->paid_amount ?? 0), $totalAmount);

            $purchase = Purchase::create([
                'vendor_id'      => $request->vendor_id,
                'location_id'    => $request->location_id,
                'purchase_date'  => $request->purchase_date,
                'invoice_no'     => $request->invoice_no,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total_amount'   => $totalAmount,
                'paid_amount'    => 0, // payment আলাদাভাবে PurchasePayment দিয়ে হবে
                'due_amount'     => $totalAmount,
                'payment_status' => 'unpaid',
                'status'         => 'received', // সরাসরি received — draft নেই
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

                // ── Stock তখনই বাড়াও ─────────────────────────────
                $product = Product::find($item['product_id']);
                $product->increment('stock_quantity', $item['quantity']);
                $product->update(['purchase_price' => $item['unit_price']]);

                $locStock = LocationStock::firstOrNew(
                    ['product_id' => $item['product_id'], 'location_id' => $purchase->location_id]
                );
                $locStock->quantity = ($locStock->quantity ?? 0) + $item['quantity'];
                $locStock->save();

                StockTransaction::create([
                    'product_id'     => $item['product_id'],
                    'location_id'    => $purchase->location_id,
                    'type'           => 'in',
                    'reason'         => 'purchase',
                    'reference_type' => 'purchase',
                    'reference_id'   => $purchase->id,
                    'quantity'       => $item['quantity'],
                    'note'           => 'Purchase: ' . $purchase->purchase_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            // ── Vendor Ledger ──────────────────────────────────────
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

            // ── Initial Payment (যদি দেওয়া থাকে) ──────────────────
            if ($paidAmount > 0) {
                app(\App\Http\Controllers\Inventory\PurchasePaymentController::class)
                    ->createInitialPayment($purchase, $paidAmount, $request);
            }
        });

        return redirect()->route('inventory.purchases.index')
                         ->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('vendor', 'location', 'items.product', 'payments.createdBy', 'returns');
        return view('inventory.purchases.show', compact('purchase'));
    }

    // ── EDIT — শুধু payment/return না থাকলে ───────────────────────
    public function edit(Purchase $purchase)
    {
        if (! $purchase->isEditable()) {
            return redirect()->route('inventory.purchases.index')
                ->with('error', 'This purchase has payment/return history and cannot be edited.');
        }

        $vendors   = Vendor::active()->get();
        $locations = StoreLocation::active()->get();
        $products  = Product::with('category')->get();
        $purchase->load('items.product');

        return view('inventory.purchases.edit', compact('purchase', 'vendors', 'locations', 'products'));
    }

    // ── UPDATE — stock recalculate ────────────────────────────────
    public function update(Request $request, Purchase $purchase)
    {
        if (! $purchase->isEditable()) {
            return back()->with('error', 'This purchase has payment/return history and cannot be edited.');
        }

        $request->validate([
            'vendor_id'           => 'required|exists:inventory_vendors,id',
            'location_id'         => 'required|exists:inventory_store_locations,id',
            'purchase_date'       => 'required|date',
            'invoice_no'          => 'nullable|string|max:255',
            'discount'            => 'nullable|numeric|min:0',
            'tax'                 => 'nullable|numeric|min:0',
            'note'                => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:inventory_products,id',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            // ── পুরনো stock revert করো ───────────────────────────
            foreach ($purchase->items as $oldItem) {
                $oldItem->product->decrement('stock_quantity', $oldItem->quantity);
                LocationStock::where('product_id', $oldItem->product_id)
                             ->where('location_id', $purchase->location_id)
                             ->decrement('quantity', $oldItem->quantity);

                StockTransaction::create([
                    'product_id'     => $oldItem->product_id,
                    'location_id'    => $purchase->location_id,
                    'type'           => 'out',
                    'reason'         => 'purchase_edit_revert',
                    'reference_type' => 'purchase',
                    'reference_id'   => $purchase->id,
                    'quantity'       => $oldItem->quantity,
                    'note'           => 'Purchase Edited (revert): ' . $purchase->purchase_no,
                    'created_by'     => auth()->id(),
                ]);
            }
            $purchase->items()->delete();

            // ── নতুন items হিসাব ────────────────────────────────
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $discount    = $request->discount ?? 0;
            $tax         = $request->tax ?? 0;
            $totalAmount = $subtotal - $discount + $tax;

            $purchase->update([
                'vendor_id'     => $request->vendor_id,
                'location_id'   => $request->location_id,
                'purchase_date' => $request->purchase_date,
                'invoice_no'    => $request->invoice_no,
                'subtotal'      => $subtotal,
                'discount'      => $discount,
                'tax'           => $tax,
                'total_amount'  => $totalAmount,
                'due_amount'    => $totalAmount,
                'note'          => $request->note,
            ]);

            foreach ($request->items as $item) {
                $purchase->items()->create([
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);

                $product = Product::find($item['product_id']);
                $product->increment('stock_quantity', $item['quantity']);
                $product->update(['purchase_price' => $item['unit_price']]);

                $locStock = LocationStock::firstOrNew(
                    ['product_id' => $item['product_id'], 'location_id' => $purchase->location_id]
                );
                $locStock->quantity = ($locStock->quantity ?? 0) + $item['quantity'];
                $locStock->save();

                StockTransaction::create([
                    'product_id'     => $item['product_id'],
                    'location_id'    => $purchase->location_id,
                    'type'           => 'in',
                    'reason'         => 'purchase_edit',
                    'reference_type' => 'purchase',
                    'reference_id'   => $purchase->id,
                    'quantity'       => $item['quantity'],
                    'note'           => 'Purchase Edited: ' . $purchase->purchase_no,
                    'created_by'     => auth()->id(),
                ]);
            }
        });

        return redirect()->route('inventory.purchases.show', $purchase)
                         ->with('success', 'Purchase updated successfully.');
    }

    // ── CANCEL — শুধু lock না থাকলে, stock revert ──────────────────
    public function cancel(Request $request, Purchase $purchase)
    {
        if (! $purchase->canCancel()) {
            return back()->with('error', 'This purchase has payment/return history and cannot be cancelled.');
        }

        DB::transaction(function () use ($purchase, $request) {
            foreach ($purchase->items as $item) {
                $item->product->decrement('stock_quantity', $item->quantity);
                LocationStock::where('product_id', $item->product_id)
                             ->where('location_id', $purchase->location_id)
                             ->decrement('quantity', $item->quantity);

                StockTransaction::create([
                    'product_id'     => $item->product_id,
                    'location_id'    => $purchase->location_id,
                    'type'           => 'out',
                    'reason'         => 'purchase_cancel',
                    'reference_type' => 'purchase',
                    'reference_id'   => $purchase->id,
                    'quantity'       => $item->quantity,
                    'note'           => 'Purchase Cancelled: ' . $purchase->purchase_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            $purchase->update([
                'status' => 'cancelled',
                'note'   => trim(($purchase->note ?? '') . ' | Cancelled: ' . ($request->reason ?? 'No reason given')),
            ]);
        });

        return redirect()->route('inventory.purchases.index')
                         ->with('success', 'Purchase cancelled. Stock reverted.');
    }

    public function destroy(Purchase $purchase)
    {
        if (! $purchase->canDelete()) {
            return back()->with('error', 'This purchase has payment/return history and cannot be deleted.');
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                $item->product->decrement('stock_quantity', $item->quantity);
                LocationStock::where('product_id', $item->product_id)
                             ->where('location_id', $purchase->location_id)
                             ->decrement('quantity', $item->quantity);

                StockTransaction::create([
                    'product_id'     => $item->product_id,
                    'location_id'    => $purchase->location_id,
                    'type'           => 'out',
                    'reason'         => 'purchase_delete',
                    'reference_type' => 'purchase',
                    'reference_id'   => $purchase->id,
                    'quantity'       => $item->quantity,
                    'note'           => 'Purchase Deleted: ' . $purchase->purchase_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            $purchase->items()->delete();
            $purchase->delete();
        });

        return redirect()->route('inventory.purchases.index')
                         ->with('success', 'Purchase deleted successfully.');
    }

    // ── DETAIL — AJAX for view modal ──────────────────────────────
    public function detail(Purchase $purchase)
    {
        $purchase->load('vendor', 'location', 'items.product', 'payments.createdBy');

        return response()->json([
            'success' => true,
            'purchase' => [
                'purchase_no'   => $purchase->purchase_no,
                'vendor_name'   => $purchase->vendor->name ?? '—',
                'purchase_date' => $purchase->purchase_date->format('d M Y'),
                'location'      => $purchase->location->name ?? '—',
                'status_badge'  => $purchase->statusBadge,
                'subtotal'      => number_format($purchase->subtotal, 2),
                'discount'      => number_format($purchase->discount, 2),
                'total_amount'  => number_format($purchase->total_amount, 2),
                'paid_amount'   => number_format($purchase->paid_amount, 2),
                'due_amount'    => number_format($purchase->due_amount, 2),
                'items' => $purchase->items->map(fn($i) => [
                    'product_name' => $i->product->name ?? '—',
                    'quantity'     => $i->quantity,
                    'unit_price'   => number_format($i->unit_price, 2),
                    'total_price'  => number_format($i->total_price, 2),
                ]),
                'payments' => $purchase->payments->map(fn($p) => [
                    'id'           => $p->id,
                    'payment_date' => $p->payment_date->format('d M Y'),
                    'amount'       => number_format($p->amount, 2),
                    'method'       => strtoupper(str_replace('_', ' ', $p->payment_method)),
                    'is_void'      => (bool) $p->is_void,
                ]),
            ],
        ]);
    }

    // ── EXPORT XLSX ────────────────────────────────────────────────
    public function exportXlsx(Request $request)
    {
        $purchases = $this->getFilteredPurchases($request);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Purchases');

        $headers = ['A'=>'#','B'=>'Purchase No','C'=>'Date','D'=>'Vendor',
                    'E'=>'Total','F'=>'Paid','G'=>'Due','H'=>'Payment Status','I'=>'Status'];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col.'1', $label);
            $sheet->getStyle($col.'1')->getFont()->setBold(true);
            $sheet->getStyle($col.'1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1a237e');
            $sheet->getStyle($col.'1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach ($purchases as $i => $p) {
            $row = $i + 2;
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, $p->purchase_no);
            $sheet->setCellValue('C'.$row, $p->purchase_date->format('d-m-Y'));
            $sheet->setCellValue('D'.$row, $p->vendor->name ?? '—');
            $sheet->setCellValue('E'.$row, (float) $p->total_amount);
            $sheet->setCellValue('F'.$row, (float) $p->paid_amount);
            $sheet->setCellValue('G'.$row, (float) $p->due_amount);
            $sheet->setCellValue('H'.$row, ucfirst($p->payment_status));
            $sheet->setCellValue('I'.$row, ucfirst($p->status));
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'purchases-' . now()->format('Y-m-d') . '.xlsx';
        $tmpPath  = storage_path('app/' . $filename);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ── EXPORT PDF ─────────────────────────────────────────────────
    public function exportPdf(Request $request)
    {
        $purchases = $this->getFilteredPurchases($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'inventory.purchases.export-pdf',
            compact('purchases')
        )->setPaper('a4', 'landscape');

        return $pdf->download('purchases-' . now()->format('Y-m-d') . '.pdf');
    }

    private function getFilteredPurchases(Request $request)
    {
        return Purchase::with('vendor', 'location')
            ->when($request->vendor_id, fn($q) => $q->where('vendor_id', $request->vendor_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->from, fn($q) => $q->whereDate('purchase_date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('purchase_date', '<=', $request->to))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get();
    }
}
