<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Stores the mapping between an internal notification `type` (used in code,
     * e.g. SmsService::sendBillDue) and the SmsTemplate `title` to look up in the
     * sms_templates table. Kept in DB (instead of hardcoded in SmsService) so it
     * can be edited from an admin screen without a code deploy.
     */
    public function up(): void
    {
        Schema::create('sms_template_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();   // e.g. 'bill_due', 'payment_confirm'
            $table->string('title');             // matches SmsTemplate.title
            $table->string('label')->nullable(); // human-readable label for admin UI, e.g. "Bill Due Reminder"
            $table->timestamps();
        });

        // Seed the 5 known notification types with their current default titles,
        // so nothing breaks on first deploy — admin can rename titles afterward.
        $now = now();
        DB::table('sms_template_mappings')->insert([
            ['type' => 'bill_due',        'title' => 'Bill Due Reminder',    'label' => 'Bill Due Reminder',    'created_at' => $now, 'updated_at' => $now],
            ['type' => 'payment_confirm', 'title' => 'Payment Confirmation', 'label' => 'Payment Confirmation', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'suspend',         'title' => 'Suspension Notice',    'label' => 'Suspension Notice',    'created_at' => $now, 'updated_at' => $now],
            ['type' => 'restore',         'title' => 'Restore Notice',       'label' => 'Restore Notice',       'created_at' => $now, 'updated_at' => $now],
            ['type' => 'welcome',         'title' => 'Welcome Message',      'label' => 'Welcome Message',      'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_template_mappings');
    }
};
