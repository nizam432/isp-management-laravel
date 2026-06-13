<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. bandwidth_sale_customers ──────────────────────────────
        Schema::create('bandwidth_sale_customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 20)->unique();
            $table->string('customer_name', 150);
            $table->string('contact_person', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('mobile_number', 20);
            $table->string('phone_number', 20)->nullable();
            $table->enum('pop_status', ['active', 'inactive'])->default('active');
            $table->string('reference_by', 100)->nullable();
            $table->text('address')->nullable();
            $table->text('remarks')->nullable();
            $table->string('facebook_url', 255)->nullable();
            $table->string('skype_id', 100)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('photo', 255)->nullable();
            // Transmission
            $table->text('attn_info')->nullable();
            $table->json('vlan_info')->nullable();
            $table->string('bzr_dr_nas_id', 100)->nullable();
            $table->date('activation_date')->nullable();
            $table->json('ip_addresses')->nullable();
            $table->string('pop_info', 255)->nullable();
            // Login
            $table->string('username', 100)->unique()->nullable();
            $table->string('password', 255)->nullable();
            $table->enum('activity_status', ['active', 'inactive'])->default('active');
            // Balance
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── 2. bws_invoices ──────────────────────────────────────────
        Schema::create('bws_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 20)->unique();
            $table->foreignId('bws_customer_id')
                  ->constrained('bandwidth_sale_customers')->onDelete('restrict');
            $table->string('billing_month', 7)->comment('Format: 2026-06');
            $table->date('payment_due')->nullable();
            $table->boolean('daily_basis')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('received_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->enum('status', ['unpaid','paid','partial','overdue'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->boolean('is_recurring')->default(0);
            $table->unsignedTinyInteger('repeat_date')->nullable();
            $table->date('recurring_start')->nullable();
            $table->date('recurring_end')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['bws_customer_id', 'billing_month']);
            $table->index('status');
        });

        // ── 3. bws_invoice_items ─────────────────────────────────────
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

        // ── 4. bws_invoice_payments ──────────────────────────────────
        Schema::create('bws_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 20)->unique();
            $table->foreignId('bws_invoice_id')
                  ->constrained('bws_invoices')->onDelete('restrict');
            $table->foreignId('bws_customer_id')
                  ->constrained('bandwidth_sale_customers')->onDelete('restrict');
            $table->date('received_date');
            $table->string('received_from', 150)->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('payment_method', [
                'cash','bkash','nagad','rocket','bank','cheque','card'
            ])->default('cash');
            $table->decimal('payable_amount', 12, 2);
            $table->decimal('received_amount', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('receipt_transaction_no', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['active', 'void'])->default('active');
            $table->text('void_reason')->nullable();
            $table->unsignedBigInteger('income_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('bws_invoice_id');
            $table->index('bws_customer_id');
            $table->index('received_date');
            $table->index('status');
        });

        // ── 5. incomes table: source_type + source_id ────────────────
        Schema::table('incomes', function (Blueprint $table) {
            $table->string('source_type', 30)->default('manual')->after('status');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->index(['source_type', 'source_id'], 'idx_income_source');
        });

        // ── 6. Bandwidth Sale income category ────────────────────────
        if (!DB::table('income_categories')->where('slug', 'bandwidth-sale')->exists()) {
            DB::table('income_categories')->insert([
                'name'        => 'Bandwidth Sale',
                'slug'        => 'bandwidth-sale',
                'color'       => '#0073b7',
                'icon'        => 'fas fa-wifi',
                'description' => 'Auto-created from Bandwidth Sale invoice payments',
                'is_system'   => 1,
                'is_active'   => 1,
                'sort_order'  => 3,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bws_invoice_payments');
        Schema::dropIfExists('bws_invoice_items');
        Schema::dropIfExists('bws_invoices');
        Schema::dropIfExists('bandwidth_sale_customers');

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropIndex('idx_income_source');
            $table->dropColumn(['source_type', 'source_id']);
        });
    }
};
