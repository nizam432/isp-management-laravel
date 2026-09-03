<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('mac_reseller_id')->nullable()->after('id');
            $table->index('mac_reseller_id');
        });
    }

    public function down(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropIndex(['mac_reseller_id']);
            $table->dropColumn('mac_reseller_id');
        });
    }
};
