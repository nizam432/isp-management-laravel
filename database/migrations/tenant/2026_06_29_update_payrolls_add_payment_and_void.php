<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_id')->nullable()->after('status');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('expense_id');
            $table->decimal('due_amount', 12, 2)->default(0)->after('paid_amount');
            $table->text('void_reason')->nullable()->after('note');
            $table->timestamp('void_date')->nullable()->after('void_reason');
            $table->unsignedBigInteger('void_by')->nullable()->after('void_date');
        });

        // Payment history table
        Schema::create('payroll_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_id');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50)->default('cash');
            $table->string('transaction_no', 100)->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('payroll_id')->references('id')->on('payrolls')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_payments');
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['expense_id', 'paid_amount', 'due_amount', 'void_reason', 'void_date', 'void_by']);
        });
    }
};
