<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mac_reseller_package_selling_rates', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('mac_reseller_id');
            $table->foreign('mac_reseller_id', 'mrpsr_reseller_fk')
                  ->references('id')->on('mac_resellers')->cascadeOnDelete();

            $table->unsignedBigInteger('mac_reseller_tariff_package_id');
            $table->foreign('mac_reseller_tariff_package_id', 'mrpsr_tariff_pkg_fk')
                  ->references('id')->on('mac_reseller_tariff_packages')->cascadeOnDelete();

            $table->decimal('selling_rate', 10, 2);
            $table->timestamps();

            // one selling price per reseller per package line
            $table->unique(['mac_reseller_id', 'mac_reseller_tariff_package_id'], 'reseller_tariff_pkg_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_reseller_package_selling_rates');
    }
};
