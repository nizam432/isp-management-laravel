<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated version of these 5 original migrations:
 *   1. 2019_09_15_000010_create_tenants_table.php
 *   2. 2026_05_20_092841_add_columns_to_tenants_table.php
 *   3. 2026_05_20_120251_update_tenants_add_reseller_type.php
 *   4. 2026_05_20_fix_tenants_parent_id_to_string.php
 *   5. 2026_05_22_133544_fix_tenants_parent_id_to_string.php  ← exact duplicate of #4
 *
 * `parent_id` goes straight to string(100) here — the original migrations
 * added it as unsignedBigInteger (#2), then changed it to string twice in a
 * row via two near-identical files (#4 and #5, same up()/down() logic,
 * different filenames — likely created by accident). This version skips the
 * back-and-forth: `parent_id` is a string from the start.
 *
 * NOTE: this file is meant for FRESH deployments/new environments — it
 * replaces the need to run all 5 original files individually. Do NOT run
 * this in an environment where those 5 have already executed (the `tenants`
 * table already exists there); it would try to create it again and fail.
 * In that case, keep the original 5 files as-is — they've already done
 * their job.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('name', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();

            $table->foreignId('plan_id')->nullable()->constrained('plans');
            $table->timestamp('plan_expires_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->tinyInteger('is_reseller')->default(1);
            $table->string('parent_id', 100)->default('0');
            $table->integer('level')->default(1);

            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};