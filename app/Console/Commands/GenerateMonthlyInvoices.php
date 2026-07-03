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
 * প্রতি মাসের নির্দিষ্ট billing date এ (Settings → Billing → Default Billing Date)
 * স্বয়ংক্রিয়ভাবে চলবে — শুধু billing_type = 'monthly' হলে।
 *
 * সব active customer এর জন্য invoice তৈরি করবে।
 *
 * Run manually (--month দিলে billing date/billing_type check bypass হয়, testing এর জন্য):
 *   php artisan invoices:generate-monthly
 *   php artisan invoices:generate-monthly --month=2026-05
 */
class GenerateMonthlyInvoices extends Command
{
    protected $signature   = 'invoices:generate-monthly {--month= : Y-m format, default current month — দিলে billing_type/billing_date check bypass হয়}';
    protected $description = 'সব active customer এর জন্য monthly invoice তৈরি করো';

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
            $this->error('Month format ভুল। সঠিক format: Y-m (যেমন 2026-05)');
            return;
        }

        // billing_type সবসময় চেক হবে — --month দিয়েও bypass করা যাবে না, কারণ
        // Date to Date সিস্টেমে ভুল করে Monthly invoice তৈরি হয়ে যাওয়া একটা real
        // data-integrity সমস্যা, শুধু টেস্টিং সুবিধার জন্য এটা bypass করা ঠিক না।
        $billingType = Setting::get('billing_type', 'monthly');
        if ($billingType !== 'monthly') {
            $this->error("Billing type এখন '{$billingType}' — এই কমান্ড শুধু 'monthly' billing type এর জন্য। বন্ধ করা হলো।");
            return;
        }

        // --month দিয়ে manually run করলে (e.g. testing/backfill) শুধু "আজ কি সঠিক
        // billing date" চেকটা skip হয় — billing_type চেক উপরে সবসময়ই হয়।
        if (!$manualMonth) {
            $billingDate = intval(Setting::get('default_billing_date', 1));
            if (now()->day !== $billingDate) {
                $this->info("আজ configured billing date ({$billingDate}) না। Skip করা হলো।");
                return;
            }
        }

        $customers = Customer::active()->with('package')->get();

        if ($customers->isEmpty()) {
            $this->info('কোনো active customer নেই।');
            return;
        }

        $this->info("মাস: {$month} | মোট customers: {$customers->count()}");
        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        $created = 0;
        $skipped = 0;

        // Invoice-generated SMS-গুলো লুপের ভেতরে একটা একটা করে পাঠানোর বদলে collect
        // করে রাখা হচ্ছে, লুপ শেষে একবারে sendDynamic() দিয়ে batch পাঠানো হবে —
        // 500 customer হলে 500টা আলাদা API call না হয়ে, ১টা call-ই হবে।
        $generated = collect();

        foreach ($customers as $customer) {
            // আগে থেকে invoice আছে কিনা চেক
            $exists = Invoice::where('customer_id', $customer->id)
                             ->where('month', $month)
                             ->exists();

            if ($exists) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Due date: মাসের শেষ দিন
            $dueDate = now()->createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

            // customer এর নিজস্ব override amount থাকলে সেটা, নাহলে package price
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

            // Advance balance থাকলে সাথে সাথে apply করো (bulkGenerate() এর মতোই)
            if ($customer->advance_balance > 0) {
                $this->billing->applyAdvanceToInvoice($invoice);
                $customer->refresh();
            }

            // "Invoice Generated" SMS এখনই না পাঠিয়ে, batch করার জন্য collect করা হচ্ছে
            $generated->push(['customer' => $customer, 'invoice' => $invoice]);

            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ তৈরি হয়েছে: {$created} টি invoice।");
        $this->info("⏭️ Skip হয়েছে: {$skipped} টি (আগে থেকে ছিল)।");

        // এখন সব invoice-এর SMS একসাথে ১টা batch call-এ পাঠানো হচ্ছে
        $smsEnabled = Setting::get('invoice_generated_sms', '1') == '1';

        if ($smsEnabled && $generated->isNotEmpty()) {
            $sms        = new SmsService();
            $recipients = [];

            foreach ($generated as $item) {
                $customer = $item['customer'];
                $invoice  = $item['invoice'];

                if (!$customer->phone) continue;

                $recipients[] = [
                    'mobile'  => $customer->phone,
                    'message' => $sms->buildInvoiceGeneratedMessage(
                        $customer->name,
                        floatval($invoice->due_amount),
                        $invoice->month
                    ),
                ];
            }

            if (!empty($recipients)) {
                $result = $sms->sendDynamic($recipients, 'invoice_generated');
                $this->info("📱 Invoice-generated SMS: {$result['sent']} sent, {$result['failed']} failed (১টা API call-এ)।");
            }
        }

        Log::info("GenerateMonthlyInvoices [{$month}]: created={$created}, skipped={$skipped}");
    }
}
