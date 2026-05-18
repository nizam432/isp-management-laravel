<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * GenerateMonthlyInvoices
 * ─────────────────────────────────────────────
 * প্রতি মাসের ১ তারিখে চলবে।
 * সব active customer এর জন্য invoice তৈরি করবে।
 *
 * Run manually:
 *   php artisan invoices:generate-monthly
 *   php artisan invoices:generate-monthly --month=2026-05
 */
class GenerateMonthlyInvoices extends Command
{
    protected $signature   = 'invoices:generate-monthly {--month= : Y-m format, default current month}';
    protected $description = 'সব active customer এর জন্য monthly invoice তৈরি করো';

    public function handle(): void
    {
        $month = $this->option('month') ?? now()->format('Y-m');

        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->error('Month format ভুল। সঠিক format: Y-m (যেমন 2026-05)');
            return;
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

            Invoice::create([
                'invoice_no'  => Invoice::generateNumber(),
                'customer_id' => $customer->id,
                'package_id'  => $customer->package_id,
                'month'       => $month,
                'amount'      => $customer->package->price ?? 0,
                'discount'    => 0,
                'due_amount'  => $customer->package->price ?? 0,
                'due_date'    => $dueDate,
                'status'      => 'unpaid',
            ]);

            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ তৈরি হয়েছে: {$created} টি invoice।");
        $this->info("⏭️ Skip হয়েছে: {$skipped} টি (আগে থেকে ছিল)।");

        Log::info("GenerateMonthlyInvoices [{$month}]: created={$created}, skipped={$skipped}");
    }
}
