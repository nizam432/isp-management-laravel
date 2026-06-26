<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_consumption_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumption_id')->constrained('inventory_consumptions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('inventory_products')->restrictOnDelete();
            $table->string('unit');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2)->comment('manually enter, auto fill from last purchase price');
            $table->decimal('total_price', 10, 2);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_consumption_items');
    }
};
