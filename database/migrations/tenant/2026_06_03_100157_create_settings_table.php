<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->timestamps();
        });

        // Default settings
        $settings = [
            // Company
            ['key' => 'company_name',         'value' => 'My ISP',          'group' => 'company'],
            ['key' => 'company_phone',         'value' => '',                'group' => 'company'],
            ['key' => 'company_email',         'value' => '',                'group' => 'company'],
            ['key' => 'company_address',       'value' => '',                'group' => 'company'],
            ['key' => 'company_logo',          'value' => '',                'group' => 'company'],

            // Billing
            ['key' => 'invoice_prefix',        'value' => 'INV',             'group' => 'billing'],
            ['key' => 'currency',              'value' => 'BDT',             'group' => 'billing'],
            ['key' => 'billing_type',          'value' => 'date_to_date',    'group' => 'billing'],
            ['key' => 'grace_period_days',     'value' => '3',               'group' => 'billing'],
            ['key' => 'default_billing_date',  'value' => '1',               'group' => 'billing'],
            ['key' => 'late_fee_amount',       'value' => '0',               'group' => 'billing'],
            ['key' => 'late_fee_after_days',   'value' => '7',               'group' => 'billing'],
            ['key' => 'vat_percentage',        'value' => '0',               'group' => 'billing'],
            ['key' => 'invoice_due_days',      'value' => '7',               'group' => 'billing'],
            ['key' => 'invoice_footer_text',   'value' => 'Thank you for your payment.', 'group' => 'billing'],

            // Notification
            ['key' => 'sms_sender_name',           'value' => '',    'group' => 'notification'],
            ['key' => 'bill_due_sms_days_before',   'value' => '3',  'group' => 'notification'],
            ['key' => 'expiry_sms_days_before',     'value' => '3',  'group' => 'notification'],
            ['key' => 'payment_confirm_sms',        'value' => '1',  'group' => 'notification'],
            ['key' => 'suspension_sms',             'value' => '1',  'group' => 'notification'],

            // MikroTik
            ['key' => 'auto_suspend_on_expire',    'value' => '1',   'group' => 'mikrotik'],
            ['key' => 'auto_restore_on_payment',   'value' => '1',   'group' => 'mikrotik'],

            // Customer
            ['key' => 'customer_code_prefix',      'value' => 'ISP', 'group' => 'customer'],
            ['key' => 'default_package_id',        'value' => '',    'group' => 'customer'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};