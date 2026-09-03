<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Explicitly registered Artisan commands. Laravel auto-discovers
     * everything under app/Console/Commands anyway (see commands() below),
     * so this array is only needed if a command lives outside that folder.
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * IMPORTANT — multi-tenant (Stancl Tenancy v3):
     * Every billing/reminder/OLT command below only makes sense run INSIDE
     * a tenant's own database connection (each ISP's own customers/invoices).
     * Calling $schedule->command('billing:generate-date-to-date') directly
     * runs it against the CENTRAL connection only — no tenant ever gets its
     * invoices generated. Wrapping with `tenants:run "..."` (Stancl's own
     * command) makes the scheduler iterate every tenant and run the inner
     * command inside each one, exactly like running it manually per tenant.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ── Admin's own customers ──
        $schedule->command('tenants:run billing:generate-date-to-date')
               //  ->daily()
               ->everyMinute()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/billing-date-to-date.log'));

        $schedule->command('tenants:run "invoices:generate-monthly"')
                 //->dailyAt('00:05')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/invoices-monthly.log'));

        $schedule->command('tenants:run billing:send-due-reminders')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/billing-due-reminder.log'));

        // ── Reseller customers (each reseller's own billing_type/settings/gateway) ──
        $schedule->command('tenants:run reseller-billing:generate-date-to-date')
                 ->daily()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/reseller-billing-date-to-date.log'));

        $schedule->command('tenants:run "reseller-billing:generate-monthly"')
                 ->dailyAt('00:10')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/reseller-invoices-monthly.log'));

        $schedule->command('tenants:run reseller-billing:send-due-reminders')
                 ->dailyAt('09:05')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/reseller-billing-due-reminder.log'));

        // ── Everything else — unchanged ──
        $schedule->command('tenants:run billing:mark-overdue')
                 ->dailyAt('01:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/billing-mark-overdue.log'));

        $schedule->command('tenants:run billing:send-expiry-reminders')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/billing-expiry-reminder.log'));

        $schedule->command('tenants:run olt:sync')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/olt-sync.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}