<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use App\Models\SmsTemplateMapping;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function index()
    {
        $templates = SmsTemplate::latest()->get();
        $mappings  = SmsTemplateMapping::with('template')->get();

        // Same hardcoded fallback text as SmsService — used only to pre-fill the
        // "Fixed Notification Messages" box when no SmsTemplate has been saved yet.
        $fixedDefaults = [
            'bill_due'        => 'প্রিয় {name}, আপনার {month} মাসের ইন্টারনেট বিল {amount} টাকা বাকি আছে। দ্রুত পরিশোধ করুন।',
            'payment_confirm' => 'প্রিয় {name}, আপনার {amount} টাকা পেমেন্ট ({method}) সফলভাবে গ্রহণ করা হয়েছে। ধন্যবাদ।',
            'suspend'         => 'প্রিয় {name}, বিল বাকি থাকায় আপনার ইন্টারনেট সংযোগ সাময়িকভাবে বন্ধ করা হয়েছে।',
            'restore'         => 'প্রিয় {name}, আপনার ইন্টারনেট সংযোগ পুনরায় চালু করা হয়েছে। ধন্যবাদ।',
            'welcome'         => 'প্রিয় {name}, আপনার ইন্টারনেট সংযোগ চালু হয়েছে। User: {pppoe_username}, Pass: {pppoe_password}।',
        ];

        return view('sms.templates', compact('templates', 'mappings', 'fixedDefaults'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'body'  => 'required|string|max:500',
        ]);

        SmsTemplate::create([
            'title'     => $request->title,
            'body'      => $request->body,
            'is_active' => true,
        ]);

        return back()->with('success', "'{$request->title}' template তৈরি হয়েছে।");
    }

    public function update(Request $request, SmsTemplate $smsTemplate)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'body'  => 'required|string|max:500',
        ]);

        $smsTemplate->update($request->only('title', 'body'));

        return back()->with('success', "'{$request->title}' template আপডেট হয়েছে।");
    }

    public function destroy(SmsTemplate $smsTemplate)
    {
        $title = $smsTemplate->title;
        $smsTemplate->delete();
        return back()->with('success', "'{$title}' template মুছে ফেলা হয়েছে।");
    }

    public function toggle(SmsTemplate $smsTemplate)
    {
        $smsTemplate->update(['is_active' => !$smsTemplate->is_active]);
        return back()->with('success', 'Template status পরিবর্তন হয়েছে।');
    }
}
