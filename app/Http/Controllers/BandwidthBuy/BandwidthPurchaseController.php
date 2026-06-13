<?php

namespace App\Http\Controllers\BandwidthBuy;

use App\Http\Controllers\Controller;
use App\Models\BandwidthBuy\BandwidthProvider;
use App\Models\BandwidthBuy\BandwidthService;
use App\Models\BandwidthBuy\BandwidthPurchase;
use App\Models\BandwidthBuy\BandwidthPurchaseLine;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BandwidthPurchaseController extends Controller
{
    // ── ISP Bandwidth expense category ID ────────────────────────────────────
    private function getBandwidthCategoryId(): ?int
    {
        return ExpenseCategory::where('slug', 'isp-bandwidth')->value('id');
    }

    // ── Build description string ──────────────────────────────────────────────
    private function expenseDescription(BandwidthPurchase $purchase): string
    {
        $services    = $purchase->lines->pluck('service.name')->filter()->implode(', ');
        $description = "Bandwidth Purchase — {$purchase->provider->company_name}";
        if ($services) $description .= " ({$services})";
        return $description;
    }

    /**
     * Store এ call হয়।
     * paid > 0 হলে Expense তৈরি হয়।
     * paid = 0 হলে কোনো Expense তৈরি হয় না।
     */
    private function createExpenseIfPaid(BandwidthPurchase $purchase): void
    {
        if ($purchase->paid <= 0) return;

        $categoryId = $this->getBandwidthCategoryId();
        if (!$categoryId) return;

        Expense::create([
            'category_id'    => $categoryId,
            'amount'         => $purchase->paid,
            'expense_date'   => $purchase->billing_date,
            'payment_method' => 'bank',
            'payee'          => $purchase->provider->company_name,
            'reference_no'   => $purchase->invoice_no,
            'description'    => $this->expenseDescription($purchase)
                                . " [Paid: ৳" . number_format($purchase->paid, 2) . "]",
            'status'         => 'approved',
            'created_by'     => auth()->id(),
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
        ]);
    }

    /**
     * Update এ call হয়।
     * পুরনো paid vs নতুন paid — difference > 0 হলে নতুন Expense।
     */
    private function recordPaymentDifference(
        BandwidthPurchase $purchase,
        float $oldPaid,
        float $newPaid,
        string $paymentDate = null
    ): void {
        $difference = round($newPaid - $oldPaid, 2);
        if ($difference <= 0) return;

        $categoryId = $this->getBandwidthCategoryId();
        if (!$categoryId) return;

        // payment_date না দিলে আজকের তারিখ
        $date = $paymentDate ?: now()->toDateString();

        Expense::create([
            'category_id'    => $categoryId,
            'amount'         => $difference,
            'expense_date'   => $date,
            'payment_method' => 'bank',
            'payee'          => $purchase->provider->company_name,
            'reference_no'   => $purchase->invoice_no,
            'description'    => $this->expenseDescription($purchase)
                                . " [Additional Payment: ৳" . number_format($difference, 2) . "]",
            'status'         => 'approved',
            'created_by'     => auth()->id(),
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
        ]);
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index()
    {
        $purchases = BandwidthPurchase::with('provider', 'lines.service')
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
        $services  = BandwidthService::active()->orderBy('name')->get();

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

            // Compute totals
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

            // ── Auto-create Accounting Expense ──────────────────────────
            $purchase->load('provider', 'lines.service');
            $this->createExpenseIfPaid($purchase);

            DB::commit();

            return redirect()->route('bandwidth-buy.purchase.index')
                ->with('success', "Purchase bill saved & expense recorded in Accounting.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    /**
     * Purchase void হলে সব linked expense void করো।
     * Expense void হয় `status = 'void'` দিয়ে — hard delete নয়।
     */
    private function voidLinkedExpenses(BandwidthPurchase $purchase, string $reason): int
    {
        $voided = Expense::where('reference_no', $purchase->invoice_no)
            ->where('payee', $purchase->provider->company_name)
            ->whereNotIn('status', ['void'])
            ->get();

        foreach ($voided as $expense) {
            $expense->update([
                'status'        => 'void',
                'reject_reason' => $reason,
            ]);
        }

        return $voided->count();
    }

    // =========================================================================
    // VOID PURCHASE
    // =========================================================================

    public function void(Request $request, BandwidthPurchase $purchase)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $purchase->load('provider');

            // Linked expense গুলো void করো
            $voidedCount = $this->voidLinkedExpenses($purchase, $request->reason);

            // Purchase নিজেও soft delete করো
            $purchase->delete();

            DB::commit();

            $msg = "Purchase #{$purchase->invoice_no} voided.";
            if ($voidedCount > 0) {
                $msg .= " {$voidedCount} linked expense(s) also voided in Accounting.";
            }

            return redirect()->route('bandwidth-buy.purchase.index')
                ->with('success', $msg);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function edit(BandwidthPurchase $purchase)
    {
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
        $request->validate([
            'provider_id'           => 'required|exists:bandwidth_providers,id',
            'invoice_no'            => 'required|string|max:100',
            'billing_date'          => 'required|date',
            'document'              => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'paid'                  => 'required|numeric|min:0',
            'bank_account'          => 'nullable|string|max:150',
            'payment_date'          => 'nullable|date',
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
            // ── পুরনো paid amount আগেই capture করো ──────────────────────
            $oldPaid = (float) $purchase->paid;

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

            $paid = min((float) $request->paid, $subTotal);
            $due  = max(0, $subTotal - $paid);

            $purchase->update([
                'invoice_no'   => $request->invoice_no,
                'provider_id'  => $request->provider_id,
                'billing_date' => $request->billing_date,
                'sub_total'    => $subTotal,
                'paid'         => $paid,
                'due'          => $due,
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

            // ── Sync Accounting Expense — শুধু নতুন payment এর difference ─
            $purchase->load('provider', 'lines.service');
            $this->recordPaymentDifference(
                $purchase,
                $oldPaid,
                $paid,
                $request->payment_date ?: now()->toDateString()
            );

            DB::commit();

            return redirect()->route('bandwidth-buy.purchase.index')
                ->with('success', 'Purchase updated & accounting expense synced.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }
}
