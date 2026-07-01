<?php

namespace App\Http\Controllers\BandwidthSale;

use App\Http\Controllers\Controller;
use App\Models\BwsInvoice;
use App\Models\BwsInvoiceItem;
use App\Models\BwsInvoicePayment;
use App\Models\BandwidthSaleCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BwsInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = BwsInvoice::with(['bwsCustomer', 'createdBy'])
            ->when($request->from_month,  fn($q) => $q->where('billing_month', '>=', $request->from_month))
            ->when($request->to_month,    fn($q) => $q->where('billing_month', '<=', $request->to_month))
            ->when($request->status,      fn($q) => $q->where('status', $request->status))
            ->when($request->customer_id, fn($q) => $q->where('bws_customer_id', $request->customer_id))
            ->when($request->created_by,  fn($q) => $q->where('created_by', $request->created_by))
            ->latest();

        $invoices  = $query->paginate($request->get('per_page', 20))->withQueryString();
        $customers = BandwidthSaleCustomer::orderBy('customer_name')->get();

        $bwsServices = $this->getBwsServices();

        $employees = \App\Models\HR\Employee::select('id', 'name', 'user_id')
            ->where('status', 'active')
            ->whereNotNull('user_id')
            ->orderBy('name')
            ->get();

        $stats = [
            'total'    => BwsInvoice::count(),
            'paid'     => BwsInvoice::where('status', 'paid')->count(),
            'unpaid'   => BwsInvoice::whereIn('status', ['unpaid', 'overdue'])->count(),
            'received' => BwsInvoicePayment::where('status', 'active')->sum('received_amount'),
        ];

        return view('bandwidth-sale.invoices.index',
            compact('invoices', 'customers', 'employees', 'bwsServices', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bws_customer_id' => 'required|exists:bandwidth_sale_customers,id',
            'billing_month'   => 'required|date_format:Y-m',
            'grand_total'     => 'required|numeric|min:0',
            'items_json'      => 'required|json',
        ]);

        DB::beginTransaction();
        try {
            $invoice = BwsInvoice::create([
                'invoice_no'      => BwsInvoice::generateNumber(),
                'bws_customer_id' => $request->bws_customer_id,
                'billing_month'   => $request->billing_month,
                'payment_due'     => $request->payment_due,
                'daily_basis'     => $request->boolean('daily_basis'),
                'total_amount'    => $request->total_amount ?? 0,
                'vat_amount'      => $request->vat_amount ?? 0,
                'discount'        => $request->discount ?? 0,
                'grand_total'     => $request->grand_total,
                'received_amount' => 0,
                'due_amount'      => $request->grand_total,
                'status'          => $request->status ?? 'unpaid',
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
            ]);
            $this->saveItems($invoice->id, $request->items_json);

            // ── If received_amount > 0 → insert payment record ────────
            $receivedAmount = floatval($request->received_amount ?? 0);
            if ($receivedAmount > 0) {
                BwsInvoicePayment::create([
                    'payment_no'      => BwsInvoicePayment::generateNumber(),
                    'bws_invoice_id'  => $invoice->id,
                    'bws_customer_id' => $invoice->bws_customer_id,
                    'received_date'   => now()->format('Y-m-d'),
                    'received_by'     => auth()->id(),
                    'payment_method'  => $request->payment_method ?? 'cash',
                    'payable_amount'  => $invoice->grand_total,
                    'received_amount' => $receivedAmount,
                    'discount'        => 0,
                    'remarks'         => 'Auto: created with invoice',
                    'status'          => 'active',
                    'created_by'      => auth()->id(),
                ]);
                // recalcDue() is triggered automatically via the model's boot event.
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Invoice {$invoice->invoice_no} created.",
                'id'      => $invoice->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, BwsInvoice $bwsInvoice)
    {
        $bwsInvoice->load(['bwsCustomer', 'items', 'activePayments.receivedBy', 'createdBy']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'invoice' => [
                    'id'              => $bwsInvoice->id,
                    'invoice_no'      => $bwsInvoice->invoice_no,
                    'bws_customer_id' => $bwsInvoice->bws_customer_id,
                    'customer_name'   => $bwsInvoice->bwsCustomer?->customer_name,
                    'contact_person'  => $bwsInvoice->bwsCustomer?->contact_person,
                    'mobile'          => $bwsInvoice->bwsCustomer?->mobile_number,
                    'billing_month'   => $bwsInvoice->billing_month,
                    'payment_due'     => optional($bwsInvoice->payment_due)->format('Y-m-d'),
                    'daily_basis'     => $bwsInvoice->daily_basis,
                    'status'          => $bwsInvoice->status,
                    'total_amount'    => $bwsInvoice->total_amount,
                    'vat_amount'      => $bwsInvoice->vat_amount,
                    'discount'        => $bwsInvoice->discount,
                    'grand_total'     => $bwsInvoice->grand_total,
                    'received_amount' => $bwsInvoice->received_amount,
                    'due_amount'      => $bwsInvoice->due_amount,
                    'notes'           => $bwsInvoice->notes,
                    'items'           => $bwsInvoice->items->map(fn($i) => [
                        'item_name'   => $i->item_name,
                        'description' => $i->description,
                        'unit'        => $i->unit,
                        'quantity'    => $i->quantity,
                        'rate'        => $i->rate,
                        'vat_percent' => $i->vat_percent,
                        'from_date'   => optional($i->from_date)->format('Y-m-d'),
                        'to_date'     => optional($i->to_date)->format('Y-m-d'),
                        'total'       => $i->total,
                    ])->toArray(),
                    'payments' => $bwsInvoice->activePayments->map(fn($p) => [
                        'id'              => $p->id,
                        'payment_no'      => $p->payment_no,
                        'received_date'   => optional($p->received_date)->format('d M Y'),
                        'payment_method'  => $p->payment_method,
                        'received_amount' => $p->received_amount,
                        'discount'        => $p->discount,
                        'status'          => $p->status,
                    ])->toArray(),
                ],
            ]);
        }

        return view('bandwidth-sale.invoices.show', compact('bwsInvoice'));
    }

    public function edit(Request $request, BwsInvoice $bwsInvoice)
    {
        if (!in_array($bwsInvoice->status, ['unpaid', 'overdue'])) {
            return response()->json(['success' => false, 'message' => ucfirst($bwsInvoice->status).' invoices cannot be edited.'], 422);
        }

        $bwsInvoice->load(['bwsCustomer', 'items']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'invoice' => [
                    'id'              => $bwsInvoice->id,
                    'invoice_no'      => $bwsInvoice->invoice_no,
                    'bws_customer_id' => $bwsInvoice->bws_customer_id,
                    'billing_month'   => $bwsInvoice->billing_month,
                    'payment_due'     => optional($bwsInvoice->payment_due)->format('Y-m-d'),
                    'daily_basis'     => $bwsInvoice->daily_basis,
                    'status'          => $bwsInvoice->status,
                    'discount'        => $bwsInvoice->discount,
                    'received_amount' => $bwsInvoice->received_amount,
                    'notes'           => $bwsInvoice->notes,
                    'items'           => $bwsInvoice->items->map(fn($i) => [
                        'item_name'   => $i->item_name,
                        'description' => $i->description,
                        'unit'        => $i->unit,
                        'quantity'    => $i->quantity,
                        'rate'        => $i->rate,
                        'vat_percent' => $i->vat_percent,
                        'from_date'   => optional($i->from_date)->format('Y-m-d'),
                        'to_date'     => optional($i->to_date)->format('Y-m-d'),
                        'total'       => $i->total,
                    ])->toArray(),
                ],
            ]);
        }

        $customers = BandwidthSaleCustomer::where('activity_status', 'active')->orderBy('customer_name')->get();
        return view('bandwidth-sale.invoices.edit', compact('bwsInvoice', 'customers'));
    }

    public function update(Request $request, BwsInvoice $bwsInvoice)
    {
        if ($bwsInvoice->isPaid()) {
            return response()->json(['success' => false, 'message' => 'Paid invoices cannot be edited.'], 422);
        }

        $request->validate([
            'bws_customer_id' => 'required|exists:bandwidth_sale_customers,id',
            'billing_month'   => 'required|date_format:Y-m',
            'grand_total'     => 'required|numeric|min:0',
            'items_json'      => 'required|json',
        ]);

        DB::beginTransaction();
        try {
            $bwsInvoice->update([
                'bws_customer_id' => $request->bws_customer_id,
                'billing_month'   => $request->billing_month,
                'payment_due'     => $request->payment_due,
                'daily_basis'     => $request->boolean('daily_basis'),
                'total_amount'    => $request->total_amount ?? 0,
                'vat_amount'      => $request->vat_amount ?? 0,
                'discount'        => $request->discount ?? 0,
                'grand_total'     => $request->grand_total,
                'notes'           => $request->notes,
            ]);
            $bwsInvoice->items()->delete();
            $this->saveItems($bwsInvoice->id, $request->items_json);
            $bwsInvoice->recalcDue();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Invoice {$bwsInvoice->invoice_no} updated.",
                'id'      => $bwsInvoice->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(BwsInvoice $bwsInvoice)
    {
        if (!in_array($bwsInvoice->status, ['unpaid', 'overdue'])) {
            return response()->json(['success' => false, 'message' => ucfirst($bwsInvoice->status).' invoices cannot be deleted.']);
        }
        if ($bwsInvoice->activePayments()->exists()) {
            return response()->json(['success' => false, 'message' => 'Payments exist — cannot delete.']);
        }
        $no = $bwsInvoice->invoice_no;
        $bwsInvoice->items()->delete();
        $bwsInvoice->delete();
        return response()->json(['success' => true, 'message' => "Invoice {$no} deleted."]);
    }

    public function pdf(BwsInvoice $bwsInvoice)
    {
        $bwsInvoice->load(['bwsCustomer', 'items', 'createdBy']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'bandwidth-sale.invoices.pdf', compact('bwsInvoice')
        )->setPaper('a4');
        return $pdf->download("invoice-{$bwsInvoice->invoice_no}.pdf");
    }

    public function exportPdf(Request $request)
    {
        $invoices = $this->getFilteredInvoices($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'bandwidth-sale.invoices.export-pdf',
            compact('invoices')
        )->setPaper('a4', 'landscape');

        return $pdf->download('bws-invoices-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportXlsx(Request $request)
    {
        $invoices = $this->getFilteredInvoices($request);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BWS Invoices');

        // Header row
        $headers = [
            'A' => '#',
            'B' => 'Invoice No',
            'C' => 'Customer',
            'D' => 'Contact Person',
            'E' => 'Billing Month',
            'F' => 'Sub Total',
            'G' => 'VAT',
            'H' => 'Invoice Total',
            'I' => 'Discount',
            'J' => 'Grand Total',
            'K' => 'Received',
            'L' => 'Balance Due',
            'M' => 'Status',
            'N' => 'Created By',
            'O' => 'Date',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col.'1', $label);
            $sheet->getStyle($col.'1')->getFont()->setBold(true);
            $sheet->getStyle($col.'1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2C3E50');
            $sheet->getStyle($col.'1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        // Data rows
        foreach ($invoices as $i => $inv) {
            $row = $i + 2;
            $invoiceTotal = $inv->total_amount + $inv->vat_amount;
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, $inv->invoice_no);
            $sheet->setCellValue('C'.$row, $inv->bwsCustomer->customer_name ?? '—');
            $sheet->setCellValue('D'.$row, $inv->bwsCustomer->contact_person ?? '—');
            $sheet->setCellValue('E'.$row, \Carbon\Carbon::parse($inv->billing_month.'-01')->format('M Y'));
            $sheet->setCellValue('F'.$row, (float)$inv->total_amount);
            $sheet->setCellValue('G'.$row, (float)$inv->vat_amount);
            $sheet->setCellValue('H'.$row, (float)$invoiceTotal);
            $sheet->setCellValue('I'.$row, (float)$inv->discount);
            $sheet->setCellValue('J'.$row, (float)$inv->grand_total);
            $sheet->setCellValue('K'.$row, (float)$inv->received_amount);
            $sheet->setCellValue('L'.$row, (float)$inv->due_amount);
            $sheet->setCellValue('M'.$row, ucfirst($inv->status));
            $sheet->setCellValue('N'.$row, $inv->createdBy->name ?? '—');
            $sheet->setCellValue('O'.$row, optional($inv->created_at)->format('d/m/Y'));

            // Color due amount red if > 0
            if ($inv->due_amount > 0) {
                $sheet->getStyle('L'.$row)->getFont()->getColor()->setRGB('DC3545');
            }
        }

        // Auto size columns
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Total row
        $lastRow = count($invoices) + 2;
        $sheet->setCellValue('A'.$lastRow, 'Total');
        $sheet->setCellValue('F'.$lastRow, $invoices->sum('total_amount'));
        $sheet->setCellValue('G'.$lastRow, $invoices->sum('vat_amount'));
        $sheet->setCellValue('J'.$lastRow, $invoices->sum('grand_total'));
        $sheet->setCellValue('K'.$lastRow, $invoices->sum('received_amount'));
        $sheet->setCellValue('L'.$lastRow, $invoices->sum('due_amount'));
        $sheet->getStyle('A'.$lastRow.':O'.$lastRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$lastRow.':O'.$lastRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8F4FD');

        // Write file
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'bws-invoices-' . now()->format('Y-m-d') . '.xlsx';
        $tmpPath  = storage_path('app/tmp_' . $filename);
        $writer->save($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function getFilteredInvoices(Request $request)
    {
        return BwsInvoice::with(['bwsCustomer', 'createdBy'])
            ->when($request->from_month,  fn($q) => $q->where('billing_month', '>=', $request->from_month))
            ->when($request->to_month,    fn($q) => $q->where('billing_month', '<=', $request->to_month))
            ->when($request->status,      fn($q) => $q->where('status', $request->status))
            ->when($request->customer_id, fn($q) => $q->where('bws_customer_id', $request->customer_id))
            ->when($request->created_by,  fn($q) => $q->where('created_by', $request->created_by))
            ->latest()
            ->get();
    }

    public function nextNo()
    {
        return response()->json(['invoice_no' => BwsInvoice::generateNumber()]);
    }

    public function dueForCustomer(BandwidthSaleCustomer $customer)
    {
        $invoices = BwsInvoice::where('bws_customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->orderByDesc('billing_month')
            ->get(['id', 'invoice_no', 'billing_month', 'grand_total', 'received_amount', 'due_amount']);
        return response()->json(['success' => true, 'invoices' => $invoices]);
    }

    public function receiveData(BwsInvoice $bwsInvoice)
    {
        $bwsInvoice->load(['bwsCustomer', 'activePayments']);

        // Only sum received_amount — discount is separate
        $prevPaid     = $bwsInvoice->activePayments->sum('received_amount');
        $prevDiscount = $bwsInvoice->activePayments->sum('discount');

        return response()->json([
            'success'          => true,
            'invoice_no'       => $bwsInvoice->invoice_no,
            'billing_month'    => $bwsInvoice->billing_month,
            'customer_name'    => $bwsInvoice->bwsCustomer->customer_name,
            'mobile'           => $bwsInvoice->bwsCustomer->mobile_number,
            'payable_amount'   => $bwsInvoice->grand_total,
            'previous_paid'    => $prevPaid,
            'previous_discount'=> $prevDiscount,
            'balance_due'      => $bwsInvoice->due_amount,
        ]);
    }

    public function receiveStore(Request $request, BwsInvoice $bwsInvoice)
    {
        $request->validate([
            'received_date'   => 'required|date',
            'received_amount' => 'required|numeric|min:0.01',
            'payment_method'  => 'required|in:cash,bkash,nagad,rocket,bank,cheque,card',
        ]);

        // ── Duplicate prevention — same invoice, same amount, same date, within 10 seconds
        $duplicate = BwsInvoicePayment::where('bws_invoice_id', $bwsInvoice->id)
            ->where('received_amount', $request->received_amount)
            ->where('received_date',   $request->received_date)
            ->where('status', 'active')
            ->where('created_at', '>=', now()->subSeconds(10))
            ->exists();

        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate payment detected. Please wait a moment and try again.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payment = BwsInvoicePayment::create([
                'payment_no'             => BwsInvoicePayment::generateNumber(),
                'bws_invoice_id'         => $bwsInvoice->id,
                'bws_customer_id'        => $bwsInvoice->bws_customer_id,
                'received_date'          => $request->received_date,
                'received_from'          => $request->received_from,
                'received_by'            => $request->received_by,
                'payment_method'         => $request->payment_method,
                'payable_amount'         => $bwsInvoice->due_amount,
                'received_amount'        => $request->received_amount,
                'discount'               => $request->discount ?? 0,
                'receipt_transaction_no' => $request->receipt_transaction_no,
                'remarks'                => $request->remarks,
                'status'                 => 'active',
                'created_by'             => auth()->id(),
            ]);
            DB::commit();
            return response()->json([
                'success'    => true,
                'message'    => "Payment {$payment->payment_no} saved. Income auto-recorded.",
                'payment_no' => $payment->payment_no,
                'income_no'  => $payment->income?->income_no,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function voidPayment(Request $request, BwsInvoicePayment $payment)
    {
        $request->validate(['reason' => 'required|string|max:255']);
        if ($payment->isVoid()) {
            return response()->json(['success' => false, 'message' => 'Already voided.']);
        }
        $payment->voidPayment($request->reason);
        return response()->json(['success' => true, 'message' => 'Payment voided. Income also voided.']);
    }

    public function deleteSelected(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $payments = BwsInvoicePayment::whereIn('id', $request->ids)->get();

        foreach ($payments as $payment) {
            if ($payment->isVoid()) {
                $payment->forceDelete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' payment(s) deleted.',
        ]);
    }

    public function approveSelected(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        BwsInvoicePayment::whereIn('id', $request->ids)
            ->where('status', 'active')
            ->update(['approved' => 1, 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' payment(s) approved.',
        ]);
    }

    public function dailyBill(Request $request)
    {
        $query = BwsInvoicePayment::with(['bwsInvoice', 'bwsCustomer', 'receivedBy', 'createdBy'])
            ->when($request->customer_id, fn($q) =>
                $q->where('bws_customer_id', $request->customer_id)
            )
            ->when($request->from_month, fn($q) =>
                $q->whereHas('bwsInvoice', fn($i) => $i->where('billing_month', '>=', $request->from_month))
            )
            ->when($request->to_month, fn($q) =>
                $q->whereHas('bwsInvoice', fn($i) => $i->where('billing_month', '<=', $request->to_month))
            )
            ->when($request->received_by, fn($q) => $q->where('received_by', $request->received_by))
            ->when($request->created_by,  fn($q) => $q->where('created_by', $request->created_by))
            ->when($request->tx_status,   fn($q) => $q->where('status', $request->tx_status))
            ->orderByDesc('received_date')
            ->orderByDesc('id');

        $payments  = $query->paginate($request->get('per_page', 100))->withQueryString();
        $customers = BandwidthSaleCustomer::orderBy('customer_name')->get();
        $pops      = BandwidthSaleCustomer::whereNotNull('pop_info')->distinct()->pluck('pop_info');

        $employees = \App\Models\HR\Employee::select('id', 'name', 'user_id')
            ->where('status', 'active')
            ->whereNotNull('user_id')
            ->orderBy('name')
            ->get();

        return view('bandwidth-sale.daily-bill.index',
            compact('payments', 'customers', 'pops', 'employees'));
    }

    public function dailyBillExportXlsx(Request $request)
    {
        $payments = $this->getFilteredPayments($request);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payment History');

        $headers = ['A'=>'#','B'=>'R.Date','C'=>'Customer','D'=>'Contact','E'=>'Mobile',
                    'F'=>'Invoice No','G'=>'Bill Month','H'=>'Bill Amount',
                    'I'=>'Received','J'=>'Discount','K'=>'Balance Due',
                    'L'=>'Received By','M'=>'Created By','N'=>'Created On',
                    'O'=>'Remarks','P'=>'Status'];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col.'1', $label);
            $sheet->getStyle($col.'1')->getFont()->setBold(true);
            $sheet->getStyle($col.'1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2C3E50');
            $sheet->getStyle($col.'1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach ($payments as $i => $pay) {
            $row = $i + 2;
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, optional($pay->received_date)->format('d-m-Y'));
            $sheet->setCellValue('C'.$row, $pay->bwsCustomer->customer_name ?? '—');
            $sheet->setCellValue('D'.$row, $pay->bwsCustomer->contact_person ?? '—');
            $sheet->setCellValue('E'.$row, $pay->bwsCustomer->mobile_number ?? '—');
            $sheet->setCellValue('F'.$row, $pay->bwsInvoice->invoice_no ?? '—');
            $sheet->setCellValue('G'.$row, $pay->bwsInvoice->billing_month ?? '—');
            $sheet->setCellValue('H'.$row, (float)($pay->bwsInvoice->grand_total ?? 0));
            $sheet->setCellValue('I'.$row, (float)$pay->received_amount);
            $sheet->setCellValue('J'.$row, (float)$pay->discount);
            $sheet->setCellValue('K'.$row, (float)($pay->bwsInvoice->due_amount ?? 0));
            $sheet->setCellValue('L'.$row, $pay->receivedBy->name ?? '—');
            $sheet->setCellValue('M'.$row, $pay->createdBy->name ?? '—');
            $sheet->setCellValue('N'.$row, optional($pay->created_at)->format('d/m/Y'));
            $sheet->setCellValue('O'.$row, $pay->remarks ?? '');
            $sheet->setCellValue('P'.$row, ucfirst($pay->status));
        }

        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'payment-history-' . now()->format('Y-m-d') . '.xlsx';
        $tmpPath  = storage_path('app/' . $filename);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function dailyBillExportPdf(Request $request)
    {
        $payments = $this->getFilteredPayments($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'bandwidth-sale.daily-bill.export-pdf',
            compact('payments')
        )->setPaper('a4', 'landscape');

        return $pdf->download('payment-history-' . now()->format('Y-m-d') . '.pdf');
    }

    private function getFilteredPayments(Request $request)
    {
        return BwsInvoicePayment::with(['bwsInvoice', 'bwsCustomer', 'receivedBy', 'createdBy'])
            ->when($request->customer_id, fn($q) =>
                $q->where('bws_customer_id', $request->customer_id)
            )
            ->when($request->from_month, fn($q) =>
                $q->whereHas('bwsInvoice', fn($i) => $i->where('billing_month', '>=', $request->from_month))
            )
            ->when($request->to_month, fn($q) =>
                $q->whereHas('bwsInvoice', fn($i) => $i->where('billing_month', '<=', $request->to_month))
            )
            ->when($request->received_by, fn($q) => $q->where('received_by', $request->received_by))
            ->when($request->created_by,  fn($q) => $q->where('created_by', $request->created_by))
            ->when($request->tx_status,   fn($q) => $q->where('status', $request->tx_status))
            ->orderByDesc('received_date')
            ->orderByDesc('id')
            ->get();
    }
    public function recurringIndex()
    {
        $invoices = BwsInvoice::with('bwsCustomer')
            ->where('is_recurring', 1)
            ->latest()
            ->paginate(request('per_page', 10));

        $customers   = BandwidthSaleCustomer::where('activity_status', 'active')
                        ->orderBy('customer_name')->get();
        $bwsServices = $this->getBwsServices();

        return view('bandwidth-sale.recurring.index',
            compact('invoices', 'customers', 'bwsServices'));
    }

    public function recurringStore(Request $request)
    {
        $request->validate([
            'bws_customer_id' => 'required|exists:bandwidth_sale_customers,id',
            'billing_month'   => 'required|date_format:Y-m',
            'repeat_date'     => 'required|integer|min:1|max:28',
            'grand_total'     => 'required|numeric|min:0',
            'items_json'      => 'required|json',
        ]);

        DB::beginTransaction();
        try {
            $invoice = BwsInvoice::create([
                'invoice_no'      => BwsInvoice::generateNumber(),
                'bws_customer_id' => $request->bws_customer_id,
                'billing_month'   => $request->billing_month,
                'payment_due'     => $request->payment_due,
                'daily_basis'     => $request->boolean('daily_basis'),
                'total_amount'    => $request->total_amount ?? 0,
                'vat_amount'      => $request->vat_amount ?? 0,
                'discount'        => $request->discount ?? 0,
                'grand_total'     => $request->grand_total,
                'due_amount'      => $request->grand_total,
                'status'          => 'unpaid',
                'notes'           => $request->notes,
                'is_recurring'    => 1,
                'repeat_date'     => $request->repeat_date,
                'recurring_start' => $request->start_date,
                'recurring_end'   => $request->end_date ?: null,
                'created_by'      => auth()->id(),
            ]);
            $this->saveItems($invoice->id, $request->items_json);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Recurring invoice {$invoice->invoice_no} created.",
                'id'      => $invoice->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function recurringEdit(Request $request, BwsInvoice $bwsInvoice)
    {
        $bwsInvoice->load(['bwsCustomer', 'items']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'invoice' => [
                    'id'              => $bwsInvoice->id,
                    'invoice_no'      => $bwsInvoice->invoice_no,
                    'bws_customer_id' => $bwsInvoice->bws_customer_id,
                    'billing_month'   => $bwsInvoice->billing_month,
                    'repeat_date'     => $bwsInvoice->repeat_date,
                    'recurring_start' => optional($bwsInvoice->recurring_start)->format('Y-m-d'),
                    'recurring_end'   => optional($bwsInvoice->recurring_end)->format('Y-m-d'),
                    'daily_basis'     => $bwsInvoice->daily_basis,
                    'discount'        => $bwsInvoice->discount,
                    'grand_total'     => $bwsInvoice->grand_total,
                    'notes'           => $bwsInvoice->notes,
                    'items'           => $bwsInvoice->items->map(fn($i) => [
                        'item_name'   => $i->item_name,
                        'description' => $i->description,
                        'unit'        => $i->unit,
                        'quantity'    => $i->quantity,
                        'rate'        => $i->rate,
                        'vat_percent' => $i->vat_percent,
                        'from_date'   => optional($i->from_date)->format('Y-m-d'),
                        'to_date'     => optional($i->to_date)->format('Y-m-d'),
                        'total'       => $i->total,
                    ])->toArray(),
                ],
            ]);
        }

        $customers = BandwidthSaleCustomer::where('activity_status', 'active')
                        ->orderBy('customer_name')->get();
        return view('bandwidth-sale.recurring.edit', compact('bwsInvoice', 'customers'));
    }

    public function recurringUpdate(Request $request, BwsInvoice $bwsInvoice)
    {
        $request->validate([
            'bws_customer_id' => 'required|exists:bandwidth_sale_customers,id',
            'repeat_date'     => 'required|integer|min:1|max:28',
            'grand_total'     => 'required|numeric|min:0',
            'items_json'      => 'required|json',
        ]);

        DB::beginTransaction();
        try {
            $bwsInvoice->update([
                'bws_customer_id' => $request->bws_customer_id,
                'billing_month'   => $request->billing_month,
                'total_amount'    => $request->total_amount ?? 0,
                'vat_amount'      => $request->vat_amount ?? 0,
                'discount'        => $request->discount ?? 0,
                'grand_total'     => $request->grand_total,
                'repeat_date'     => $request->repeat_date,
                'recurring_start' => $request->start_date,
                'recurring_end'   => $request->end_date ?: null,
                'notes'           => $request->notes,
            ]);
            $bwsInvoice->items()->delete();
            $this->saveItems($bwsInvoice->id, $request->items_json);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Recurring invoice updated.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function recurringDestroy(BwsInvoice $bwsInvoice)
    {
        $bwsInvoice->items()->delete();
        $bwsInvoice->delete();
        return response()->json(['success' => true, 'message' => 'Recurring invoice deleted.']);
    }

    public function dashboard()
    {
        $totalCustomers  = BandwidthSaleCustomer::count();
        $activeCustomers = BandwidthSaleCustomer::where('activity_status', 'active')->count();
        $totalInvoices   = BwsInvoice::count();
        $paidInvoices    = BwsInvoice::where('status', 'paid')->count();
        $dueInvoices     = BwsInvoice::whereIn('status', ['unpaid', 'overdue'])->count();
        $thisMonthIncome = BwsInvoicePayment::where('status', 'active')
            ->whereYear('received_date', now()->year)
            ->whereMonth('received_date', now()->month)
            ->sum('received_amount');
        $totalDue = BwsInvoice::whereIn('status', ['unpaid', 'partial', 'overdue'])->sum('due_amount');

        return view('bandwidth-sale.dashboard', compact(
            'totalCustomers', 'activeCustomers', 'totalInvoices',
            'paidInvoices', 'dueInvoices', 'thisMonthIncome', 'totalDue'
        ));
    }

    private function saveItems(int $invoiceId, string $itemsJson): void
    {
        $items = json_decode($itemsJson, true);
        foreach ($items as $i => $item) {
            // skip empty rows
            if (empty($item['rate']) && empty($item['quantity'])) continue;

            BwsInvoiceItem::create([
                'bws_invoice_id' => $invoiceId,
                // blade sends 'item_name' key (service id or name)
                'item_name'      => $item['item_name'] ?? $item['item_id'] ?? null,
                'description'    => $item['description'] ?? null,
                'unit'           => $item['unit'] ?? null,
                'quantity'       => $item['quantity'] ?? 1,
                'rate'           => $item['rate'] ?? 0,
                'vat_percent'    => $item['vat'] ?? $item['vat_percent'] ?? 0,
                'from_date'      => !empty($item['from_date']) ? $item['from_date'] : null,
                'to_date'        => !empty($item['to_date'])   ? $item['to_date']   : null,
                'total'          => $item['total'] ?? 0,
                'sort_order'     => $i,
            ]);
        }
    }

    private function getBwsServices(): \Illuminate\Support\Collection
    {
        return \App\Models\BandwidthBuy\BandwidthService::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'unit' => '']);
    }
}
