{{-- resources/views/bandwidth-sale/customers/show.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Customer Detail')

@section('page_actions')
    <a href="{{ route('bandwidth-sale.customers.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
    <button class="btn btn-warning btn-sm" id="btnEdit" data-id="{{ $customer->id }}">
        <i class="fas fa-edit mr-1"></i> Edit
    </button>
@endsection

@section('page_content')

@php
    $statusColor = match($customer->activity_status) {
        'active'   => '#00a65a',
        'inactive' => '#6c757d',
        default    => '#6c757d',
    };
    $popColor = match($customer->pop_status) {
        'active'   => '#00a65a',
        'inactive' => '#dc3545',
        default    => '#6c757d',
    };
@endphp

<div class="row">

    {{-- ══════════════════════════════════════════════════════
         LEFT COLUMN — Profile Card
    ══════════════════════════════════════════════════════ --}}
    <div class="col-md-3">

        {{-- Profile Card --}}
        <div class="card card-primary card-outline">
            <div class="card-body text-center pt-4">
                {{-- Photo --}}
                @if($customer->photo)
                    <img src="{{ asset('storage/' . $customer->photo) }}"
                         class="img-circle elevation-2"
                         style="width:90px;height:90px;object-fit:cover;" alt="Photo">
                @else
                    <div class="img-circle elevation-2 d-inline-flex align-items-center justify-content-center"
                         style="width:90px;height:90px;background:{{ $statusColor }};">
                        <i class="fas fa-building fa-2x text-white"></i>
                    </div>
                @endif

                <h5 class="mt-3 mb-1 font-weight-bold">{{ $customer->customer_name }}</h5>
                <code class="text-muted small">{{ $customer->customer_code }}</code>

                <div class="mt-2">
                    <span class="badge badge-pill px-3 py-2"
                          style="background:{{ $statusColor }};color:#fff;font-size:12px;">
                        {{ ucfirst($customer->activity_status) }}
                    </span>
                </div>
            </div>

            {{-- Info Table --}}
            <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:13px;">

                    {{-- Contact Person --}}
                    @if($customer->contact_person)
                    <tr>
                        <td class="text-muted pl-3" style="width:40%;white-space:nowrap;">
                            <i class="fas fa-user fa-fw mr-1"></i>Contact
                        </td>
                        <td>{{ $customer->contact_person }}</td>
                    </tr>
                    @endif

                    {{-- Mobile --}}
                    <tr>
                        <td class="text-muted pl-3" style="white-space:nowrap;">
                            <i class="fas fa-mobile-alt fa-fw mr-1"></i>Mobile
                        </td>
                        <td>
                            <a href="tel:{{ $customer->mobile_number }}">{{ $customer->mobile_number }}</a>
                        </td>
                    </tr>

                    {{-- Phone --}}
                    @if($customer->phone_number)
                    <tr>
                        <td class="text-muted pl-3" style="white-space:nowrap;">
                            <i class="fas fa-phone fa-fw mr-1"></i>Phone
                        </td>
                        <td>{{ $customer->phone_number }}</td>
                    </tr>
                    @endif

                    {{-- Email --}}
                    @if($customer->email)
                    <tr>
                        <td class="text-muted pl-3" style="white-space:nowrap;">
                            <i class="fas fa-envelope fa-fw mr-1"></i>Email
                        </td>
                        <td><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></td>
                    </tr>
                    @endif

                    {{-- POP Status --}}
                    <tr>
                        <td class="text-muted pl-3" style="white-space:nowrap;">
                            <i class="fas fa-circle fa-fw mr-1"></i>POP
                        </td>
                        <td>
                            <span class="badge badge-{{ $customer->pop_status === 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($customer->pop_status) }}
                            </span>
                        </td>
                    </tr>

                    {{-- Balance Due --}}
                    <tr>
                        <td class="text-muted pl-3" style="white-space:nowrap;">
                            <i class="fas fa-balance-scale fa-fw mr-1"></i>Balance
                        </td>
                        <td>
                            <strong class="{{ $customer->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                ৳ {{ number_format($customer->balance_due, 2) }}
                            </strong>
                        </td>
                    </tr>

                    {{-- Reference --}}
                    @if($customer->reference_by)
                    <tr>
                        <td class="text-muted pl-3" style="white-space:nowrap;">
                            <i class="fas fa-handshake fa-fw mr-1"></i>Ref By
                        </td>
                        <td>{{ $customer->reference_by }}</td>
                    </tr>
                    @endif

                    {{-- Address --}}
                    @if($customer->address)
                    <tr>
                        <td class="text-muted pl-3" style="white-space:nowrap;">
                            <i class="fas fa-map-marker-alt fa-fw mr-1"></i>Address
                        </td>
                        <td><small>{{ $customer->address }}</small></td>
                    </tr>
                    @endif

                </table>
            </div>

            {{-- Social Links --}}
            @if($customer->facebook_url || $customer->skype_id || $customer->website)
            <div class="card-footer text-center">
                @if($customer->facebook_url)
                    <a href="{{ $customer->facebook_url }}" target="_blank"
                       class="btn btn-sm btn-outline-primary mr-1" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                @endif
                @if($customer->skype_id)
                    <a href="skype:{{ $customer->skype_id }}?call"
                       class="btn btn-sm btn-outline-info mr-1" title="Skype">
                        <i class="fab fa-skype"></i>
                    </a>
                @endif
                @if($customer->website)
                    <a href="{{ $customer->website }}" target="_blank"
                       class="btn btn-sm btn-outline-secondary" title="Website">
                        <i class="fas fa-globe"></i>
                    </a>
                @endif
            </div>
            @endif
        </div>

        {{-- Quick Info Card --}}
        <div class="card card-outline card-info">
            <div class="card-header py-2">
                <h3 class="card-title small font-weight-bold">
                    <i class="fas fa-key mr-1"></i> Login Info
                </h3>
            </div>
            <div class="card-body py-2 px-3" style="font-size:13px;">
                <div class="mb-1">
                    <span class="text-muted">Username:</span>
                    <code class="ml-1">{{ $customer->username ?? '—' }}</code>
                </div>
                <div>
                    <span class="text-muted">Activity:</span>
                    <span class="badge badge-{{ $customer->activity_status === 'active' ? 'success' : 'secondary' }} ml-1">
                        {{ ucfirst($customer->activity_status) }}
                    </span>
                </div>
            </div>
        </div>

    </div>
    {{-- END LEFT --}}


    {{-- ══════════════════════════════════════════════════════
         RIGHT COLUMN — Tabs
    ══════════════════════════════════════════════════════ --}}
    <div class="col-md-9">

        {{-- Tab Nav --}}
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="customerTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-transmission" data-toggle="pill"
                           href="#pane-transmission" role="tab">
                            <i class="fas fa-network-wired mr-1"></i> Transmission
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-invoices" data-toggle="pill"
                           href="#pane-invoices" role="tab">
                            <i class="fas fa-file-invoice mr-1"></i> Invoices
                            @if(isset($invoiceCount) && $invoiceCount > 0)
                                <span class="badge badge-info ml-1">{{ $invoiceCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-payments" data-toggle="pill"
                           href="#pane-payments" role="tab">
                            <i class="fas fa-money-bill-wave mr-1"></i> Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-remarks" data-toggle="pill"
                           href="#pane-remarks" role="tab">
                            <i class="fas fa-sticky-note mr-1"></i> Remarks
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="customerTabsContent">

                    {{-- ── TAB 1: Transmission ────────────────────── --}}
                    <div class="tab-pane fade show active" id="pane-transmission" role="tabpanel">

                        <div class="row">

                            {{-- ATTN Info --}}
                            <div class="col-md-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-cyan">
                                        <i class="fas fa-satellite-dish"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">ATTN Info</span>
                                        <span class="info-box-number" style="font-size:13px;">
                                            {{ $customer->attn_info ?? '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- BZR DR / NAS ID --}}
                            <div class="col-md-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-orange">
                                        <i class="fas fa-server"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">BZR DR / NAS ID</span>
                                        <span class="info-box-number" style="font-size:13px;">
                                            {{ $customer->bzr_dr_nas_id ?? '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Activation Date --}}
                            <div class="col-md-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-green">
                                        <i class="fas fa-calendar-check"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Activation Date</span>
                                        <span class="info-box-number" style="font-size:14px;">
                                            {{ $customer->activation_date
                                                ? \Carbon\Carbon::parse($customer->activation_date)->format('d M Y')
                                                : '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- POP Info --}}
                            <div class="col-md-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-purple" style="background:#6f42c1!important;">
                                        <i class="fas fa-broadcast-tower"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">POP Info</span>
                                        <span class="info-box-number" style="font-size:13px;">
                                            {{ $customer->pop_info ?? '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- VLAN Info --}}
                        @php $vlans = is_array($customer->vlan_info) ? $customer->vlan_info : json_decode($customer->vlan_info ?? '[]', true); @endphp
                        @if(!empty($vlans))
                        <div class="card card-outline card-secondary mt-2">
                            <div class="card-header py-2">
                                <h3 class="card-title small">
                                    <i class="fas fa-project-diagram mr-1 text-info"></i> VLAN Info
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0" style="font-size:13px;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>VLAN Name</th>
                                            <th>VLAN ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($vlans as $i => $vlan)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $vlan['vlan_name'] ?? '—' }}</td>
                                            <td><code>{{ $vlan['vlan_id'] ?? '—' }}</code></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        {{-- IP Addresses --}}
                        @php $ips = is_array($customer->ip_addresses) ? $customer->ip_addresses : json_decode($customer->ip_addresses ?? '[]', true); @endphp
                        @if(!empty($ips))
                        <div class="card card-outline card-secondary mt-2">
                            <div class="card-header py-2">
                                <h3 class="card-title small">
                                    <i class="fas fa-network-wired mr-1 text-success"></i> IP Addresses
                                </h3>
                            </div>
                            <div class="card-body py-2">
                                @foreach($ips as $ip)
                                    <span class="badge badge-dark mr-1 mb-1 p-2" style="font-size:13px;">
                                        <i class="fas fa-circle text-success mr-1" style="font-size:8px;"></i>
                                        {{ $ip }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>
                    {{-- END TAB 1 --}}


                    {{-- ── TAB 2: Invoices ─────────────────────────── --}}
                    <div class="tab-pane fade" id="pane-invoices" role="tabpanel">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="fas fa-file-invoice mr-1"></i> Invoice History</h6>
                            <button class="btn btn-sm btn-success" id="btnNewInvoice">
                                <i class="fas fa-plus mr-1"></i> New Invoice
                            </button>
                        </div>

                        <table class="table table-sm table-striped table-hover" style="font-size:13px;">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices ?? [] as $i => $inv)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><code>{{ $inv->invoice_no }}</code></td>
                                    <td>{{ optional($inv->created_at)->format('d M Y') }}</td>
                                    <td>৳ {{ number_format($inv->amount, 2) }}</td>
                                    <td class="{{ $inv->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                                        ৳ {{ number_format($inv->due_amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'overdue' ? 'danger' : ($inv->status === 'partial' ? 'info' : 'warning')) }}">
                                            {{ ucfirst($inv->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-xs btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" class="btn btn-xs btn-secondary" title="PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-file-invoice fa-2x d-block mb-2 opacity-50"></i>
                                        No invoices yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                    {{-- END TAB 2 --}}


                    {{-- ── TAB 3: Payments ─────────────────────────── --}}
                    <div class="tab-pane fade" id="pane-payments" role="tabpanel">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="fas fa-money-bill-wave mr-1"></i> Payment History</h6>
                            <button class="btn btn-sm btn-primary" id="btnCollectPayment">
                                <i class="fas fa-hand-holding-usd mr-1"></i> Collect Payment
                            </button>
                        </div>

                        {{-- Summary Cards --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="small-box bg-success mb-0">
                                    <div class="inner">
                                        <h4>৳ {{ number_format($totalPaid ?? 0, 2) }}</h4>
                                        <p style="font-size:12px;">Total Paid</p>
                                    </div>
                                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="small-box bg-danger mb-0">
                                    <div class="inner">
                                        <h4>৳ {{ number_format($customer->balance_due, 2) }}</h4>
                                        <p style="font-size:12px;">Balance Due</p>
                                    </div>
                                    <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="small-box bg-info mb-0">
                                    <div class="inner">
                                        <h4>{{ $totalPayments ?? 0 }}</h4>
                                        <p style="font-size:12px;">Total Transactions</p>
                                    </div>
                                    <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                                </div>
                            </div>
                        </div>

                        <table class="table table-sm table-striped table-hover" style="font-size:13px;">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Method</th>
                                    <th>Trx ID</th>
                                    <th>Received By</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments ?? [] as $i => $pay)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ optional($pay->payment_date)->format('d M Y') }}</td>
                                    <td><code>{{ $pay->invoice->invoice_no ?? '—' }}</code></td>
                                    <td><span class="badge badge-secondary">{{ strtoupper($pay->method ?? '') }}</span></td>
                                    <td>{{ $pay->transaction_id ?? '—' }}</td>
                                    <td>{{ $pay->receivedBy->name ?? '—' }}</td>
                                    <td class="text-right font-weight-bold text-success">
                                        ৳ {{ number_format($pay->amount, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-money-bill-wave fa-2x d-block mb-2 opacity-50"></i>
                                        No payments yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                    {{-- END TAB 3 --}}


                    {{-- ── TAB 4: Remarks ──────────────────────────── --}}
                    <div class="tab-pane fade" id="pane-remarks" role="tabpanel">

                        <div class="row">
                            @if($customer->remarks)
                            <div class="col-md-12 mb-3">
                                <div class="callout callout-info">
                                    <h5><i class="fas fa-sticky-note mr-1"></i> Remarks / Notes</h5>
                                    <p class="mb-0">{{ $customer->remarks }}</p>
                                </div>
                            </div>
                            @endif

                            <div class="col-md-6">
                                <table class="table table-sm table-bordered" style="font-size:13px;">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light" style="width:40%">Customer Code</th>
                                            <td><code>{{ $customer->customer_code }}</code></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Created At</th>
                                            <td>{{ optional($customer->created_at)->format('d M Y, h:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Updated At</th>
                                            <td>{{ optional($customer->updated_at)->format('d M Y, h:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Created By</th>
                                            <td>{{ $customer->createdBy->name ?? '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            @if($customer->website || $customer->facebook_url || $customer->skype_id)
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered" style="font-size:13px;">
                                    <tbody>
                                        @if($customer->website)
                                        <tr>
                                            <th class="bg-light" style="width:40%">Website</th>
                                            <td><a href="{{ $customer->website }}" target="_blank">{{ $customer->website }}</a></td>
                                        </tr>
                                        @endif
                                        @if($customer->facebook_url)
                                        <tr>
                                            <th class="bg-light">Facebook</th>
                                            <td><a href="{{ $customer->facebook_url }}" target="_blank">Visit Page</a></td>
                                        </tr>
                                        @endif
                                        @if($customer->skype_id)
                                        <tr>
                                            <th class="bg-light">Skype</th>
                                            <td>{{ $customer->skype_id }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>

                    </div>
                    {{-- END TAB 4 --}}

                </div>
            </div>
        </div>
        {{-- END TABS CARD --}}

    </div>
    {{-- END RIGHT --}}

</div>
{{-- END ROW --}}


{{-- ════════════════════════════════════════════════════════════
     EDIT MODAL — single page AJAX (same 3 sections)
════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editCustomerModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i> Edit Customer — <span id="editModalCode"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body pb-2">

                {{-- Section Nav --}}
                <ul class="nav nav-pills nav-fill mb-4" id="editSectionNav">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="pill" href="#edit-sec1">
                            <i class="fas fa-user mr-1"></i> Customer Info
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#edit-sec2">
                            <i class="fas fa-network-wired mr-1"></i> Transmission
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#edit-sec3">
                            <i class="fas fa-key mr-1"></i> Login Info
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    {{-- Section 1 --}}
                    <div class="tab-pane fade show active" id="edit-sec1">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Customer Name <span class="text-danger">*</span></label>
                                    <input type="text" id="e_customer_name" class="form-control" placeholder="Company / Customer name">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Contact Person</label>
                                    <input type="text" id="e_contact_person" class="form-control" placeholder="Contact person name">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Email</label>
                                    <input type="email" id="e_email" class="form-control" placeholder="email@example.com">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" id="e_mobile_number" class="form-control" placeholder="01XXXXXXXXX">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Phone Number</label>
                                    <input type="text" id="e_phone_number" class="form-control" placeholder="Optional">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">POP Status <span class="text-danger">*</span></label>
                                    <select id="e_pop_status" class="form-control">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Reference By</label>
                                    <input type="text" id="e_reference_by" class="form-control" placeholder="Referred by">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="font-weight-bold">Address</label>
                                    <input type="text" id="e_address" class="form-control" placeholder="Full address">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Facebook URL</label>
                                    <input type="text" id="e_facebook_url" class="form-control" placeholder="https://facebook.com/...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Skype ID</label>
                                    <input type="text" id="e_skype_id" class="form-control" placeholder="skype.id">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Website</label>
                                    <input type="text" id="e_website" class="form-control" placeholder="https://...">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="font-weight-bold">Remarks / Notes</label>
                                    <textarea id="e_remarks" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2 --}}
                    <div class="tab-pane fade" id="edit-sec2">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="font-weight-bold">ATTN Info</label>
                                    <input type="text" id="e_attn_info" class="form-control" placeholder="ATTN information">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">BZR DR / NAS ID</label>
                                    <input type="text" id="e_bzr_dr_nas_id" class="form-control" placeholder="NAS ID">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Activation Date</label>
                                    <input type="date" id="e_activation_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="font-weight-bold">POP Info</label>
                                    <input type="text" id="e_pop_info" class="form-control" placeholder="POP AGRI ART BU PI">
                                </div>
                            </div>

                            {{-- VLAN rows --}}
                            <div class="col-md-12">
                                <label class="font-weight-bold">VLAN Info</label>
                                <div id="e_vlan_rows">
                                    <!-- rows injected by JS -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-info mt-1" id="e_addVlanRow">
                                    <i class="fas fa-plus mr-1"></i> Add VLAN Row
                                </button>
                            </div>

                            {{-- IP rows --}}
                            <div class="col-md-12 mt-3">
                                <label class="font-weight-bold">IP Addresses</label>
                                <div id="e_ip_rows">
                                    <!-- rows injected by JS -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success mt-1" id="e_addIpRow">
                                    <i class="fas fa-plus mr-1"></i> Add IP Row
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3 --}}
                    <div class="tab-pane fade" id="edit-sec3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Username</label>
                                    <input type="text" id="e_username" class="form-control" placeholder="Login username">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">New Password <small class="text-muted">(leave blank = no change)</small></label>
                                    <input type="password" id="e_password" class="form-control" placeholder="••••••••">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Activity Status</label>
                                    <select id="e_activity_status" class="form-control">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-warning" id="btnSaveEdit">
                    <i class="fas fa-save mr-1"></i> Update Customer
                </button>
            </div>
        </div>
    </div>
</div>

@endsection


@section('extra_css')
<style>
    .info-box { border-radius: 6px; }
    .info-box-icon { border-radius: 6px 0 0 6px; }
    .callout { border-left: 5px solid #17a2b8; padding: 15px; background: #f8f9fa; }
    .nav-pills .nav-link { border-radius: 20px; font-size: 13px; }
    .nav-pills .nav-link.active { background: #007bff; }
    #edit-sec2 .vlan-row,
    #edit-sec2 .ip-row { background: #f8f9fa; border-radius: 6px; padding: 8px 10px; margin-bottom: 6px; }
</style>
@endsection


@section('js')
<script>
const CSRF      = '{{ csrf_token() }}';
const CUST_ID   = {{ $customer->id }};
const UPDATE_URL = '/bandwidth-sale/customers/' + CUST_ID;

// ── Toast helper ──────────────────────────────────────────────
function toastOk(msg) {
    Swal.fire({ toast:true, position:'top-end', icon:'success', title: msg, showConfirmButton:false, timer:2500 });
}
function toastErr(msg) {
    Swal.fire({ toast:true, position:'top-end', icon:'error', title: msg, showConfirmButton:false, timer:3500 });
}

// ── Open Edit Modal — pre-fill data ──────────────────────────
$('#btnEdit').on('click', function () {
    // Basic
    $('#e_customer_name').val('{{ addslashes($customer->customer_name) }}');
    $('#e_contact_person').val('{{ addslashes($customer->contact_person ?? '') }}');
    $('#e_email').val('{{ $customer->email ?? '' }}');
    $('#e_mobile_number').val('{{ $customer->mobile_number }}');
    $('#e_phone_number').val('{{ $customer->phone_number ?? '' }}');
    $('#e_pop_status').val('{{ $customer->pop_status }}');
    $('#e_reference_by').val('{{ addslashes($customer->reference_by ?? '') }}');
    $('#e_address').val('{{ addslashes($customer->address ?? '') }}');
    $('#e_facebook_url').val('{{ $customer->facebook_url ?? '' }}');
    $('#e_skype_id').val('{{ $customer->skype_id ?? '' }}');
    $('#e_website').val('{{ $customer->website ?? '' }}');
    $('#e_remarks').val('{{ addslashes($customer->remarks ?? '') }}');
    $('#editModalCode').text('{{ $customer->customer_code }}');

    // Transmission
    $('#e_attn_info').val('{{ addslashes($customer->attn_info ?? '') }}');
    $('#e_bzr_dr_nas_id').val('{{ $customer->bzr_dr_nas_id ?? '' }}');
    $('#e_activation_date').val('{{ $customer->activation_date ?? '' }}');
    $('#e_pop_info').val('{{ addslashes($customer->pop_info ?? '') }}');

    // VLAN rows
    $('#e_vlan_rows').empty();
    var vlans = @json($customer->vlan_info ?? []);
    if (!Array.isArray(vlans)) vlans = [];
    if (vlans.length === 0) vlans = [{ vlan_name: '', vlan_id: '' }];
    vlans.forEach(function(v) { addVlanRow(v.vlan_name, v.vlan_id); });

    // IP rows
    $('#e_ip_rows').empty();
    var ips = @json($customer->ip_addresses ?? []);
    if (!Array.isArray(ips)) ips = [];
    if (ips.length === 0) ips = [''];
    ips.forEach(function(ip) { addIpRow(ip); });

    // Login
    $('#e_username').val('{{ $customer->username ?? '' }}');
    $('#e_password').val('');
    $('#e_activity_status').val('{{ $customer->activity_status }}');

    // Show first tab
    $('#editSectionNav a[href="#edit-sec1"]').tab('show');

    $('#editCustomerModal').modal('show');
});

// ── VLAN row helpers ──────────────────────────────────────────
function addVlanRow(name, id) {
    name = name || ''; id = id || '';
    var html = `<div class="vlan-row d-flex align-items-center mb-1">
        <input type="text" class="form-control form-control-sm mr-2 vlan-name" placeholder="VLAN Name" value="${name}" style="max-width:200px;">
        <input type="text" class="form-control form-control-sm mr-2 vlan-id"   placeholder="VLAN ID"   value="${id}"   style="max-width:150px;">
        <button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('.vlan-row').remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>`;
    $('#e_vlan_rows').append(html);
}
$('#e_addVlanRow').on('click', function() { addVlanRow('', ''); });

// ── IP row helpers ────────────────────────────────────────────
function addIpRow(ip) {
    ip = ip || '';
    var html = `<div class="ip-row d-flex align-items-center mb-1">
        <input type="text" class="form-control form-control-sm mr-2 ip-value" placeholder="e.g. 192.168.1.1" value="${ip}" style="max-width:250px;">
        <button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('.ip-row').remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>`;
    $('#e_ip_rows').append(html);
}
$('#e_addIpRow').on('click', function() { addIpRow(''); });

// ── Save Edit via AJAX ────────────────────────────────────────
$('#btnSaveEdit').on('click', function () {

    var $btn = $(this);

    // Collect VLAN data
    var vlans = [];
    $('#e_vlan_rows .vlan-row').each(function() {
        var n = $(this).find('.vlan-name').val().trim();
        var i = $(this).find('.vlan-id').val().trim();
        if (n || i) vlans.push({ vlan_name: n, vlan_id: i });
    });

    // Collect IP data
    var ips = [];
    $('#e_ip_rows .ip-row').each(function() {
        var v = $(this).find('.ip-value').val().trim();
        if (v) ips.push(v);
    });

    var payload = {
        _token:          CSRF,
        _method:         'PUT',
        customer_name:   $('#e_customer_name').val().trim(),
        contact_person:  $('#e_contact_person').val().trim(),
        email:           $('#e_email').val().trim(),
        mobile_number:   $('#e_mobile_number').val().trim(),
        phone_number:    $('#e_phone_number').val().trim(),
        pop_status:      $('#e_pop_status').val(),
        reference_by:    $('#e_reference_by').val().trim(),
        address:         $('#e_address').val().trim(),
        facebook_url:    $('#e_facebook_url').val().trim(),
        skype_id:        $('#e_skype_id').val().trim(),
        website:         $('#e_website').val().trim(),
        remarks:         $('#e_remarks').val().trim(),
        attn_info:       $('#e_attn_info').val().trim(),
        bzr_dr_nas_id:   $('#e_bzr_dr_nas_id').val().trim(),
        activation_date: $('#e_activation_date').val(),
        pop_info:        $('#e_pop_info').val().trim(),
        vlan_info:       JSON.stringify(vlans),
        ip_addresses:    JSON.stringify(ips),
        username:        $('#e_username').val().trim(),
        password:        $('#e_password').val(),
        activity_status: $('#e_activity_status').val(),
    };

    // Validate required
    if (!payload.customer_name) {
        $('#editSectionNav a[href="#edit-sec1"]').tab('show');
        toastErr('Customer Name is required.');
        return;
    }
    if (!payload.mobile_number) {
        $('#editSectionNav a[href="#edit-sec1"]').tab('show');
        toastErr('Mobile Number is required.');
        return;
    }

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');

    $.ajax({
        url:    UPDATE_URL,
        method: 'POST',
        data:   payload,
        success: function(res) {
            if (res.success) {
                $('#editCustomerModal').modal('hide');
                toastOk(res.message || 'Customer updated successfully.');
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                toastErr(res.message || 'Update failed.');
            }
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors;
            if (errors) {
                var first = Object.values(errors).flat()[0];
                toastErr(first);
            } else {
                toastErr(xhr.responseJSON?.message || 'Something went wrong.');
            }
        },
        complete: function() {
            $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Customer');
        }
    });
});
</script>
@endsection
