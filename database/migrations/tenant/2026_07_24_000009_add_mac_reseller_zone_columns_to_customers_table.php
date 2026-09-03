<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('mac_reseller_zone_id')->nullable()->after('mac_reseller_id');
            $table->unsignedBigInteger('mac_reseller_sub_zone_id')->nullable()->after('mac_reseller_zone_id');

            $table->foreign('mac_reseller_zone_id', 'cust_mrzone_fk')
                  ->references('id')->on('mac_reseller_zones')->nullOnDelete();

            $table->foreign('mac_reseller_sub_zone_id', 'cust_mrsubzone_fk')
                  ->references('id')->on('mac_reseller_sub_zones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign('cust_mrzone_fk');
            $table->dropForeign('cust_mrsubzone_fk');
            $table->dropColumn(['mac_reseller_zone_id', 'mac_reseller_sub_zone_id']);
        });
    }
};
