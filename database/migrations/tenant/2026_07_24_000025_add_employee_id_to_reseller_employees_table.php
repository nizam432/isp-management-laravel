<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->after('mac_reseller_id');
            $table->foreign('employee_id', 'resemp_hremp_fk')
                  ->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reseller_employees', function (Blueprint $table) {
            $table->dropForeign('resemp_hremp_fk');
            $table->dropColumn('employee_id');
        });
    }
};
