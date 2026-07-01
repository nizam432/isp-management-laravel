<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // payments table method column
        Schema::table('payments', function (Blueprint $table) {
            $table->string('method', 50)->change();
        });

        // incomes table payment_method column
        Schema::table('incomes', function (Blueprint $table) {
            $table->string('payment_method', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('method', 20)->change();
        });
        Schema::table('incomes', function (Blueprint $table) {
            $table->string('payment_method', 20)->change();
        });
    }
};
