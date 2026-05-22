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
        // Current logged in user এর tenant id বের করো
        $tenant = Tenant::where('email', auth()->user()->email)->first();
        return $tenant?->id ?? 'default';
    }

    /**
     * GET /sms/settings
     * ISP Company এর SMS settings page
     */
    public function index()
    {
        $tenantId = $this->getTenantId();

        // Super Admin এ enabled গুলো দেখাও
        $gateways = SmsGateway::where('is_enabled', true)->get();

        // এই ISP এর existing settings
        $settings = TenantSmsSetting::where('tenant_id', $tenantId)->get();

        return view('sms.tenant-settings', compact('gateways', 'settings'));
    }

    /**
     * POST /sms/settings/{slug}/save
     * Gateway credentials save করো
     */
    public function save(Request $request, string $slug)
    {
        $tenantId = $this->getTenantId();

        $setting = TenantSmsSetting::updateOrCreate(
            ['tenant_id' => $tenantId, 'gateway_slug' => $slug],
            ['config' => $request->input('config', []), 'is_active' => true]
        );

        // অন্য সব gateway off করো (একটাই active থাকবে)
        TenantSmsSetting::where('tenant_id', $tenantId)
            ->where('gateway_slug', '!=', $slug)
            ->update(['is_active' => false]);

        return back()->with('success', 'SMS Gateway সংরক্ষণ ও activate হয়েছে।');
    }

    /**
     * POST /sms/settings/{slug}/toggle
     * Gateway on/off করো
     */
    public function toggle(string $slug)
    {
        $tenantId = $this->getTenantId();

        $setting = TenantSmsSetting::where('tenant_id', $tenantId)
            ->where('gateway_slug', $slug)
            ->first();

        if (!$setting) {
            return back()->with('error', 'আগে credentials save করুন।');
        }

        // Toggle
        $setting->update(['is_active' => !$setting->is_active]);

        // Active করলে অন্য গুলো off করো
        if ($setting->is_active) {
            TenantSmsSetting::where('tenant_id', $tenantId)
                ->where('gateway_slug', '!=', $slug)
                ->update(['is_active' => false]);
        }

        return back()->with('success', 'Gateway status পরিবর্তন হয়েছে।');
    }
}
