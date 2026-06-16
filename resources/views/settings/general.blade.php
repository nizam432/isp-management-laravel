{{-- resources/views/settings/general.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Settings')
@section('page_content')

<div class="row">

    {{-- Left Tabs --}}
    <div class="col-md-3">
        <div class="card">
            <div class="card-body p-0">
                <div class="nav flex-column nav-pills" id="settingsTabs" role="tablist">
                    <a class="nav-link active rounded-0 border-bottom" data-toggle="pill" href="#tab-general">
                        <i class="fas fa-sliders-h mr-2"></i> General
                    </a>
                    <a class="nav-link rounded-0 border-bottom" data-toggle="pill" href="#tab-company">
                        <i class="fas fa-building mr-2"></i> Company
                    </a>
                    <a class="nav-link rounded-0 border-bottom" data-toggle="pill" href="#tab-billing">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Billing
                    </a>
                    <a class="nav-link rounded-0 border-bottom" data-toggle="pill" href="#tab-mikrotik">
                        <i class="fas fa-server mr-2"></i> MikroTik
                    </a>
                    <a class="nav-link rounded-0 border-bottom" data-toggle="pill" href="#tab-sms">
                        <i class="fas fa-sms mr-2"></i> SMS Notifications
                    </a>
                    <a class="nav-link rounded-0 border-bottom" data-toggle="pill" href="#tab-customer">
                        <i class="fas fa-users mr-2"></i> Customer
                    </a>
                    <a class="nav-link rounded-0 border-bottom" data-toggle="pill" href="#tab-payment">
                        <i class="fas fa-credit-card mr-2"></i> Payment Gateways
                    </a>
                    <a class="nav-link rounded-0" data-toggle="pill" href="#tab-email">
                        <i class="fas fa-envelope mr-2"></i> Email Notifications
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Content --}}
    <div class="col-md-9">
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="tab-content">

                {{-- ── General ──────────────────────────────── --}}
                <div class="tab-pane fade show active" id="tab-general">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-sliders-h mr-1"></i> General Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Customer Code Prefix</label>
                                        <input type="text" name="customer_code_prefix" class="form-control"
                                               value="{{ $customer['customer_code_prefix'] ?? 'ISP' }}"
                                               placeholder="ISP" maxlength="10">
                                        <small class="text-muted">e.g. ISP → ISP-0001</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Default Package</label>
                                        <select name="default_package_id" class="form-control">
                                            <option value="">-- No Default --</option>
                                            @foreach($packages as $pkg)
                                                <option value="{{ $pkg->id }}"
                                                    {{ ($customer['default_package_id'] ?? '') == $pkg->id ? 'selected' : '' }}>
                                                    {{ $pkg->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Currency</label>
                                        <input type="text" name="currency" class="form-control"
                                               value="{{ $billing['currency'] ?? 'BDT' }}"
                                               placeholder="BDT" maxlength="10">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Save
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── Company ──────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-company">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-building mr-1"></i> Company Info</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Company Name</label>
                                <input type="text" name="company_name" class="form-control"
                                       value="{{ $company['company_name'] ?? '' }}"
                                       placeholder="My ISP Ltd.">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Phone</label>
                                        <input type="text" name="company_phone" class="form-control"
                                               value="{{ $company['company_phone'] ?? '' }}"
                                               placeholder="01XXXXXXXXX">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Email</label>
                                        <input type="email" name="company_email" class="form-control"
                                               value="{{ $company['company_email'] ?? '' }}"
                                               placeholder="info@myisp.com">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Address</label>
                                <textarea name="company_address" class="form-control" rows="2"
                                          placeholder="Company address...">{{ $company['company_address'] ?? '' }}</textarea>
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Logo</label>
                                @if(!empty($company['company_logo']))
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $company['company_logo']) }}"
                                             height="50" class="border rounded">
                                    </div>
                                @endif
                                <input type="file" name="company_logo" class="form-control-file" accept="image/*">
                                <small class="text-muted">PNG, JPG — max 2MB</small>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Save
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── Billing ──────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-billing">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-1"></i> Billing Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Invoice Prefix</label>
                                        <input type="text" name="invoice_prefix" class="form-control"
                                               value="{{ $billing['invoice_prefix'] ?? 'INV' }}"
                                               placeholder="INV" maxlength="10">
                                        <small class="text-muted">e.g. INV-2026-001</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Grace Period</label>
                                        <div class="input-group">
                                            <input type="number" name="grace_period_days" class="form-control"
                                                   value="{{ $billing['grace_period_days'] ?? 3 }}" min="0" max="30">
                                            <div class="input-group-append">
                                                <span class="input-group-text">days</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Default Bill Date</label>
                                        <select name="default_billing_date" class="form-control">
                                            @for($d = 1; $d <= 28; $d++)
                                                <option value="{{ $d }}"
                                                    {{ ($billing['default_billing_date'] ?? 1) == $d ? 'selected' : '' }}>
                                                    {{ $d }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Billing Type</label>
                                <div class="mt-1">
                                    <div class="custom-control custom-radio d-inline-block mr-4">
                                        <input type="radio" id="billing_date_to_date" name="billing_type"
                                               value="date_to_date" class="custom-control-input"
                                               {{ ($billing['billing_type'] ?? 'date_to_date') === 'date_to_date' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="billing_date_to_date">
                                            Date to Date (30 days)
                                        </label>
                                    </div>
                                    <div class="custom-control custom-radio d-inline-block">
                                        <input type="radio" id="billing_monthly" name="billing_type"
                                               value="monthly" class="custom-control-input"
                                               {{ ($billing['billing_type'] ?? '') === 'monthly' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="billing_monthly">
                                            Monthly (1st of month)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Late Fee Amount</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">৳</span>
                                            </div>
                                            <input type="number" name="late_fee_amount" class="form-control"
                                                   value="{{ $billing['late_fee_amount'] ?? 0 }}" min="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Late Fee After</label>
                                        <div class="input-group">
                                            <input type="number" name="late_fee_after_days" class="form-control"
                                                   value="{{ $billing['late_fee_after_days'] ?? 7 }}" min="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">days</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">VAT/Tax %</label>
                                        <div class="input-group">
                                            <input type="number" name="vat_percentage" class="form-control"
                                                   value="{{ $billing['vat_percentage'] ?? 0 }}"
                                                   min="0" max="100" step="0.01">
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Invoice Due After</label>
                                        <div class="input-group">
                                            <input type="number" name="invoice_due_days" class="form-control"
                                                   value="{{ $billing['invoice_due_days'] ?? 7 }}" min="1">
                                            <div class="input-group-append">
                                                <span class="input-group-text">days</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Invoice Footer Text</label>
                                <textarea name="invoice_footer_text" class="form-control" rows="2">{{ $billing['invoice_footer_text'] ?? 'Thank you for your payment.' }}</textarea>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Save
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── SMS Notifications ────────────────────── --}}
                <div class="tab-pane fade" id="tab-sms">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-sms mr-1"></i> SMS Notifications</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Notification</th>
                                        <th class="text-center" style="width:120px;">Enable</th>
                                        <th style="width:220px;">Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Payment Confirmation</strong><small class="text-muted d-block">Send when payment is received</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="payment_confirm_sms" name="payment_confirm_sms" {{ ($notification['payment_confirm_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="payment_confirm_sms"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Account Created</strong><small class="text-muted d-block">Send when new customer is added</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="account_created_sms" name="account_created_sms" {{ ($notification['account_created_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="account_created_sms"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Invoice Generated</strong><small class="text-muted d-block">Send when new invoice is created</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="invoice_generated_sms" name="invoice_generated_sms" {{ ($notification['invoice_generated_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="invoice_generated_sms"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bill Due Reminder</strong><small class="text-muted d-block">Send before bill due date</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input sms-toggle" id="bill_due_sms" name="bill_due_sms" data-target="bill_due_days_wrap" {{ ($notification['bill_due_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="bill_due_sms"></label>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div id="bill_due_days_wrap" class="{{ ($notification['bill_due_sms'] ?? '1') == '1' ? '' : 'd-none' }}">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="bill_due_sms_days_before" class="form-control" value="{{ $notification['bill_due_sms_days_before'] ?? 3 }}" min="1">
                                                    <div class="input-group-append"><span class="input-group-text">days before</span></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Expiry Reminder</strong><small class="text-muted d-block">Send before account expires</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input sms-toggle" id="expiry_sms" name="expiry_sms" data-target="expiry_days_wrap" {{ ($notification['expiry_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="expiry_sms"></label>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div id="expiry_days_wrap" class="{{ ($notification['expiry_sms'] ?? '1') == '1' ? '' : 'd-none' }}">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="expiry_sms_days_before" class="form-control" value="{{ $notification['expiry_sms_days_before'] ?? 3 }}" min="1">
                                                    <div class="input-group-append"><span class="input-group-text">days before</span></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Overdue</strong><small class="text-muted d-block">Send when invoice becomes overdue</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="overdue_sms" name="overdue_sms" {{ ($notification['overdue_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="overdue_sms"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Suspension</strong><small class="text-muted d-block">Send when customer is suspended</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="suspension_sms" name="suspension_sms" {{ ($notification['suspension_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="suspension_sms"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Connection Restored</strong><small class="text-muted d-block">Send when connection is restored after payment</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="restore_sms" name="restore_sms" {{ ($notification['restore_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="restore_sms"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Package Changed</strong><small class="text-muted d-block">Send when customer package is changed</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="package_changed_sms" name="package_changed_sms" {{ ($notification['package_changed_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="package_changed_sms"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Password Reset</strong><small class="text-muted d-block">Send when portal password is reset</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="password_reset_sms" name="password_reset_sms" {{ ($notification['password_reset_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="password_reset_sms"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button>
                        </div>
                    </div>
                </div>

                {{-- ── Email Notifications ──────────────────── --}}
                <div class="tab-pane fade" id="tab-email">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-envelope mr-1"></i> Email Notifications</h3>
                        </div>
                        <div class="card-body">
                            @if(empty(\App\Models\Setting::get('company_email')))
                            <div class="alert alert-warning py-2 mb-3" style="font-size:13px;">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Email not configured.</strong> Go to <a href="#tab-company" data-toggle="pill">Company Settings</a> to set up your email. Email notifications will not be sent until configured.
                            </div>
                            @endif
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Notification</th>
                                        <th class="text-center" style="width:120px;">Enable</th>
                                        <th style="width:220px;">Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Payment Confirmation</strong><small class="text-muted d-block">Send when payment is received</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="payment_confirm_email" name="payment_confirm_email" {{ ($notification['payment_confirm_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="payment_confirm_email"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Account Created</strong><small class="text-muted d-block">Send when new customer is added</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="account_created_email" name="account_created_email" {{ ($notification['account_created_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="account_created_email"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Invoice Generated</strong><small class="text-muted d-block">Send when new invoice is created</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="invoice_generated_email" name="invoice_generated_email" {{ ($notification['invoice_generated_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="invoice_generated_email"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bill Due Reminder</strong><small class="text-muted d-block">Send before bill due date</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input sms-toggle" id="bill_due_email" name="bill_due_email" data-target="bill_due_email_days_wrap" {{ ($notification['bill_due_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="bill_due_email"></label>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div id="bill_due_email_days_wrap" class="{{ ($notification['bill_due_email'] ?? '0') == '1' ? '' : 'd-none' }}">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="bill_due_email_days_before" class="form-control" value="{{ $notification['bill_due_email_days_before'] ?? 3 }}" min="1">
                                                    <div class="input-group-append"><span class="input-group-text">days before</span></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Expiry Reminder</strong><small class="text-muted d-block">Send before account expires</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input sms-toggle" id="expiry_email" name="expiry_email" data-target="expiry_email_days_wrap" {{ ($notification['expiry_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="expiry_email"></label>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div id="expiry_email_days_wrap" class="{{ ($notification['expiry_email'] ?? '0') == '1' ? '' : 'd-none' }}">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="expiry_email_days_before" class="form-control" value="{{ $notification['expiry_email_days_before'] ?? 3 }}" min="1">
                                                    <div class="input-group-append"><span class="input-group-text">days before</span></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Overdue</strong><small class="text-muted d-block">Send when invoice becomes overdue</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="overdue_email" name="overdue_email" {{ ($notification['overdue_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="overdue_email"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Suspension</strong><small class="text-muted d-block">Send when customer is suspended</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="suspension_email" name="suspension_email" {{ ($notification['suspension_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="suspension_email"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Connection Restored</strong><small class="text-muted d-block">Send when connection is restored after payment</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="restore_email" name="restore_email" {{ ($notification['restore_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="restore_email"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Package Changed</strong><small class="text-muted d-block">Send when customer package is changed</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="package_changed_email" name="package_changed_email" {{ ($notification['package_changed_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="package_changed_email"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Password Reset</strong><small class="text-muted d-block">Send when portal password is reset</small></td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="password_reset_email" name="password_reset_email" {{ ($notification['password_reset_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="password_reset_email"></label>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button>
                        </div>
                    </div>
                </div>

                {{-- ── MikroTik ──────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-mikrotik">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-server mr-1"></i> MikroTik Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="custom-control custom-switch mb-4">
                                <input type="checkbox" class="custom-control-input"
                                       id="auto_suspend_on_expire" name="auto_suspend_on_expire"
                                       {{ ($mikrotik['auto_suspend_on_expire'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="auto_suspend_on_expire">
                                    Auto Suspend on Expire
                                    <small class="text-muted d-block">Automatically disable customer on MikroTik when expired</small>
                                </label>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input"
                                       id="auto_restore_on_payment" name="auto_restore_on_payment"
                                       {{ ($mikrotik['auto_restore_on_payment'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="auto_restore_on_payment">
                                    Auto Restore on Payment
                                    <small class="text-muted d-block">Automatically enable customer on MikroTik after payment</small>
                                </label>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Save
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── Customer ──────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-customer">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-users mr-1"></i> Customer Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Customer Code Prefix</label>
                                        <input type="text" name="customer_code_prefix" class="form-control"
                                               value="{{ $customer['customer_code_prefix'] ?? 'ISP' }}"
                                               placeholder="ISP" maxlength="10">
                                        <small class="text-muted">e.g. ISP → ISP-0001</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Default Package</label>
                                        <select name="default_package_id" class="form-control">
                                            <option value="">-- No Default --</option>
                                            @foreach($packages as $pkg)
                                                <option value="{{ $pkg->id }}"
                                                    {{ ($customer['default_package_id'] ?? '') == $pkg->id ? 'selected' : '' }}>
                                                    {{ $pkg->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Save
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── Payment Gateways ──────────────────────── --}}
                <div class="tab-pane fade" id="tab-payment">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-credit-card mr-1"></i> Payment Gateways</h3>
                        </div>
                        <div class="card-body">
                            @include('settings.partials.tab-payment')
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<script>
// SMS toggle show/hide days input
document.querySelectorAll('.sms-toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
        var targetId = this.getAttribute('data-target');
        var target   = document.getElementById(targetId);
        if (target) {
            target.classList.toggle('d-none', !this.checked);
        }
    });
});
</script>
@endpush