<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The super_admins table already exists but only has id/timestamps —
     * no login was ever implemented for it. Adding the actual auth columns.
     */
    public function up(): void
    {
        Schema::table('super_admins', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('email')->unique()->after('name');
            $table->string('password')->after('email');
            $table->rememberToken()->after('password');
            $table->boolean('is_active')->default(true)->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('super_admins', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'password', 'remember_token', 'is_active']);
        });
    }
};
