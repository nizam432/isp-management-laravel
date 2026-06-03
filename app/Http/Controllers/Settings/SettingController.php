<?php

namespace App\Http\Controllers\Settings;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $company      = Setting::getGroup('company');
        $billing      = Setting::getGroup('billing');
        $notification = Setting::getGroup('notification');
        $mikrotik     = Setting::getGroup('mikrotik');
        $customer     = Setting::getGroup('customer');
        $packages     = Package::active()->get();

        return view('settings.general', compact(
            'company', 'billing', 'notification', 'mikrotik', 'customer', 'packages'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name'              => 'nullable|string|max:100',
            'company_phone'             => 'nullable|string|max:20',
            'company_email'             => 'nullable|email|max:100',
            'company_address'           => 'nullable|string',
            'invoice_prefix'            => 'nullable|string|max:10',
            'currency'                  => 'nullable|string|max:10',
            'billing_type'              => 'nullable|in:date_to_date,monthly',
            'grace_period_days'         => 'nullable|integer|min:0|max:30',
            'default_billing_date'      => 'nullable|integer|min:1|max:28',
            'late_fee_amount'           => 'nullable|numeric|min:0',
            'late_fee_after_days'       => 'nullable|integer|min:0',
            'vat_percentage'            => 'nullable|numeric|min:0|max:100',
            'invoice_due_days'          => 'nullable|integer|min:1',
            'invoice_footer_text'       => 'nullable|string',
            'sms_sender_name'           => 'nullable|string|max:11',
            'bill_due_sms_days_before'  => 'nullable|integer|min:0',
            'expiry_sms_days_before'    => 'nullable|integer|min:0',
            'payment_confirm_sms'       => 'nullable',
            'suspension_sms'            => 'nullable',
            'auto_suspend_on_expire'    => 'nullable',
            'auto_restore_on_payment'   => 'nullable',
            'customer_code_prefix'      => 'nullable|string|max:10',
            'default_package_id'        => 'nullable|exists:packages,id',
        ]);

        // Logo upload
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('settings', 'public');
            Setting::set('company_logo', $path);
        }

        $fields = [
            'company_name', 'company_phone', 'company_email', 'company_address',
            'invoice_prefix', 'currency', 'billing_type', 'grace_period_days',
            'default_billing_date', 'late_fee_amount', 'late_fee_after_days',
            'vat_percentage', 'invoice_due_days', 'invoice_footer_text',
            'sms_sender_name', 'bill_due_sms_days_before', 'expiry_sms_days_before',
            'customer_code_prefix', 'default_package_id',
        ];

        foreach ($fields as $field) {
            Setting::set($field, $request->input($field));
        }

        // Checkboxes
        Setting::set('payment_confirm_sms',      $request->has('payment_confirm_sms') ? '1' : '0');
        Setting::set('suspension_sms',           $request->has('suspension_sms')      ? '1' : '0');
        Setting::set('auto_suspend_on_expire',   $request->has('auto_suspend_on_expire')  ? '1' : '0');
        Setting::set('auto_restore_on_payment',  $request->has('auto_restore_on_payment') ? '1' : '0');

        return back()->with('success', 'Settings saved successfully.');
    }
}