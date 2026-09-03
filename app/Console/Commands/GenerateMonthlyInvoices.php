<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\BillingService;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * GenerateMonthlyInvoices
 * ─────────────────────────────────────────────
 * Admin's own customers only. Reseller customers use their own billing_type
 * (set in the Reseller Portal's Settings) and are handled by
 * ResellerGenerateMonthlyInvoices instead.
 *
 * Run manually (--month bypasses billing date check for testing/backfill):
 * php artisan tenants:run "invoices:generate-monthly"
 * php artisan tenants:run invoices:generate-monthly --option=month=2026-06
 */
class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate-monthly {--month= : Billing month in Y-m format (defaults to the current month). When provided, the billing date check is skipped.}';
    protected $description = 'Generate monthly invoices for all active customers.';
    public function __construct(protected BillingService $billing)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $manualMonth = $this->option('month');
        $month       = $manualMonth ?? now()->format('Y-m');

        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->error('Invalid month format. Expected format: Y-m (e.g. 2026-05).');
            return;
        }

        // Always verify billing_type.
        // Even when --month is provided, this check must not be bypassed because
        // generating Monthly invoices in a Date-to-Date billing system would create
        // data integrity issues. The manual option is intended only for testing
        // or backfilling invoices.
        $billingType = Setting::get('billing_type', 'monthly');
        if ($billingType !== 'monthly') {
            $this->error("Current billing type is '{$billingType}'. This command can only be used when billing_type is 'monthly'.");
            return;
        }

        // When running manually with the --month option (e.g. for testing or backfilling),
        // only the configured billing date check is skipped.
        // The billing_type validation above is always enforced.
        if (!$manualMonth) {
            $billingDate = intval(Setting::get('default_billing_date', 1));
            if (now()->day !== $billingDate) {
               $this->info("Today is not the configured billing date ({$billingDate}). Skipping invoice generation.");
                return;
            }
        }

        // Admin's own customers only — see class docblock.
        $customers = Customer::active()->whereNull('mac_reseller_id')->with('package')->get();

        if ($customers->isEmpty()) {
           $this->info('No active customers found.');
            return;
        }

        $this->info("Month: {$month} | Total customers: {$customers->count()}");
        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        $created = 0;
        $skipped = 0;

    // Instead of sending invoice-generated SMS messages one by one inside the loop,
    // collect them first and send them in a single batch using sendDynamic() after
    // the loop completes. This reduces API requests (e.g. 500 customers = 1 API call
    // instead of 500 separate calls).
        $generated = collect();
        
        foreach ($customers as $customer) {
           
            $exists = Invoice::where('customer_id', $customer->id)
                             ->where('month', $month)
                             ->exists();

            if ($exists) {
                $skipped++;
                $bar->advance();
                continue;
            }

           // Due date: Last day of the month.
            $dueDate = now()->createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

           // Use the customer's overridden monthly bill amount;
            // otherwise, fall back to the package price.
            $amount = $customer->monthly_bill_amount > 0
                ? $customer->monthly_bill_amount
                : ($customer->package->price ?? 0);

            $invoice = Invoice::create([
                'invoice_no'  => Invoice::generateNumber(),
                'customer_id' => $customer->id,
                'package_id'  => $customer->package_id,
                'month'       => $month,
                'amount'      => $amount,
                'discount'    => 0,
                'due_amount'  => $amount,
                'due_date'    => $dueDate,
                'status'      => 'unpaid',
            ]);

           // Apply the customer's advance balance, if available,
            // following the same logic as bulkGenerate().
            if ($customer->advance_balance > 0) {
                $this->billing->applyAdvanceToInvoice($invoice);
                $customer->refresh();
            }

            // Queue the invoice-generated SMS for batch sending.
            $generated->push(['customer' => $customer, 'invoice' => $invoice]);

            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Invoices created: {$created}");
        $this->info("⏭️ Invoices skipped: {$skipped} (already existed).");

        // Send all invoice-generated SMS messages in a single batch API call.
        // Safe to use a plain (no macResellerId) SmsService here — this command
        // now only ever processes Admin's own customers.
        $smsEnabled = Setting::get('invoice_generated_sms', '1') == '1';

        if ($smsEnabled && $generated->isNotEmpty()) {
            $sms        = new SmsService();
            $recipients = [];
       
       
            $totalDueMap = Invoice::whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->selectRaw('customer_id, SUM(due_amount) as total')
            ->groupBy('customer_id')
            ->pluck('total', 'customer_id');
          
          
            foreach ($generated as $item) {
                $customer = $item['customer'];
                $invoice  = $item['invoice'];
                 $due = $totalDueMap->get($customer->id, 0);
           

                if (!$customer->phone) continue;

                $recipients[] = [
                    'mobile'  => $customer->phone,
                    'message' => $sms->buildInvoiceGeneratedMessage(
                        $customer->name,
                        floatval($due),
                        $invoice->month
                    ),
                ];
            }

            if (!empty($recipients)) {
                $result = $sms->sendDynamic($recipients, 'invoice_generated');
                $this->info("📱 Invoice-generated SMS: {$result['sent']} sent, {$result['failed']} failed (single API call).");
            }
        }

        Log::info("GenerateMonthlyInvoices [{$month}]: created={$created}, skipped={$skipped}");
    }
}