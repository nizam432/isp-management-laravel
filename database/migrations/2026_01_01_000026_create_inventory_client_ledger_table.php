<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_client_ledger', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->comment('FK → existing customers table');
            $table->date('date');
            $table->enum('type', ['sale', 'payment', 'return', 'adjustment']);
            $table->unsignedBigInteger('reference_id')->nullable()->comment('sale_id / return_id');
            $table->decimal('debit', 10, 2)->default(0)->comment('payment পেলাম');
            $table->decimal('credit', 10, 2)->default(0)->comment('sale করলাম');
            $table->decimal('balance', 10, 2)->default(0)->comment('running balance');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('customers')->cascadeOnDelete();

            $table->index(['client_id', 'date']);
            $table->index(['client_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_client_ledger');
    }
};
