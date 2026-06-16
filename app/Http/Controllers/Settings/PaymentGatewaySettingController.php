<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\Request;

class PaymentGatewaySettingController extends Controller
{
    // ── Single-tenant system — always 'default' ───────────────────
    private function tenantId(): string
    {
        return 'default';
    }

    /**
     * AJAX: GET /settings/payment-gateways/{slug}/config
     * Return current saved config for a gateway (called when ISP clicks a gateway in the list)
     */
    public function config(string $slug)
    {
        // Only show if Super Admin has enabled it
        $gateway = PaymentGateway::where('slug', $slug)->where('is_enabled', true)->first();
        if (!$gateway) {
            return response()->json(['error' => 'Gateway not enabled by admin.'], 403);
        }

        $setting = PaymentGatewaySetting::where('tenant_id', $this->tenantId())
            ->where('gateway_slug', $slug)
            ->first();

        return response()->json([
            'gateway'   => $gateway,
            'is_active' => $setting?->is_active ?? false,
            'sandbox'   => $setting?->sandbox   ?? true,
            'config'    => $setting?->config    ?? [],
        ]);
    }

    /**
     * POST /settings/payment-gateways/{slug}/save
     * Save credentials for a gateway
     */
    public function save(Request $request, string $slug)
    {
        $gateway = PaymentGateway::where('slug', $slug)->where('is_enabled', true)->first();
        if (!$gateway) {
            return back()->with('error', 'This gateway is not enabled by admin.');
        }

        $tenantId = $this->tenantId();

        PaymentGatewaySetting::updateOrCreate(
            ['tenant_id' => $tenantId, 'gateway_slug' => $slug],
            [
                'config'    => $request->input('config', []),
                'sandbox'   => $request->boolean('sandbox', true),
                'is_active' => $request->boolean('is_active', false),
            ]
        );

        return back()->with('success', "{$gateway->name} credentials saved successfully.");
    }

    /**
     * POST /settings/payment-gateways/{slug}/toggle
     * Enable/disable a gateway for this ISP
     */
    public function toggle(string $slug)
    {
        $gateway = PaymentGateway::where('slug', $slug)->where('is_enabled', true)->first();
        if (!$gateway) {
            return back()->with('error', 'This gateway is not enabled by admin.');
        }

        $tenantId = $this->tenantId();

        $setting = PaymentGatewaySetting::where('tenant_id', $tenantId)
            ->where('gateway_slug', $slug)
            ->first();

        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Please save credentials first.'], 422);
        }

        $setting->update(['is_active' => !$setting->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $setting->is_active,
            'message'   => $gateway->name . ' has been ' . ($setting->is_active ? 'enabled' : 'disabled') . '.',
        ]);
    }
}
