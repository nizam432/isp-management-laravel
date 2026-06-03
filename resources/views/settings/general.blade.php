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
                    <a class="nav-link rounded-0 border-bottom" data-toggle="pill" href="#tab-sms">
                        <i class="fas fa-sms mr-2"></i> SMS Notifications
                    </a>
                    <a class="nav-link rounded-0 border-bottom" data-toggle="pill" href="#tab-mikrotik">
                        <i class="fas fa-server mr-2"></i> MikroTik
                    </a>
                    <a class="nav-link rounded-0 border-bottom" data-toggle="pill" href="#tab-customer">
                        <i class="fas fa-users mr-2"></i> Customer
                    </a>
                    <a class="nav-link rounded-0" data-toggle="pill" href="#tab-payment">
                        <i class="fas fa-credit-card mr-2"></i> Payment Gateways
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
                            <div class="form-group">
                                <label class="font-weight-bold">SMS Sender Name</label>
                                <input type="text" name="sms_sender_name" class="form-control"
                                       value="{{ $notification['sms_sender_name'] ?? '' }}"
                                       placeholder="MyISP" maxlength="11">
                                <small class="text-muted">Max 11 characters</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Bill Due SMS Before</label>
                                        <div class="input-group">
                                            <input type="number" name="bill_due_sms_days_before" class="form-control"
                                                   value="{{ $notification['bill_due_sms_days_before'] ?? 3 }}" min="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">days</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Expiry SMS Before</label>
                                        <div class="input-group">
                                            <input type="number" name="expiry_sms_days_before" class="form-control"
                                                   value="{{ $notification['expiry_sms_days_before'] ?? 3 }}" min="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">days</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input"
                                       id="payment_confirm_sms" name="payment_confirm_sms"
                                       {{ ($notification['payment_confirm_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="payment_confirm_sms">
                                    Payment Confirmation SMS
                                    <small class="text-muted d-block">Send SMS when payment is received</small>
                                </label>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input"
                                       id="suspension_sms" name="suspension_sms"
                                       {{ ($notification['suspension_sms'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="suspension_sms">
                                    Suspension SMS
                                    <small class="text-muted d-block">Send SMS when customer is suspended</small>
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
                            <div class="row">
                                {{-- Gateway List --}}
                                <div class="col-md-4">
                                    <div class="list-group" id="gatewayTabs">
                                        @php
                                            $gateways = [
                                                ['slug' => 'bkash',      'name' => 'bKash',      'icon' => 'fas fa-mobile-alt', 'color' => '#E2136E', 'type' => 'local'],
                                                ['slug' => 'nagad',      'name' => 'Nagad',      'icon' => 'fas fa-mobile-alt', 'color' => '#F05A22', 'type' => 'local'],
                                                ['slug' => 'rocket',     'name' => 'Rocket',     'icon' => 'fas fa-mobile-alt', 'color' => '#8C3494', 'type' => 'local'],
                                                ['slug' => 'sslcommerz', 'name' => 'SSL Commerz','icon' => 'fas fa-credit-card','color' => '#0B6E4F', 'type' => 'local'],
                                                ['slug' => 'amarpayz',    'name' => 'AmarPay',    'icon' => 'fas fa-credit-card','color' => '#FF6B00', 'type' => 'local'],
                                                ['slug' => 'shurjopay',  'name' => 'ShurjoPay',  'icon' => 'fas fa-credit-card','color' => '#E4A11B', 'type' => 'local'],
                                                ['slug' => 'stripe',     'name' => 'Stripe',     'icon' => 'fab fa-stripe',     'color' => '#6772E5', 'type' => 'international'],
                                                ['slug' => 'paypal',     'name' => 'PayPal',     'icon' => 'fab fa-paypal',     'color' => '#003087', 'type' => 'international'],
                                                ['slug' => 'razorpay',   'name' => 'Razorpay',   'icon' => 'fas fa-credit-card','color' => '#072654', 'type' => 'international'],
                                            ];
                                        @endphp

                                        <div class="list-group-item list-group-item-dark py-1 px-3">
                                            <small class="font-weight-bold">LOCAL</small>
                                        </div>
                                        @foreach($gateways as $gw)
                                            @if($gw['type'] === 'local')
                                            <a href="#" class="list-group-item list-group-item-action gateway-tab-link"
                                               data-gateway="{{ $gw['slug'] }}">
                                                <i class="{{ $gw['icon'] }} mr-2" style="color:{{ $gw['color'] }}"></i>
                                                {{ $gw['name'] }}
                                                @if(\App\Models\Setting::get('gateway_' . $gw['slug'] . '_enabled') == '1')
                                                    <span class="badge badge-success float-right">Active</span>
                                                @endif
                                            </a>
                                            @endif
                                        @endforeach

                                        <div class="list-group-item list-group-item-dark py-1 px-3 mt-2">
                                            <small class="font-weight-bold">INTERNATIONAL</small>
                                        </div>
                                        @foreach($gateways as $gw)
                                            @if($gw['type'] === 'international')
                                            <a href="#" class="list-group-item list-group-item-action gateway-tab-link"
                                               data-gateway="{{ $gw['slug'] }}">
                                                <i class="{{ $gw['icon'] }} mr-2" style="color:{{ $gw['color'] }}"></i>
                                                {{ $gw['name'] }}
                                                @if(\App\Models\Setting::get('gateway_' . $gw['slug'] . '_enabled') == '1')
                                                    <span class="badge badge-success float-right">Active</span>
                                                @endif
                                            </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Gateway Config --}}
                                <div class="col-md-8" id="gatewayConfig">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-credit-card fa-3x mb-3 d-block"></i>
                                        Select a gateway to configure
                                    </div>
                                </div>
                            </div>
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
// ── Gateway Tab ───────────────────────────────────────
var gatewayConfigs = {
    bkash: {
        name: 'bKash', color: '#E2136E',
        fields: [
            { key: 'app_key',    label: 'App Key',    type: 'text' },
            { key: 'app_secret', label: 'App Secret', type: 'password' },
            { key: 'username',   label: 'Username',   type: 'text' },
            { key: 'password',   label: 'Password',   type: 'password' },
        ]
    },
    nagad: {
        name: 'Nagad', color: '#F05A22',
        fields: [
            { key: 'merchant_id',      label: 'Merchant ID',      type: 'text' },
            { key: 'merchant_number',  label: 'Merchant Number',  type: 'text' },
            { key: 'public_key',       label: 'Public Key',       type: 'textarea' },
            { key: 'private_key',      label: 'Private Key',      type: 'textarea' },
        ]
    },
    rocket: {
        name: 'Rocket', color: '#8C3494',
        fields: [
            { key: 'merchant_number', label: 'Merchant Number', type: 'text' },
            { key: 'api_key',         label: 'API Key',         type: 'password' },
        ]
    },
    sslcommerz: {
        name: 'SSL Commerz', color: '#0B6E4F',
        fields: [
            { key: 'store_id',     label: 'Store ID',     type: 'text' },
            { key: 'store_passwd', label: 'Store Password', type: 'password' },
        ]
    },
    amarpayz: {
        name: 'AmarPay', color: '#FF6B00',
        fields: [
            { key: 'app_id',  label: 'App ID',  type: 'text' },
            { key: 'app_key', label: 'App Key', type: 'password' },
        ]
    },
    shurjopay: {
        name: 'ShurjoPay', color: '#E4A11B',
        fields: [
            { key: 'username', label: 'Username', type: 'text' },
            { key: 'password', label: 'Password', type: 'password' },
            { key: 'prefix',   label: 'Prefix',   type: 'text' },
        ]
    },
    stripe: {
        name: 'Stripe', color: '#6772E5',
        fields: [
            { key: 'publishable_key', label: 'Publishable Key', type: 'text' },
            { key: 'secret_key',      label: 'Secret Key',      type: 'password' },
            { key: 'webhook_secret',  label: 'Webhook Secret',  type: 'password' },
        ]
    },
    paypal: {
        name: 'PayPal', color: '#003087',
        fields: [
            { key: 'client_id',     label: 'Client ID',     type: 'text' },
            { key: 'client_secret', label: 'Client Secret', type: 'password' },
        ]
    },
    razorpay: {
        name: 'Razorpay', color: '#072654',
        fields: [
            { key: 'key_id',     label: 'Key ID',     type: 'text' },
            { key: 'key_secret', label: 'Key Secret', type: 'password' },
        ]
    },
};

document.querySelectorAll('.gateway-tab-link').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.gateway-tab-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');

        var slug   = this.getAttribute('data-gateway');
        var config = gatewayConfigs[slug];
        if (!config) return;

        var html = '<div class="d-flex align-items-center mb-3">';
        html += '<h5 class="mb-0" style="color:' + config.color + '">' + config.name + ' Settings</h5>';
        html += '</div>';

        // Mode toggle
        html += '<div class="form-group">';
        html += '<label class="font-weight-bold">Mode</label>';
        html += '<div class="btn-group btn-group-sm d-block">';
        html += '<button type="button" class="btn btn-outline-warning mode-btn" data-gateway="' + slug + '" data-mode="sandbox">Sandbox</button>';
        html += '<button type="button" class="btn btn-outline-success mode-btn" data-gateway="' + slug + '" data-mode="live">Live</button>';
        html += '</div>';
        html += '<input type="hidden" name="gateway_' + slug + '_mode" id="mode_' + slug + '" value="sandbox">';
        html += '</div>';

        // Enable toggle
        html += '<div class="custom-control custom-switch mb-3">';
        html += '<input type="checkbox" class="custom-control-input" id="enable_' + slug + '" name="gateway_' + slug + '_enabled" value="1">';
        html += '<label class="custom-control-label" for="enable_' + slug + '">Enable ' + config.name + '</label>';
        html += '</div>';

        // Fields
        config.fields.forEach(function(field) {
            html += '<div class="form-group">';
            html += '<label class="font-weight-bold small">' + field.label + '</label>';
            if (field.type === 'textarea') {
                html += '<textarea name="gateway_' + slug + '_' + field.key + '" class="form-control form-control-sm" rows="3" placeholder="' + field.label + '"></textarea>';
            } else {
                html += '<input type="' + field.type + '" name="gateway_' + slug + '_' + field.key + '" class="form-control form-control-sm" placeholder="' + field.label + '">';
            }
            html += '</div>';
        });

        html += '<button type="submit" class="btn btn-primary btn-block">';
        html += '<i class="fas fa-save mr-1"></i> Save ' + config.name + ' Settings</button>';

        document.getElementById('gatewayConfig').innerHTML = html;

        // Mode button events
        document.querySelectorAll('.mode-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var gw   = this.getAttribute('data-gateway');
                var mode = this.getAttribute('data-mode');
                document.getElementById('mode_' + gw).value = mode;
                document.querySelectorAll('.mode-btn').forEach(b => {
                    b.classList.remove('btn-warning', 'btn-success');
                    b.classList.add(b.getAttribute('data-mode') === 'sandbox' ? 'btn-outline-warning' : 'btn-outline-success');
                });
                this.classList.remove('btn-outline-warning', 'btn-outline-success');
                this.classList.add(mode === 'sandbox' ? 'btn-warning' : 'btn-success');
            });
        });
    });
});
</script>
@endpush