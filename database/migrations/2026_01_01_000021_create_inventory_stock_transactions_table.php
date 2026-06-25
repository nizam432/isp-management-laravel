<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('inventory_products')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('inventory_store_locations')->restrictOnDelete();
            $table->foreignId('from_location_id')->nullable()->constrained('inventory_store_locations')->nullOnDelete()->comment('transfer এর জন্য');
            $table->foreignId('to_location_id')->nullable()->constrained('inventory_store_locations')->nullOnDelete()->comment('transfer এর জন্য');
            $table->enum('type', ['in', 'out']);
            $table->enum('reason', ['purchase', 'sale', 'consumption', 'transfer', 'return', 'damage', 'adjustment']);
            $table->string('reference_type')->nullable()->comment('purchase, sale, consumption etc');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'type']);
            $table->index(['location_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_transactions');
    }
};
