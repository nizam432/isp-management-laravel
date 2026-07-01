<?php

namespace App\Http\Controllers;

use App\Models\SmsGateway;
use App\Models\TenantSmsSetting;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantSmsController extends Controller
{
    private function getTenantId(): string
    {
        $tenant = Tenant::where('email', auth()->user()->email)->first();
        return $tenant?->id ?? 'default';
    }

    /** GET /sms/settings — show SMS gateway settings for this ISP tenant. */
    public function index()
    {
        $tenantId = $this->getTenantId();
        $gateways = SmsGateway::where('is_enabled', true)->get();
        $settings = TenantSmsSetting::where('tenant_id', $tenantId)->get();

        return view('sms.tenant-settings', compact('gateways', 'settings'));
    }

    /** POST /sms/settings/{slug}/save — persist gateway credentials and activate it. */
    public function save(Request $request, string $slug)
    {
        $tenantId = $this->getTenantId();

        $setting = TenantSmsSetting::updateOrCreate(
            ['tenant_id' => $tenantId, 'gateway_slug' => $slug],
            ['config' => $request->input('config', []), 'is_active' => true]
        );

        // Only one gateway may be active per tenant.
        TenantSmsSetting::where('tenant_id', $tenantId)
            ->where('gateway_slug', '!=', $slug)
            ->update(['is_active' => false]);

        return back()->with('success', 'SMS Gateway সংরক্ষণ ও activate হয়েছে।');
    }

    /** POST /sms/settings/{slug}/toggle — toggle a gateway active/inactive for this tenant. */
    public function toggle(string $slug)
    {
        $tenantId = $this->getTenantId();

        $setting = TenantSmsSetting::where('tenant_id', $tenantId)
            ->where('gateway_slug', $slug)
            ->first();

        if (!$setting) {
            return back()->with('error', 'আগে credentials save করুন।');
        }

        $setting->update(['is_active' => !$setting->is_active]);

        // Deactivate other gateways when this one is turned on.
        if ($setting->is_active) {
            TenantSmsSetting::where('tenant_id', $tenantId)
                ->where('gateway_slug', '!=', $slug)
                ->update(['is_active' => false]);
        }

        return back()->with('success', 'Gateway status পরিবর্তন হয়েছে।');
    }
}
