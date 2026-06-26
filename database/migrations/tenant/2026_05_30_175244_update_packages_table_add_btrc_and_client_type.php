<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'type')) {
                $table->dropColumn('type');
            }
            if (!Schema::hasColumn('packages', 'client_type_id')) {
                $table->unsignedBigInteger('client_type_id')->default(0)->after('description');
            }
            if (!Schema::hasColumn('packages', 'btrc_price')) {
                $table->decimal('btrc_price', 10, 2)->nullable()->after('client_type_id')
                      ->comment('Price for BTRC report');
            }
            if (!Schema::hasColumn('packages', 'btrc_bandwidth')) {
                $table->string('btrc_bandwidth', 50)->nullable()->after('btrc_price')
                      ->comment('Bandwidth for BTRC report');
            }
        });
    }
};