<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bandwidth_purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50)->default('bank');
            // cash | bkash | nagad | rocket | bank | cheque | card
            $table->string('transaction_no', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('purchase_id')->references('id')->on('bandwidth_purchases')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bandwidth_purchase_payments');
    }
};
