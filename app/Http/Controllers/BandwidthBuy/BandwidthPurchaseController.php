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

    // =========================================================================
    // INDEX
    // =========================================================================
    public function index()
    {
        $purchases = BandwidthPurchase::with('provider', 'lines.service', 'payments')
            ->latest('billing_date')
            ->paginate(20);

        return view('bandwidth-buy.purchase.index', compact('purchases'));
    }

    // =========================================================================
    // CREATE
    // =========================================================================
    public function create()
    {
        $providers = BandwidthProvider::active()->orderBy('company_name')->get();
        $services  = BandwidthService::orderBy('name')->get();
        return view('bandwidth-buy.purchase.create', compact('providers', 'services'));
    }

    // =========================================================================
    // STORE
    // =========================================================================
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

    // =========================================================================
    // EDIT
    // =========================================================================
    public function edit(BandwidthPurchase $purchase)
    {
        // Partial/Paid হলে edit করা যাবে না
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

    // =========================================================================
    // UPDATE
    // =========================================================================
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

    // =========================================================================
    // PAY — AJAX POST
    // =========================================================================
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

    // =========================================================================
    // PAYMENT HISTORY — AJAX GET
    // =========================================================================
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

    // =========================================================================
    // VOID
    // =========================================================================
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

    // =========================================================================
    // VOID INDIVIDUAL PAYMENT
    // =========================================================================
    public function voidPayment(Request $request, BandwidthPurchasePayment $payment)
    {
        $request->validate(['reason' => 'required|string|max:255']);

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

            $payment->delete();

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

    // =========================================================================
    // DESTROY
    // =========================================================================
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
