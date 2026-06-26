<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('inventory_product_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('model')->nullable();
            $table->enum('unit', ['pcs', 'meter', 'roll', 'box'])->default('pcs');
            $table->integer('meter_per_roll')->nullable()->comment('শুধু roll type এর জন্য');
            $table->decimal('stock_quantity', 10, 2)->default(0);
            $table->integer('low_stock_alert')->default(5);
            $table->decimal('purchase_price', 10, 2)->nullable()->comment('last purchase price — auto fill এর জন্য');
            $table->decimal('sell_price', 10, 2)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_products');
    }
};
