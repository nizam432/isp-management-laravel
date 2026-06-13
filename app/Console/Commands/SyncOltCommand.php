<?php

namespace App\Console\Commands;

use App\Models\Olt;
use App\Services\Olt\OltSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncOltCommand extends Command
{
    protected $signature   = 'olt:sync {--id= : নির্দিষ্ট OLT ID}';
    protected $description = 'সব active OLT sync করো (Web Scraping / SNMP)';

    public function handle(): int
    {
        $id   = $this->option('id');
        $olts = $id
            ? Olt::active()->where('id', $id)->get()
            : Olt::active()->get();

        if ($olts->isEmpty()) {
            $this->warn('কোনো active OLT পাওয়া যায়নি।');
            return 0;
        }

        $success = 0;
        $failed  = 0;
        $service = new OltSyncService();

        foreach ($olts as $olt) {
            try {
                $result = $service->sync($olt);
                $this->info("✓ [{$olt->ip_address}] {$result['total']} ONUs ({$result['method']})");
                Log::info("Auto sync OK [{$olt->ip_address}]: {$result['total']} ONUs");
                $success++;
            } catch (\Exception $e) {
                $this->error("✗ [{$olt->ip_address}] " . $e->getMessage());
                Log::error("Auto sync failed [{$olt->ip_address}]: " . $e->getMessage());
                $failed++;
            }
        }

        $this->line("━━━ সম্পন্ন: {$success} সফল, {$failed} ব্যর্থ ━━━");

        return 0;
    }
}
