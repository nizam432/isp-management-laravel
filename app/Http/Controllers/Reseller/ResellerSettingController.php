<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\MacResellerTariffPackage;
use App\Models\ResellerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerSettingController extends Controller
{
    public function index()
    {
        $reseller   = Auth::guard('mac_reseller')->user();
        $resellerId = $reseller->id;

        $company  = ResellerSetting::getGroup($resellerId, 'company');
        $billing  = ResellerSetting::getGroup($resellerId, 'billing');
        $mikrotik = ResellerSetting::getGroup($resellerId, 'mikrotik');
        $customer = ResellerSetting::getGroup($resellerId, 'customer');

        // "Default Package" dropdown pulls from THIS reseller's own Tariff packages
        $packages = collect();
        if ($reseller->tariff_id) {
            $packages = MacResellerTariffPackage::where('tariff_id', $reseller->tariff_id)
                ->with('package')
                ->get();
        }

        // ── Payment Gateways tab data ──
        $allGateways = \App\Models\PaymentGateway::enabled()->orderBy('type')->orderBy('id')->get();
        $gwSettings  = \App\Models\ResellerPaymentGatewaySetting::where('mac_reseller_id', $resellerId)
            ->get()->keyBy('gateway_slug');

        return view('reseller.settings.general', compact('company', 'billing', 'mikrotik', 'customer', 'packages', 'allGateways', 'gwSettings'));
    }

    public function update(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            // Company
            'company_name'                => 'nullable|string|max:100',
            'company_phone'               => 'nullable|string|max:20',
            'company_email'               => 'nullable|email|max:100',
            'company_address'             => 'nullable|string',

            // Billing
            'invoice_prefix'              => 'nullable|string|max:10',
            'currency'                    => 'nullable|string|max:10',
            'billing_type'                => 'nullable|in:date_to_date,monthly',
            'grace_period_days'           => 'nullable|integer|min:0|max:30',
            'default_billing_date'        => 'nullable|integer|min:1|max:28',
            'late_fee_amount'             => 'nullable|numeric|min:0',
            'late_fee_after_days'         => 'nullable|integer|min:0',
            'vat_percentage'              => 'nullable|numeric|min:0|max:100',
            'invoice_due_days'            => 'nullable|integer|min:1',
            'invoice_footer_text'         => 'nullable|string',

            // Customer
            'customer_code_prefix'        => 'nullable|string|max:10',
            'default_mac_reseller_tariff_package_id' => 'nullable|exists:mac_reseller_tariff_packages,id',
        ]);

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('reseller-settings/' . $resellerId, 'public');
            ResellerSetting::set($resellerId, 'company_logo', $path, 'company');
        }

        $fields = [
            'company_name'    => 'company',
            'company_phone'   => 'company',
            'company_email'   => 'company',
            'company_address' => 'company',

            'invoice_prefix'       => 'billing',
            'currency'             => 'billing',
            'billing_type'         => 'billing',
            'grace_period_days'    => 'billing',
            'default_billing_date' => 'billing',
            'late_fee_amount'      => 'billing',
            'late_fee_after_days'  => 'billing',
            'vat_percentage'       => 'billing',
            'invoice_due_days'     => 'billing',
            'invoice_footer_text'  => 'billing',

            'customer_code_prefix' => 'customer',
            'default_mac_reseller_tariff_package_id' => 'customer',
        ];

        foreach ($fields as $field => $group) {
            if ($request->filled($field) || $request->has($field)) {
                ResellerSetting::set($resellerId, $field, $request->input($field), $group);
            }
        }

        // MikroTik checkboxes (default OFF if not checked)
        ResellerSetting::set($resellerId, 'auto_suspend_on_expire',  $request->has('auto_suspend_on_expire')  ? '1' : '0', 'mikrotik');
        ResellerSetting::set($resellerId, 'auto_restore_on_payment', $request->has('auto_restore_on_payment') ? '1' : '0', 'mikrotik');

        return back()->with('success', 'Settings saved successfully.');
    }
}