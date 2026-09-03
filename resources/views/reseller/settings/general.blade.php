@extends('reseller.layouts.app')

@section('title', 'Settings')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

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
        <form action="{{ route('reseller.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="tab-content">

                {{-- ── General ──────────────────────────────── --}}
                <div class="tab-pane fade show active" id="tab-general">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-sliders-h mr-1"></i> General Settings</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Customer Code Prefix</label>
                                        <input type="text" name="customer_code_prefix" class="form-control"
                                               value="{{ $customer['customer_code_prefix'] ?? 'ISP' }}" maxlength="10">
                                        <small class="text-muted">e.g. ISP → ISP-0001</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Default Package</label>
                                        <select name="default_mac_reseller_tariff_package_id" class="form-control">
                                            <option value="">-- No Default --</option>
                                            @foreach($packages as $pkg)
                                                <option value="{{ $pkg->id }}"
                                                    {{ ($customer['default_mac_reseller_tariff_package_id'] ?? '') == $pkg->id ? 'selected' : '' }}>
                                                    {{ $pkg->package->name ?? 'Package #' . $pkg->id }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Currency</label>
                                        <input type="text" name="currency" class="form-control"
                                               value="{{ $billing['currency'] ?? 'BDT' }}" maxlength="10">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button>
                        </div>
                    </div>
                </div>

                {{-- ── Company ──────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-company">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-building mr-1"></i> Company Info</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Company Name</label>
                                <input type="text" name="company_name" class="form-control"
                                       value="{{ $company['company_name'] ?? '' }}" placeholder="My POP Ltd.">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Phone</label>
                                        <input type="text" name="company_phone" class="form-control"
                                               value="{{ $company['company_phone'] ?? '' }}" placeholder="01XXXXXXXXX">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Email</label>
                                        <input type="email" name="company_email" class="form-control"
                                               value="{{ $company['company_email'] ?? '' }}" placeholder="info@mypop.com">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Address</label>
                                <textarea name="company_address" class="form-control" rows="2">{{ $company['company_address'] ?? '' }}</textarea>
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Logo</label>
                                @if(!empty($company['company_logo']))
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $company['company_logo']) }}" style="max-height:60px;">
                                    </div>
                                @endif
                                <div class="custom-file">
                                    <input type="file" name="company_logo" class="custom-file-input" id="logoInput" accept="image/*">
                                    <label class="custom-file-label" for="logoInput">Choose File</label>
                                </div>
                                <small class="text-muted">PNG, JPG — max 2MB</small>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button>
                        </div>
                    </div>
                </div>

                {{-- ── Billing ──────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-billing">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-1"></i> Billing Settings</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Invoice Prefix</label>
                                        <input type="text" name="invoice_prefix" class="form-control"
                                               value="{{ $billing['invoice_prefix'] ?? 'INV' }}" maxlength="10">
                                        <small class="text-muted">e.g. INV-2026-001</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Grace Period</label>
                                        <div class="input-group">
                                            <input type="number" name="grace_period_days" class="form-control"
                                                   value="{{ $billing['grace_period_days'] ?? 3 }}" min="0" max="30">
                                            <div class="input-group-append"><span class="input-group-text">days</span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Default Bill Date</label>
                                        <select name="default_billing_date" class="form-control">
                                            @for($d = 1; $d <= 28; $d++)
                                                <option value="{{ $d }}" {{ ($billing['default_billing_date'] ?? 1) == $d ? 'selected' : '' }}>{{ $d }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <label class="font-weight-bold d-block">Billing Type</label>
                            <div class="form-check form-check-inline mb-3">
                                <input type="radio" name="billing_type" value="date_to_date" class="form-check-input"
                                       id="bt-dtd" {{ ($billing['billing_type'] ?? 'monthly') == 'date_to_date' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bt-dtd">Date to Date (30 days)</label>
                            </div>
                            <div class="form-check form-check-inline mb-3">
                                <input type="radio" name="billing_type" value="monthly" class="form-check-input"
                                       id="bt-monthly" {{ ($billing['billing_type'] ?? 'monthly') == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bt-monthly">Monthly (1st of month)</label>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Late Fee Amount</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                            <input type="number" step="0.01" name="late_fee_amount" class="form-control"
                                                   value="{{ $billing['late_fee_amount'] ?? 0 }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Late Fee After</label>
                                        <div class="input-group">
                                            <input type="number" name="late_fee_after_days" class="form-control"
                                                   value="{{ $billing['late_fee_after_days'] ?? 7 }}">
                                            <div class="input-group-append"><span class="input-group-text">days</span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">VAT/Tax %</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="vat_percentage" class="form-control"
                                                   value="{{ $billing['vat_percentage'] ?? 0 }}">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Invoice Due After</label>
                                <div class="input-group" style="max-width:200px;">
                                    <input type="number" name="invoice_due_days" class="form-control"
                                           value="{{ $billing['invoice_due_days'] ?? 7 }}">
                                    <div class="input-group-append"><span class="input-group-text">days</span></div>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Invoice Footer Text</label>
                                <textarea name="invoice_footer_text" class="form-control" rows="2">{{ $billing['invoice_footer_text'] ?? 'Thank you for your payment.' }}</textarea>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button>
                        </div>
                    </div>
                </div>

                {{-- ── MikroTik ──────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-mikrotik">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-server mr-1"></i> MikroTik Settings</h3></div>
                        <div class="card-body">
                            <div class="custom-control custom-switch mb-4">
                                <input type="checkbox" class="custom-control-input" id="auto_suspend_on_expire" name="auto_suspend_on_expire"
                                       {{ ($mikrotik['auto_suspend_on_expire'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="auto_suspend_on_expire">
                                    Auto Suspend on Expire
                                    <small class="text-muted d-block">Automatically disable client on MikroTik when expired</small>
                                </label>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="auto_restore_on_payment" name="auto_restore_on_payment"
                                       {{ ($mikrotik['auto_restore_on_payment'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="auto_restore_on_payment">
                                    Auto Restore on Payment
                                    <small class="text-muted d-block">Automatically enable client on MikroTik after payment</small>
                                </label>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button>
                        </div>
                    </div>
                </div>

                {{-- ── Customer ──────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-customer">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-users mr-1"></i> Customer Settings</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Customer Code Prefix</label>
                                        <input type="text" name="customer_code_prefix" class="form-control"
                                               value="{{ $customer['customer_code_prefix'] ?? 'ISP' }}" maxlength="10">
                                        <small class="text-muted">e.g. ISP → ISP-0001</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Default Package</label>
                                        <select name="default_mac_reseller_tariff_package_id" class="form-control">
                                            <option value="">-- No Default --</option>
                                            @foreach($packages as $pkg)
                                                <option value="{{ $pkg->id }}"
                                                    {{ ($customer['default_mac_reseller_tariff_package_id'] ?? '') == $pkg->id ? 'selected' : '' }}>
                                                    {{ $pkg->package->name ?? 'Package #' . $pkg->id }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button>
                        </div>
                    </div>
                </div>

                {{-- ── Payment Gateways ──────────────────────── --}}
                <div class="tab-pane fade" id="tab-payment">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-credit-card mr-1"></i> Payment Gateways</h3></div>
                        <div class="card-body">
                            @include('reseller.settings.partials.tab-payment')
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection