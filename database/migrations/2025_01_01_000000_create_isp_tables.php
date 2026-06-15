<?php

// =============================================================
//  ISP Software — Laravel Migrations (সব একসাথে)
//  Run: php artisan migrate
//  Required: composer require spatie/laravel-permission
// =============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ─────────────────────────────────────────────
// 1. USERS
// ─────────────────────────────────────────────
return new class extends Migration
{
    public function up(): void
    {
        // 1. users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->boolean('is_active')->default(1);
            $table->rememberToken();
            $table->timestamps();
        });

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
        // 8. invoices
        // ─────────────────────────────────────────────
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 20)->unique()->comment('e.g. INV-2025-0001');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('package_id')->nullable()->constrained('packages')->onDelete('set null');
            $table->string('month', 7)->comment('format: 2025-01');
            $table->decimal('amount', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->enum('status', ['unpaid', 'paid', 'partial', 'overdue'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'month']);
            $table->index('status');
        });

        // ─────────────────────────────────────────────
        // 9. payments
        // ─────────────────────────────────────────────

        // ─────────────────────────────────────────────
        // 10. agent_commissions
        // ─────────────────────────────────────────────
        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->dateTime('paid_at')->nullable();
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
        // 13. tickets
        // ─────────────────────────────────────────────
   /*      Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 20)->unique()->comment('e.g. TKT-2025-0001');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('subject', 200);
            $table->text('description')->nullable();
            $table->enum('category', ['connection', 'billing', 'speed', 'device', 'other'])->default('connection');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'assigned', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('priority');
        });
 */
        // ─────────────────────────────────────────────
        // 14. ticket_replies
        // ─────────────────────────────────────────────
/*         Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('message');
            $table->timestamps();
        }); */

        // ─────────────────────────────────────────────
        // 15. sms_logs
        // ─────────────────────────────────────────────
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('phone', 20);
            $table->text('message');
            $table->enum('type', ['bill_reminder', 'payment_confirm', 'expiry', 'welcome', 'custom'])->default('custom');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('gateway_response')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // ─────────────────────────────────────────────
        // 16. activity_logs
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
        // 17. inventory_items
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

        // ─────────────────────────────────────────────
        // 18. inventory_transactions
        // ─────────────────────────────────────────────
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->enum('type', ['in', 'out'])->comment('in=stock received, out=used');
            $table->unsignedInteger('quantity');
            $table->string('reference', 100)->nullable()->comment('work order or note');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Drop in reverse order to respect FK constraints
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('ip_pools');
        Schema::dropIfExists('mikrotik_routers');
        Schema::dropIfExists('agent_commissions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('agents');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};