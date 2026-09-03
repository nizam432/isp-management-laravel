<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each tenant database needs its own `cache`/`cache_locks` tables too —
 * once DatabaseTenancyBootstrapper switches the default connection to the
 * tenant's database, Laravel's 'database' cache driver (CACHE_STORE=database)
 * queries THAT connection for the cache table, not the central one. Without
 * this, login rate-limiting (and anything else using cache()) fails with
 * "table cache doesn't exist" on every tenant subdomain.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        if (!Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
