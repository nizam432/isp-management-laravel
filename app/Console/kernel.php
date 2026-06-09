<?php

// app/Console/Kernel.php এর $commands array তে যোগ করুন:

protected $commands = [
    \App\Console\Commands\GenerateDateToDateInvoices::class,
    \App\Console\Commands\MarkOverdueInvoices::class,
    \App\Console\Commands\SendBillDueReminders::class,
    \App\Console\Commands\SendExpiryReminders::class,
];

// schedule() method এ যোগ করুন:

protected function schedule(Schedule $schedule): void
{
    // Generate Date to Date invoices — every day at midnight
    $schedule->command('billing:generate-date-to-date')->daily();

    // Mark overdue invoices — every day at 1 AM
    $schedule->command('billing:mark-overdue')->dailyAt('01:00');

    // Send bill due reminders — every day at 9 AM
    $schedule->command('billing:send-due-reminders')->dailyAt('09:00');

    // Send expiry reminders — every day at 9 AM
    $schedule->command('billing:send-expiry-reminders')->dailyAt('09:00');
}