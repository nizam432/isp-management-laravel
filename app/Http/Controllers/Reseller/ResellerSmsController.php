<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ResellerSmsSetting;
use App\Models\ResellerSmsTemplate;
use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerSmsController extends Controller
{
    /** GET /reseller/sms-service — lands on the Send SMS page. */
    public function index()
    {
        return redirect()->route('reseller.sms-service.send');
    }

    /** GET /reseller/sms-service/send */
    public function sendPage()
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $activeGateway = ResellerSmsSetting::where('mac_reseller_id', $resellerId)->where('is_active', true)->first();
        $templates     = ResellerSmsTemplate::forReseller($resellerId)->active()->get();
        $todaySent     = SmsLog::forReseller($resellerId)->today()->success()->count();
        $todayFailed   = SmsLog::forReseller($resellerId)->today()->failed()->count();

        return view('reseller.sms.send', compact('activeGateway', 'templates', 'todaySent', 'todayFailed'));
    }

    /** POST /reseller/sms-service/send/test */
    public function sendTest(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'mobile'  => 'required|string',
            'message' => 'required|string|max:500',
        ]);

        $gateway = ResellerSmsSetting::where('mac_reseller_id', $resellerId)->where('is_active', true)->first();

        if (!$gateway) {
            return back()->with('error', 'কোনো SMS Gateway সক্রিয় নেই। আগে Gateway Settings থেকে একটি gateway সেভ ও activate করুন।');
        }

        $sms    = new SmsService(macResellerId: $resellerId);
        $result = $sms->send($request->mobile, $request->message, 'general');

        return back()->with(
            $result ? 'success' : 'error',
            $result ? 'Test SMS পাঠানো হয়েছে!' : 'SMS পাঠাতে ব্যর্থ হয়েছে। SMS Reports দেখুন।'
        );
    }

    /** POST /reseller/sms-service/send/bulk — only to this reseller's own clients. */
    public function sendBulk(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'message' => 'required|string|max:500',
            'status'  => 'nullable|in:active,suspended,all',
        ]);

        $gateway = ResellerSmsSetting::where('mac_reseller_id', $resellerId)->where('is_active', true)->first();

        if (!$gateway) {
            return back()->with('error', 'কোনো SMS Gateway সক্রিয় নেই। আগে Gateway Settings থেকে একটি gateway সেভ ও activate করুন।');
        }

        $query = Customer::forReseller($resellerId);
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        $mobiles = $query->pluck('phone')->filter()->toArray();

        $sms  = new SmsService(macResellerId: $resellerId);
        $sent = $sms->sendMany($mobiles, $request->message, 'general');

        return back()->with('success', "{$sent} জন ক্লায়েন্টের কাছে SMS পাঠানো হয়েছে (মোট {" . count($mobiles) . "} জন matched)।");
    }
}

