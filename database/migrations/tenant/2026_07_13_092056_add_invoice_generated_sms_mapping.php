<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The original sms_template_mappings seed migration only included 5 types
 * (bill_due, payment_confirm, suspend, restore, welcome) — invoice_generated
 * was missing, so that SMS always fell back to the hardcoded message and
 * was never editable via the SMS Templates admin UI. This adds it.
 *
 * Guarded with updateOrCreate-style checks so it's safe to run on any
 * tenant regardless of whether they already have these rows somehow.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        if (!DB::table('sms_template_mappings')->where('type', 'invoice_generated')->exists()) {
            DB::table('sms_template_mappings')->insert([
                'type'       => 'invoice_generated',
                'title'      => 'Invoice Generated',
                'label'      => 'Invoice Generated',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (!DB::table('sms_templates')->where('title', 'Invoice Generated')->exists()) {
            DB::table('sms_templates')->insert([
                'title'      => 'Invoice Generated',
                'body'       => 'প্রিয় {name}, আপনার {month} মাসের ইনভয়েস তৈরি হয়েছে। বিল পরিমাণ {amount} টাকা। দ্রুত পরিশোধ করুন।',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('sms_template_mappings')->where('type', 'invoice_generated')->delete();
        DB::table('sms_templates')->where('title', 'Invoice Generated')->delete();
    }
};
