<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olt_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Default seed (sample থেকে সব types) ──
        $now = now();
        DB::table('olt_types')->insert([
            ['name' => 'BDCOM_EPON',       'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'BDCOM_GPON',       'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'VSOL_EPON',        'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'VSOL_GPON',        'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'VSOL_EPON_TYPE_2', 'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ECOM_EPON',        'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ECOM_GPON',        'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'DBC_EPON',         'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'DBC_GPON',         'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CDATA_EPON',       'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CDATA_GPON',       'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'AVEIS_EPON',       'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'AVEIS_GPON',       'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ATOP_EPON',        'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ITLINK_EPON',      'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PHOTON_EPON',      'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PHOTON_GPON',      'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'AURORA_EPON',      'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CoreLink_EPON',    'details' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('olt_types');
    }
};
