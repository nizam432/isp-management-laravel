<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'is_reseller')) {
                $table->tinyInteger('is_reseller')->default(1)->after('is_active');
            }
            if (!Schema::hasColumn('tenants', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->default(0)->after('is_reseller');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'is_reseller')) {
                $table->dropColumn('is_reseller');
            }
            if (Schema::hasColumn('tenants', 'parent_id')) {
                $table->dropColumn('parent_id');
            }
        });
    }
};