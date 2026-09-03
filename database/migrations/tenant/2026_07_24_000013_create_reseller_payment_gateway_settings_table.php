<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_payment_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mac_reseller_id')->constrained('mac_resellers')->cascadeOnDelete();
            $table->string('gateway_slug');
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('sandbox')->default(true);
            $table->timestamps();

            $table->unique(['mac_reseller_id', 'gateway_slug'], 'reseller_pg_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_payment_gateway_settings');
    }
};
