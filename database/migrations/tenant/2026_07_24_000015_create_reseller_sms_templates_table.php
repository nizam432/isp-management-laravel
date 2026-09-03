<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_sms_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mac_reseller_id')->constrained('mac_resellers')->cascadeOnDelete();
            $table->string('title', 100);
            $table->string('body', 500);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['mac_reseller_id', 'title'], 'reseller_sms_templates_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_sms_templates');
    }
};
