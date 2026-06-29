<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bandwidth_purchase_payments', function (Blueprint $table) {
            $table->enum('status', ['active', 'void'])->default('active')->after('remarks');
            $table->text('void_reason')->nullable()->after('status');
            $table->timestamp('void_date')->nullable()->after('void_reason');
            $table->unsignedBigInteger('void_by')->nullable()->after('void_date');
        });
    }

    public function down(): void
    {
        Schema::table('bandwidth_purchase_payments', function (Blueprint $table) {
            $table->dropColumn(['status', 'void_reason', 'void_date', 'void_by']);
        });
    }
};
