<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * MarkOverdueInvoices
 * ─────────────────────────────────────────────
 * প্রতিদিন চলবে।
 * Due date পার হয়ে গেছে এমন unpaid invoice গুলো
 * overdue হিসেবে mark করবে।
 *
 * Run manually:
 *   php artisan invoices:mark-overdue
 */
class MarkOverdueInvoices extends Command
{
    protected $signature   = 'invoices:mark-overdue';
    protected $description = 'Due date পার হওয়া invoice গুলো overdue mark করো';

    public function handle(): void
    {
        $count = Invoice::whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $this->info("✅ {$count} টি invoice overdue mark হয়েছে।");

        Log::info("MarkOverdueInvoices: {$count} invoices marked overdue.");
    }
}
