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
    /** Called internally by SaleController when an initial payment is provided at creation. */
    public function createInitialPayment(Sale $sale, float $amount, Request $request): SalePayment
    {
        $payment = SalePayment::create([
            'sale_id'        => $sale->id,
            'amount'         => $amount,
            'payment_date'   => $request->sale_date ?? now()->toDateString(),
            'payment_method' => $request->payment_method ?? 'cash',
            'reference_no'   => $request->payment_reference ?? null,
            'note'           => 'Initial payment on sale creation',
            'created_by'     => auth()->id(),
        ]);

        $newPaid = $sale->paid_amount + $amount;
        $newDue  = $sale->total_amount - $newPaid;

        $sale->update([
            'paid_amount'    => $newPaid,
            'due_amount'     => $newDue,
            'payment_status' => $newDue <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid'),
        ]);

        if ($sale->client_id) {
            $lastBalance = ClientLedger::lastBalance($sale->client_id);
            ClientLedger::create([
                'client_id'    => $sale->client_id,
                'date'         => $payment->payment_date,
                'type'         => 'payment',
                'reference_id' => $payment->id,
                'debit'        => $amount,
                'credit'       => 0,
                'balance'      => $lastBalance - $amount,
                'note'         => 'Payment for: ' . $sale->sale_no,
                'created_by'   => auth()->id(),
            ]);
        }

        app(AccountingService::class)->createSaleIncome($payment);

        return $payment;
    }

    // ── Pay — AJAX from Sale List Pay Modal ──────────────────────
    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01|max:' . $sale->due_amount,
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank,mobile_banking,bkash,nagad',
            'reference_no'   => 'nullable|string|max:255',
            'note'           => 'nullable|string',
        ]);

        if ($sale->isCancelled()) {
            return response()->json(['success' => false, 'message' => 'Cannot pay a cancelled sale.'], 422);
        }

        if ($sale->due_amount <= 0) {
            return response()->json(['success' => false, 'message' => 'This sale is already fully paid.'], 422);
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

            $newPaid = $sale->paid_amount + $request->amount;
            $newDue  = $sale->total_amount - $newPaid;

            $sale->update([
                'paid_amount'    => $newPaid,
                'due_amount'     => $newDue,
                'payment_status' => $newDue <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid'),
            ]);

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

            app(AccountingService::class)->createSaleIncome($payment);
        });

        return response()->json([
            'success' => true,
            'message' => "Payment of ৳" . number_format($request->amount, 2) . " recorded.",
            'paid'    => number_format($sale->fresh()->paid_amount, 2),
            'due'     => number_format($sale->fresh()->due_amount, 2),
            'status'  => $sale->fresh()->payment_status,
        ]);
    }

    public function void(Request $request, Sale $sale, SalePayment $payment)
    {
        $request->validate([
            'void_reason' => 'required|string|max:500',
        ]);

        if ($payment->isVoid()) {
            return response()->json(['success' => false, 'message' => 'This payment is already void.'], 422);
        }

        DB::transaction(function () use ($request, $sale, $payment) {
            $payment->update([
                'is_void'     => true,
                'void_reason' => $request->void_reason,
                'void_by'     => auth()->id(),
                'void_at'     => now(),
            ]);

            $newPaid = $sale->paid_amount - $payment->amount;
            $newDue  = $sale->total_amount - $newPaid;

            $sale->update([
                'paid_amount'    => $newPaid,
                'due_amount'     => $newDue,
                'payment_status' => $newPaid <= 0 ? 'unpaid' : ($newDue > 0 ? 'partial' : 'paid'),
            ]);

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

            app(AccountingService::class)->voidSaleIncome($payment);
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Payment voided successfully.']);
        }

        return back()->with('success', 'Payment voided successfully.');
    }
}
