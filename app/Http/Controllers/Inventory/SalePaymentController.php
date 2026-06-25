<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Sale;
use App\Models\Inventory\SalePayment;
use App\Models\Inventory\ClientLedger;
use App\Services\Inventory\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalePaymentController extends Controller
{
    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01|max:' . $sale->due_amount,
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank,mobile_banking,bkash,nagad',
            'reference_no'   => 'nullable|string|max:255',
            'note'           => 'nullable|string',
        ]);

        if (!$sale->isConfirmed()) {
            return back()->with('error', 'Payment can only be added to confirmed sales.');
        }

        DB::transaction(function () use ($request, $sale) {
            $payment = SalePayment::create([
                'sale_id'        => $sale->id,
                'amount'         => $request->amount,
                'payment_date'   => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_no'   => $request->reference_no,
                'note'           => $request->note,
                'created_by'     => auth()->id(),
            ]);

            // Sale paid/due update
            $newPaid = $sale->paid_amount + $request->amount;
            $newDue  = $sale->total_amount - $newPaid;

            $sale->update([
                'paid_amount'    => $newPaid,
                'due_amount'     => $newDue,
                'payment_status' => $newDue <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid'),
            ]);

            // Client Ledger এ debit করো (payment পেলাম)
            if ($sale->client_id) {
                $lastBalance = ClientLedger::lastBalance($sale->client_id);
                ClientLedger::create([
                    'client_id'    => $sale->client_id,
                    'date'         => $request->payment_date,
                    'type'         => 'payment',
                    'reference_id' => $payment->id,
                    'debit'        => $request->amount,
                    'credit'       => 0,
                    'balance'      => $lastBalance - $request->amount,
                    'note'         => 'Payment for: ' . $sale->sale_no,
                    'created_by'   => auth()->id(),
                ]);
            }

            // Accounting → Income এ auto entry
            app(AccountingService::class)->createSaleIncome($payment);
        });

        return back()->with('success', 'Payment added successfully.');
    }

    public function void(Request $request, Sale $sale, SalePayment $payment)
    {
        $request->validate([
            'void_reason' => 'required|string|max:500',
        ]);

        if ($payment->isVoid()) {
            return back()->with('error', 'This payment is already void.');
        }

        DB::transaction(function () use ($request, $sale, $payment) {
            $payment->update([
                'is_void'     => true,
                'void_reason' => $request->void_reason,
                'void_by'     => auth()->id(),
                'void_at'     => now(),
            ]);

            // Sale paid/due reverse
            $newPaid = $sale->paid_amount - $payment->amount;
            $newDue  = $sale->total_amount - $newPaid;

            $sale->update([
                'paid_amount'    => $newPaid,
                'due_amount'     => $newDue,
                'payment_status' => $newDue >= $sale->total_amount ? 'unpaid' : ($newPaid > 0 ? 'partial' : 'unpaid'),
            ]);

            // Client Ledger reverse
            if ($sale->client_id) {
                $lastBalance = ClientLedger::lastBalance($sale->client_id);
                ClientLedger::create([
                    'client_id'    => $sale->client_id,
                    'date'         => now()->toDateString(),
                    'type'         => 'adjustment',
                    'reference_id' => $payment->id,
                    'debit'        => 0,
                    'credit'       => $payment->amount,
                    'balance'      => $lastBalance + $payment->amount,
                    'note'         => 'Payment Void: ' . $sale->sale_no,
                    'created_by'   => auth()->id(),
                ]);
            }

            // Accounting → Income void
            app(AccountingService::class)->voidSaleIncome($payment);
        });

        return back()->with('success', 'Payment voided successfully.');
    }
}
