<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add bill_due_sms_sent_at to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'bill_due_sms_sent_at')) {
                $table->timestamp('bill_due_sms_sent_at')->nullable()->after('billing_type');
            }
        });

        // Add expiry_sms_sent_at to customers table
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'expiry_sms_sent_at')) {
                $table->timestamp('expiry_sms_sent_at')->nullable()->after('advance_balance');
            }
        });
    }

    public function down(): void
    {
        // Do not rollback on production.
    }
};
