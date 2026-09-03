<?php

namespace App\Http\Controllers\MacReseller;

use App\Http\Controllers\Controller;
use App\Models\MacReseller;
use App\Models\MacResellerFunding;
use App\Models\MacResellerFundPayment;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MacResellerFundingController extends Controller
{
    public function index(Request $request)
    {
        $query = MacResellerFunding::with(['reseller', 'fundGivenBy', 'receivedBy'])->latest();

        if ($request->reseller_id)        $query->where('reseller_id', $request->reseller_id);
        if ($request->transaction_status) $query->where('transaction_status', $request->transaction_status);
        if ($request->from_date)          $query->whereDate('funding_date', '>=', $request->from_date);
        if ($request->to_date)            $query->whereDate('funding_date', '<=', $request->to_date);
        if ($request->payment_by)         $query->where('fund_given_by', $request->payment_by);
        if ($request->received_by)        $query->where('received_by', $request->received_by);
        if ($request->restrict_status !== null && $request->restrict_status !== '') {
            $query->where('restrict_online', $request->restrict_status);
        }

        $fundings  = $query->paginate(25);
        $resellers = MacReseller::orderBy('business_name')->get();
        $employees = User::orderBy('name')->get();

        return view('mac-reseller.funding.index', compact('fundings', 'resellers', 'employees'));
    }

    /**
     * Create a new Fund "invoice" — the amount the reseller intends to fund.
     * If a `payment` amount is included (initial installment), it's recorded
     * as the first row in mac_reseller_fund_payments and (if > 0) generates
     * an Income entry, and bumps the reseller's remaining_fund wallet.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reseller_id'   => 'required|exists:mac_resellers,id',
            'fund_amount'   => 'required|numeric|min:1',
            'payment'       => 'nullable|numeric|min:0',
            'apply_vat'     => 'nullable|boolean',
            'vat'           => 'nullable|numeric|min:0',
            'discount'      => 'nullable|numeric|min:0',
            'received_by'   => 'nullable|exists:users,id',
            'received_date' => 'nullable|date',
            'remarks'       => 'nullable|string',
        ]);

        $fundAmount = (float) $data['fund_amount'];
        $payment    = (float) ($data['payment'] ?? 0);
        $vat        = (float) ($data['vat'] ?? 0);
        $discount   = (float) ($data['discount'] ?? 0);
        $netAmount  = $fundAmount + $vat - $discount;

        // "Received amount must never exceed the net fund amount" — enforced here.
        if ($payment > $netAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Received amount cannot exceed the net fund amount.',
            ], 422);
        }

        $funding = DB::transaction(function () use ($data, $fundAmount, $payment, $vat, $discount) {
            $funding = MacResellerFunding::create([
                'reseller_id'        => $data['reseller_id'],
                'invoice_number'     => MacResellerFunding::generateInvoiceNumber(),
                'fund_amount'        => $fundAmount,
                'apply_vat'          => $data['apply_vat'] ?? false,
                'vat'                => $vat,
                'discount'           => $discount,
                'processing_fee'     => 0,
                'payment'            => 0,   // recalculated below
                'due_amount'         => $fundAmount + $vat - $discount, // recalculated below
                'funding_date'       => now()->toDateString(),
                'fund_given_by'      => auth()->id(),
                'transaction_status' => 'due', // recalculated below
                'remarks'            => $data['remarks'] ?? null,
            ]);

            if ($payment > 0) {
                $this->addPayment($funding, $payment, [
                    'payment_method' => 'cash', // form doesn't currently collect method — defaults to cash
                    'received_by'    => $data['received_by'] ?? auth()->id(),
                    'received_date'  => $data['received_date'] ?? now()->toDateString(),
                    'remarks'        => $data['remarks'] ?? null,
                ]);
            }

            return $funding;
        });

        return response()->json([
            'success' => true,
            'message' => 'Fund transaction saved.',
            'invoice' => $funding->invoice_number,
        ]);
    }

    /**
     * Record a new installment payment against an existing funding record.
     * Replaces the old "pay everything due at once" behavior — now accepts
     * a specific amount, so partial installments work. Route/URL kept as
     * '/paid' to match the existing blade view's hardcoded JS call; if no
     * amount is sent, defaults to the full due amount (old one-click button
     * behavior still works unchanged).
     */
    public function markPaid(Request $request, MacResellerFunding $funding)
    {
        $request->validate([
            'amount'         => 'nullable|numeric|min:0.01',
            'payment_method' => 'nullable|in:cash,bkash,nagad,rocket,card,bank',
            'remarks'        => 'nullable|string',
        ]);

        $amount = (float) ($request->amount ?? $funding->due_amount);

        if ($amount > $funding->due_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Amount cannot exceed the due amount (' . number_format($funding->due_amount, 2) . ').',
            ], 422);
        }

        DB::transaction(function () use ($funding, $amount, $request) {
            $this->addPayment($funding, $amount, [
                'payment_method' => $request->payment_method ?? 'cash',
                'received_by'    => auth()->id(),
                'received_date'  => now()->toDateString(),
                'remarks'        => $request->remarks,
            ]);
        });

        return response()->json(['success' => true]);
    }

    /**
     * Void a single payment installment (not the whole funding record).
     * Reverses its Income entry, decrements the reseller's remaining_fund
     * wallet back, and recalculates the parent funding's cached totals.
     * Replaces the old refund() which wiped the ENTIRE funding's payment —
     * that no longer makes sense once a funding can have multiple
     * independent installments.
     */
    public function voidPayment(Request $request, MacResellerFundPayment $payment)
    {
        $request->validate([
            'void_reason' => 'required|string|max:255',
        ]);

        if ($payment->isVoid()) {
            return response()->json(['success' => false, 'message' => 'This payment is already void.'], 422);
        }

        DB::transaction(function () use ($payment, $request) {
            $payment->update([
                'status'      => 'void',
                'void_reason' => $request->void_reason,
                'void_by'     => auth()->id(),
                'void_date'   => now(),
            ]);

            if ($payment->income_id) {
                Income::where('id', $payment->income_id)->update([
                    'status'      => 'void',
                    'void_reason' => 'MAC Reseller fund payment voided: ' . $request->void_reason,
                    'void_date'   => now(),
                    'void_by'     => auth()->id(),
                ]);
            }

            $payment->funding->reseller?->decrement('remaining_fund', $payment->amount);

            $this->recalculateFunding($payment->funding);
        });

        return response()->json(['success' => true]);
    }

    public function toggleRestrict(MacResellerFunding $funding)
    {
        $funding->update(['restrict_online' => !$funding->restrict_online]);
        return response()->json(['success' => true]);
    }

    public function bulkToggleRestrict(Request $request)
    {
        $request->validate(['action' => 'required|in:block,unblock']);
        $restrict = $request->action === 'block';
        MacReseller::query()->update(['restrict_online_payment' => $restrict]);
        return response()->json(['success' => true]);
    }

    /** "Fund Received History" report — flat list of individual payment installments. */
    public function history(Request $request)
    {
        $query = MacResellerFundPayment::with(['funding.reseller', 'receivedBy'])->latest();

        if ($request->reseller_id) {
            $query->whereHas('funding', fn($q) => $q->where('reseller_id', $request->reseller_id));
        }
        if ($request->status)    $query->where('status', $request->status);
        if ($request->from_date) $query->whereDate('received_date', '>=', $request->from_date);
        if ($request->to_date)   $query->whereDate('received_date', '<=', $request->to_date);

        $payments  = $query->paginate(25);
        $resellers = MacReseller::orderBy('business_name')->get();

        return view('mac-reseller.funding.history', compact('payments', 'resellers'));
    }

    public function downloadPdf(Request $request)
    {
        $fundings = MacResellerFunding::with('reseller')->latest()->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mac-reseller.funding.pdf', compact('fundings'));
        return $pdf->download('mac-reseller-funding.pdf');
    }

    public function downloadExcel(Request $request)
    {
        $fundings = MacResellerFunding::with('reseller', 'fundGivenBy')->latest()->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('MAC Reseller Funding');

        $headers = ['#', 'Reseller', 'Invoice No.', 'Fund Amount', 'Paid', 'Due', 'Funding Date', 'Given By', 'Status'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $row = 2;
        foreach ($fundings as $i => $f) {
            $sheet->fromArray([
                $i + 1,
                $f->reseller?->business_name,
                $f->invoice_number,
                (float) $f->fund_amount,
                (float) $f->payment,
                (float) $f->due_amount,
                $f->funding_date?->format('d/m/Y'),
                $f->fundGivenBy?->name,
                ucfirst($f->transaction_status),
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'mac-reseller-funding-' . now()->format('Y-m-d') . '.xlsx';
        $tempPath = storage_path('app/' . $filename);
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    // ── Private Helpers ──────────────────────────────────────────

    /**
     * Adds one payment installment row, creates its Income entry (if
     * amount > 0), bumps the reseller's remaining_fund wallet, updates the
     * funding's "last payment" snapshot columns (received_date/received_by
     * — kept for the existing blade view's table columns), and recalculates
     * the funding's cached totals. Centralized here so store() and
     * markPaid() can't drift out of sync.
     */
    private function addPayment(MacResellerFunding $funding, float $amount, array $meta): MacResellerFundPayment
    {
        $payment = MacResellerFundPayment::create([
            'funding_id'     => $funding->id,
            'amount'         => $amount,
            'payment_method' => $meta['payment_method'] ?? 'cash',
            'received_by'    => $meta['received_by'] ?? null,
            'received_date'  => $meta['received_date'] ?? now()->toDateString(),
            'remarks'        => $meta['remarks'] ?? null,
            'status'         => 'active',
        ]);

        if ($amount > 0) {
            $category = IncomeCategory::where('slug', 'mac-reseller-funding')->first();

            $income = Income::create([
                'category_id'    => $category?->id,
                'amount'         => $amount,
                'income_date'    => $payment->received_date,
                'payment_method' => $payment->payment_method,
                'payer'          => $funding->reseller->business_name ?? null,
                'reference_no'   => $funding->invoice_number,
                'description'    => 'MAC Reseller fund payment — ' . ($funding->reseller->business_name ?? ''),
                'status'         => 'active',
                'source_type'    => Income::SOURCE_MAC_RESELLER_FUNDING,
                'source_id'      => $payment->id,
                'created_by'     => auth()->id(),
            ]);

            $payment->update(['income_id' => $income->id]);

            $funding->reseller?->increment('remaining_fund', $amount);
        }

        // Cache "last payment" snapshot on the funding record itself —
        // matches the existing blade view's "ReceivedDate(Last)"/"ReceivedBy(Last)" columns.
        $funding->update([
            'received_date' => $payment->received_date,
            'received_by'   => $payment->received_by,
        ]);

        $this->recalculateFunding($funding);

        return $payment;
    }

    /**
     * Recomputes the funding's cached payment/due_amount/transaction_status
     * from its active (non-void) payment history rows. Called after every
     * payment add/void — never edited independently, per design decision.
     */
    private function recalculateFunding(MacResellerFunding $funding): void
    {
        $netAmount = (float) $funding->fund_amount + (float) $funding->vat - (float) $funding->discount;
        $totalPaid = $funding->payments()->active()->sum('amount');
        $due       = max(0, $netAmount - $totalPaid);

        $status = $due <= 0
            ? 'paid'
            : ($totalPaid > 0 ? 'partial' : 'due');

        $funding->update([
            'payment'            => $totalPaid,
            'due_amount'         => $due,
            'transaction_status' => $status,
        ]);
    }
} 