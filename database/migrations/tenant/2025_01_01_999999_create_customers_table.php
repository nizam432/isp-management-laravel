<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // ── Identity ──────────────────────────────────
            $table->string('customer_code', 20)->unique()->comment('e.g. ISP-0001');
            $table->string('name', 100);
            $table->string('phone', 20)->index();
            $table->string('email', 150)->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('nid_number', 30)->nullable();
            $table->string('nid_photo', 255)->nullable();
            $table->string('photo', 255)->nullable();

            // ── Address / Location ───────────────────────
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // ── Zone / Type Relations ─────────────────────
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('sub_zone_id')->nullable()->constrained('sub_zones')->nullOnDelete();
            $table->foreignId('connection_type_id')->nullable()->constrained('connection_types')->nullOnDelete();
            $table->foreignId('client_type_id')->nullable()->constrained('client_types')->nullOnDelete();
            $table->foreignId('protocol_type_id')->nullable()->constrained('protocol_types')->nullOnDelete();

            // ── Service / Ownership ────────────────────────
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('router_id')->nullable()->constrained('mikrotik_routers')->nullOnDelete();
            $table->foreignId('mac_reseller_id')->nullable()->constrained('mac_resellers')->nullOnDelete();

            // ── Billing ───────────────────────────────────
            $table->date('connection_date')->nullable();
            $table->decimal('connection_fee', 10, 2)->nullable();
            $table->tinyInteger('billing_date')->default(1)->comment('day of month 1-28');
            $table->date('expire_date')->nullable();
            $table->date('last_payment_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'expired'])->default('active');
            $table->enum('billing_status', ['active', 'inactive', 'left', 'free'])->default('active');
            $table->decimal('monthly_bill_amount', 10, 2)->nullable();
            $table->decimal('advance_balance', 10, 2)->default(0);

            // ── Network / Mikrotik ────────────────────────
            $table->string('ip_address', 20)->nullable();
            $table->string('mac_address', 20)->nullable();
            $table->string('pppoe_username', 50)->nullable();
            $table->string('pppoe_password', 100)->nullable();
            $table->string('mikrotik_status')->nullable()->default('pending');
            $table->string('mikrotik_uid')->nullable()->comment('RouterOS internal .id, e.g. *3A');
            $table->timestamp('last_online_at')->nullable();

            // ── Misc ──────────────────────────────────────
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
       // Schema::dropIfExists('customers');
    }
};
