<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SmsGateway;

class SmsGatewayController extends Controller
{
    public function index()
    {
        $gateways = SmsGateway::all();
        return view('super-admin.sms-gateways', compact('gateways'));
    }

    public function toggle(SmsGateway $gateway)
    {
        $gateway->update(['is_enabled' => !$gateway->is_enabled]);
        $status = $gateway->is_enabled ? 'ISP দের জন্য চালু' : 'ISP দের জন্য বন্ধ';
        return back()->with('success', "{$gateway->name} {$status} করা হয়েছে।");
    }
}
