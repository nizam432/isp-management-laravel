<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_client_device_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->comment('FK → existing customers table');
            $table->foreignId('product_id')->constrained('inventory_products')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('inventory_store_locations')->restrictOnDelete();
            $table->string('serial_no')->nullable()->comment('device serial number');
            $table->date('assigned_date');
            $table->date('return_date')->nullable()->comment('null মানে এখনো assigned');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('returned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('customers')->cascadeOnDelete();

            $table->index(['client_id', 'return_date']);
            $table->index(['product_id', 'return_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_client_device_assignments');
    }
};
