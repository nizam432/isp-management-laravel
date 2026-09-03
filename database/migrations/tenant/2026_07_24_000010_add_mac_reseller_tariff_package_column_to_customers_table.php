<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('mac_reseller_tariff_package_id')->nullable()->after('package_id');

            $table->foreign('mac_reseller_tariff_package_id', 'cust_mrtariffpkg_fk')
                  ->references('id')->on('mac_reseller_tariff_packages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign('cust_mrtariffpkg_fk');
            $table->dropColumn('mac_reseller_tariff_package_id');
        });
    }
};
