<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_vendor_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('inventory_vendors')->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['purchase', 'payment', 'return', 'adjustment']);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('debit', 10, 2)->default(0)->comment('আমরা payment দিলাম');
            $table->decimal('credit', 10, 2)->default(0)->comment('purchase করলাম');
            $table->decimal('balance', 10, 2)->default(0)->comment('running balance');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vendor_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_vendor_ledger');
    }
};
