<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * NOTE: `payment_gateways` (the master gateway list Super Admin manages)
     * was REMOVED from this tenant migration — it was wrongly creating a
     * separate copy of that table in every tenant's own database, when it's
     * supposed to be one shared, centrally-managed table. It now lives in
     * database/migrations/2026_07_06_023537_create_payment_gateways_table.php
     * (central). This file keeps only the genuinely tenant-scoped tables
     * (they have foreign keys to this tenant's own customers/invoices).
     */
    public function up(): void
    {
        // ── ISP-level credentials ──────────────────────────
        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50);
            $table->string('gateway_slug', 30);
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('sandbox')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'gateway_slug']);
            $table->index('tenant_id');
        });

        // ── Transaction log ─────────────────────────────────
        Schema::create('payment_gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('txn_ref', 60)->unique();
            $table->string('tenant_id', 50);
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('gateway', 30);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('BDT');
            $table->string('gateway_txn_id', 200)->nullable();
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');
            $table->json('gateway_response')->nullable();
            $table->string('payer_ip', 45)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
            $table->index(['invoice_id',  'status']);
            $table->index(['tenant_id',   'gateway']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_transactions');
        Schema::dropIfExists('payment_gateway_settings');
    }
};
