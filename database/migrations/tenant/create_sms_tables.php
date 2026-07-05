<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── SMS Gateways ──────────────────────────────
        if (!Schema::hasTable('sms_gateways')) {
            Schema::create('sms_gateways', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 50)->unique();
                $table->boolean('is_active')->default(false);
                $table->json('config')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // ── SMS Logs ──────────────────────────────────
        if (!Schema::hasTable('sms_logs')) {
            Schema::create('sms_logs', function (Blueprint $table) {
                $table->id();
                $table->string('gateway', 50);
                $table->string('mobile', 20);
                $table->text('message');
                $table->string('type', 50)->default('general');
                $table->enum('status', ['success', 'failed'])->default('success');
                $table->text('response')->nullable();
                $table->timestamps();
            });
        }

        // ── Default Gateways Seed ─────────────────────
        if (DB::table('sms_gateways')->count() === 0) {
            DB::table('sms_gateways')->insert([
                [
                    'name'        => '24BulkSMSBD',
                    'slug'        => '24bulksmsbd',
                    'is_active'   => true,
                    'config'      => json_encode([
                        'customer_id' => '',
                        'api_key'     => '',
                    ]),
                    'description' => 'বাংলাদেশি SMS gateway — সস্তা ও reliable',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'name'        => 'SSL Wireless',
                    'slug'        => 'ssl_wireless',
                    'is_active'   => false,
                    'config'      => json_encode([
                        'username' => '',
                        'password' => '',
                        'sid'      => '',
                    ]),
                    'description' => 'SSL Wireless Bangladesh',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'name'        => 'Muthofun',
                    'slug'        => 'muthofun',
                    'is_active'   => false,
                    'config'      => json_encode([
                        'api_key'   => '',
                        'sender_id' => '',
                    ]),
                    'description' => 'Muthofun SMS Gateway',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'name'        => 'Alpha Net',
                    'slug'        => 'alpha_net',
                    'is_active'   => false,
                    'config'      => json_encode([
                        'username'  => '',
                        'password'  => '',
                        'sender_id' => '',
                    ]),
                    'description' => 'Alpha Net — ISP popular gateway',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'name'        => 'Twilio',
                    'slug'        => 'twilio',
                    'is_active'   => false,
                    'config'      => json_encode([
                        'account_sid' => '',
                        'auth_token'  => '',
                        'from_number' => '',
                    ]),
                    'description' => 'Twilio — International SMS',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('sms_gateways');
    }
};