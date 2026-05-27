<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('customers', function (Blueprint $table) {
        $table->string('occupation', 100)->nullable()->after('address');
        $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('occupation');
        $table->foreignId('zone_id')->nullable()->after('area')->constrained('zones')->nullOnDelete();
        $table->foreignId('sub_zone_id')->nullable()->after('zone_id')->constrained('sub_zones')->nullOnDelete();
        $table->foreignId('connection_type_id')->nullable()->after('sub_zone_id')->constrained('connection_types')->nullOnDelete();
        $table->foreignId('client_type_id')->nullable()->after('connection_type_id')->constrained('client_types')->nullOnDelete();
        $table->foreignId('protocol_type_id')->nullable()->after('client_type_id')->constrained('protocol_types')->nullOnDelete();
        $table->foreignId('router_id')->nullable()->after('protocol_type_id')->constrained('mikrotik_routers')->nullOnDelete();
        $table->enum('billing_status', ['active', 'inactive', 'left', 'free'])->default('active')->after('status');
        $table->decimal('monthly_bill_amount', 10, 2)->nullable()->after('billing_status');
        $table->string('portal_password', 255)->nullable()->after('monthly_bill_amount');
    });
}

public function down(): void
{
    Schema::table('customers', function (Blueprint $table) {
        $table->dropForeign(['zone_id']);
        $table->dropForeign(['sub_zone_id']);
        $table->dropForeign(['connection_type_id']);
        $table->dropForeign(['client_type_id']);
        $table->dropForeign(['protocol_type_id']);
        $table->dropForeign(['router_id']);
        $table->dropColumn([
            'occupation', 'gender',
            'zone_id', 'sub_zone_id',
            'connection_type_id', 'client_type_id', 'protocol_type_id',
            'router_id', 'billing_status', 'monthly_bill_amount', 'portal_password',
        ]);
    });
}
};
