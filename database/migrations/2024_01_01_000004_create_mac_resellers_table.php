<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mac_resellers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('contact_person');
            $table->string('email')->nullable();
            $table->string('mobile');
            $table->string('phone')->nullable();
            $table->string('national_id')->nullable();
            $table->string('district')->nullable();
            $table->string('upazila')->nullable();
            $table->string('zone')->nullable();
            $table->string('pop_prefix')->nullable();
            $table->boolean('use_prefix_in_mikrotik_username')->default(false);
            $table->enum('pop_type', ['prepaid', 'postpaid'])->default('prepaid');
            $table->decimal('min_rechargeable_amount', 12, 2)->default(5);
            $table->text('address')->nullable();
            $table->string('logo')->nullable();

            // Business & Login
            $table->string('business_name');
            $table->foreignId('tariff_id')->nullable()->constrained('mac_reseller_tariffs')->nullOnDelete();
            $table->boolean('want_to_disable_clients')->default(true);
            $table->decimal('min_balance', 12, 2)->default(0);
            $table->string('username')->unique();
            $table->string('password');
            $table->json('allowed_menus')->nullable();

            // Stats / Status
            $table->enum('level', ['level_1', 'level_2'])->default('level_1');
            $table->decimal('remaining_fund', 14, 2)->default(0);
            $table->boolean('client_enabled')->default(true);
            $table->boolean('fund_start')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('restrict_online_payment')->default(false);
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_resellers');
    }
};
