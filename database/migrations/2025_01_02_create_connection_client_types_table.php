<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Connection Types ───────────────────────────────
        Schema::create('connection_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Client Types ───────────────────────────────────
        Schema::create('client_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Add foreign keys to customers ──────────────────
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('connection_type_id')
                  ->nullable()
                  ->after('area')
                  ->constrained('connection_types')
                  ->nullOnDelete();

            $table->foreignId('client_type_id')
                  ->nullable()
                  ->after('connection_type_id')
                  ->constrained('client_types')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['connection_type_id']);
            $table->dropForeign(['client_type_id']);
            $table->dropColumn(['connection_type_id', 'client_type_id']);
        });

        Schema::dropIfExists('client_types');
        Schema::dropIfExists('connection_types');
    }
};
