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
                    ->orderByDesc('sale_date')
                    ->orderByDesc('id')
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

    // ── STORE — সরাসরি Confirmed, stock তখনই কমবে ──────────────────
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
            'paid_amount'        => 'nullable|numeric|min:0',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:inventory_products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount'   => 'nullable|numeric|min:0',
        ]);

        // ── Stock check আগে ──────────────────────────────────────
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if ($product->stock_quantity < $item['quantity']) {
                return back()->withInput()->with('error',
                    "Insufficient stock for: {$product->name} (Available: {$product->stock_quantity})");
            }
        }

        DB::transaction(function () use ($request) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $itemDiscount = $item['discount'] ?? 0;
                $subtotal += ($item['quantity'] * $item['unit_price']) - $itemDiscount;
            }

            $discount    = $request->discount ?? 0;
            $tax         = $request->tax ?? 0;
            $totalAmount = $subtotal - $discount + $tax;
            $paidAmount  = min((float) ($request->paid_amount ?? 0), $totalAmount);
            $dueAmount   = $totalAmount - $paidAmount;

            $sale = Sale::create([
                'client_id'      => $request->client_id,
                'walk_in_name'   => $request->walk_in_name,
                'location_id'    => $request->location_id,
                'sale_date'      => $request->sale_date,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total_amount'   => $totalAmount,
                'paid_amount'    => 0, // payment আলাদাভাবে SalePayment দিয়ে হবে
                'due_amount'     => $totalAmount,
                'payment_status' => 'unpaid',
                'sale_type'      => $request->sale_type,
                'status'         => 'confirmed', // সরাসরি confirmed — draft নেই
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

                // ── Stock কমাও তখনই ──────────────────────────────
                $product->decrement('stock_quantity', $item['quantity']);

                LocationStock::where('product_id', $item['product_id'])
                             ->where('location_id', $sale->location_id)
                             ->decrement('quantity', $item['quantity']);

                StockTransaction::create([
                    'product_id'     => $item['product_id'],
                    'location_id'    => $sale->location_id,
                    'type'           => 'out',
                    'reason'         => 'sale',
                    'reference_type' => 'sale',
                    'reference_id'   => $sale->id,
                    'quantity'       => $item['quantity'],
                    'note'           => 'Sale: ' . $sale->sale_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            // ── Client Ledger ──────────────────────────────────────
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

            // ── Initial Payment (যদি দেওয়া থাকে) ──────────────────
            if ($paidAmount > 0) {
                app(\App\Http\Controllers\Inventory\SalePaymentController::class)
                    ->createInitialPayment($sale, $paidAmount, $request);
            }
        });

        return redirect()->route('inventory.sales.index')
                         ->with('success', 'Sale created successfully.');
    }

    public function show(Sale $sale)
    {
        $sale->load('client', 'location', 'items.product', 'payments.createdBy', 'returns');
        return view('inventory.sales.show', compact('sale'));
    }

    // ── EDIT — শুধু payment না থাকলে ─────────────────────────────
    public function edit(Sale $sale)
    {
        if (! $sale->isEditable()) {
            return redirect()->route('inventory.sales.index')
                ->with('error', 'This sale has payment history and cannot be edited.');
        }

        $locations = StoreLocation::active()->get();
        $products  = Product::with('category')->inStock()->get();
        $clients   = Customer::active()->get();
        $sale->load('items.product');

        return view('inventory.sales.edit', compact('sale', 'locations', 'products', 'clients'));
    }

    // ── UPDATE — stock recalculate (return old, deduct new) ──────
    public function update(Request $request, Sale $sale)
    {
        if (! $sale->isEditable()) {
            return back()->with('error', 'This sale has payment history and cannot be edited.');
        }

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

        DB::transaction(function () use ($request, $sale) {
            // ── পুরনো stock return করো ──────────────────────────
            foreach ($sale->items as $oldItem) {
                $oldItem->product->increment('stock_quantity', $oldItem->quantity);
                LocationStock::where('product_id', $oldItem->product_id)
                             ->where('location_id', $sale->location_id)
                             ->increment('quantity', $oldItem->quantity);

                StockTransaction::create([
                    'product_id'     => $oldItem->product_id,
                    'location_id'    => $sale->location_id,
                    'type'           => 'in',
                    'reason'         => 'sale_edit_revert',
                    'reference_type' => 'sale',
                    'reference_id'   => $sale->id,
                    'quantity'       => $oldItem->quantity,
                    'note'           => 'Sale Edited (revert): ' . $sale->sale_no,
                    'created_by'     => auth()->id(),
                ]);
            }
            $sale->items()->delete();

            // ── নতুন items হিসাব করো ────────────────────────────
            $subtotal = 0;
            foreach ($request->items as $item) {
                $itemDiscount = $item['discount'] ?? 0;
                $subtotal += ($item['quantity'] * $item['unit_price']) - $itemDiscount;
            }

            $discount    = $request->discount ?? 0;
            $tax         = $request->tax ?? 0;
            $totalAmount = $subtotal - $discount + $tax;

            $sale->update([
                'client_id'    => $request->client_id,
                'walk_in_name' => $request->walk_in_name,
                'location_id'  => $request->location_id,
                'sale_date'    => $request->sale_date,
                'subtotal'     => $subtotal,
                'discount'     => $discount,
                'tax'          => $tax,
                'total_amount' => $totalAmount,
                'due_amount'   => $totalAmount, // paid 0 ছিল
                'sale_type'    => $request->sale_type,
                'note'         => $request->note,
            ]);

            foreach ($request->items as $item) {
                $product      = Product::find($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for: {$product->name}");
                }

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

                $product->decrement('stock_quantity', $item['quantity']);
                LocationStock::where('product_id', $item['product_id'])
                             ->where('location_id', $sale->location_id)
                             ->decrement('quantity', $item['quantity']);

                StockTransaction::create([
                    'product_id'     => $item['product_id'],
                    'location_id'    => $sale->location_id,
                    'type'           => 'out',
                    'reason'         => 'sale_edit',
                    'reference_type' => 'sale',
                    'reference_id'   => $sale->id,
                    'quantity'       => $item['quantity'],
                    'note'           => 'Sale Edited: ' . $sale->sale_no,
                    'created_by'     => auth()->id(),
                ]);
            }
        });

        return redirect()->route('inventory.sales.show', $sale)
                         ->with('success', 'Sale updated successfully.');
    }

    // ── VOID — শুধু payment না থাকলে, stock return ───────────────
    public function void(Request $request, Sale $sale)
    {
        if (! $sale->canCancel()) {
            return back()->with('error', 'This sale has payment history and cannot be voided.');
        }

        $request->validate(['reason' => 'required|string|max:255']);

        DB::transaction(function () use ($sale, $request) {
            foreach ($sale->items as $item) {
                $item->product->increment('stock_quantity', $item->quantity);
                LocationStock::where('product_id', $item->product_id)
                             ->where('location_id', $sale->location_id)
                             ->increment('quantity', $item->quantity);

                StockTransaction::create([
                    'product_id'     => $item->product_id,
                    'location_id'    => $sale->location_id,
                    'type'           => 'in',
                    'reason'         => 'sale_void',
                    'reference_type' => 'sale',
                    'reference_id'   => $sale->id,
                    'quantity'       => $item->quantity,
                    'note'           => 'Sale Voided: ' . $sale->sale_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            $sale->update([
                'status' => 'cancelled',
                'note'   => trim(($sale->note ?? '') . " | Voided: {$request->reason}"),
            ]);
        });

        return redirect()->route('inventory.sales.index')
                         ->with('success', 'Sale voided. Stock returned.');
    }

    // ── DESTROY — শুধু payment না থাকলে ───────────────────────────
    public function destroy(Sale $sale)
    {
        if (! $sale->canDelete()) {
            return back()->with('error', 'This sale has payment history and cannot be deleted.');
        }

        DB::transaction(function () use ($sale) {
            // Stock return
            foreach ($sale->items as $item) {
                $item->product->increment('stock_quantity', $item->quantity);
                LocationStock::where('product_id', $item->product_id)
                             ->where('location_id', $sale->location_id)
                             ->increment('quantity', $item->quantity);

                StockTransaction::create([
                    'product_id'     => $item->product_id,
                    'location_id'    => $sale->location_id,
                    'type'           => 'in',
                    'reason'         => 'sale_delete',
                    'reference_type' => 'sale',
                    'reference_id'   => $sale->id,
                    'quantity'       => $item->quantity,
                    'note'           => 'Sale Deleted: ' . $sale->sale_no,
                    'created_by'     => auth()->id(),
                ]);
            }

            $sale->items()->delete();
            $sale->delete();
        });

        return redirect()->route('inventory.sales.index')
                         ->with('success', 'Sale deleted successfully.');
    }

    // ── EXPORT XLSX ────────────────────────────────────────────────
    public function exportXlsx(Request $request)
    {
        $sales = $this->getFilteredSales($request);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sales');

        $headers = ['A'=>'#','B'=>'Invoice No','C'=>'Sale No','D'=>'Date','E'=>'Customer',
                    'F'=>'Total','G'=>'Paid','H'=>'Due','I'=>'Payment Status','J'=>'Status'];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col.'1', $label);
            $sheet->getStyle($col.'1')->getFont()->setBold(true);
            $sheet->getStyle($col.'1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1a237e');
            $sheet->getStyle($col.'1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach ($sales as $i => $sale) {
            $row = $i + 2;
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, $sale->invoice_no);
            $sheet->setCellValue('C'.$row, $sale->sale_no);
            $sheet->setCellValue('D'.$row, $sale->sale_date->format('d-m-Y'));
            $sheet->setCellValue('E'.$row, $sale->customer_name);
            $sheet->setCellValue('F'.$row, (float) $sale->total_amount);
            $sheet->setCellValue('G'.$row, (float) $sale->paid_amount);
            $sheet->setCellValue('H'.$row, (float) $sale->due_amount);
            $sheet->setCellValue('I'.$row, ucfirst($sale->payment_status));
            $sheet->setCellValue('J'.$row, ucfirst($sale->status));
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'sales-' . now()->format('Y-m-d') . '.xlsx';
        $tmpPath  = storage_path('app/' . $filename);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ── EXPORT PDF ─────────────────────────────────────────────────
    public function exportPdf(Request $request)
    {
        $sales = $this->getFilteredSales($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'inventory.sales.export-pdf',
            compact('sales')
        )->setPaper('a4', 'landscape');

        return $pdf->download('sales-' . now()->format('Y-m-d') . '.pdf');
    }

    private function getFilteredSales(Request $request)
    {
        return Sale::with('client', 'location')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->from, fn($q) => $q->whereDate('sale_date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('sale_date', '<=', $request->to))
            ->when($request->search, fn($q) => $q->where('sale_no', 'like', '%' . $request->search . '%')
                                                  ->orWhere('invoice_no', 'like', '%' . $request->search . '%'))
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->get();
    }

    // ── DETAIL — AJAX for view modal ──────────────────────────────
    public function detail(Sale $sale)
    {
        $sale->load('client', 'location', 'items.product', 'payments.createdBy');

        return response()->json([
            'success' => true,
            'sale' => [
                'invoice_no'    => $sale->invoice_no,
                'customer_name' => $sale->customer_name,
                'sale_date'     => $sale->sale_date->format('d M Y'),
                'location'      => $sale->location->name ?? '—',
                'status_badge'  => $sale->statusBadge,
                'subtotal'      => number_format($sale->subtotal, 2),
                'discount'      => number_format($sale->discount, 2),
                'total_amount'  => number_format($sale->total_amount, 2),
                'paid_amount'   => number_format($sale->paid_amount, 2),
                'due_amount'    => number_format($sale->due_amount, 2),
                'items' => $sale->items->map(fn($i) => [
                    'product_name' => $i->product->name ?? '—',
                    'quantity'     => $i->quantity,
                    'unit_price'   => number_format($i->unit_price, 2),
                    'discount'     => number_format($i->discount, 2),
                    'total_price'  => number_format($i->total_price, 2),
                ]),
                'payments' => $sale->payments->map(fn($p) => [
                    'id'           => $p->id,
                    'payment_date' => $p->payment_date->format('d M Y'),
                    'amount'       => number_format($p->amount, 2),
                    'method'       => strtoupper(str_replace('_', ' ', $p->payment_method)),
                    'is_void'      => (bool) $p->is_void,
                ]),
            ],
        ]);
    }

    // ── INVOICE PDF ────────────────────────────────────────────────
    public function invoicePdf(Sale $sale)
    {
        $sale->load('client', 'location', 'items.product', 'payments');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'inventory.sales.invoice-pdf',
            compact('sale')
        )->setPaper('a4', 'portrait');

        return $pdf->download('invoice-' . $sale->invoice_no . '.pdf');
    }
}
