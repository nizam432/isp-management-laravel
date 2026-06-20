<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SmsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerSmsController extends Controller
{
    public function index(Request $request)
    {
        $resellerId  = Auth::guard('mac_reseller')->id();
        $customerIds = Customer::forReseller($resellerId)->pluck('id');

        $clients = Customer::forReseller($resellerId)->orderBy('name')->get(['id', 'name', 'phone']);

        $logs = SmsLog::whereIn('customer_id', $customerIds)
            ->latest()
            ->paginate(25);

        $stats = [
            'total_sent' => SmsLog::whereIn('customer_id', $customerIds)->where('status', 'sent')->count(),
            'failed'     => SmsLog::whereIn('customer_id', $customerIds)->where('status', 'failed')->count(),
        ];

        return view('reseller.sms.index', compact('clients', 'logs', 'stats'));
    }

    /**
     * নোট: এই মেথড একটা placeholder যেটা existing SMS gateway service
     * ব্যবহার করবে (যদি আপনার project এ SmsService/SmsGateway class থাকে,
     * সেটার মাধ্যমে actual SMS পাঠানো হবে)। আপাতত শুধু SmsLog এ entry তৈরি করছে।
     */
    public function send(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'message'     => 'required|string|max:500',
        ]);

        $customer = Customer::forReseller($resellerId)->findOrFail($request->customer_id);

        // TODO: এখানে actual SMS Gateway call বসবে (App\Services\SmsService বা similar)
        SmsLog::create([
            'customer_id' => $customer->id,
            'phone'       => $customer->phone,
            'message'     => $request->message,
            'status'      => 'sent', // বাস্তবে gateway response অনুযায়ী sent/failed হবে
            'sent_by'     => 'reseller',
        ]);

        return response()->json(['success' => true, 'message' => 'SMS sent successfully.']);
    }
}
