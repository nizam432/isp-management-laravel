<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stores how many SMS segments a message actually consumed (using the same
     * concatenated-SMS counting rules as the frontend count_sms() JS helper).
     * Nullable/default null — older log rows won't have this retroactively
     * calculated, only new ones going forward.
     */
    public function up(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->unsignedInteger('count_sms')->nullable()->default(null)->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropColumn('count_sms');
        });
    }
};
