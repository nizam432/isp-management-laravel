<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: this migration file already existed but was left empty (no column was
     * ever actually added) — that's why /sms/settings was throwing "Unknown column
     * 'is_enabled'" even after the file was "run". Filling it in now.
     *
     * `is_enabled` is a Super Admin-level toggle: which gateways are made available
     * for ISP tenants to choose from (separate from `is_active`, which is which
     * gateway a specific tenant currently has selected). Defaults to true so
     * existing gateways don't suddenly disappear from every tenant's options.
     */
    public function up(): void
    {
        Schema::table('sms_gateways', function (Blueprint $table) {
            $table->boolean('is_enabled')->default(true)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_gateways', function (Blueprint $table) {
            $table->dropColumn('is_enabled');
        });
    }
};