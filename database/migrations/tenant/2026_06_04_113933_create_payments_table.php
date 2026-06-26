<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'bkash', 'nagad', 'rocket', 'card', 'bank', 'advance'])->default('cash');
            $table->string('transaction_id', 100)->nullable()->comment('bKash/Nagad TrxID');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('receive_from', 100)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->enum('status', ['active', 'void'])->default('active');
            $table->boolean('send_sms')->default(0);
            $table->boolean('set_next_billing_date')->default(0);
            $table->date('payment_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};