<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_no')->unique();
            $table->string('invoice_no')->unique();
            $table->unsignedBigInteger('client_id')->nullable()->comment('FK → existing customers table');
            $table->string('walk_in_name')->nullable()->comment('client না হলে');
            $table->foreignId('location_id')->constrained('inventory_store_locations')->restrictOnDelete();
            $table->date('sale_date');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->enum('sale_type', ['cash', 'credit'])->default('cash');
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // client_id → existing customers table (foreign key manually — table name may vary)
            $table->foreign('client_id')->references('id')->on('customers')->nullOnDelete();

            $table->index(['client_id', 'status']);
            $table->index(['sale_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_sales');
    }
};
