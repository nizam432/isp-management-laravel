<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Central table — Super Admin manages which SMS gateways are available at
 * all (is_enabled), and which one is the global fallback (is_active, used
 * by SmsService::getActiveSetting() when no tenant_sms_settings row exists).
 *
 * NOTE: this table (along with the is_enabled column) was originally created
 * in database/migrations/tenant/create_sms_tables.php + tenant/2026_05_23_043912
 * _add_is_enabled_to_sms_gateways_table.php — meaning every tenant got its own
 * separate copy, and Super Admin's central panel had no table to manage at
 * all. This central migration replaces that part. The tenant migration file
 * needs its `sms_gateways` table creation removed, keeping only `sms_logs`
 * (which correctly stays tenant-scoped — each tenant's own SMS history).
 * The old is_enabled-adding tenant migration is no longer needed at all,
 * since is_enabled is included here from the start.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 50)->unique();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->json('config')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('sms_gateways')->insert([
            [
                'name'        => '24BulkSMSBD',
                'slug'        => '24bulksmsbd',
                'is_active'   => true,
                'is_enabled'  => true,
                'config'      => json_encode(['customer_id' => '', 'api_key' => '']),
                'description' => 'বাংলাদেশি SMS gateway — সস্তা ও reliable',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'SSL Wireless',
                'slug'        => 'ssl_wireless',
                'is_active'   => false,
                'is_enabled'  => true,
                'config'      => json_encode(['username' => '', 'password' => '', 'sid' => '']),
                'description' => 'SSL Wireless Bangladesh',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Muthofun',
                'slug'        => 'muthofun',
                'is_active'   => false,
                'is_enabled'  => true,
                'config'      => json_encode(['api_key' => '', 'sender_id' => '']),
                'description' => 'Muthofun SMS Gateway',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Alpha Net',
                'slug'        => 'alpha_net',
                'is_active'   => false,
                'is_enabled'  => true,
                'config'      => json_encode(['username' => '', 'password' => '', 'sender_id' => '']),
                'description' => 'Alpha Net — ISP popular gateway',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Twilio',
                'slug'        => 'twilio',
                'is_active'   => false,
                'is_enabled'  => true,
                'config'      => json_encode(['account_sid' => '', 'auth_token' => '', 'from_number' => '']),
                'description' => 'Twilio — International SMS',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_gateways');
    }
};
