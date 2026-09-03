<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ResellerSmsTemplate;
use App\Models\ResellerSmsTemplateMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerSmsTemplateController extends Controller
{
    public function index()
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        // make sure the 5 standard notification types have a mapping row for this reseller
        ResellerSmsTemplateMapping::ensureDefaultsFor($resellerId);

        $templates = ResellerSmsTemplate::forReseller($resellerId)->latest()->get();
        $mappings  = ResellerSmsTemplateMapping::forReseller($resellerId)->get();

        // same fallback text as Admin's version — only used to pre-fill a mapping's
        // textarea when the reseller hasn't saved a template for it yet
        $fixedDefaults = [
            'bill_due'        => 'প্রিয় {name}, আপনার {month} মাসের ইন্টারনেট বিল {amount} টাকা বাকি আছে। দ্রুত পরিশোধ করুন।',
            'payment_confirm' => 'প্রিয় {name}, আপনার {amount} টাকা পেমেন্ট ({method}) সফলভাবে গ্রহণ করা হয়েছে। ধন্যবাদ।',
            'suspend'         => 'প্রিয় {name}, বিল বাকি থাকায় আপনার ইন্টারনেট সংযোগ সাময়িকভাবে বন্ধ করা হয়েছে।',
            'restore'         => 'প্রিয় {name}, আপনার ইন্টারনেট সংযোগ পুনরায় চালু করা হয়েছে। ধন্যবাদ।',
            'welcome'         => 'প্রিয় {name}, আপনার ইন্টারনেট সংযোগ চালু হয়েছে। User: {pppoe_username}, Pass: {pppoe_password}।',
        ];

        return view('reseller.sms.templates', compact('templates', 'mappings', 'fixedDefaults'));
    }

    public function store(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'title' => 'required|string|max:100|unique:reseller_sms_templates,title,NULL,id,mac_reseller_id,' . $resellerId,
            'body'  => 'required|string|max:500',
        ]);

        ResellerSmsTemplate::create([
            'mac_reseller_id' => $resellerId,
            'title'           => $request->title,
            'body'            => $request->body,
            'is_active'       => true,
        ]);

        return back()->with('success', "'{$request->title}' template তৈরি হয়েছে।");
    }

    public function update(Request $request, ResellerSmsTemplate $template)
    {
        $this->authorizeTemplate($template);

        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'title' => 'required|string|max:100|unique:reseller_sms_templates,title,' . $template->id . ',id,mac_reseller_id,' . $resellerId,
            'body'  => 'required|string|max:500',
        ]);

        $template->update($request->only('title', 'body'));

        return back()->with('success', "'{$request->title}' template আপডেট হয়েছে।");
    }

    public function destroy(ResellerSmsTemplate $template)
    {
        $this->authorizeTemplate($template);

        $title = $template->title;
        $template->delete();

        return back()->with('success', "'{$title}' template মুছে ফেলা হয়েছে।");
    }

    public function toggle(ResellerSmsTemplate $template)
    {
        $this->authorizeTemplate($template);

        $template->update(['is_active' => !$template->is_active]);

        return back()->with('success', 'Template status পরিবর্তন হয়েছে।');
    }

    private function authorizeTemplate(ResellerSmsTemplate $template): void
    {
        abort_unless($template->mac_reseller_id === Auth::guard('mac_reseller')->id(), 403);
    }
}
