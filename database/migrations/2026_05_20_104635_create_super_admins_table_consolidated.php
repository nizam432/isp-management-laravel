<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated version of these 2 original migrations:
 *   1. 2026_05_20_104635_create_super_admins_table.php (id + timestamps only)
 *   2. 2026_07_05_121102_add_auth_fields_to_super_admins_table.php (added auth columns)
 *
 * NOTE: for FRESH deployments/new environments only. Do NOT run this where
 * the 2 original files have already executed (super_admins table already
 * exists there) — keep those files as-is in that environment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_admins');
    }
};
