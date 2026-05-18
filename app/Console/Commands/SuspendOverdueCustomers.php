<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * SuspendOverdueCustomers
 * ─────────────────────────────────────────────
 * প্রতিদিন রাতে চলবে।
 * বিল বাকি customer গুলো MikroTik এ suspend হবে।
 *
 * Run manually:
 *   php artisan mikrotik:suspend-overdue
 *
 * Scheduled: প্রতিদিন রাত ১২টায়
 */
class SuspendOverdueCustomers extends Command
{
    protected $signature   = 'mikrotik:suspend-overdue';
    protected $description = 'বিল বাকি customers কে MikroTik এ suspend করো';

    public function handle(MikrotikService $mikrotik): void
    {
        $router = MikrotikRouter::where('is_active', 1)->first();

        if (!$router) {
            $this->error('কোনো active MikroTik router নেই।');
            return;
        }

        // বিল বাকি customers — overdue invoice আছে এবং এখনো active
        $customers = Customer::where('status', 'active')
            ->where('mikrotik_status', 'active')
            ->whereHas('invoices', fn($q) => $q
                ->whereIn('status', ['unpaid', 'overdue'])
                ->where('due_date', '<', now())
            )
            ->get();

        if ($customers->isEmpty()) {
            $this->info('কোনো overdue customer নেই।');
            return;
        }

        $this->info("মোট {$customers->count()} জন customer suspend হবে...");
        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        $successCount = 0;
        $failCount    = 0;

        try {
            $mikrotik->withRouter($router, function (MikrotikService $m) use ($customers, $bar, &$successCount, &$failCount) {
                foreach ($customers as $customer) {
                    try {
                        $m->suspendCustomer($customer);
                        $customer->update([
                            'status'          => 'suspended',
                            'mikrotik_status' => 'suspended',
                        ]);
                        $successCount++;
                        Log::info("Suspended: {$customer->customer_code} — {$customer->name}");
                    } catch (\Exception $e) {
                        $failCount++;
                        Log::warning("Suspend failed [{$customer->customer_code}]: " . $e->getMessage());
                    }
                    $bar->advance();
                }
            });
        } catch (\Exception $e) {
            $this->error("MikroTik connection failed: " . $e->getMessage());
            Log::error("SuspendOverdueCustomers failed: " . $e->getMessage());
            return;
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Success: {$successCount} জন suspend হয়েছে।");

        if ($failCount > 0) {
            $this->warn("⚠️ Failed: {$failCount} জন suspend হয়নি। Log দেখুন।");
        }
    }
}
