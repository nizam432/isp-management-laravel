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
        Schema::table('packages', function (Blueprint $table) {
            $table->foreignId('protocol_type_id')
                  ->nullable()
                  ->after('client_type_id')
                  ->constrained('protocol_types')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['protocol_type_id']);
            $table->dropColumn('protocol_type_id');
        });
    }
};
