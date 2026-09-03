<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('reseller_employee_id')->nullable()->after('received_by');

            $table->foreign('reseller_employee_id', 'pay_reseller_emp_fk')
                  ->references('id')->on('reseller_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('pay_reseller_emp_fk');
            $table->dropColumn('reseller_employee_id');
        });
    }
};
