<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_heads', function (Blueprint $table) {
            $table->unsignedBigInteger('mac_reseller_id')->nullable()->after('id');
            $table->foreign('mac_reseller_id', 'salhead_mrid_fk')
                  ->references('id')->on('mac_resellers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salary_heads', function (Blueprint $table) {
            $table->dropForeign('salhead_mrid_fk');
            $table->dropColumn('mac_reseller_id');
        });
    }
};
