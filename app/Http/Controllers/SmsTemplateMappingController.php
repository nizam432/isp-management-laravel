<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use App\Models\SmsTemplateMapping;
use Illuminate\Http\Request;

class SmsTemplateMappingController extends Controller
{
    /**
     * POST /admin/sms/templates/fixed — directly edit the message body for each of the
     * 5 fixed notification types (bill_due, payment_confirm, suspend, restore, welcome).
     *
     * Each type is already linked (via sms_template_mappings.title) to a SmsTemplate.
     * If that SmsTemplate doesn't exist yet, it's created automatically so the admin
     * never has to deal with the mapping/title concept directly — they just see 5
     * editable boxes and Save.
     */
    public function update(Request $request)
    {
        $request->validate([
            'messages'   => 'required|array',
            'messages.*' => 'required|string|max:500',
        ]);

        foreach ($request->input('messages') as $type => $body) {
            $mapping = SmsTemplateMapping::where('type', $type)->first();
            if (!$mapping) continue; // unknown type, ignore silently

            SmsTemplate::updateOrCreate(
                ['title' => $mapping->title],
                ['body' => $body, 'is_active' => true]
            );
        }

        return back()->with('success', 'Notification message গুলো সংরক্ষণ হয়েছে।');
    }
}
