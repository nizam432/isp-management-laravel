<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ResellerSmsSetting;
use App\Models\SmsGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerSmsSettingController extends Controller
{
    /** GET /reseller/sms/settings — gateways Super Admin has enabled, plus this reseller's own credentials. */
    public function index()
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $gateways = SmsGateway::where('is_enabled', true)->get();
        $settings = ResellerSmsSetting::where('mac_reseller_id', $resellerId)->get();

        return view('reseller.sms.settings', compact('gateways', 'settings'));
    }

    /** POST /reseller/sms/settings/{slug}/save — persist this reseller's gateway credentials and activate it. */
    public function save(Request $request, string $slug)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        // gateway must actually be enabled by Super Admin
        abort_unless(SmsGateway::where('is_enabled', true)->where('slug', $slug)->exists(), 404);

        ResellerSmsSetting::updateOrCreate(
            ['mac_reseller_id' => $resellerId, 'gateway_slug' => $slug],
            ['config' => $request->input('config', []), 'is_active' => true]
        );

        // only one gateway may be active per reseller
        ResellerSmsSetting::where('mac_reseller_id', $resellerId)
            ->where('gateway_slug', '!=', $slug)
            ->update(['is_active' => false]);

        return back()->with('success', 'SMS Gateway সংরক্ষণ ও activate হয়েছে।');
    }

    /** POST /reseller/sms/settings/{slug}/toggle — toggle a gateway active/inactive for this reseller. */
    public function toggle(string $slug)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $setting = ResellerSmsSetting::where('mac_reseller_id', $resellerId)
            ->where('gateway_slug', $slug)
            ->first();

        if (!$setting) {
            return back()->with('error', 'আগে credentials save করুন।');
        }

        $setting->update(['is_active' => !$setting->is_active]);

        if ($setting->is_active) {
            ResellerSmsSetting::where('mac_reseller_id', $resellerId)
                ->where('gateway_slug', '!=', $slug)
                ->update(['is_active' => false]);
        }

        return back()->with('success', 'Gateway status পরিবর্তন হয়েছে।');
    }
}
