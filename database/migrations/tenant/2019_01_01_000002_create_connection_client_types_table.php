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
    }

    public function down(): void
    {
        Schema::dropIfExists('client_types');
        Schema::dropIfExists('connection_types');
    }
};