<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks individual payment installments against a mac_reseller_fundings
 * record. A single funding (e.g. 1000 tk requested) can be paid in multiple
 * partial installments over time (300, then 200, then 500) — each becomes
 * its own row here, independently voidable, and independently linked to
 * an Income record (only created when amount > 0).
 *
 * The parent mac_reseller_fundings row keeps cached payment/due_amount/
 * transaction_status columns (matching this app's existing Invoice/Payment
 * pattern) — these are recalculated from this table's active rows every
 * time a payment is added or voided, never edited independently.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mac_reseller_fund_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_id')->constrained('mac_reseller_fundings')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bkash', 'nagad', 'rocket', 'card', 'bank'])->default('cash');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->date('received_date');
            $table->text('remarks')->nullable();

            $table->enum('status', ['active', 'void'])->default('active');
            $table->text('void_reason')->nullable();
            $table->unsignedBigInteger('void_by')->nullable();
            $table->timestamp('void_date')->nullable();

            $table->unsignedBigInteger('income_id')->nullable();

            $table->timestamps();

            $table->foreign('received_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('void_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_reseller_fund_payments');
    }
};
