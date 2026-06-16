<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;

class PaymentGatewayController extends Controller
{
    /**
     * GET /super-admin/payment-gateways
     * List all gateways — Super Admin can enable/disable
     */
    public function index()
    {
        $gateways = PaymentGateway::all();
        return view('super-admin.payment-gateways', compact('gateways'));
    }

    /**
     * POST /super-admin/payment-gateways/{gateway}/toggle
     * Enable or disable a gateway for all ISPs
     */
    public function toggle(PaymentGateway $gateway)
    {
        $gateway->update(['is_enabled' => !$gateway->is_enabled]);

        $status = $gateway->is_enabled ? 'ISP দের জন্য চালু' : 'ISP দের জন্য বন্ধ';
        return back()->with('success', "{$gateway->name} {$status} করা হয়েছে।");
    }
}
