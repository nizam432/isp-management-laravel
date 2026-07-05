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

        // Previously called NotificationService::billDueReminder() — a dead/unused
        // class (same one found commented-out in BillingService). Now uses
        // SmsService::sendDynamic() so all matching invoices' reminders go out in
        // a single 24BulkSMS DynamicSMSApi call, each with its own personalized
        // message (via buildBillDueMessage(), same DB template used everywhere else).
        if ($smsOn) {
            $targetDate = Carbon::today()->addDays($smsDays)->toDateString();
            $invoices   = Invoice::with('customer')
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereDate('due_date', $targetDate)
                ->whereNull('bill_due_sms_sent_at')
                ->get();

            if ($invoices->isEmpty()) {
                $this->info("আজকের ({$targetDate}) জন্য কোনো bill due reminder পাঠানোর মতো invoice নেই।");
            } else {
                $sms        = new SmsService();
                $recipients = [];
                $matched    = collect();

                foreach ($invoices as $invoice) {
                    $customer = $invoice->customer;

                    if (!$customer || !$customer->phone) {
                        $this->warn("Skip করা হলো — phone নেই: invoice #{$invoice->invoice_no}");
                        continue;
                    }

                    $recipients[] = [
                        'mobile'  => $customer->phone,
                        'message' => $sms->buildBillDueMessage(
                            $customer->name,
                            floatval($invoice->due_amount),
                            $invoice->month
                        ),
                    ];
                    $matched->push($invoice);
                }

                if (empty($recipients)) {
                    $this->info('পাঠানোর মতো কোনো valid recipient (phone number) পাওয়া যায়নি।');
                } else {
                    $result = $sms->sendDynamic($recipients, 'bill_due');

                    // NOTE: DynamicSMSApi রিটার্ন করে পুরো batch-এর জন্য ONE status,
                    // তাই individual recipient-level success/fail আলাদা করে জানা যায় না।
                    // Batch সফল হলে সব matched invoice-কে "reminded" মার্ক করা হচ্ছে।
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

        // Email reminder এখনো এই command-এ implement করা হয়নি — শুধু SMS handle করা
        // হচ্ছে। emailOn true থাকলেও এই command কিছু করবে না email-এর জন্য।
        if ($emailOn) {
            $this->warn('Email reminder এখনো implement করা হয়নি এই command-এ — শুধু SMS পাঠানো হচ্ছে।');
        }

        $this->info("মোট SMS reminder পাঠানো হয়েছে: {$sentSms}");
    }
}
