<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            // ── Core fields ───────────────────────────────────────────────
            $table->string('expense_no', 20)->unique()
                ->comment('Auto-generated, e.g. EXP-2026-0001');

            $table->foreignId('category_id')
                ->constrained('expense_categories')
                ->onDelete('restrict')
                ->comment('FK → expense_categories.id');

            $table->decimal('amount', 12, 2)
                ->comment('Expense amount in BDT');

            $table->date('expense_date')
                ->comment('Date the expense was incurred');

            // ── Payment details ───────────────────────────────────────────
            $table->enum('payment_method', [
                'cash', 'bkash', 'nagad', 'rocket', 'bank', 'cheque', 'card'
            ])->default('cash');

            $table->string('transaction_id', 100)->nullable()
                ->comment('bKash/Nagad TrxID or cheque/bank ref');

            // ── Who & what ───────────────────────────────────────────────
            $table->string('payee', 150)->nullable()
                ->comment('Person or company paid to');

            $table->string('reference_no', 100)->nullable()
                ->comment('Vendor invoice no. or any external reference');

            $table->text('description')->nullable()
                ->comment('Short note about the expense');

            // ── Attachment ───────────────────────────────────────────────
            $table->string('receipt_path', 255)->nullable()
                ->comment('Uploaded receipt file path (storage/receipts/...)');

            // ── Approval / status ─────────────────────────────────────────
            $table->enum('status', ['pending', 'approved', 'rejected', 'void'])
                ->default('approved')
                ->comment('pending=needs approval, approved=confirmed');

            $table->text('reject_reason')->nullable()
                ->comment('Filled when status = rejected or void');

            // ── Audit trail ───────────────────────────────────────────────
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User who recorded this expense');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User who approved (if approval workflow enabled)');

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();  // safe delete — keeps history intact

            // ── Indexes ───────────────────────────────────────────────────
            $table->index('expense_date');
            $table->index('category_id');
            $table->index('status');
            $table->index(['expense_date', 'category_id']);  // P&L monthly query
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
