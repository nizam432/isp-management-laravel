<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\DatabasePool;

class MigratePoolDatabases extends Command
{
    /**
     * Usage: php artisan pool:migrate
     * Runs a plain (non-destructive) `migrate` on every database in the
     * database_pool table — both assigned and unassigned. Only NEW,
     * not-yet-run migrations execute; existing tables/data are untouched.
     */
    protected $signature = 'pool:migrate';
    protected $description = 'Run pending migrations on all pool databases (assigned and unassigned), without wiping data.';

    public function handle(): void
    {
        foreach (DatabasePool::all() as $pool) {
            $this->info("Migrating: {$pool->database_name} ...");

            config(['database.connections.pool.database' => $pool->database_name]);
            DB::purge('pool');

            Artisan::call('migrate', [
                '--database' => 'pool',
                '--path'     => 'database/migrations/tenant',
                '--force'    => true,
            ]);

            $this->line(Artisan::output());
        }

        $this->info('Done — all pool databases migrated.');
    }
}
