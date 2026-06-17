<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mac_reseller_fundings', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('reseller_id')->constrained('mac_resellers')->cascadeOnDelete();
            $table->decimal('fund_amount', 14, 2);
            $table->decimal('payment', 14, 2)->default(0);
            $table->decimal('processing_fee', 10, 2)->default(0);
            $table->decimal('vat', 10, 2)->default(0);
            $table->boolean('apply_vat')->default(false);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('due_amount', 14, 2)->default(0);
            $table->date('funding_date');
            $table->foreignId('fund_given_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('received_date')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->enum('transaction_status', ['paid', 'due', 'partial'])->default('due');
            $table->boolean('restrict_online')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_reseller_fundings');
    }
};
