<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks pre-created, pre-migrated tenant databases (since this hosting
 * environment's MySQL user has no CREATE DATABASE privilege — confirmed via
 * hosting support ticket). Admin creates empty databases manually via
 * cPanel's "MySQL Databases" UI, migrates them once, then this pool table
 * tracks which ones are free vs already assigned to a tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_pool', function (Blueprint $table) {
            $table->id();
            $table->string('database_name')->unique();
            $table->boolean('is_used')->default(false);
            $table->string('tenant_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_pool');
    }
};
