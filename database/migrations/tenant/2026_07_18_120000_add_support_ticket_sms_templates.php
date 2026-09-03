<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds SMS template mappings + default templates for the 3 Client Support
 * Ticket notification events (created, solved, assigned), following the
 * same pattern used for the invoice_generated fix — so these become
 * editable via the "Fixed Notification Messages" admin UI instead of
 * always falling back to the hardcoded message in SmsService.
 *
 * Guarded with existence checks so it's safe to run on any tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $entries = [
            [
                'type'  => 'support_ticket_created',
                'title' => 'Support Ticket Created',
                'label' => 'Support Ticket Created',
                'body'  => 'প্রিয় {name}, আপনার সাপোর্ট টিকিট #{ticket_no} সফলভাবে গ্রহণ করা হয়েছে। বিষয়: {category}। আমাদের টিম শীঘ্রই যোগাযোগ করবে।',
            ],
            [
                'type'  => 'support_ticket_solved',
                'title' => 'Support Ticket Solved',
                'label' => 'Support Ticket Solved',
                'body'  => 'প্রিয় {name}, আপনার সাপোর্ট টিকিট #{ticket_no} সমাধান করা হয়েছে। ধন্যবাদ।',
            ],
            [
                'type'  => 'support_ticket_assigned',
                'title' => 'Support Ticket Assigned',
                'label' => 'Support Ticket Assigned',
                'body'  => 'আপনাকে সাপোর্ট টিকিট #{ticket_no} ({complained_no}) এসাইন করা হয়েছে। দ্রুত সমাধান করুন।',
            ],
        ];

        foreach ($entries as $entry) {
            if (!DB::table('sms_template_mappings')->where('type', $entry['type'])->exists()) {
                DB::table('sms_template_mappings')->insert([
                    'type'       => $entry['type'],
                    'title'      => $entry['title'],
                    'label'      => $entry['label'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (!DB::table('sms_templates')->where('title', $entry['title'])->exists()) {
                DB::table('sms_templates')->insert([
                    'title'      => $entry['title'],
                    'body'       => $entry['body'],
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $types  = ['support_ticket_created', 'support_ticket_solved', 'support_ticket_assigned'];
        $titles = ['Support Ticket Created', 'Support Ticket Solved', 'Support Ticket Assigned'];

        DB::table('sms_template_mappings')->whereIn('type', $types)->delete();
        DB::table('sms_templates')->whereIn('title', $titles)->delete();
    }
};
