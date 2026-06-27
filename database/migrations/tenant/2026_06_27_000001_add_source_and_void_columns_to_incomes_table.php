<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->timestamp('void_date')->nullable()->after('void_reason');
            $table->unsignedBigInteger('void_by')->nullable()->after('void_date');
            $table->foreign('void_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['void_by']);
            $table->dropColumn(['void_date', 'void_by']);
        });
    }
};
