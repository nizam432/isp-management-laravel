<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Master gateway list (Super Admin manages) ──────────
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('slug', 30)->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['local', 'international'])->default('local');
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });

        DB::table('payment_gateways')->insert([
            // LOCAL
            ['name' => 'bKash',      'slug' => 'bkash',      'type' => 'local',         'description' => 'বাংলাদেশের সবচেয়ে জনপ্রিয় MFS — bKash Checkout URL API',              'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nagad',      'slug' => 'nagad',      'type' => 'local',         'description' => 'ডাক বিভাগের MFS — Nagad Merchant API',                                  'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SSLCommerz', 'slug' => 'sslcommerz', 'type' => 'local',         'description' => 'BD payment aggregator — MFS + Card + বিকাশ + নগদ সব এক জায়গায়',        'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'AmarPay',    'slug' => 'amarpayz',   'type' => 'local',         'description' => 'AmarPay (aamarpay) — MFS ও card payment gateway',                        'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            // INTERNATIONAL
            ['name' => 'Stripe',     'slug' => 'stripe',     'type' => 'international', 'description' => 'Global card payment — Visa, Mastercard, Apple Pay, Google Pay',          'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PayPal',     'slug' => 'paypal',     'type' => 'international', 'description' => 'PayPal Orders API v2 — worldwide acceptance',                            'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Razorpay',   'slug' => 'razorpay',   'type' => 'international', 'description' => 'Razorpay Payment Links — card, UPI, net banking',                        'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── 2. ISP-level credentials ──────────────────────────────
        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50);
            $table->string('gateway_slug', 30);
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('sandbox')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'gateway_slug']);
            $table->index('tenant_id');
        });

        // ── 3. Transaction log ────────────────────────────────────
        Schema::create('payment_gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('txn_ref', 60)->unique();
            $table->string('tenant_id', 50);
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('gateway', 30);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('BDT');
            $table->string('gateway_txn_id', 200)->nullable();
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');
            $table->json('gateway_response')->nullable();
            $table->string('payer_ip', 45)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
            $table->index(['invoice_id',  'status']);
            $table->index(['tenant_id',   'gateway']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_transactions');
        Schema::dropIfExists('payment_gateway_settings');
        Schema::dropIfExists('payment_gateways');
    }
};
