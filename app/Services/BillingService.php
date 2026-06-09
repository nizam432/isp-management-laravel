<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentVoid;
use App\Models\Customer;
use App\Models\AdvanceTransaction;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingService
{
    /**
     * Collect payment using FIFO logic.
     * Regardless of which invoice is selected, the oldest unpaid invoice is paid first.
     * Any excess amount is added to the customer's advance balance.
     * After payment, checks if MikroTik auto restore is needed.
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

                    $invoice->update(['due_amount' => 0, 'status' => 'paid']);
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

            // Add any excess amount to advance balance
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

            // Auto restore MikroTik if setting enabled and all dues cleared
            $this->checkAndRestoreMikrotik($customer);

            // Send payment confirmation notification
            try {
                (new NotificationService())->paymentConfirm($customer, $totalPaid, $data['method']);
            } catch (\Exception $e) {
                \Log::error('Notification failed: ' . $e->getMessage());
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
     */
    public function applyAdvanceToInvoice(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {

            $customer = Customer::lockForUpdate()->find($invoice->customer_id);
            $invoice  = Invoice::lockForUpdate()->find($invoice->id);

            $due     = floatval($invoice->due_amount);
            $advance = floatval($customer->advance_balance);

            if ($advance <= 0) return;

            if ($advance >= $due) {
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
                $customer->decrement('advance_balance', $deduct);
            }

            AdvanceTransaction::create([
                'customer_id' => $customer->id,
                'type'        => 'debit',
                'amount'      => $deduct,
                'description' => 'Auto-deducted for invoice ' . $invoice->invoice_no,
                'invoice_id'  => $invoice->id,
            ]);

            // Auto restore MikroTik if all dues cleared
            $this->checkAndRestoreMikrotik($customer);
        });
    }

    /**
     * Void a payment.
     * Only marks the payment as void and recalculates invoice status.
     * Does NOT refund to advance balance.
     */
    public function voidPayment(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {

            $payment->update(['status' => 'void']);

            PaymentVoid::create([
                'payment_id' => $payment->id,
                'voided_by'  => auth()->id(),
                'amount'     => $payment->amount,
                'reason'     => $reason,
                'voided_at'  => now(),
            ]);

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
        });
    }

    /**
     * Manually add advance balance for a customer.
     */
    public function addAdvance(Customer $customer, array $data): Payment
    {
        return DB::transaction(function () use ($customer, $data) {

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
     * Generate invoice for Date to Date billing.
     * Period: connection_date → connection_date + 29 days (30 days total)
     */
    public function generateDateToDateInvoice(Customer $customer): ?Invoice
    {
        $connectionDate = $customer->connection_date;

        if (!$connectionDate) return null;

        // Find last invoice to determine next period start
        $lastInvoice = Invoice::where('customer_id', $customer->id)
            ->where('billing_type', 'date_to_date')
            ->latest('period_start')
            ->first();

        if ($lastInvoice) {
            $periodStart = Carbon::parse($lastInvoice->period_end)->addDay();
        } else {
            $periodStart = Carbon::parse($connectionDate);
        }

        $periodEnd = $periodStart->copy()->addDays(29); // 30 days total

        // Check if already generated for this period
        $exists = Invoice::where('customer_id', $customer->id)
            ->where('period_start', $periodStart->toDateString())
            ->exists();

        if ($exists) return null;

        $dueDays = intval(Setting::get('invoice_due_days', 7));
        $dueDate = $periodStart->copy()->addDays($dueDays);

        $invoice = Invoice::create([
            'invoice_no'   => Invoice::generateNumber(),
            'customer_id'  => $customer->id,
            'package_id'   => $customer->package_id,
            'month'        => $periodStart->format('Y-m'),
            'period_start' => $periodStart->toDateString(),
            'period_end'   => $periodEnd->toDateString(),
            'billing_type' => 'date_to_date',
            'amount'       => $customer->package->price ?? 0,
            'due_amount'   => $customer->package->price ?? 0,
            'due_date'     => $dueDate->toDateString(),
            'status'       => 'unpaid',
        ]);

        // Auto-deduct advance if available
        if ($customer->advance_balance > 0) {
            $this->applyAdvanceToInvoice($invoice);
        }

        return $invoice;
    }

    /**
     * Check overdue invoices based on grace period setting.
     * Run via scheduler.
     */
    public function markOverdueInvoices(): int
    {
        $gracePeriod = intval(Setting::get('grace_period_days', 3));
        $cutoffDate  = now()->subDays($gracePeriod)->toDateString();

        $count = Invoice::whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $cutoffDate)
            ->update(['status' => 'overdue']);

        return $count;
    }

    /**
     * Apply late fee to overdue invoices.
     * Run via scheduler.
     */
    public function applyLateFees(): int
    {
        $lateFeeAmount = floatval(Setting::get('late_fee_amount', 0));
        $lateFeeAfter  = intval(Setting::get('late_fee_after_days', 7));

        if ($lateFeeAmount <= 0) return 0;

        $cutoffDate = now()->subDays($lateFeeAfter)->toDateString();
        $count      = 0;

        $overdueInvoices = Invoice::where('status', 'overdue')
            ->whereDate('due_date', '<', $cutoffDate)
            ->whereNull('late_fee_applied_at')
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $invoice->increment('due_amount', $lateFeeAmount);
            $invoice->increment('amount', $lateFeeAmount);
            $invoice->update(['late_fee_applied_at' => now()]);
            $count++;
        }

        return $count;
    }

    /**
     * Check if MikroTik auto restore is needed after payment.
     */
    public function checkAndRestoreMikrotik(Customer $customer): void
    {
        // Check if auto restore is enabled in settings
        $autoRestore = Setting::get('mikrotik_auto_restore_on_payment', false);

        if (!$autoRestore) return;

        // Check if all invoices are paid
        $hasDue = $customer->invoices()
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->exists();

        if (!$hasDue && $customer->router_id) {
            // All dues cleared — restore MikroTik
            try {
                app(\App\Http\Controllers\MikrotikController::class)
                    ->restoreCustomerById($customer->id);
            } catch (\Exception $e) {
                \Log::error('MikroTik restore failed for customer ' . $customer->id . ': ' . $e->getMessage());
            }
        }
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

        $advanceTotal    = Customer::sum('advance_balance');
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
