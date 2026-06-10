<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();

            $table->string('income_no', 20)->unique()
                ->comment('Auto-generated: INC-2026-0001');

            $table->foreignId('category_id')
                ->constrained('income_categories')
                ->onDelete('restrict');

            $table->decimal('amount', 12, 2);

            $table->date('income_date');

            $table->enum('payment_method', [
                'cash', 'bkash', 'nagad', 'rocket', 'bank', 'cheque', 'card'
            ])->default('cash');

            $table->string('transaction_id', 100)->nullable();

            // Optional link to customer (e.g. connection fee paid by a customer)
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->onDelete('set null');

            $table->string('payer', 150)->nullable()
                ->comment('Person/company who paid — if not a registered customer');

            $table->string('reference_no', 100)->nullable();

            $table->text('description')->nullable();

            $table->string('receipt_path', 255)->nullable();

            $table->enum('status', ['active', 'void'])->default('active');

            $table->text('void_reason')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('income_date');
            $table->index('category_id');
            $table->index('status');
            $table->index('customer_id');
            $table->index(['income_date', 'category_id']); // P&L query
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
