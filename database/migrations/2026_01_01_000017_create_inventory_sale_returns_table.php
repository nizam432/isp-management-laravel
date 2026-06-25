<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();
            $table->foreignId('sale_id')->constrained('inventory_sales')->restrictOnDelete();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreignId('location_id')->constrained('inventory_store_locations')->restrictOnDelete();
            $table->date('return_date');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('reason');
            $table->enum('refund_type', ['cash', 'adjust', 'none'])->default('none');
            $table->enum('status', ['draft', 'approved', 'cancelled'])->default('draft');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_sale_returns');
    }
};
