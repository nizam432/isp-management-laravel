<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\PurchasePayment;
use App\Models\Inventory\VendorLedger;
use App\Services\Inventory\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasePaymentController extends Controller
{
    /** Called internally by PurchaseController when an initial payment is provided at creation. */
    public function createInitialPayment(Purchase $purchase, float $amount, Request $request): PurchasePayment
    {
        $payment = PurchasePayment::create([
            'purchase_id'    => $purchase->id,
            'amount'         => $amount,
            'payment_date'   => $request->purchase_date ?? now()->toDateString(),
            'payment_method' => $request->payment_method ?? 'cash',
            'reference_no'   => $request->payment_reference ?? null,
            'note'           => 'Initial payment on purchase creation',
            'created_by'     => auth()->id(),
        ]);

        $newPaid = $purchase->paid_amount + $amount;
        $newDue  = $purchase->total_amount - $newPaid;

        $purchase->update([
            'paid_amount'    => $newPaid,
            'due_amount'     => $newDue,
            'payment_status' => $newDue <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid'),
        ]);

        $lastBalance = $purchase->vendor->ledger()->latest('id')->value('balance') ?? 0;
        VendorLedger::create([
            'vendor_id'    => $purchase->vendor_id,
            'date'         => $payment->payment_date,
            'type'         => 'payment',
            'reference_id' => $payment->id,
            'debit'        => $amount,
            'credit'       => 0,
            'balance'      => $lastBalance - $amount,
            'note'         => 'Payment for: ' . $purchase->purchase_no,
            'created_by'   => auth()->id(),
        ]);

        app(AccountingService::class)->createPurchaseExpense($payment);

        return $payment;
    }

    // ── Pay — AJAX from Purchase List Pay Modal ───────────────────
    public function store(Request $request, Purchase $purchase)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01|max:' . $purchase->due_amount,
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank,mobile_banking',
            'reference_no'   => 'nullable|string|max:255',
            'note'           => 'nullable|string',
        ]);

        if ($purchase->isCancelled()) {
            return response()->json(['success' => false, 'message' => 'Cannot pay a cancelled purchase.'], 422);
        }

        if ($purchase->due_amount <= 0) {
            return response()->json(['success' => false, 'message' => 'This purchase is already fully paid.'], 422);
        }

        DB::transaction(function () use ($request, $purchase) {
            $payment = PurchasePayment::create([
                'purchase_id'    => $purchase->id,
                'amount'         => $request->amount,
                'payment_date'   => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_no'   => $request->reference_no,
                'note'           => $request->note,
                'created_by'     => auth()->id(),
            ]);

            $newPaid = $purchase->paid_amount + $request->amount;
            $newDue  = $purchase->total_amount - $newPaid;

            $purchase->update([
                'paid_amount'    => $newPaid,
                'due_amount'     => $newDue,
                'payment_status' => $newDue <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid'),
            ]);

            $lastBalance = $purchase->vendor->ledger()->latest('id')->value('balance') ?? 0;
            VendorLedger::create([
                'vendor_id'    => $purchase->vendor_id,
                'date'         => $request->payment_date,
                'type'         => 'payment',
                'reference_id' => $payment->id,
                'debit'        => $request->amount,
                'credit'       => 0,
                'balance'      => $lastBalance - $request->amount,
                'note'         => 'Payment for: ' . $purchase->purchase_no,
                'created_by'   => auth()->id(),
            ]);

            app(AccountingService::class)->createPurchaseExpense($payment);
        });

        return response()->json([
            'success' => true,
            'message' => "Payment of ৳" . number_format($request->amount, 2) . " recorded.",
            'paid'    => number_format($purchase->fresh()->paid_amount, 2),
            'due'     => number_format($purchase->fresh()->due_amount, 2),
            'status'  => $purchase->fresh()->payment_status,
        ]);
    }

    public function void(Request $request, Purchase $purchase, PurchasePayment $payment)
    {
        $request->validate([
            'void_reason' => 'required|string|max:500',
        ]);

        if ($payment->isVoid()) {
            return response()->json(['success' => false, 'message' => 'This payment is already void.'], 422);
        }

        DB::transaction(function () use ($request, $purchase, $payment) {
            $payment->update([
                'is_void'     => true,
                'void_reason' => $request->void_reason,
                'void_by'     => auth()->id(),
                'void_at'     => now(),
            ]);

            $newPaid = $purchase->paid_amount - $payment->amount;
            $newDue  = $purchase->total_amount - $newPaid;

            $purchase->update([
                'paid_amount'    => $newPaid,
                'due_amount'     => $newDue,
                'payment_status' => $newPaid <= 0 ? 'unpaid' : ($newDue > 0 ? 'partial' : 'paid'),
            ]);

            $lastBalance = $purchase->vendor->ledger()->latest('id')->value('balance') ?? 0;
            VendorLedger::create([
                'vendor_id'    => $purchase->vendor_id,
                'date'         => now()->toDateString(),
                'type'         => 'adjustment',
                'reference_id' => $payment->id,
                'debit'        => 0,
                'credit'       => $payment->amount,
                'balance'      => $lastBalance + $payment->amount,
                'note'         => 'Payment Void: ' . $purchase->purchase_no,
                'created_by'   => auth()->id(),
            ]);

            app(AccountingService::class)->voidPurchaseExpense($payment);
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Payment voided successfully.']);
        }

        return back()->with('success', 'Payment voided successfully.');
    }
}
