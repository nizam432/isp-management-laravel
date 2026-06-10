<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 100);         // e.g. 160.250.241.40:100
            $table->string('community', 100)->nullable()->default('public');
            $table->foreignId('olt_type_id')->constrained('olt_types')->restrictOnDelete();
            $table->string('web_ip', 100)->nullable();
            $table->string('web_username', 100)->nullable()->default('admin');
            $table->string('web_password', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};
