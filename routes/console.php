<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ══════════════════════════════════════════════════════
// ISP Management — Scheduled Tasks
// ══════════════════════════════════════════════════════

// প্রতিদিন রাত ১২টায় বিল বাকি customer suspend
Schedule::command('mikrotik:suspend-overdue')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/suspend-overdue.log'));

// প্রতি মাসের ১ তারিখে সব active customer এর invoice generate
// (যদি bulk-generate command থাকে)
// Schedule::command('invoices:generate-monthly')
//     ->monthlyOn(1, '08:00');
