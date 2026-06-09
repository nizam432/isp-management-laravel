<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\NotificationService;
use Carbon\Carbon;

class SendBillDueReminders extends Command
{
    protected $signature   = 'billing:send-due-reminders';
    protected $description = 'Send bill due reminder SMS/Email to customers before due date.';

    public function handle(): void
    {
        $smsDays   = intval(Setting::get('bill_due_sms_days_before', 3));
        $emailDays = intval(Setting::get('bill_due_email_days_before', 3));
        $smsOn     = Setting::get('bill_due_sms', '1') == '1';
        $emailOn   = Setting::get('bill_due_email', '0') == '1';

        if (!$smsOn && !$emailOn) {
            $this->info('Bill due reminders are disabled.');
            return;
        }

        $notification = new NotificationService();
        $sent         = 0;

        // Find invoices due on target date (SMS days)
        if ($smsOn) {
            $targetDate = Carbon::today()->addDays($smsDays)->toDateString();
            $invoices   = Invoice::with('customer')
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereDate('due_date', $targetDate)
                ->whereNull('bill_due_sms_sent_at')
                ->get();

            foreach ($invoices as $invoice) {
                $notification->billDueReminder($invoice->customer, $invoice);
                $invoice->update(['bill_due_sms_sent_at' => now()]);
                $sent++;
                $this->info("Reminder sent to: {$invoice->customer->name}");
            }
        }

        $this->info("Total reminders sent: {$sent}");
    }
}
