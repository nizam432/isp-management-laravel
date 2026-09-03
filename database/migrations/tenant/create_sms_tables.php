<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOTE: `sms_gateways` (+ its seed data) was REMOVED from this tenant
 * migration — it was wrongly creating a separate copy in every tenant's
 * own database, when it's supposed to be one shared, centrally-managed
 * table (Super Admin controls is_enabled/is_active from one place). It now
 * lives in database/migrations/2026_07_06_061624_create_sms_gateways_table_central.php
 * (central). This file keeps only `sms_logs`, which correctly stays
 * tenant-scoped (each tenant's own SMS history).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sms_logs')) {
            Schema::create('sms_logs', function (Blueprint $table) {
                $table->id();
                $table->string('gateway', 50);
                $table->string('mobile', 20);
                $table->string('phone', 20)->nullable();
                $table->text('message');
                $table->string('type', 50)->default('general');
                $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
                $table->text('response')->nullable();
                $table->unsignedInteger('count_sms')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
