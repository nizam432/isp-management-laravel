<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function index()
    {
        $templates = SmsTemplate::latest()->get();
        return view('sms.templates', compact('templates'));
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
