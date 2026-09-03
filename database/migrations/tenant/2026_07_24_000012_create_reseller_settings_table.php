<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mac_reseller_id')->constrained('mac_resellers')->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();

            $table->unique(['mac_reseller_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_settings');
    }
};
