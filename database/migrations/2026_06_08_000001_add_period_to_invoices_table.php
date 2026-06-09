<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'period_start')) {
                $table->date('period_start')->nullable()->after('month');
            }
            if (!Schema::hasColumn('invoices', 'period_end')) {
                $table->date('period_end')->nullable()->after('period_start');
            }
            if (!Schema::hasColumn('invoices', 'billing_type')) {
                $table->enum('billing_type', ['monthly', 'date_to_date'])->default('monthly')->after('period_end');
            }
        });
    }

    public function down(): void
    {
        // Do not rollback on production.
    }
};
