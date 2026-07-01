<?php

namespace App\Http\Controllers\BandwidthBuy;

use App\Http\Controllers\Controller;
use App\Models\BandwidthBuy\BandwidthProvider;
use App\Models\BandwidthBuy\BandwidthService;
use App\Models\BandwidthBuy\BandwidthPurchase;
use App\Models\BandwidthBuy\BandwidthPurchaseLine;
use App\Models\BandwidthBuy\BandwidthPurchasePayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BandwidthPurchaseController extends Controller
{
    // ── Bandwidth expense category ────────────────────────────────
    private function getBandwidthCategoryId(): ?int
    {
        return ExpenseCategory::where('slug', 'isp-bandwidth')
            ->orWhere('name', 'ISP Bandwidth Cost')
            ->value('id');
    }

    // ── Description ───────────────────────────────────────────────
    private function expenseDescription(BandwidthPurchase $purchase): string
    {
        $services    = $purchase->lines->pluck('service.name')->filter()->implode(', ');
        $description = "Bandwidth Purchase — {$purchase->provider->company_name}";
        if ($services) $description .= " ({$services})";
        return $description;
    }

    // ── Create Expense for a payment ──────────────────────────────
    private function createExpenseForPayment(
        BandwidthPurchase $purchase,
        BandwidthPurchasePayment $payment
    ): ?Expense {
        $categoryId = $this->getBandwidthCategoryId();
        if (! $categoryId) return null;

        $expense = Expense::create([
            'category_id'       => $categoryId,
            'amount'            => $payment->amount,
            'expense_date'      => $payment->payment_date,
            'payment_method'    => $payment->payment_method,
            'payee'             => $purchase->provider->company_name,
            'reference_no'      => $purchase->invoice_no,
            'description'       => $this->expenseDescription($purchase)
                                   . " [Payment: ৳" . number_format($payment->amount, 2) . "]",
            'status'            => 'approved',
            'source_type'       => 'bandwidth_purchase',
            'source_id'         => $payment->id,
            'source_invoice_id' => $purchase->id,
            'created_by'        => auth()->id(),
            'approved_by'       => auth()->id(),
            'approved_at'       => now(),
        ]);

        return $expense;
    }

    public function paymentDetail(BandwidthPurchasePayment $payment)
    {
        $payment->load(['purchase.provider', 'createdBy']);

        return response()->json([
            'success' => true,
            'payment' => [
                'id'             => $payment->id,
                'invoice_no'     => $payment->purchase->invoice_no ?? '—',
                'provider'       => $payment->purchase->provider->company_name ?? '—',
                'payment_date'   => optional($payment->payment_date)->format('d M Y'),
                'amount'         => number_format($payment->amount, 2),
                'method'         => strtoupper($payment->payment_method),
                'transaction_no' => $payment->transaction_no ?? '—',
                'remarks'        => $payment->remarks ?? '—',
                'created_by'     => $payment->createdBy->name ?? '—',
                'is_void'        => $payment->status === 'void',
                'void_date'      => $payment->void_date ? $payment->void_date->format('d M Y h:i A') : '—',
                'void_by'        => $payment->void_by
                                    ? (\App\Models\User::find($payment->void_by)?->name ?? '—')
                                    : '—',
                'void_reason'    => $payment->void_reason ?? '—',
            ],
        ]);
    }

    public function allPaymentHistory(Request $request)
    {
        $query = BandwidthPurchasePayment::with(['purchase.provider', 'createdBy'])
            ->when($request->provider_id, fn($q) =>
                $q->whereHas('purchase', fn($p) => $p->where('provider_id', $request->provider_id))
            )
            ->when($request->from_date, fn($q) =>
                $q->whereDate('payment_date', '>=', $request->from_date)
            )
            ->when($request->to_date, fn($q) =>
                $q->whereDate('payment_date', '<=', $request->to_date)
            )
            ->when($request->status, fn($q) =>
                $q->where('status', $request->status)
            )
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        $payments    = $query->paginate($request->get('per_page', 20))->withQueryString();
        $providers   = BandwidthProvider::active()->orderBy('company_name')->get();
        $totalAmount = BandwidthPurchasePayment::where('status', 'active')
            ->when($request->provider_id, fn($q) =>
                $q->whereHas('purchase', fn($p) => $p->where('provider_id', $request->provider_id))
            )
            ->when($request->from_date, fn($q) => $q->whereDate('payment_date', '>=', $request->from_date))
            ->when($request->to_date,   fn($q) => $q->whereDate('payment_date', '<=', $request->to_date))
            ->sum('amount');

        return view('bandwidth-buy.purchase.payment-history',
            compact('payments', 'providers', 'totalAmount'));
    }

    public function allPaymentHistoryXlsx(Request $request)
    {
        $payments = $this->getFilteredAllPayments($request);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payment History');

        $headers = ['A'=>'#','B'=>'Payment Date','C'=>'Invoice No','D'=>'Provider',
                    'E'=>'Amount (৳)','F'=>'Method','G'=>'Tx No','H'=>'Remarks',
                    'I'=>'Created By'];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col.'1', $label);
            $sheet->getStyle($col.'1')->getFont()->setBold(true);
            $sheet->getStyle($col.'1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1a237e');
            $sheet->getStyle($col.'1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach ($payments as $i => $p) {
            $row = $i + 2;
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, optional($p->payment_date)->format('d-m-Y'));
            $sheet->setCellValue('C'.$row, $p->purchase->invoice_no ?? '—');
            $sheet->setCellValue('D'.$row, $p->purchase->provider->company_name ?? '—');
            $sheet->setCellValue('E'.$row, (float) $p->amount);
            $sheet->setCellValue('F'.$row, strtoupper($p->payment_method));
            $sheet->setCellValue('G'.$row, $p->transaction_no ?? '—');
            $sheet->setCellValue('H'.$row, $p->remarks ?? '—');
            $sheet->setCellValue('I'.$row, $p->createdBy->name ?? '—');
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'payment-history-' . now()->format('Y-m-d') . '.xlsx';
        $tmpPath  = storage_path('app/' . $filename);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function allPaymentHistoryPdf(Request $request)
    {
        $payments = $this->getFilteredAllPayments($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'bandwidth-buy.purchase.payment-history-pdf',
            compact('payments')
        )->setPaper('a4', 'landscape');

        return $pdf->download('payment-history-' . now()->format('Y-m-d') . '.pdf');
    }

    private function getFilteredAllPayments(Request $request)
    {
        return BandwidthPurchasePayment::with(['purchase.provider', 'createdBy'])
            ->when($request->provider_id, fn($q) =>
                $q->whereHas('purchase', fn($p) => $p->where('provider_id', $request->provider_id))
            )
            ->when($request->from_date, fn($q) =>
                $q->whereDate('payment_date', '>=', $request->from_date)
            )
            ->when($request->to_date, fn($q) =>
                $q->whereDate('payment_date', '<=', $request->to_date)
            )
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();
    }

    public function show(BandwidthPurchase $purchase)
    {
        $purchase->load(['provider', 'lines.service', 'payments.createdBy', 'createdBy']);
        return view('bandwidth-buy.purchase.show', compact('purchase'));
    }

    public function index(Request $request)
    {
        $query = BandwidthPurchase::with('provider', 'lines.service', 'payments')
            ->when($request->provider_id, fn($q) => $q->where('provider_id', $request->provider_id))
            ->when($request->from_date,   fn($q) => $q->whereDate('billing_date', '>=', $request->from_date))
            ->when($request->to_date,     fn($q) => $q->whereDate('billing_date', '<=', $request->to_date))
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'paid')    return $q->where('due', '<=', 0)->where('paid', '>', 0);
                if ($request->status === 'partial') return $q->where('paid', '>', 0)->where('due', '>', 0);
                if ($request->status === 'due')     return $q->where('paid', '<=', 0);
            })
            ->latest('billing_date');

        $purchases = $query->paginate(20)->withQueryString();
        $providers = BandwidthProvider::active()->orderBy('company_name')->get();

        return view('bandwidth-buy.purchase.index', compact('purchases', 'providers'));
    }

    public function create()
    {
        $providers = BandwidthProvider::active()->orderBy('company_name')->get();
        $services  = BandwidthService::orderBy('name')->get();
        return view('bandwidth-buy.purchase.create', compact('providers', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'provider_id'           => 'required|exists:bandwidth_providers,id',
            'invoice_no'            => 'required|string|max:100',
            'billing_date'          => 'required|date',
            'document'              => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'paid'                  => 'required|numeric|min:0',
            'payment_method'        => 'required_if:paid,>,0|nullable|in:cash,bkash,nagad,rocket,bank,cheque,card',
            'transaction_no'        => 'nullable|string|max:100',
            'payment_date'          => 'nullable|date',
            'bank_account'          => 'nullable|string|max:150',
            'lines'                 => 'required|array|min:1',
            'lines.*.service_id'    => 'required|exists:bandwidth_services,id',
            'lines.*.from_date'     => 'required|date',
            'lines.*.to_date'       => 'required|date',
            'lines.*.quantity_mb'   => 'required|numeric|min:0',
            'lines.*.rate'          => 'required|numeric|min:0',
            'lines.*.vat_percent'   => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')
                    ->store('bandwidth/purchases', 'public');
            }

            $subTotal = 0;
            $lineData = [];
            foreach ($request->lines as $line) {
                $total     = BandwidthPurchaseLine::computeTotal(
                    (float) $line['quantity_mb'],
                    (float) $line['rate'],
                    (float) $line['vat_percent']
                );
                $subTotal += $total;
                $lineData[] = array_merge($line, ['line_total' => $total]);
            }

            $paid = min((float) $request->paid, $subTotal);
            $due  = max(0, $subTotal - $paid);

            $purchase = BandwidthPurchase::create([
                'invoice_no'   => $request->invoice_no,
                'provider_id'  => $request->provider_id,
                'billing_date' => $request->billing_date,
                'document'     => $documentPath,
                'sub_total'    => $subTotal,
                'paid'         => $paid,
                'due'          => $due,
                'bank_account' => $request->bank_account ?: null,
                'created_by'   => auth()->id(),
            ]);

            foreach ($lineData as $line) {
                $purchase->lines()->create([
                    'service_id'  => $line['service_id'],
                    'from_date'   => $line['from_date'],
                    'to_date'     => $line['to_date'],
                    'quantity_mb' => $line['quantity_mb'],
                    'rate'        => $line['rate'],
                    'vat_percent' => $line['vat_percent'],
                    'line_total'  => $line['line_total'],
                ]);
            }

            // ── Payment History & Expense ──────────────────────────
            if ($paid > 0) {
                $purchase->load('provider', 'lines.service');
                $payment = BandwidthPurchasePayment::create([
                    'purchase_id'    => $purchase->id,
                    'amount'         => $paid,
                    'payment_date'   => $request->payment_date ?: $request->billing_date,
                    'payment_method' => $request->payment_method ?? 'bank',
                    'transaction_no' => $request->transaction_no,
                    'remarks'        => 'Initial payment on purchase creation',
                    'created_by'     => auth()->id(),
                ]);

                $expense = $this->createExpenseForPayment($purchase, $payment);
                if ($expense) {
                    $payment->update(['expense_id' => $expense->id]);
                }
            }

            DB::commit();

            return redirect()->route('bandwidth-buy.purchase.index')
                ->with('success', "Purchase bill saved & expense recorded in Accounting.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function edit(BandwidthPurchase $purchase)
    {
        if (! $purchase->isEditable()) {
            return redirect()->route('bandwidth-buy.purchase.index')
                ->with('error', 'This purchase has payments and cannot be edited.');
        }

        $purchase->load('lines.service');
        $providers = BandwidthProvider::active()->orderBy('company_name')->get();
        $services  = BandwidthService::active()->orderBy('name')->get();

        return view('bandwidth-buy.purchase.edit',
            compact('purchase', 'providers', 'services'));
    }

    public function update(Request $request, BandwidthPurchase $purchase)
    {
        if (! $purchase->isEditable()) {
            return redirect()->route('bandwidth-buy.purchase.index')
                ->with('error', 'This purchase has payments and cannot be edited.');
        }

        $request->validate([
            'provider_id'           => 'required|exists:bandwidth_providers,id',
            'invoice_no'            => 'required|string|max:100',
            'billing_date'          => 'required|date',
            'document'              => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'bank_account'          => 'nullable|string|max:150',
            'lines'                 => 'required|array|min:1',
            'lines.*.service_id'    => 'required|exists:bandwidth_services,id',
            'lines.*.from_date'     => 'required|date',
            'lines.*.to_date'       => 'required|date',
            'lines.*.quantity_mb'   => 'required|numeric|min:0',
            'lines.*.rate'          => 'required|numeric|min:0',
            'lines.*.vat_percent'   => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('document')) {
                if ($purchase->document) {
                    Storage::disk('public')->delete($purchase->document);
                }
                $purchase->document = $request->file('document')
                    ->store('bandwidth/purchases', 'public');
                $purchase->save();
            }

            $subTotal = 0;
            $lineData = [];
            foreach ($request->lines as $line) {
                $total     = BandwidthPurchaseLine::computeTotal(
                    (float) $line['quantity_mb'],
                    (float) $line['rate'],
                    (float) $line['vat_percent']
                );
                $subTotal += $total;
                $lineData[] = array_merge($line, ['line_total' => $total]);
            }

            $purchase->update([
                'invoice_no'   => $request->invoice_no,
                'provider_id'  => $request->provider_id,
                'billing_date' => $request->billing_date,
                'sub_total'    => $subTotal,
                'paid'         => 0,
                'due'          => $subTotal,
                'bank_account' => $request->bank_account ?: null,
            ]);

            $purchase->lines()->delete();
            foreach ($lineData as $line) {
                $purchase->lines()->create([
                    'service_id'  => $line['service_id'],
                    'from_date'   => $line['from_date'],
                    'to_date'     => $line['to_date'],
                    'quantity_mb' => $line['quantity_mb'],
                    'rate'        => $line['rate'],
                    'vat_percent' => $line['vat_percent'],
                    'line_total'  => $line['line_total'],
                ]);
            }

            DB::commit();

            return redirect()->route('bandwidth-buy.purchase.index')
                ->with('success', 'Purchase updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function pay(Request $request, BandwidthPurchase $purchase)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bkash,nagad,rocket,bank,cheque,card',
            'payment_date'   => 'required|date',
            'transaction_no' => 'nullable|string|max:100',
            'remarks'        => 'nullable|string|max:255',
        ]);

        // Amount check
        $due = (float) $purchase->due;
        if ((float) $request->amount > $due) {
            return response()->json([
                'success' => false,
                'message' => "Payment amount (৳{$request->amount}) cannot exceed Due (৳{$due}).",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $purchase->load('provider', 'lines.service');

            $payment = BandwidthPurchasePayment::create([
                'purchase_id'    => $purchase->id,
                'amount'         => $request->amount,
                'payment_date'   => $request->payment_date,
                'payment_method' => $request->payment_method,
                'transaction_no' => $request->transaction_no,
                'remarks'        => $request->remarks,
                'created_by'     => auth()->id(),
            ]);

            // Expense create
            $expense = $this->createExpenseForPayment($purchase, $payment);
            if ($expense) {
                $payment->update(['expense_id' => $expense->id]);
            }

            // Recalc paid/due
            $purchase->recalculateDue();

            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => "Payment of ৳" . number_format($request->amount, 2) . " recorded.",
                'paid'      => number_format($purchase->fresh()->paid, 2),
                'due'       => number_format($purchase->fresh()->due, 2),
                'status'    => $purchase->fresh()->statusLabel,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function paymentHistory(BandwidthPurchase $purchase)
    {
        $payments = $purchase->payments()
            ->with('createdBy')
            ->latest('payment_date')
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'payment_no'     => $p->id,
                'payment_date'   => optional($p->payment_date)->format('d M Y'),
                'amount'         => number_format($p->amount, 2),
                'payment_method' => strtoupper($p->payment_method),
                'transaction_no' => $p->transaction_no ?? '—',
                'remarks'        => $p->remarks ?? '—',
                'created_by'     => $p->createdBy->name ?? '—',
            ]);

        return response()->json([
            'success'  => true,
            'payments' => $payments,
            'total'    => number_format($purchase->payments()->sum('amount'), 2),
        ]);
    }

    public function void(Request $request, BandwidthPurchase $purchase)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        DB::beginTransaction();
        try {
            $purchase->load('provider', 'payments');

            // Linked expenses void
            foreach ($purchase->payments as $payment) {
                if ($payment->expense_id) {
                    $expense = \App\Models\Expense::find($payment->expense_id);
                    if ($expense && $expense->status !== 'void') {
                        $expense->update([
                            'status'        => 'void',
                            'reject_reason' => $request->reason,
                            'void_date'     => now(),
                            'void_by'       => auth()->id(),
                        ]);
                    }
                }
            }

            $purchase->delete();

            DB::commit();

            return redirect()->route('bandwidth-buy.purchase.index')
                ->with('success', "Purchase #{$purchase->invoice_no} voided. Linked expenses also voided.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function exportXlsx()
    {
        $purchases = BandwidthPurchase::with('provider', 'lines.service', 'payments')
            ->latest('billing_date')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Purchase Bills');

        $headers = ['A'=>'#','B'=>'Invoice No','C'=>'Provider','D'=>'Billing Date',
                    'E'=>'Sub Total','F'=>'Paid','G'=>'Due','H'=>'Status'];

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
            $sheet->setCellValue('B'.$row, $p->invoice_no);
            $sheet->setCellValue('C'.$row, $p->provider->company_name ?? '—');
            $sheet->setCellValue('D'.$row, optional($p->billing_date)->format('d-m-Y'));
            $sheet->setCellValue('E'.$row, (float) $p->sub_total);
            $sheet->setCellValue('F'.$row, (float) $p->paid);
            $sheet->setCellValue('G'.$row, (float) $p->due);
            $sheet->setCellValue('H'.$row, $p->statusLabel);
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'purchase-bills-' . now()->format('Y-m-d') . '.xlsx';
        $tmpPath  = storage_path('app/' . $filename);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function exportPdf()
    {
        $purchases = BandwidthPurchase::with('provider', 'lines.service', 'payments')
            ->latest('billing_date')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'bandwidth-buy.purchase.export-pdf',
            compact('purchases')
        )->setPaper('a4', 'landscape');

        return $pdf->download('purchase-bills-' . now()->format('Y-m-d') . '.pdf');
    }

    public function voidPayment(Request $request, BandwidthPurchasePayment $payment)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        if ($payment->status === 'void') {
            return response()->json(['success' => false, 'message' => 'Already voided.'], 422);
        }

        DB::beginTransaction();
        try {
            // Linked expense void
            if ($payment->expense_id) {
                $expense = \App\Models\Expense::find($payment->expense_id);
                if ($expense && $expense->status !== 'void') {
                    $expense->update([
                        'status'        => 'void',
                        'reject_reason' => $request->reason,
                        'void_date'     => now(),
                        'void_by'       => auth()->id(),
                    ]);
                }
            }

            // Void the payment record rather than deleting it, to preserve audit history.
            $payment->update([
                'status'      => 'void',
                'void_reason' => $request->reason,
                'void_date'   => now(),
                'void_by'     => auth()->id(),
            ]);

            // Recalc purchase paid/due
            $payment->purchase->recalculateDue();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Payment voided. Linked expense also voided.",
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(BandwidthPurchase $purchase)
    {
        if (! $purchase->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete — this purchase has payments.',
            ], 422);
        }

        $purchase->delete();

        return response()->json(['success' => true, 'message' => 'Purchase deleted.']);
    }
}
