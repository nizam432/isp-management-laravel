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
    public function store(Request $request, Purchase $purchase)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01|max:' . $purchase->due_amount,
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank,mobile_banking',
            'reference_no'   => 'nullable|string|max:255',
            'note'           => 'nullable|string',
        ]);

        if (!$purchase->isReceived()) {
            return back()->with('error', 'Payment can only be added to received purchases.');
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

            // Purchase paid/due update
            $newPaid = $purchase->paid_amount + $request->amount;
            $newDue  = $purchase->total_amount - $newPaid;

            $purchase->update([
                'paid_amount'    => $newPaid,
                'due_amount'     => $newDue,
                'payment_status' => $newDue <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid'),
            ]);

            // Vendor Ledger এ debit করো
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

            // Accounting → Expense এ auto entry
            app(AccountingService::class)->createPurchaseExpense($payment);
        });

        return back()->with('success', 'Payment added successfully.');
    }

    public function void(Request $request, Purchase $purchase, PurchasePayment $payment)
    {
        $request->validate([
            'void_reason' => 'required|string|max:500',
        ]);

        if ($payment->isVoid()) {
            return back()->with('error', 'This payment is already void.');
        }

        DB::transaction(function () use ($request, $purchase, $payment) {
            // Payment void করো
            $payment->update([
                'is_void'     => true,
                'void_reason' => $request->void_reason,
                'void_by'     => auth()->id(),
                'void_at'     => now(),
            ]);

            // Purchase paid/due reverse করো
            $newPaid = $purchase->paid_amount - $payment->amount;
            $newDue  = $purchase->total_amount - $newPaid;

            $purchase->update([
                'paid_amount'    => $newPaid,
                'due_amount'     => $newDue,
                'payment_status' => $newDue >= $purchase->total_amount ? 'unpaid' : ($newPaid > 0 ? 'partial' : 'unpaid'),
            ]);

            // Vendor Ledger reverse
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

            // Accounting → Expense void
            app(AccountingService::class)->voidPurchaseExpense($payment);
        });

        return back()->with('success', 'Payment voided successfully.');
    }
}
