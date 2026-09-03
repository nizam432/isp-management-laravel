<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Central table — Super Admin enables/disables which payment gateways
     * tenants are allowed to configure/use.
     *
     * NOTE: this exact table was originally created inside
     * database/migrations/tenant/2026_06_16_000001_create_payment_gateways_table.php
     * — which meant every tenant got its OWN separate copy of this table,
     * defeating the whole point of Super Admin managing it centrally for
     * everyone. This migration recreates just the `payment_gateways` part
     * in the CENTRAL migrations folder. The tenant migration file needs its
     * `Schema::create('payment_gateways', ...)` block (and matching insert)
     * removed, keeping only `payment_gateway_settings` and
     * `payment_gateway_transactions` (which correctly stay tenant-scoped,
     * since they have foreign keys to tenant-local `customers`/`invoices`).
     */
    public function up(): void
    {
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
            ['name' => 'bKash',      'slug' => 'bkash',      'type' => 'local',         'description' => 'বাংলাদেশের সবচেয়ে জনপ্রিয় MFS — bKash Checkout URL API', 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nagad',      'slug' => 'nagad',      'type' => 'local',         'description' => 'ডাক বিভাগের MFS — Nagad Merchant API',                                  'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SSLCommerz', 'slug' => 'sslcommerz', 'type' => 'local',         'description' => 'BD payment aggregator — MFS + Card + বিকাশ + নগদ সব এক জায়গায়', 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'AmarPay',    'slug' => 'amarpayz',   'type' => 'local',         'description' => 'AmarPay (aamarpay) — MFS ও card payment gateway', 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ShurjoPay',  'slug' => 'shurjopay',  'type' => 'local',         'description' => 'ShurjoPay — Bangladeshi payment gateway','is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stripe',     'slug' => 'stripe',     'type' => 'international', 'description' => 'Global card payment — Visa, Mastercard, Apple Pay, Google Pay', 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PayPal',     'slug' => 'paypal',     'type' => 'international', 'description' => 'PayPal Orders API v2 — worldwide acceptance', 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Razorpay',   'slug' => 'razorpay',   'type' => 'international', 'description' => 'Razorpay Payment Links — card, UPI, net banking', 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
