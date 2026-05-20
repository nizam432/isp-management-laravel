<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);          // Free, Basic, Standard, Premium
            $table->string('slug', 50)->unique();  // free, basic, standard, premium
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('max_customers')->default(25);  // -1 = unlimited
            $table->integer('max_routers')->default(1);     // -1 = unlimited
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('reseller_enabled')->default(false);
            $table->integer('trial_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Default plans seed
        DB::table('plans')->insert([
            [
                'name'             => 'Free',
                'slug'             => 'free',
                'price'            => 0,
                'max_customers'    => 25,
                'max_routers'      => 1,
                'sms_enabled'      => false,
                'reseller_enabled' => false,
                'trial_days'       => 30,
                'is_active'        => true,
                'description'      => 'Free plan — ২৫ জন customer, ১টা MikroTik',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'name'             => 'Basic',
                'slug'             => 'basic',
                'price'            => 500,
                'max_customers'    => 100,
                'max_routers'      => 1,
                'sms_enabled'      => true,
                'reseller_enabled' => false,
                'trial_days'       => 0,
                'is_active'        => true,
                'description'      => 'Basic plan — ১০০ জন customer, SMS সহ',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'name'             => 'Standard',
                'slug'             => 'standard',
                'price'            => 1000,
                'max_customers'    => 500,
                'max_routers'      => 3,
                'sms_enabled'      => true,
                'reseller_enabled' => true,
                'trial_days'       => 0,
                'is_active'        => true,
                'description'      => 'Standard plan — ৫০০ জন customer, Reseller সহ',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'name'             => 'Premium',
                'slug'             => 'premium',
                'price'            => 2000,
                'max_customers'    => -1,
                'max_routers'      => -1,
                'sms_enabled'      => true,
                'reseller_enabled' => true,
                'trial_days'       => 0,
                'is_active'        => true,
                'description'      => 'Premium plan — Unlimited customer & router',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};