<?php

// =============================================================
//  ISP Software — Part 1 (customers-এর আগে চলবে)
//  Split from: 2025_01_01_000000_create_isp_tables.php
//  Reason: customers table needs packages/agents/mikrotik_routers to
//  already exist (foreign keys) — these must run first.
// =============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 2. password_reset_tokens (Laravel default)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // ─────────────────────────────────────────────
        // 4. Spatie Permission Tables
        // ─────────────────────────────────────────────
        $teams = false;
        $tableNames = config('permission.table_names', [
            'roles'                 => 'roles',
            'permissions'          => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles'      => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ]);

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams) {
            $table->id();
            if ($teams) $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->foreign('permission_id')->references('id')->on($tableNames['permissions'])->onDelete('cascade');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign('role_id')->references('id')->on($tableNames['roles'])->onDelete('cascade');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on($tableNames['permissions'])->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on($tableNames['roles'])->onDelete('cascade');
            $table->primary(['permission_id', 'role_id']);
        });

        // ─────────────────────────────────────────────
        // 5. packages
        // ─────────────────────────────────────────────
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedInteger('speed_download')->comment('Mbps');
            $table->unsignedInteger('speed_upload')->comment('Mbps');
            $table->unsignedInteger('data_limit')->default(0)->comment('GB, 0=unlimited');
            $table->decimal('price', 10, 2)->comment('monthly price BDT');
            $table->decimal('connection_fee', 10, 2)->default(0);
            $table->enum('type', ['home', 'business', 'student'])->default('home');
            $table->string('mikrotik_profile', 100)->nullable()->comment('MikroTik queue profile name');
            $table->boolean('is_active')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ─────────────────────────────────────────────
        // 6. agents
        // ─────────────────────────────────────────────
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('area', 100)->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0)->comment('percentage');
            $table->decimal('balance', 10, 2)->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        // ─────────────────────────────────────────────
        // 11. mikrotik_routers
        // ─────────────────────────────────────────────
        Schema::create('mikrotik_routers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('ip_address', 20);
            $table->unsignedInteger('api_port')->default(8728);
            $table->string('username', 50);
            $table->string('password', 100);
            $table->string('area', 100)->nullable();
            $table->boolean('is_active')->default(1);
            $table->dateTime('last_seen')->nullable();
            $table->timestamps();
        });

        // ─────────────────────────────────────────────
        // 12. ip_pools
        // ─────────────────────────────────────────────
        Schema::create('ip_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained('mikrotik_routers')->onDelete('cascade');
            $table->string('pool_name', 100);
            $table->string('start_ip', 20);
            $table->string('end_ip', 20);
            $table->unsignedInteger('total_ip')->default(0);
            $table->unsignedInteger('used_ip')->default(0);
            $table->timestamps();
        });

        // ─────────────────────────────────────────────
        // 16. activity_logs (only needs `users`, already exists early)
        // ─────────────────────────────────────────────
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action', 100);
            $table->string('model_type', 100)->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });

        // ─────────────────────────────────────────────
        // 17. inventory_items (no external FK)
        // ─────────────────────────────────────────────
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('category', ['router', 'cable', 'onu', 'switch', 'splitter', 'other'])->default('other');
            $table->string('unit', 20)->default('pcs')->comment('pcs, meter, roll');
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('min_stock')->default(0)->comment('alert threshold');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
    }
};
