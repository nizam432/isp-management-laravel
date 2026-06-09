<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Setting;
use App\Services\NotificationService;
use Carbon\Carbon;

class SendExpiryReminders extends Command
{
    protected $signature   = 'billing:send-expiry-reminders';
    protected $description = 'Send expiry reminder SMS/Email to customers before account expires.';

    public function handle(): void
    {
        $smsDays = intval(Setting::get('expiry_sms_days_before', 3));
        $smsOn   = Setting::get('expiry_sms', '1') == '1';
        $emailOn = Setting::get('expiry_email', '0') == '1';

        if (!$smsOn && !$emailOn) {
            $this->info('Expiry reminders are disabled.');
            return;
        }

        $notification = new NotificationService();
        $targetDate   = Carbon::today()->addDays($smsDays)->toDateString();
        $sent         = 0;

        $customers = Customer::active()
            ->whereDate('expire_date', $targetDate)
            ->whereNull('expiry_sms_sent_at')
            ->get();

        foreach ($customers as $customer) {
            $notification->expiryReminder($customer);
            $customer->update(['expiry_sms_sent_at' => now()]);
            $sent++;
            $this->info("Expiry reminder sent to: {$customer->name}");
        }

        $this->info("Total expiry reminders sent: {$sent}");
    }
}
