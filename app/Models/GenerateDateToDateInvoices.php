<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Setting;
use App\Services\BillingService;

class GenerateDateToDateInvoices extends Command
{
    protected $signature   = 'billing:generate-date-to-date';
    protected $description = 'Generate Date to Date invoices for customers whose billing period has ended.';

    public function __construct(protected BillingService $billing)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $billingType = Setting::get('billing_type', 'monthly');

        if ($billingType !== 'date_to_date') {
            $this->info('Billing type is not Date to Date. Skipping.');
            return;
        }

        $customers = Customer::active()->with('package')->get();
        $created   = 0;

        foreach ($customers as $customer) {
            $invoice = $this->billing->generateDateToDateInvoice($customer);
            if ($invoice) {
                $created++;
                $this->info("Invoice generated: {$invoice->invoice_no} for {$customer->name}");
            }
        }

        $this->info("Total invoices generated: {$created}");
    }
}
