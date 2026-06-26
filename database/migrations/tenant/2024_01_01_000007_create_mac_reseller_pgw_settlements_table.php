<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mac_reseller_pgw_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained('mac_resellers')->cascadeOnDelete();
            $table->decimal('total_received', 14, 2)->default(0);
            $table->decimal('settled_amount', 14, 2)->default(0);
            $table->decimal('remaining_amount', 14, 2)->default(0);
            $table->enum('payment_status', ['settled', 'no_transaction', 'pending'])->default('no_transaction');
            $table->date('settlement_date')->nullable();
            $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_reseller_pgw_settlements');
    }
};
