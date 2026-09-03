<?php

// =============================================================
//  ISP Software — Part 2 (customers-এর পরে চলবে)
//  Split from: 2025_01_01_000000_create_isp_tables.php
//  Reason: these tables have a foreign key to `customers`.
//
//  NOTE — sms_logs conflict: this table is ALSO (re-)defined in
//  create_sms_tables.php with a DIFFERENT, richer schema (gateway, mobile,
//  response, count_sms columns). That file's `if (!Schema::hasTable(...))`
//  guard means whichever one runs FIRST wins — right now that's this one,
//  so SmsService.php's SmsLog::create() calls (which use gateway/mobile/
//  response/count_sms) will fail with "unknown column" against this schema.
//  This needs a separate fix — either remove sms_logs creation from here
//  entirely (rely solely on create_sms_tables.php's version), or make this
//  version match the richer schema. Not fixed in this pass — flagging only.
// =============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────
        // 8. invoices
        // ─────────────────────────────────────────────
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 20)->unique()->comment('e.g. INV-2025-0001');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('package_id')->nullable()->constrained('packages')->onDelete('set null');
            $table->string('month', 7)->comment('format: 2025-01');
            $table->decimal('amount', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->enum('status', ['unpaid', 'paid', 'partial', 'overdue'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'month']);
            $table->index('status');
        });

        // ─────────────────────────────────────────────
        // 15. sms_logs — see NOTE above about the schema conflict
        // ─────────────────────────────────────────────
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('phone', 20);
            $table->text('message');
            $table->enum('type', ['bill_reminder', 'payment_confirm', 'expiry', 'welcome', 'custom'])->default('custom');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('gateway_response')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // ─────────────────────────────────────────────
        // 18. inventory_transactions
        // ─────────────────────────────────────────────
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->enum('type', ['in', 'out'])->comment('in=stock received, out=used');
            $table->unsignedInteger('quantity');
            $table->string('reference', 100)->nullable()->comment('work order or note');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
    }
};
