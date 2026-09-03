<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mac_reseller_sub_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mac_reseller_id')->constrained('mac_resellers')->cascadeOnDelete();
            $table->foreignId('mac_reseller_zone_id')->constrained('mac_reseller_zones')->cascadeOnDelete();
            $table->string('name');
            $table->text('details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['mac_reseller_zone_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_reseller_sub_zones');
    }
};
