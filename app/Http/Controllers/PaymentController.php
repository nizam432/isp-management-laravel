<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\AgentCommission;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a paginated list of payments.
     * Shows today's and this month's total collection as summary.
     */
    public function index(Request $request)
    {
        $payments = Payment::with(['customer', 'invoice', 'receivedBy'])
            // Filter by payment method: cash / bkash / nagad / rocket / card / bank
            ->when($request->method, fn($q) => $q->where('method', $request->method))
            // Filter by specific date
            ->when($request->date, fn($q) => $q->whereDate('paid_at', $request->date))
            // Search by customer name or phone
            ->when($request->search, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")))
            ->latest('paid_at')
            ->paginate(20);

        // Summary totals
        $todayTotal = Payment::today()->sum('amount');
        $monthTotal = Payment::thisMonth()->sum('amount');

        return view('payments.index', compact('payments', 'todayTotal', 'monthTotal'));
    }

    /**
     * Record a new payment against an invoice.
     * Automatically updates invoice status and calculates agent commission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id'     => 'required|exists:invoices,id',
            'amount'         => 'required|numeric|min:1',
            'method'         => 'required|in:cash,bkash,nagad,rocket,card,bank',
            'transaction_id' => 'nullable|string|max:100', // bKash / Nagad transaction ID
            'paid_at'        => 'required|date',
            'remarks'        => 'nullable|string|max:255',
        ]);

        // Load invoice with customer's agent data
        $invoice = Invoice::with('customer.agent')->findOrFail($request->invoice_id);

        // Save the payment record
        $payment = Payment::create([
            'invoice_id'     => $invoice->id,
            'customer_id'    => $invoice->customer_id,
            'amount'         => $request->amount,
            'method'         => $request->method,
            'transaction_id' => $request->transaction_id,
            'paid_at'        => $request->paid_at,
            'received_by'    => auth()->id(),
            'remarks'        => $request->remarks,
        ]);

        // Recalculate invoice status based on total amount paid
        $totalPaid = $invoice->payments()->sum('amount');

        if ($totalPaid >= $invoice->amount) {
            // Fully paid
            $invoice->update(['status' => 'paid', 'due_amount' => 0]);
        } else {
            // Partially paid
            $invoice->update([
                'status'     => 'partial',
                'due_amount' => $invoice->amount - $totalPaid,
            ]);
        }

        // Calculate and record agent commission if applicable
        if ($invoice->customer->agent && $invoice->customer->agent->commission_rate > 0) {
            AgentCommission::create([
                'agent_id'   => $invoice->customer->agent_id,
                'payment_id' => $payment->id,
                // commission = payment_amount * rate / 100
                'amount'     => round(
                    $payment->amount * $invoice->customer->agent->commission_rate / 100, 2
                ),
                'status'     => 'pending',
            ]);
        }

        ActivityLog::log('Payment received', 'Payment', $payment->id, null, $payment->toArray());

        return back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * Delete a payment record.
     * Automatically recalculates the invoice status and removes commission.
     */
    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
        $old     = $payment->toArray();

        // Remove associated commission record first
        $payment->commission()->delete();
        $payment->delete();

        // Recalculate invoice status after payment removal
        $totalPaid = $invoice->payments()->sum('amount');

        if ($totalPaid <= 0) {
            // No payments remain — mark as unpaid
            $invoice->update(['status' => 'unpaid', 'due_amount' => $invoice->amount]);
        } else {
            // Some payments remain — mark as partial
            $invoice->update([
                'status'     => 'partial',
                'due_amount' => $invoice->amount - $totalPaid,
            ]);
        }

        ActivityLog::log('Payment deleted', 'Payment', $old['id'], $old, null);

        return back()->with('success', 'Payment deleted successfully.');
    }
}
