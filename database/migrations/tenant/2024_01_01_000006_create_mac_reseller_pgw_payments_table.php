<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mac_reseller_pgw_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained('mac_resellers')->cascadeOnDelete();
            $table->string('client_code')->nullable();
            $table->string('client_ip')->nullable();
            $table->string('client_name')->nullable();
            $table->string('package')->nullable();
            $table->string('billing_status')->nullable();
            $table->string('trx_id')->nullable();
            $table->decimal('monthly_bill', 12, 2)->default(0);
            $table->decimal('received', 12, 2)->default(0);
            $table->string('money_receipt_no')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('gateway_type')->nullable();
            $table->enum('transaction_status', ['pending', 'success', 'failed'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_reseller_pgw_payments');
    }
};
