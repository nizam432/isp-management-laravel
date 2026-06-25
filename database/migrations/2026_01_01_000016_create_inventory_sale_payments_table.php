<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('inventory_sales')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank', 'mobile_banking', 'bkash', 'nagad'])->default('cash');
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_void')->default(false);
            $table->text('void_reason')->nullable();
            $table->foreignId('void_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('void_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sale_id', 'is_void']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_sale_payments');
    }
};
