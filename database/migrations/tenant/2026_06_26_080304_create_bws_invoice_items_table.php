<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bws_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bws_invoice_id')
                  ->constrained('bws_invoices')->onDelete('cascade');
            $table->string('item_name', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('unit', 30)->nullable();
            $table->decimal('quantity', 10, 4)->default(1);
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
