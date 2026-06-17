<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mac_reseller_tariff_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tariff_id')->constrained('mac_reseller_tariffs')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('mac_reseller_packages')->cascadeOnDelete();
            $table->string('server_name')->nullable();
            $table->string('protocol')->nullable();
            $table->string('profile')->nullable();
            $table->decimal('rate', 12, 2)->default(0);
            $table->unsignedInteger('validity_days')->default(30);
            $table->unsignedInteger('min_activation_days')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_reseller_tariff_packages');
    }
};
