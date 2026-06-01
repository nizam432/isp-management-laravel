<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'expire_date')) {
                $table->date('expire_date')->nullable()->after('billing_date');
            }
            if (!Schema::hasColumn('customers', 'last_payment_date')) {
                $table->date('last_payment_date')->nullable()->after('expire_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['expire_date', 'last_payment_date']);
        });
    }
};