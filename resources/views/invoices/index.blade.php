@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@extends('layouts.app')

@section('title', 'Invoices')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Invoices</h1>
        <div>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#newInvoiceModal">
                <i class="fas fa-plus"></i> New Invoice
            </button>
            <button type="button" class="btn btn-success btn-sm ml-1" data-toggle="modal" data-target="#bulkGenerateModal">
                <i class="fas fa-cogs"></i> Bulk Generate
            </button>
        </div>
    </div>
@endsection

@section('content')

{{-- ── Stats Cards ─────────────────────────────── --}}
@php
    $paidChange     = $stats['paid_clients']['last'] > 0    ? round((($stats['paid_clients']['current']    - $stats['paid_clients']['last'])    / $stats['paid_clients']['last'])    * 100) : 0;
    $unpaidChange   = $stats['unpaid_clients']['last'] > 0  ? round((($stats['unpaid_clients']['current']  - $stats['unpaid_clients']['last'])  / $stats['unpaid_clients']['last'])  * 100) : 0;
    $receivedChange = $stats['received_bill']['last'] > 0   ? round((($stats['received_bill']['current']   - $stats['received_bill']['last'])   / $stats['received_bill']['last'])   * 100) : 0;
    $genChange      = $stats['generated_bill']['last'] > 0  ? round((($stats['generated_bill']['current']  - $stats['generated_bill']['last'])  / $stats['generated_bill']['last'])  * 100) : 0;
    $billChange     = $stats['monthly_bill']['last'] > 0    ? round((($stats['monthly_bill']['current']    - $stats['monthly_bill']['last'])    / $stats['monthly_bill']['last'])    * 100) : 0;
    $rateChange     = $stats['collection_rate']['current']  - $stats['collection_rate']['last'];
@endphp

<style>
.stat-card { border-radius:6px; color:#fff; overflow:hidden; margin-bottom:12px; }
.stat-card .sc-top { padding:10px 14px 6px; }
.stat-card .sc-label { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px; color:#fff; }
.stat-card .sc-value { font-size:28px; font-weight:700; line-height:1.2; color:#fff; }
.stat-card .sc-sub { font-size:10px; font-weight:600; margin-top:1px; color:rgba(255,255,255,.85); }
.stat-card .sc-bottom { padding:3px 12px 5px; background:rgba(0,0,0,.12); }
.sc-bars { display:flex; align-items:flex-end; gap:3px; height:16px; }
.sc-bar { flex:1; border-radius:2px 2px 0 0; background:rgba(255,255,255,.3); }
.sc-bar.now { background:rgba(255,255,255,.9); }
.sc-badge { font-size:10px; padding:2px 7px; border-radius:20px; background:rgba(255,255,255,.25); font-weight:500; }
</style>

<div class="row">

    {{-- Paid Clients --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#00a65a;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-user-check mr-1"></i> Paid Clients</div>
                    <span class="sc-badge">{{ $paidChange >= 0 ? '+' : '' }}{{ $paidChange }}%</span>
                </div>
                <div class="sc-value">{{ $stats['paid_clients']['current'] }}</div>
                <div class="sc-sub">{{-- vs {{ $stats['paid_clients']['last'] }} last month --}}</div>
            </div>
            {{-- <div class="sc-bottom">
                <div class="sc-bars">
                    <div class="sc-bar" style="height:40%"></div>
                    <div class="sc-bar" style="height:55%"></div>
                    <div class="sc-bar" style="height:50%"></div>
                    <div class="sc-bar" style="height:65%"></div>
                    <div class="sc-bar" style="height:80%"></div>
                    <div class="sc-bar now" style="height:100%"></div>
                </div>
            </div> --}}
        </div>
    </div>

    {{-- Unpaid Clients --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#dd4b39;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-user-times mr-1"></i> Unpaid Clients</div>
                    <span class="sc-badge">{{ $unpaidChange >= 0 ? '+' : '' }}{{ $unpaidChange }}%</span>
                </div>
                <div class="sc-value">{{ $stats['unpaid_clients']['current'] }}</div>
                <div class="sc-sub">{{-- vs {{ $stats['unpaid_clients']['last'] }} last month --}}</div>
            </div>
            {{-- <div class="sc-bottom">
                <div class="sc-bars">
                    <div class="sc-bar" style="height:60%"></div>
                    <div class="sc-bar" style="height:65%"></div>
                    <div class="sc-bar" style="height:70%"></div>
                    <div class="sc-bar" style="height:75%"></div>
                    <div class="sc-bar" style="height:85%"></div>
                    <div class="sc-bar now" style="height:100%"></div>
                </div>
            </div> --}}
        </div>
    </div>

    {{-- Received Invoice --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#0073b7;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-money-bill-wave mr-1"></i> Received Invoice</div>
                    <span class="sc-badge">{{ $receivedChange >= 0 ? '+' : '' }}{{ $receivedChange }}%</span>
                </div>
                <div class="sc-value">&#2547;{{ number_format($stats['received_bill']['current'], 0) }}</div>
                <div class="sc-sub">{{-- vs &#2547;{{ number_format($stats['received_bill']['last'], 0) }} last month --}}</div>
            </div>
            {{-- <div class="sc-bottom">
                <div class="sc-bars">
                    <div class="sc-bar" style="height:50%"></div>
                    <div class="sc-bar" style="height:60%"></div>
                    <div class="sc-bar" style="height:55%"></div>
                    <div class="sc-bar" style="height:70%"></div>
                    <div class="sc-bar" style="height:80%"></div>
                    <div class="sc-bar now" style="height:100%"></div>
                </div>
            </div> --}}
        </div>
    </div>

    {{-- Total Due --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#f39c12;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-exclamation-circle mr-1"></i> Total Due</div>
                    <span class="sc-badge">All time</span>
                </div>
                <div class="sc-value">&#2547;{{ number_format($stats['total_due'], 0) }}</div>
                <div class="sc-sub">{{-- All time outstanding --}}</div>
            </div>
            {{-- <div class="sc-bottom">
                <div class="sc-bars">
                    <div class="sc-bar" style="height:45%"></div>
                    <div class="sc-bar" style="height:55%"></div>
                    <div class="sc-bar" style="height:65%"></div>
                    <div class="sc-bar" style="height:75%"></div>
                    <div class="sc-bar" style="height:85%"></div>
                    <div class="sc-bar now" style="height:100%"></div>
                </div>
            </div> --}}
        </div>
    </div>

    {{-- Generated Invoice --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#00a65a;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-file-invoice mr-1"></i> Generated Invoice</div>
                    <span class="sc-badge">{{ $genChange >= 0 ? '+' : '' }}{{ $genChange }}%</span>
                </div>
                <div class="sc-value">{{ $stats['generated_bill']['current'] }}</div>
                <div class="sc-sub">{{-- vs {{ $stats['generated_bill']['last'] }} last month --}}</div>
            </div>
            {{-- <div class="sc-bottom">
                <div class="sc-bars">
                    <div class="sc-bar" style="height:55%"></div>
                    <div class="sc-bar" style="height:60%"></div>
                    <div class="sc-bar" style="height:65%"></div>
                    <div class="sc-bar" style="height:75%"></div>
                    <div class="sc-bar" style="height:85%"></div>
                    <div class="sc-bar now" style="height:100%"></div>
                </div>
            </div> --}}
        </div>
    </div>

    {{-- Advance Amount --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#dd4b39;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-wallet mr-1"></i> Advance Amount</div>
                    <span class="sc-badge">Total</span>
                </div>
                <div class="sc-value">&#2547;{{ number_format($stats['advance_amount'], 0) }}</div>
                <div class="sc-sub">{{-- Total advance balance --}}</div>
            </div>
            {{-- <div class="sc-bottom">
                <div class="sc-bars">
                    <div class="sc-bar" style="height:35%"></div>
                    <div class="sc-bar" style="height:45%"></div>
                    <div class="sc-bar" style="height:55%"></div>
                    <div class="sc-bar" style="height:70%"></div>
                    <div class="sc-bar" style="height:80%"></div>
                    <div class="sc-bar now" style="height:100%"></div>
                </div>
            </div> --}}
        </div>
    </div>

    {{-- Monthly Invoice --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#0073b7;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-chart-bar mr-1"></i> Monthly Invoice</div>
                    <span class="sc-badge">{{ $billChange >= 0 ? '+' : '' }}{{ $billChange }}%</span>
                </div>
                <div class="sc-value">&#2547;{{ number_format($stats['monthly_bill']['current'], 0) }}</div>
                <div class="sc-sub">{{-- vs &#2547;{{ number_format($stats['monthly_bill']['last'], 0) }} last month --}}</div>
            </div>
            {{-- <div class="sc-bottom">
                <div class="sc-bars">
                    <div class="sc-bar" style="height:50%"></div>
                    <div class="sc-bar" style="height:60%"></div>
                    <div class="sc-bar" style="height:65%"></div>
                    <div class="sc-bar" style="height:75%"></div>
                    <div class="sc-bar" style="height:85%"></div>
                    <div class="sc-bar now" style="height:100%"></div>
                </div>
            </div> --}}
        </div>
    </div>

    {{-- Collection Rate --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#f39c12;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-percentage mr-1"></i> Collection Rate</div>
                    <span class="sc-badge">{{ $rateChange >= 0 ? '+' : '' }}{{ $rateChange }}%</span>
                </div>
                <div class="sc-value">{{ $stats['collection_rate']['current'] }}%</div>
                <div class="sc-sub">{{-- {{ $stats['paid_clients']['current'] }} of {{ $stats['paid_clients']['current'] + $stats['unpaid_clients']['current'] }} clients paid --}}</div>
            </div>
            {{-- <div class="sc-bottom">
                <div style="background:rgba(255,255,255,.25); border-radius:3px; height:6px; margin-top:4px;">
                    <div style="width:{{ $stats['collection_rate']['current'] }}%; height:6px; background:rgba(255,255,255,.9); border-radius:3px;"></div>
                </div>
            </div> --}}
        </div>
    </div>

</div>

{{-- ── Filter ───────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-header py-2" style="cursor:pointer;" id="filterToggle">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-filter mr-1"></i> Search & Filter</h6>
            <button type="button" class="btn btn-tool">
                <i class="fas fa-minus" id="filterIcon"></i>
            </button>
        </div>
    </div>
    <div class="card-body pt-3" id="filterBody">
        <form method="GET" action="{{ route('invoices.index') }}" id="filterForm">

            {{-- Row 1 --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="col-form-label-sm font-weight-bold mb-1">Search</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control"
                            placeholder="Name / Phone / Code" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Month</label>
                    <input type="month" name="month" class="form-control form-control-sm"
                        value="{{ request('month') }}">
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Status</label>
                    <select name="status" class="form-control form-control-sm select2">
                        <option value="">All Status</option>
                        <option value="paid"    {{ request('status') == 'paid'    ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid"  {{ request('status') == 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Package</label>
                    <select name="package_id" class="form-control form-control-sm select2">
                        <option value="">All Packages</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Router</label>
                    <select name="router_id" class="form-control form-control-sm select2">
                        <option value="">All Routers</option>
                        @foreach($routers as $router)
                            <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>
                                {{ $router->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Row 2 --}}
            <div class="row">
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Bill From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                        value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Bill To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                        value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Zone</label>
                    <select name="zone_id" class="form-control form-control-sm select2">
                        <option value="">All Zones</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Connection Type</label>
                    <select name="connection_type_id" class="form-control form-control-sm select2">
                        <option value="">All Connections</option>
                        @foreach($connectionTypes as $ct)
                            <option value="{{ $ct->id }}" {{ request('connection_type_id') == $ct->id ? 'selected' : '' }}>
                                {{ $ct->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Client Type</label>
                    <select name="client_type_id" class="form-control form-control-sm select2">
                        <option value="">All Types</option>
                        @foreach($clientTypes as $ct)
                            <option value="{{ $ct->id }}" {{ request('client_type_id') == $ct->id ? 'selected' : '' }}>
                                {{ $ct->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm mr-1">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>

        </form>
    </div>
</div>

{{-- ── Bill Table ────────────────────────────── --}}
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead class="bg-light">
                <tr>
                    <th>Invoice No</th>
                    <th>Customer</th>
                    <th>Month</th>
                    <th>Amount</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td>
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-danger font-weight-bold">
                            {{ $invoice->invoice_no }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('customers.show', $invoice->customer) }}">
                            {{ $invoice->customer->name }}
                        </a>
                        <br><small class="text-muted">{{ $invoice->customer->phone }}</small>
                    </td>
                    <td>{{ $invoice->month }}</td>
                    <td>৳{{ number_format($invoice->amount, 0) }}</td>
                    <td>
                        @if($invoice->due_amount > 0)
                            <span class="text-danger font-weight-bold">৳{{ number_format($invoice->due_amount, 0) }}</span>
                        @else
                            <span class="text-success">0</span>
                        @endif
                    </td>
                    <td>
                        @if($invoice->status == 'paid')
                            <span class="badge badge-success">Paid</span>
                        @elseif($invoice->status == 'partial')
                            <span class="badge badge-warning">Partial</span>
                        @elseif($invoice->status == 'overdue')
                            <span class="badge badge-danger">Overdue</span>
                        @else
                            <span class="badge badge-secondary">Unpaid</span>
                        @endif
                    </td>
                    <td>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</td>
                    <td>
                        {{-- View --}}
                        <a href="{{ route('invoices.show', $invoice) }}"
                           class="btn btn-xs btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>

                        {{-- Pay — শুধু unpaid/partial/overdue --}}
                        @if($invoice->status !== 'paid')
                            <button type="button"
                                class="btn btn-xs btn-success pay-btn"
                                title="Pay"
                                data-invoice-id="{{ $invoice->id }}"
                                data-invoice-no="{{ $invoice->invoice_no }}"
                                data-customer="{{ $invoice->customer->name }}"
                                data-mobile="{{ $invoice->customer->phone }}"
                                data-username="{{ $invoice->customer->username ?? '-' }}"
                                data-package="{{ $invoice->package->name ?? '-' }}"
                                data-due="{{ $invoice->due_amount }}"
                                data-customer-id="{{ $invoice->customer_id }}"
                                data-toggle="modal"
                                data-target="#payModal">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                        @endif

                        {{-- PDF --}}
                        <a href="{{ route('invoices.pdf', $invoice) }}"
                           class="btn btn-xs btn-secondary" title="PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>

                        {{-- Delete — শুধু unpaid --}}
                        @if($invoice->status === 'unpaid')
                            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Delete this bill?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No bills found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer">
        {{ $invoices->links() }}
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════ --}}

{{-- ── New Invoice Modal ──────────────────────── --}}
<div class="modal fade" id="newInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-file-bill mr-1"></i> New Invoice</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('invoices.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-control select2" required>
                            <option value="">— Select Customer —</option>
                            @foreach(\App\Models\Customer::active()->get() as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Month <span class="text-danger">*</span></label>
                                <input type="month" name="month" class="form-control"
                                    value="{{ date('Y-m') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" name="due_date" class="form-control"
                                    value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control"
                                    step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Discount</label>
                                <input type="number" name="discount" class="form-control"
                                    step="0.01" min="0" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save mr-1"></i> Create Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Bulk Generate Modal ─────────────────────── --}}
<div class="modal fade" id="bulkGenerateModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="fas fa-cogs mr-1"></i> Bulk Generate Invoices</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('invoices.bulk-generate') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Select Month</label>
                        <input type="month" name="month" class="form-control"
                            value="{{ date('Y-m') }}" required>
                        <small class="text-muted">Invoices will be generated for all active customers.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-cogs mr-1"></i> Generate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Pay Modal ───────────────────────────────── --}}
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-money-bill-wave mr-1"></i> Collect Payment</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="payForm" method="POST">
                @csrf
                <div class="modal-body">

                    {{-- Customer Info --}}
                    <div class="bg-light rounded p-2 mb-3">
                        <div class="row" style="font-size:13px;">
                            <div class="col-md-6">
                                <span class="text-muted">Customer:</span>
                                <strong id="pay_customer"></strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted">Mobile:</span>
                                <span id="pay_mobile"></span>
                            </div>
                            <div class="col-md-6 mt-1">
                                <span class="text-muted">Username:</span>
                                <span id="pay_username"></span>
                            </div>
                            <div class="col-md-6 mt-1">
                                <span class="text-muted">Package:</span>
                                <span id="pay_package"></span>
                            </div>
                            <div class="col-md-6 mt-1">
                                <span class="text-muted">Bill:</span>
                                <span id="pay_invoice_no"></span>
                            </div>
                            <div class="col-md-6 mt-1">
                                <span class="text-muted">Total Due (All Invoices):</span>
                                <strong class="text-danger" id="pay_due"></strong>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" class="form-control form-control-sm"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select name="method" class="form-control form-control-sm" required>
                                    <option value="cash">Cash</option>
                                    <option value="bkash">bKash</option>
                                    <option value="nagad">Nagad</option>
                                    <option value="rocket">Rocket</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="card">Card</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Received By</label>
                                <select name="received_by" class="form-control form-control-sm select2">
                                    <option value="">— Select —</option>
                                    @foreach(\App\Models\User::all() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Transaction / Receipt No</label>
                                <input type="text" name="transaction_id" class="form-control form-control-sm"
                                    placeholder="Optional">
                            </div>
                        </div>
                    </div>

                    {{-- Amount Table --}}
                    <table class="table table-sm table-bordered mb-2">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>Details</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Payable Amount</td>
                                <td class="text-right" id="pay_payable">0.00</td>
                            </tr>
                            <tr>
                                <td>Discount</td>
                                <td class="text-right">
                                    <input type="number" name="discount" id="pay_discount"
                                        class="form-control form-control-sm text-right" value="0" min="0"
                                        style="width:100px; float:right;">
                                </td>
                            </tr>
                            <tr>
                                <td>Received Amount</td>
                                <td class="text-right">
                                    <input type="number" name="amount" id="pay_amount"
                                        class="form-control form-control-sm text-right" min="1" required
                                        style="width:100px; float:right;">
                                </td>
                            </tr>
                            <tr id="pay_balance_row" class="table-danger">
                                <td><strong>Balance Due</strong></td>
                                <td class="text-right"><strong id="pay_balance_due">0.00</strong></td>
                            </tr>
                            <tr id="pay_advance_row" class="table-success d-none">
                                <td><strong><i class="fas fa-wallet mr-1"></i> Advance to Wallet</strong></td>
                                <td class="text-right text-success"><strong id="pay_advance_to_wallet">0.00</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="form-group">
                        <label>Remarks / Note</label>
                        <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Optional">
                    </div>

                    <div class="d-flex" style="gap:20px;">
                        <div class="form-check">
                            <input type="checkbox" name="set_next_billing_date" value="1"
                                class="form-check-input" id="setNextBilling" checked>
                            <label class="form-check-label" for="setNextBilling">Set next billing date</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="send_sms" value="1"
                                class="form-check-input" id="sendSms" checked>
                            <label class="form-check-label" for="sendSms">Send SMS</label>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save mr-1"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {

    // Pay button click — modal populate
    $('.pay-btn').on('click', function () {
        var btn        = $(this);
        var invoiceId  = btn.data('invoice-id');
        var customerId = btn.data('customer-id');

        $('#pay_customer').text(btn.data('customer'));
        $('#pay_mobile').text(btn.data('mobile'));
        $('#pay_username').text(btn.data('username'));
        $('#pay_package').text(btn.data('package'));
        $('#pay_invoice_no').text(btn.data('invoice-no'));
        $('#pay_discount').val(0);
        $('#pay_balance_due').text('0.00');
        $('#pay_advance_row').addClass('d-none');

        // Form action set
        $('#payForm').attr('action', '/payments/invoice/' + invoiceId);

        // Fetch total due via AJAX
        $.get('/payments/customer-due/' + customerId, function (data) {
            var totalDue = parseFloat(data.total_due).toFixed(2);
            $('#pay_due').text('BDT ' + totalDue);
            $('#pay_payable').text(totalDue);
            $('#pay_amount').val(totalDue);
        });
    });

    // Balance due auto-calculate
    $('#pay_amount, #pay_discount').on('input', function () {
        var payable  = parseFloat($('#pay_payable').text()) || 0;
        var amount   = parseFloat($('#pay_amount').val()) || 0;
        var discount = parseFloat($('#pay_discount').val()) || 0;
        var net      = payable - discount;
        var balance  = net - amount;

        if (balance > 0) {
            // Due আছে
            $('#pay_balance_due').text(balance.toFixed(2));
            $('#pay_balance_row').removeClass('table-success').addClass('table-danger');
            $('#pay_advance_row').addClass('d-none');
            $('#pay_advance_to_wallet').text('0.00');
        } else if (balance < 0) {
            // Extra টাকা — advance এ যাবে
            $('#pay_balance_due').text('0.00');
            $('#pay_balance_row').removeClass('table-danger').addClass('table-success');
            $('#pay_advance_to_wallet').text(Math.abs(balance).toFixed(2));
            $('#pay_advance_row').removeClass('d-none');
        } else {
            // Exact
            $('#pay_balance_due').text('0.00');
            $('#pay_balance_row').removeClass('table-danger').addClass('table-success');
            $('#pay_advance_row').addClass('d-none');
        }
    });

    // Filter toggle
    $('#filterToggle').on('click', function () {
        $('#filterBody').slideToggle();
        $('#filterIcon').toggleClass('fa-minus fa-plus');
    });

    // Select2 init
    $('.select2').select2({ width: '100%' });

});
</script>
@endpush