<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Invoice;
use App\Services\BillingService;
class GenerateDateToDateInvoices extends Command
{
    
    // php artisan tenants:run billing:generate-date-to-date
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

        // Admin's own customers only — resellers' customers use their own
        // billing_type (set in the Reseller Portal's Settings) and are handled
        // by ResellerGenerateDateToDateInvoices instead. Without this filter,
        // reseller customers (whose package_id is always null — they use
        // mac_reseller_tariff_package_id) were getting ৳0 invoices here.
        $customers = Customer::active()->whereNull('mac_reseller_id')->with('package')->get();
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