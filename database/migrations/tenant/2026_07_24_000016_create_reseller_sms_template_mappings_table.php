<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_sms_template_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mac_reseller_id')->constrained('mac_resellers')->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->string('label')->nullable();
            $table->timestamps();

            $table->unique(['mac_reseller_id', 'type'], 'reseller_sms_mapping_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_sms_template_mappings');
    }
};
