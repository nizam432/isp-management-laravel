<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\ResellerPaymentGatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerPaymentGatewayController extends Controller
{
    /** Save (create or update) this reseller's credentials for one gateway. */
    public function save(Request $request, string $slug)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        // the gateway must actually be enabled by Super Admin
        $gateway = PaymentGateway::enabled()->where('slug', $slug)->first();
        abort_unless($gateway, 404, 'This payment gateway is not available.');

        $setting = ResellerPaymentGatewaySetting::firstOrNew([
            'mac_reseller_id' => $resellerId,
            'gateway_slug'    => $slug,
        ]);

        // merge new config values over the existing ones — this lets password-type
        // fields stay untouched when the form leaves them blank (the JS only sends
        // a value for fields the person actually typed something into).
        $existingConfig = $setting->config ?? [];
        $incomingConfig = $request->input('config', []);
        $setting->config = array_merge($existingConfig, $incomingConfig);

        $setting->sandbox = $request->boolean('sandbox', true);
        $setting->mac_reseller_id = $resellerId;
        $setting->gateway_slug = $slug;

        // is_active is only changed via the dedicated toggle() endpoint, but the
        // JS resends the current value on every save — keep it as-is if this row
        // already exists, otherwise default to inactive until toggled on.
        if (!$setting->exists) {
            $setting->is_active = false;
        }

        $setting->save();

        return response()->json(['success' => true]);
    }

    /** Flip is_active for this reseller's gateway setting. */
    public function toggle(Request $request, string $slug)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $gateway = PaymentGateway::enabled()->where('slug', $slug)->first();
        abort_unless($gateway, 404, 'This payment gateway is not available.');

        $setting = ResellerPaymentGatewaySetting::firstOrCreate(
            ['mac_reseller_id' => $resellerId, 'gateway_slug' => $slug],
            ['config' => [], 'sandbox' => true, 'is_active' => false]
        );

        $setting->update(['is_active' => !$setting->is_active]);

        return response()->json(['success' => true, 'is_active' => $setting->is_active]);
    }
}
