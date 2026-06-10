<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olt_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olts')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            $table->string('mac_address', 30)->nullable()->index();
            $table->string('onu_mac_address', 30)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('olt_port', 30)->nullable();           // e.g. 1/1/1
            $table->decimal('optical_power', 8, 2)->nullable();  // RX dBm
            $table->enum('onu_status', ['online', 'offline', 'unknown'])->default('unknown');
            $table->string('description', 255)->nullable();
            $table->integer('distance')->nullable();              // meters
            $table->timestamp('last_deregister_time')->nullable();
            $table->string('deregister_reason', 255)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('previous_snapshot')->nullable();

            $table->timestamps();
            $table->index(['olt_id', 'onu_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olt_users');
    }
};
