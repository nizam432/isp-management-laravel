<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('source_type', 50)->nullable()->default('manual')->after('status');
            // manual | bandwidth_purchase | hr_payroll | inventory_purchase
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->unsignedBigInteger('source_invoice_id')->nullable()->after('source_id');
            $table->index(['source_type', 'source_id'], 'expenses_source_index');

            $table->timestamp('void_date')->nullable()->after('reject_reason');
            $table->unsignedBigInteger('void_by')->nullable()->after('void_date');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_source_index');
            $table->dropColumn(['source_type', 'source_id', 'source_invoice_id', 'void_date', 'void_by']);
        });
    }
};
