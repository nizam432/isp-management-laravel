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
        Schema::create('protocol_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('protocol_type_id')
                  ->nullable()
                  ->after('client_type_id')
                  ->constrained('protocol_types')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['protocol_type_id']);
            $table->dropColumn('protocol_type_id');
        });

        Schema::dropIfExists('protocol_types');
    }
};
