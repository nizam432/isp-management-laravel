<?php

// =============================================================
//  ISP Software — Part 3 (payments-এর পরে চলবে)
//  Split from: 2025_01_01_000000_create_isp_tables.php
//  Reason: agent_commissions has a foreign key to `payments`, which is
//  created in a completely separate, later-timestamped migration
//  (2026_06_04_113933_create_payments_table.php). Also needs `agents`,
//  which is already created in Part 1.
// =============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
    }
};
