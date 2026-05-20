php<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('name', 100)->nullable()->after('id');
            $table->string('email', 150)->nullable()->after('name');
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('address')->nullable()->after('phone');
            $table->foreignId('plan_id')->nullable()->constrained('plans')->after('address');
            $table->timestamp('plan_expires_at')->nullable()->after('plan_id');
            $table->boolean('is_active')->default(true)->after('plan_expires_at');
            $table->unsignedBigInteger('parent_id')->nullable()->after('is_active');
            $table->integer('level')->default(1)->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'email', 'phone', 'address',
                'plan_id', 'plan_expires_at', 'is_active',
                'parent_id', 'level',
            ]);
        });
    }
};