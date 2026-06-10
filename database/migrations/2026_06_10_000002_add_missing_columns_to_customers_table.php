<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            if (!Schema::hasColumn('customers', 'gender'))
                $table->string('gender')->nullable()->after('email');

            if (!Schema::hasColumn('customers', 'occupation'))
                $table->string('occupation')->nullable()->after('gender');

            if (!Schema::hasColumn('customers', 'nid_number'))
                $table->string('nid_number')->nullable()->after('occupation');

            if (!Schema::hasColumn('customers', 'client_type_id'))
                $table->unsignedBigInteger('client_type_id')->nullable()->after('nid_number');

            if (!Schema::hasColumn('customers', 'zone_id'))
                $table->unsignedBigInteger('zone_id')->nullable()->after('client_type_id');

            if (!Schema::hasColumn('customers', 'sub_zone_id'))
                $table->unsignedBigInteger('sub_zone_id')->nullable()->after('zone_id');

            if (!Schema::hasColumn('customers', 'connection_type_id'))
                $table->unsignedBigInteger('connection_type_id')->nullable()->after('sub_zone_id');

            if (!Schema::hasColumn('customers', 'protocol_type_id'))
                $table->unsignedBigInteger('protocol_type_id')->nullable()->after('connection_type_id');

            if (!Schema::hasColumn('customers', 'monthly_bill_amount'))
                $table->decimal('monthly_bill_amount', 10, 2)->nullable()->after('protocol_type_id');

            if (!Schema::hasColumn('customers', 'billing_status'))
                $table->string('billing_status')->nullable()->default('active')->after('monthly_bill_amount');

            if (!Schema::hasColumn('customers', 'expire_date'))
                $table->date('expire_date')->nullable()->after('billing_status');

        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'gender', 'occupation', 'nid_number',
                'client_type_id', 'zone_id', 'sub_zone_id',
                'connection_type_id', 'protocol_type_id',
                'monthly_bill_amount', 'billing_status', 'expire_date',
            ]);
        });
    }
};
