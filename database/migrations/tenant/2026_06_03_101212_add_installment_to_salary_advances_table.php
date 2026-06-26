<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_advances', function (Blueprint $table) {
            $table->enum('payment_type', ['one_time', 'installment'])->default('one_time')->after('amount');
            $table->decimal('installment_amount', 10, 2)->default(0)->after('payment_type');
            $table->integer('total_installments')->default(1)->after('installment_amount');
            $table->integer('paid_installments')->default(0)->after('total_installments');
            $table->decimal('remaining_amount', 10, 2)->default(0)->after('paid_installments');
        });
    }

    public function down(): void
    {
        Schema::table('salary_advances', function (Blueprint $table) {
            $table->dropColumn([
                'payment_type',
                'installment_amount',
                'total_installments',
                'paid_installments',
                'remaining_amount',
            ]);
        });
    }
};