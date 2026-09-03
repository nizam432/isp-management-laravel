<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The `sms_logs` table that actually wins (from
 * 2025_01_02_000000_create_isp_tables_part2.php) is missing columns that
 * SmsService.php (the SMS module built earlier in this project) expects to
 * write to: `gateway`, `mobile`, `response`, `count_sms`. This was flagged
 * as a known conflict back when the isp_tables.php split happened, but
 * never actually fixed — this migration adds the missing columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('sms_logs', 'gateway')) {
                $table->string('gateway', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('sms_logs', 'mobile')) {
                $table->string('mobile', 20)->nullable()->after('gateway');
            }
            if (!Schema::hasColumn('sms_logs', 'response')) {
                $table->text('response')->nullable()->after('gateway_response');
            }
            if (!Schema::hasColumn('sms_logs', 'count_sms')) {
                $table->unsignedInteger('count_sms')->nullable()->after('response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            foreach (['gateway', 'mobile', 'response', 'count_sms'] as $col) {
                if (Schema::hasColumn('sms_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
