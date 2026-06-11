<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. bandwidth_providers ────────────────────────────────────────────
        // (কোনো FK নেই — আগে তৈরি হবে)
        Schema::create('bandwidth_providers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 150);
            $table->string('contact_person', 100);
            $table->string('email', 150);
            $table->string('phone_no', 11);
            $table->string('document', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->boolean('is_active')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // ── 2. bandwidth_services ─────────────────────────────────────────────
        // (কোনো FK নেই — আগে তৈরি হবে)
        Schema::create('bandwidth_services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        // ── 3. bandwidth_purchases ────────────────────────────────────────────
        // (providers আগে তৈরি হয়ে গেছে — FK এখন কাজ করবে)
        Schema::create('bandwidth_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 100);

            $table->unsignedBigInteger('provider_id');
            $table->foreign('provider_id')
                  ->references('id')
                  ->on('bandwidth_providers')
                  ->cascadeOnDelete();

            $table->date('billing_date');
            $table->string('document', 255)->nullable();
            $table->decimal('sub_total', 14, 2)->default(0);
            $table->decimal('paid',      14, 2)->default(0);
            $table->decimal('due',       14, 2)->default(0);
            $table->string('bank_account', 150)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->timestamps();
        });

        // ── 4. bandwidth_purchase_lines ───────────────────────────────────────
        // (purchases ও services আগে তৈরি হয়ে গেছে)
        Schema::create('bandwidth_purchase_lines', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('purchase_id');
            $table->foreign('purchase_id')
                  ->references('id')
                  ->on('bandwidth_purchases')
                  ->cascadeOnDelete();

            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')
                  ->references('id')
                  ->on('bandwidth_services')
                  ->cascadeOnDelete();

            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('quantity_mb', 10, 2)->default(0);
            $table->decimal('rate',        10, 2)->default(0);
            $table->decimal('vat_percent',  5, 2)->default(0);
            $table->decimal('line_total',  14, 2)->default(0);

            $table->timestamps();
        });

        // ── Default services seed ─────────────────────────────────────────────
        $now = now();
        DB::table('bandwidth_services')->insert([
            ['name' => 'IIG',  'description' => 'International Internet Gateway',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'GGC',  'description' => 'Google Global Cache',              'created_at' => $now, 'updated_at' => $now],
            ['name' => 'FNA',  'description' => 'Facebook Network Accelerator',     'created_at' => $now, 'updated_at' => $now],
            ['name' => 'BDIX', 'description' => 'Bangladesh Internet Exchange',     'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PNI',  'description' => 'Private Network Interconnect',     'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CDN',  'description' => 'Content Delivery Network',         'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        // Reverse order — child tables আগে drop
        Schema::dropIfExists('bandwidth_purchase_lines');
        Schema::dropIfExists('bandwidth_purchases');
        Schema::dropIfExists('bandwidth_services');
        Schema::dropIfExists('bandwidth_providers');
    }
};
