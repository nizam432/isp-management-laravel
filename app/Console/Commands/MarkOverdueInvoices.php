<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BillingService;

class MarkOverdueInvoices extends Command
{
    protected $signature   = 'billing:mark-overdue';
    protected $description = 'Mark invoices as overdue based on grace period setting.';

    public function __construct(protected BillingService $billing)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $count = $this->billing->markOverdueInvoices();
        $this->info("Marked {$count} invoice(s) as overdue.");
    }
}
