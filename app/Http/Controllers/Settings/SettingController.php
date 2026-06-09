<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Package;
use Illuminate\Http\Request;

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
            // Company
            'company_name'                  => 'nullable|string|max:100',
            'company_phone'                 => 'nullable|string|max:20',
            'company_email'                 => 'nullable|email|max:100',
            'company_address'               => 'nullable|string',

            // Billing
            'invoice_prefix'                => 'nullable|string|max:10',
            'currency'                      => 'nullable|string|max:10',
            'billing_type'                  => 'nullable|in:date_to_date,monthly',
            'grace_period_days'             => 'nullable|integer|min:0|max:30',
            'default_billing_date'          => 'nullable|integer|min:1|max:28',
            'late_fee_amount'               => 'nullable|numeric|min:0',
            'late_fee_after_days'           => 'nullable|integer|min:0',
            'vat_percentage'                => 'nullable|numeric|min:0|max:100',
            'invoice_due_days'              => 'nullable|integer|min:1',
            'invoice_footer_text'           => 'nullable|string',

            // SMS
            'bill_due_sms_days_before'      => 'nullable|integer|min:1',
            'expiry_sms_days_before'        => 'nullable|integer|min:1',

            // Email
            'bill_due_email_days_before'    => 'nullable|integer|min:1',
            'expiry_email_days_before'      => 'nullable|integer|min:1',

            // Customer
            'customer_code_prefix'          => 'nullable|string|max:10',
            'default_package_id'            => 'nullable|exists:packages,id',
        ]);

        // Logo upload
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('settings', 'public');
            Setting::set('company_logo', $path, 'company');
        }

        // Text/number fields with group
        $fields = [
            // company group
            'company_name'               => 'company',
            'company_phone'              => 'company',
            'company_email'              => 'company',
            'company_address'            => 'company',

            // billing group
            'invoice_prefix'             => 'billing',
            'currency'                   => 'billing',
            'billing_type'               => 'billing',
            'grace_period_days'          => 'billing',
            'default_billing_date'       => 'billing',
            'late_fee_amount'            => 'billing',
            'late_fee_after_days'        => 'billing',
            'vat_percentage'             => 'billing',
            'invoice_due_days'           => 'billing',
            'invoice_footer_text'        => 'billing',

            // notification group
            'bill_due_sms_days_before'   => 'notification',
            'expiry_sms_days_before'     => 'notification',
            'bill_due_email_days_before' => 'notification',
            'expiry_email_days_before'   => 'notification',

            // customer group
            'customer_code_prefix'       => 'customer',
            'default_package_id'         => 'customer',
        ];

        foreach ($fields as $field => $group) {
            if ($request->has($field) || $request->filled($field)) {
                Setting::set($field, $request->input($field), $group);
            }
        }

        // SMS checkboxes (default OFF if not checked)
        $smsCheckboxes = [
            'payment_confirm_sms',
            'account_created_sms',
            'invoice_generated_sms',
            'bill_due_sms',
            'expiry_sms',
            'overdue_sms',
            'suspension_sms',
            'restore_sms',
            'package_changed_sms',
            'password_reset_sms',
        ];

        foreach ($smsCheckboxes as $key) {
            Setting::set($key, $request->has($key) ? '1' : '0', 'notification');
        }

        // Email checkboxes (default OFF if not checked)
        $emailCheckboxes = [
            'payment_confirm_email',
            'account_created_email',
            'invoice_generated_email',
            'bill_due_email',
            'expiry_email',
            'overdue_email',
            'suspension_email',
            'restore_email',
            'package_changed_email',
            'password_reset_email',
        ];

        foreach ($emailCheckboxes as $key) {
            Setting::set($key, $request->has($key) ? '1' : '0', 'notification');
        }

        // MikroTik checkboxes
        Setting::set('auto_suspend_on_expire',  $request->has('auto_suspend_on_expire')  ? '1' : '0', 'mikrotik');
        Setting::set('auto_restore_on_payment', $request->has('auto_restore_on_payment') ? '1' : '0', 'mikrotik');

        return back()->with('success', 'Settings saved successfully.');
    }
}
