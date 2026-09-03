<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\SmsService;
use Carbon\Carbon;

class SendBillDueReminders extends Command
{
    protected $signature   = 'billing:send-due-reminders';
    protected $description = 'Send bill due reminder SMS/Email to customers before due date.';

    public function handle(): void
    {
        // Settings → SMS Notifications → "Bill Due Reminder" toggle + "days before"
        $smsDays   = intval(Setting::get('bill_due_sms_days_before', 3));
        $emailDays = intval(Setting::get('bill_due_email_days_before', 3));
        $smsOn     = Setting::get('bill_due_sms', '1') == '1';
        $emailOn   = Setting::get('bill_due_email', '0') == '1';

        if (!$smsOn && !$emailOn) {
            $this->info('Bill due reminders are disabled।');
            return;
        }

        $sentSms = 0;
        
        if ($smsOn) {
            $targetDate = Carbon::today()->addDays($smsDays)->toDateString();
            $invoices   = Invoice::with('customer')
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereDate('due_date', $targetDate)
                ->whereNull('bill_due_sms_sent_at')
                ->get()
                // Group by customer — a customer with multiple due invoices on the
                // same due_date was previously getting one SMS PER invoice, each
                // showing only that invoice's amount/month (never the total owed).
                // Now one SMS per customer: amount = sum of all their due invoices,
                // month = the latest one among them.
                ->groupBy('customer_id');

            if ($invoices->isEmpty()) {
                $this->info("No invoices found for bill due reminders on {$targetDate}.");
            } else {
                $sms        = new SmsService();
                $recipients = [];
                $matched    = collect();

                foreach ($invoices as $customerId => $customerInvoices) {
                    $customer = $customerInvoices->first()->customer;

                    if (!$customer || !$customer->phone) {
                        $this->warn("Skip করা হলো — phone নেই: customer #{$customerId}");
                        continue;
                    }

                    $totalDue  = $customerInvoices->sum('due_amount');
                    $lastMonth = $customerInvoices->sortByDesc('month')->first()->month;

                    $recipients[] = [
                        'mobile'  => $customer->phone,
                        'message' => $sms->buildBillDueMessage(
                            $customer->name,
                            floatval($totalDue),
                            $lastMonth
                        ),
                    ];

                    // All of this customer's matched invoices get marked together,
                    // since they're all covered by the one combined SMS.
                    $matched = $matched->concat($customerInvoices);
                }

                if (empty($recipients)) {
                    $this->info('No valid recipients (phone numbers) found to send reminders.');
                } else {
                    $result = $sms->sendDynamic($recipients, 'bill_due');
                    // NOTE: DynamicSMSApi returns a single status for the entire batch,
                    // so individual recipient success/failure cannot be determined.
                    // If the batch is successful, all matched invoices are marked as "reminded".
                    if ($result['sent'] > 0) {
                        foreach ($matched as $invoice) {
                            $invoice->update(['bill_due_sms_sent_at' => now()]);
                        }
                    }
                    $sentSms = $result['sent'];
                    $this->info("SMS reminder পাঠানো হয়েছে {$sentSms} জন customer কে (১টা API call-এ)।");
                    if ($result['failed'] > 0) {
                        $this->warn("{$result['failed']} টি reminder পাঠাতে ব্যর্থ হয়েছে — SMS Reports-এ log দেখো।");
                    }
                }
            }
        }
        // Previously called NotificationService::billDueReminder() — a dead/unused
        // class (same one found commented-out in BillingService). Now uses
        // SmsService::sendDynamic() so all matching invoices' reminders go out in
        // a single 24BulkSMS DynamicSMSApi call, each with its own personalized
        // message (via buildBillDueMessage(), same DB template used everywhere else).
        
        

        // Email reminders are not yet implemented in this command.
        // Even if email reminders are enabled, only SMS reminders are processed.
        if ($emailOn) {
            $this->warn('Email reminders are not yet implemented in this command. Only SMS reminders are being sent.');
        }

        $this->info("Total SMS reminders sent: {$sentSms}");
    }
}
