<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_sms_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mac_reseller_id')->constrained('mac_resellers')->cascadeOnDelete();
            $table->string('gateway_slug', 50);
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['mac_reseller_id', 'gateway_slug'], 'reseller_sms_settings_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_sms_settings');
    }
};
