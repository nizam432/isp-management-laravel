<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentVoid;
use App\Models\Customer;
use App\Models\AdvanceTransaction;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Collect payment using FIFO logic.
     * Regardless of which invoice is selected, the oldest unpaid invoice is paid first.
     * Any excess amount is added to the customer's advance balance.
     */
    public function collectPayment(Customer $customer, array $data): array
    {
        return DB::transaction(function () use ($customer, $data) {

            $totalPaid    = floatval($data['amount']);
            $remaining    = $totalPaid;
            $paidInvoices = [];

            // FIFO — oldest unpaid invoice first
            $unpaidInvoices = Invoice::where('customer_id', $customer->id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->orderBy('month', 'asc')
                ->get();

            foreach ($unpaidInvoices as $invoice) {
                if ($remaining <= 0) break;

                $due = floatval($invoice->due_amount);

                if ($remaining >= $due) {
                    // Fully pay this invoice
                    $payAmount = $due;
                    $remaining -= $due;

                    Payment::create([
                        'invoice_id'            => $invoice->id,
                        'customer_id'           => $customer->id,
                        'amount'                => $payAmount,
                        'method'                => $data['method'],
                        'transaction_id'        => $data['transaction_id'] ?? null,
                        'remarks'               => $data['remarks'] ?? null,
                        'received_by'           => $data['received_by'] ?? null,
                        'receive_from'          => $data['receive_from'] ?? null,
                        'send_sms'              => $data['send_sms'] ?? false,
                        'set_next_billing_date' => $data['set_next_billing_date'] ?? false,
                        'payment_date'          => $data['payment_date'] ?? now()->toDateString(),
                        'status'                => 'active',
                        'paid_at'               => now(),
                    ]);

                    $invoice->update([
                        'due_amount' => 0,
                        'status'     => 'paid',
                    ]);

                    $paidInvoices[] = $invoice->invoice_no;

                } else {
                    // Partial payment
                    $payAmount = $remaining;
                    $remaining = 0;

                    Payment::create([
                        'invoice_id'            => $invoice->id,
                        'customer_id'           => $customer->id,
                        'amount'                => $payAmount,
                        'method'                => $data['method'],
                        'transaction_id'        => $data['transaction_id'] ?? null,
                        'remarks'               => $data['remarks'] ?? null,
                        'received_by'           => $data['received_by'] ?? null,
                        'receive_from'          => $data['receive_from'] ?? null,
                        'send_sms'              => $data['send_sms'] ?? false,
                        'set_next_billing_date' => $data['set_next_billing_date'] ?? false,
                        'payment_date'          => $data['payment_date'] ?? now()->toDateString(),
                        'status'                => 'active',
                        'paid_at'               => now(),
                    ]);

                    $invoice->update([
                        'due_amount' => $due - $payAmount,
                        'status'     => 'partial',
                    ]);

                    $paidInvoices[] = $invoice->invoice_no . ' (partial)';
                }
            }

            // Add any excess amount to the customer's advance balance
            if ($remaining > 0) {
                $customer->increment('advance_balance', $remaining);

                AdvanceTransaction::create([
                    'customer_id' => $customer->id,
                    'type'        => 'credit',
                    'amount'      => $remaining,
                    'description' => 'Advance from overpayment',
                    'created_by'  => auth()->id(),
                ]);
            }

            return [
                'paid_invoices' => $paidInvoices,
                'advance_added' => $remaining > 0 ? $remaining : 0,
                'total_paid'    => $totalPaid,
            ];
        });
    }

    /**
     * Auto-deduct from advance balance when a new invoice is generated.
     * Uses lockForUpdate to get fresh data and prevent race conditions.
     */
    public function applyAdvanceToInvoice(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {

            // Get fresh data from DB with lock to prevent race conditions
            $customer = Customer::lockForUpdate()->find($invoice->customer_id);
            $invoice  = Invoice::lockForUpdate()->find($invoice->id);

            $due     = floatval($invoice->due_amount);
            $advance = floatval($customer->advance_balance);

            if ($advance <= 0) return;

            if ($advance >= $due) {
                // Advance covers the full invoice amount
                $deduct = $due;

                Payment::create([
                    'invoice_id'   => $invoice->id,
                    'customer_id'  => $customer->id,
                    'amount'       => $deduct,
                    'method'       => 'advance',
                    'remarks'      => 'Auto-deducted from advance balance',
                    'payment_date' => now()->toDateString(),
                    'status'       => 'active',
                    'paid_at'      => now(),
                ]);

                $invoice->update(['due_amount' => 0, 'status' => 'paid']);
                $customer->decrement('advance_balance', $deduct);

            } else {
                // Advance partially covers the invoice
                $deduct = $advance;

                Payment::create([
                    'invoice_id'   => $invoice->id,
                    'customer_id'  => $customer->id,
                    'amount'       => $deduct,
                    'method'       => 'advance',
                    'remarks'      => 'Auto-deducted from advance balance',
                    'payment_date' => now()->toDateString(),
                    'status'       => 'active',
                    'paid_at'      => now(),
                ]);

                $invoice->update([
                    'due_amount' => $due - $deduct,
                    'status'     => 'partial',
                ]);

                // Decrement instead of setting to 0 to avoid overwriting concurrent updates
                $customer->decrement('advance_balance', $deduct);
            }

            AdvanceTransaction::create([
                'customer_id' => $customer->id,
                'type'        => 'debit',
                'amount'      => $deduct,
                'description' => 'Auto-deducted for invoice ' . $invoice->invoice_no,
                'invoice_id'  => $invoice->id,
            ]);
        });
    }

    /**
     * Void a payment.
     * Only marks the payment as void and recalculates invoice status.
     * Does NOT refund to advance balance — invoice remains unpaid for re-payment.
     */
    public function voidPayment(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {

            // Mark payment as void
            $payment->update(['status' => 'void']);

            // Log the void
            PaymentVoid::create([
                'payment_id' => $payment->id,
                'voided_by'  => auth()->id(),
                'amount'     => $payment->amount,
                'reason'     => $reason,
                'voided_at'  => now(),
            ]);

            // Recalculate invoice status after void
            $invoice   = $payment->invoice;
            $totalPaid = $invoice->payments()->active()->sum('amount');
            $due       = $invoice->amount - $invoice->discount - $totalPaid;

            if ($due <= 0) {
                $status = 'paid';
                $due    = 0;
            } elseif ($totalPaid > 0) {
                $status = 'partial';
            } else {
                $status = 'unpaid';
            }

            $invoice->update(['due_amount' => $due, 'status' => $status]);

            // NOTE: Advance balance is NOT refunded on void.
            // Invoice is set to unpaid — customer will re-pay.
            // If unpaid invoice is deleted (no payments), advance logic handles separately.
        });
    }

    /**
     * Manually add advance balance for a customer.
     */
    public function addAdvance(Customer $customer, array $data): Payment
    {
        return DB::transaction(function () use ($customer, $data) {

            // Create a payment record without an invoice
            $payment = Payment::create([
                'invoice_id'     => null,
                'customer_id'    => $customer->id,
                'amount'         => $data['amount'],
                'method'         => $data['method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'remarks'        => $data['remarks'] ?? 'Manual advance',
                'received_by'    => $data['received_by'] ?? null,
                'payment_date'   => $data['payment_date'] ?? now()->toDateString(),
                'status'         => 'active',
                'paid_at'        => now(),
            ]);

            $customer->increment('advance_balance', $data['amount']);

            AdvanceTransaction::create([
                'customer_id' => $customer->id,
                'type'        => 'credit',
                'amount'      => $data['amount'],
                'description' => 'Manual advance payment',
                'payment_id'  => $payment->id,
                'created_by'  => auth()->id(),
            ]);

            return $payment;
        });
    }

    /**
     * Get stats data for invoice list page cards.
     */
    public function getInvoiceStats(): array
    {
        $thisMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');

        $paidThis   = Invoice::where('month', $thisMonth)->where('status', 'paid')->count();
        $paidLast   = Invoice::where('month', $lastMonth)->where('status', 'paid')->count();

        $unpaidThis = Invoice::where('month', $thisMonth)->whereIn('status', ['unpaid', 'partial', 'overdue'])->count();
        $unpaidLast = Invoice::where('month', $lastMonth)->whereIn('status', ['unpaid', 'partial', 'overdue'])->count();

        $receivedThis = Payment::active()->thisMonth()->sum('amount');
        $receivedLast = Payment::active()
            ->whereMonth('payment_date', now()->subMonth()->month)
            ->whereYear('payment_date', now()->subMonth()->year)
            ->sum('amount');

        $totalDue = Invoice::whereIn('status', ['unpaid', 'partial', 'overdue'])->sum('due_amount');

        $generatedThis = Invoice::where('month', $thisMonth)->count();
        $generatedLast = Invoice::where('month', $lastMonth)->count();

        $advanceTotal = Customer::sum('advance_balance');

        $monthlyBillThis = Invoice::where('month', $thisMonth)->sum('amount');
        $monthlyBillLast = Invoice::where('month', $lastMonth)->sum('amount');

        $totalClients       = Customer::active()->count();
        $collectionRate     = $totalClients > 0 ? round(($paidThis / $totalClients) * 100) : 0;
        $collectionRateLast = $totalClients > 0 ? round(($paidLast / $totalClients) * 100) : 0;

        return [
            'paid_clients'    => ['current' => $paidThis,        'last' => $paidLast],
            'unpaid_clients'  => ['current' => $unpaidThis,       'last' => $unpaidLast],
            'received_bill'   => ['current' => $receivedThis,     'last' => $receivedLast],
            'total_due'       => $totalDue,
            'generated_bill'  => ['current' => $generatedThis,    'last' => $generatedLast],
            'advance_amount'  => $advanceTotal,
            'monthly_bill'    => ['current' => $monthlyBillThis,  'last' => $monthlyBillLast],
            'collection_rate' => ['current' => $collectionRate,   'last' => $collectionRateLast],
        ];
    }
}