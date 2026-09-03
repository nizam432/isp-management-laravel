<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\DatabasePool;

class SetupDatabasePool extends Command
{
    //exit;
    /**
     * Usage:
     *   php artisan pool:setup --prefix=amarsmsb_pool_ --count=10
     *
     * Migrates each pool database (prefix + 01, 02, ... count) using the
     * tenant migrations, then registers it in the database_pool table.
     * Safe to re-run — already-registered databases are skipped (firstOrCreate),
     * and migrate --force on an already-migrated DB just does nothing new.
     */
    protected $signature = 'pool:setup {--prefix=amarsmsb_pool_} {--count=10}';
    protected $description = 'Migrate and register pool databases (database_pool table) for tenant assignment.';

    public function handle(): void
    {
        $prefix = $this->option('prefix');
        $count  = (int) $this->option('count');

        for ($i = 1; $i <= $count; $i++) {
            $dbName = $prefix . str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            $this->info("Migrating: {$dbName} ...");

            config(['database.connections.pool.database' => $dbName]);
            DB::purge('pool');

            Artisan::call('migrate:fresh', [
                '--database' => 'pool',
                '--path'     => 'database/migrations/tenant',
                '--force'    => true,
            ]);

            $this->line(Artisan::output());

            DatabasePool::firstOrCreate(['database_name' => $dbName]);

            // Seed the 'isp-admin' role here too, so future tenants assigned
            // to this pool database don't rely solely on the on-demand
            // firstOrCreate() fallback in TenantController::store().
            \Spatie\Permission\Models\Role::on('pool')->firstOrCreate([
                'name'       => 'isp-admin',
                'guard_name' => 'web',
            ]);

            $this->info("Registered: {$dbName}");
            $this->newLine();
        }

        $this->info('Done! Total pool rows: ' . DatabasePool::count());
    }
}