{{-- resources/views/invoices/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Invoices')

@section('page_actions')
    {{-- Bulk Generate (monthly only) --}}
    @if($billingType !== 'date_to_date')
    <button class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#bulkGenerateModal">
        <i class="fas fa-magic mr-1"></i> Bulk Generate
    </button>
    @endif

    <button class="btn btn-primary btn-sm mr-1" data-toggle="modal" data-target="#createInvoiceModal">
        <i class="fas fa-plus mr-1"></i> New Invoice
    </button>

    <div class="btn-group">
        <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
            <i class="fas fa-download mr-1"></i> Export
        </button>
        <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="{{ route('invoices.bulk-xlsx') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}">
                <i class="fas fa-file-excel mr-1 text-success"></i> Excel (XLSX)
            </a>
            <a class="dropdown-item" href="{{ route('invoices.bulk-pdf') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}">
                <i class="fas fa-file-pdf mr-1 text-danger"></i> PDF
            </a>
        </div>
    </div>
@endsection

@section('page_content')

{{-- ══ STAT CARDS ══════════════════════════════════════════════════════════ --}}
<div class="row mb-3">

    {{-- Paid Clients --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="small-box bg-success mb-0">
            <div class="inner">
                <h4>{{ $stats['paid_clients']['current'] }}</h4>
                <p>Paid Clients
                    @if($stats['paid_clients']['last'] > 0)
                        <small class="opacity-75">/ {{ $stats['paid_clients']['last'] }} last mo.</small>
                    @endif
                </p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('invoices.index', array_merge(request()->query(), ['status'=>'paid','month'=>now()->format('Y-m')])) }}" class="small-box-footer">
                View <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Unpaid Clients --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="small-box bg-warning mb-0">
            <div class="inner">
                <h4>{{ $stats['unpaid_clients']['current'] }}</h4>
                <p>Unpaid / Overdue
                    @if($stats['unpaid_clients']['last'] > 0)
                        <small class="opacity-75">/ {{ $stats['unpaid_clients']['last'] }} last mo.</small>
                    @endif
                </p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
            <a href="{{ route('invoices.index', array_merge(request()->query(), ['status'=>'unpaid'])) }}" class="small-box-footer">
                View <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Received Bill --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="small-box bg-info mb-0">
            <div class="inner">
                <h4>৳ {{ number_format($stats['received_bill']['current']) }}</h4>
                <p>Received This Month
                    @if($stats['received_bill']['last'] > 0)
                        <small class="opacity-75">/ ৳{{ number_format($stats['received_bill']['last']) }} last</small>
                    @endif
                </p>
            </div>
            <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
            <a href="{{ route('payments.index') }}" class="small-box-footer">
                Payments <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Total Due --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="small-box bg-danger mb-0">
            <div class="inner">
                <h4>৳ {{ number_format($stats['total_due']) }}</h4>
                <p>Total Outstanding Due</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            <a href="{{ route('invoices.index', ['status'=>'overdue']) }}" class="small-box-footer">
                Overdue <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Generated Invoices --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="small-box bg-secondary mb-0">
            <div class="inner">
                <h4>{{ $stats['generated_bill']['current'] }}</h4>
                <p>Generated This Month</p>
            </div>
            <div class="icon"><i class="fas fa-file-invoice"></i></div>
            <a href="{{ route('invoices.index', ['month'=>now()->format('Y-m')]) }}" class="small-box-footer">
                View <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Advance Balance --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="small-box mb-0" style="background:#6f42c1;color:#fff;">
            <div class="inner">
                <h4>৳ {{ number_format($stats['advance_amount']) }}</h4>
                <p>Total Advance Balance</p>
            </div>
            <div class="icon"><i class="fas fa-piggy-bank"></i></div>
            <a href="#" class="small-box-footer" style="background:rgba(0,0,0,.1);">
                All Customers <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Monthly Bill --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="small-box mb-0" style="background:#17a2b8;color:#fff;">
            <div class="inner">
                <h4>৳ {{ number_format($stats['monthly_bill']['current']) }}</h4>
                <p>Monthly Bill Amount</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
            <a href="#" class="small-box-footer" style="background:rgba(0,0,0,.1);">
                This Month <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Collection Rate --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="small-box mb-0" style="background:#20c997;color:#fff;">
            <div class="inner">
                <h4>{{ $stats['collection_rate']['current'] }}%</h4>
                <p>Collection Rate
                    @if($stats['collection_rate']['last'] > 0)
                        <small class="opacity-75">/ {{ $stats['collection_rate']['last'] }}% last mo.</small>
                    @endif
                </p>
            </div>
            <div class="icon"><i class="fas fa-chart-pie"></i></div>
            <a href="{{ route('reports.revenue') }}" class="small-box-footer" style="background:rgba(0,0,0,.1);">
                Revenue Report <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

</div>

{{-- ══ FILTER ═══════════════════════════════════════════════════════════ --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-header py-2" id="filterToggle" style="cursor:pointer;">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 font-weight-bold">
                <i class="fas fa-filter mr-1 text-info"></i> Filter & Search
                @if(request()->hasAny(['status','month','package_id','router_id','zone_id','sub_zone_id','connection_type_id','client_type_id','search','date_from','date_to']))
                    <span class="badge badge-info ml-1">Active</span>
                @endif
            </h6>
            <i class="fas fa-chevron-up text-muted" id="filterChevron"></i>
        </div>
    </div>
    <div class="card-body pt-3 pb-2" id="filterBody">
        <form method="GET" action="{{ route('invoices.index') }}" id="filterForm">
            <div class="row">

                <div class="col-md-2 col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Month</label>
                        <input type="month" name="month" class="form-control form-control-sm"
                               value="{{ request('month') }}" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            @foreach(['paid'=>'Paid','unpaid'=>'Unpaid','partial'=>'Partial','overdue'=>'Overdue'] as $v=>$l)
                                <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Package</label>
                        <select name="package_id" class="form-control form-control-sm">
                            <option value="">All Packages</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" {{ request('package_id')==$pkg->id?'selected':'' }}>
                                    {{ $pkg->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Zone</label>
                        <select name="zone_id" class="form-control form-control-sm">
                            <option value="">All Zones</option>
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}" {{ request('zone_id')==$z->id?'selected':'' }}>
                                    {{ $z->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Router</label>
                        <select name="router_id" class="form-control form-control-sm">
                            <option value="">All Routers</option>
                            @foreach($routers as $r)
                                <option value="{{ $r->id }}" {{ request('router_id')==$r->id?'selected':'' }}>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Connection Type</label>
                        <select name="connection_type_id" class="form-control form-control-sm">
                            <option value="">All Types</option>
                            @foreach($connectionTypes as $ct)
                                <option value="{{ $ct->id }}" {{ request('connection_type_id')==$ct->id?'selected':'' }}>
                                    {{ $ct->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Client Type</label>
                        <select name="client_type_id" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($clientTypes as $cl)
                                <option value="{{ $cl->id }}" {{ request('client_type_id')==$cl->id?'selected':'' }}>
                                    {{ $cl->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                               value="{{ request('date_from') }}">
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                               value="{{ request('date_to') }}">
                    </div>
                </div>

                <div class="col-md-4 col-12">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Name / Phone..." value="{{ request('search') }}">
                    </div>
                </div>

            </div>

            <div class="mt-1 d-flex align-items-center">
                <button type="submit" class="btn btn-sm btn-primary mr-1">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-secondary mr-1">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>

                {{-- Bulk SMS --}}
                <button type="button" class="btn btn-sm btn-warning mr-1" id="btnBulkSms">
                    <i class="fas fa-sms mr-1"></i> Bulk SMS
                </button>

                {{-- Bulk Delete --}}
                <button type="button" class="btn btn-sm btn-danger" id="btnBulkDelete">
                    <i class="fas fa-trash mr-1"></i> Delete Selected
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ TABLE ═══════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0 font-weight-bold">
            <i class="fas fa-file-invoice mr-1 text-orange"></i> Invoice List
            <span class="badge badge-info ml-1">{{ $invoices->total() }}</span>
        </h6>
        <div class="d-flex align-items-center">
            <input type="text" id="tableSearch" class="form-control form-control-sm mr-2"
                   placeholder="Quick search..." style="width:180px;" autocomplete="off">
            <select id="perPage" class="form-control form-control-sm" style="width:70px;">
                @foreach([20,50,100,200] as $pp)
                    <option value="{{ $pp }}" {{ request('per_page',20)==$pp?'selected':'' }}>{{ $pp }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="invTable">
                <thead class="thead-light">
                    <tr>
                        <th style="width:32px;">
                            <input type="checkbox" id="checkAll" title="Select All">
                        </th>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Customer</th>
                        <th>Package</th>
                        <th>Month</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Due</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-center" style="width:130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $i => $invoice)
                    <tr>
                        <td>
                            <input type="checkbox" class="rowCheck" value="{{ $invoice->id }}">
                        </td>
                        <td class="text-muted small">{{ $invoices->firstItem() + $i }}</td>
                        <td>
                            <a href="{{ route('invoices.show', $invoice) }}" class="font-weight-bold text-dark">
                                {{ $invoice->invoice_no }}
                            </a>
                            @if($invoice->billing_type === 'date_to_date')
                                <br><small class="text-muted">
                                    {{ $invoice->period_start?->format('d M') ?? '' }}
                                    — {{ $invoice->period_end?->format('d M Y') ?? '' }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('customers.show', $invoice->customer_id) }}" class="text-dark">
                                {{ $invoice->customer->name ?? '—' }}
                            </a>
                            <br><small class="text-muted">{{ $invoice->customer->phone ?? '' }}</small>
                        </td>
                        <td>
                            <small>{{ $invoice->package->name ?? '—' }}</small>
                        </td>
                        <td>{{ $invoice->month }}</td>
                        <td class="text-right">৳ {{ number_format($invoice->amount) }}</td>
                        <td class="text-right {{ $invoice->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                            ৳ {{ number_format($invoice->due_amount) }}
                        </td>
                        <td>
                            @if($invoice->due_date)
                                <span class="{{ $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-danger' : '' }}">
                                    {{ $invoice->due_date->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeMap = [
                                    'paid'    => 'success',
                                    'partial' => 'info',
                                    'unpaid'  => 'warning',
                                    'overdue' => 'danger',
                                ];
                                $badge = $badgeMap[$invoice->status] ?? 'secondary';
                            @endphp
                            <span class="badge badge-{{ $badge }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="text-center" style="white-space:nowrap;">
                            {{-- View --}}
                            <a href="{{ route('invoices.show', $invoice) }}"
                               class="btn btn-xs btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>

                            {{-- Pay --}}
                            @if($invoice->status !== 'paid')
                            <button class="btn btn-xs btn-success btnPay"
                                    data-id="{{ $invoice->id }}"
                                    data-no="{{ $invoice->invoice_no }}"
                                    data-due="{{ $invoice->due_amount }}"
                                    data-customer="{{ $invoice->customer->name ?? '' }}"
                                    title="Collect Payment">
                                <i class="fas fa-hand-holding-usd"></i>
                            </button>
                            @endif

                            {{-- PDF --}}
                            <a href="{{ route('invoices.pdf', $invoice) }}"
                               class="btn btn-xs btn-secondary" title="Download PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>

                            {{-- Delete --}}
                            @if($invoice->status !== 'paid')
                            <button class="btn btn-xs btn-danger btnDelete"
                                    data-id="{{ $invoice->id }}"
                                    data-no="{{ $invoice->invoice_no }}"
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="fas fa-file-invoice fa-2x d-block mb-2 text-muted"></i>
                            No invoices found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($invoices->hasPages())
    <div class="card-footer py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <small class="text-muted mb-1">
                Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }}
            </small>
            {{ $invoices->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: PAY INVOICE
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title">
                    <i class="fas fa-hand-holding-usd mr-1"></i>
                    Collect Payment — <span id="pm_no"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" id="payForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        Customer: <strong id="pm_customer"></strong> &nbsp;|&nbsp;
                        Due: <strong class="text-danger" id="pm_due"></strong>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="pm_amount" class="form-control form-control-sm"
                                       step="0.01" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Method <span class="text-danger">*</span></label>
                                <select name="method" class="form-control form-control-sm" required>
                                    @foreach(['cash'=>'Cash','bkash'=>'bKash','nagad'=>'Nagad','rocket'=>'Rocket','card'=>'Card','bank'=>'Bank Transfer','advance'=>'Advance Balance'] as $v=>$l)
                                        <option value="{{ $v }}">{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control form-control-sm"
                                       value="{{ now()->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Discount</label>
                                <input type="number" name="discount" class="form-control form-control-sm"
                                       step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Transaction ID</label>
                                <input type="text" name="transaction_id" class="form-control form-control-sm"
                                       placeholder="Optional">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Remarks</label>
                                <input type="text" name="remarks" class="form-control form-control-sm"
                                       placeholder="Optional">
                            </div>
                        </div>
                    </div>
                    <div class="form-check mt-1">
                        <input type="checkbox" name="send_sms" value="1" class="form-check-input" id="pm_sms">
                        <label class="form-check-label small" for="pm_sms">Send SMS to customer</label>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success btn-sm" id="btnPaySubmit">
                        <i class="fas fa-save mr-1"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: CREATE INVOICE
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="createInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-1"></i> Create Invoice
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('invoices.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-control form-control-sm select2" required>
                            <option value="">— Select Customer —</option>
                            {{-- Populated via select2 AJAX or static list --}}
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Month <span class="text-danger">*</span></label>
                                <input type="month" name="month" class="form-control form-control-sm"
                                       value="{{ now()->format('Y-m') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control form-control-sm"
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Due Date</label>
                                <input type="date" name="due_date" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Discount</label>
                                <input type="number" name="discount" class="form-control form-control-sm"
                                       step="0.01" min="0" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Notes</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save mr-1"></i> Create Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: BULK GENERATE
══════════════════════════════════════════════════════ --}}
@if($billingType !== 'date_to_date')
<div class="modal fade" id="bulkGenerateModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title">
                    <i class="fas fa-magic mr-1"></i> Bulk Generate Invoices
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('invoices.bulk-generate') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small">
                        <i class="fas fa-info-circle mr-1"></i>
                        This will generate invoices for all active customers for the selected month.
                        Existing invoices will be skipped.
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Month <span class="text-danger">*</span></label>
                        <input type="month" name="month" class="form-control"
                               value="{{ now()->format('Y-m') }}" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-magic mr-1"></i> Generate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     FORM: BULK DELETE (hidden)
══════════════════════════════════════════════════════ --}}
<form method="POST" action="{{ route('invoices.bulk-delete') }}" id="bulkDeleteForm">
    @csrf
    <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

{{-- ══════════════════════════════════════════════════════
     FORM: BULK SMS (hidden)
══════════════════════════════════════════════════════ --}}
<form method="POST" action="{{ route('invoices.bulk-sms') }}" id="bulkSmsForm">
    @csrf
    <input type="hidden" name="ids" id="bulkSmsIds">
</form>

@endsection


@section('extra_css')
<style>
    .small-box .inner h4 { font-size: 1.5rem; font-weight: 700; }
    .opacity-75 { opacity: .75; }
    #invTable td, #invTable th { vertical-align: middle; font-size: 13px; }
    .btn-xs { padding: 2px 6px; font-size: 11px; }
</style>
@endsection


@section('js')
<script>
const CSRF = '{{ csrf_token() }}';

$(function () {

    // ── Filter collapse ────────────────────────
    @if(!request()->hasAny(['status','month','package_id','router_id','zone_id','sub_zone_id','connection_type_id','client_type_id','search','date_from','date_to']))
    $('#filterBody').hide();
    $('#filterChevron').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    @endif

    $('#filterToggle').on('click', function () {
        $('#filterBody').slideToggle(200);
        $('#filterChevron').toggleClass('fa-chevron-up fa-chevron-down');
    });

    // ── Per page ──────────────────────────────
    $('#perPage').on('change', function () {
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', $(this).val());
        window.location.href = url.toString();
    });

    // ── Quick search ──────────────────────────
    $('#tableSearch').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#invTable tbody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(val));
        });
    });

    // ── Check all ─────────────────────────────
    $('#checkAll').on('change', function () {
        $('.rowCheck').prop('checked', this.checked);
    });

    // ── Pay button ────────────────────────────
    $(document).on('click', '.btnPay', function () {
        var id  = $(this).data('id');
        var due = parseFloat($(this).data('due'));
        $('#pm_no').text($(this).data('no'));
        $('#pm_customer').text($(this).data('customer'));
        $('#pm_due').text('৳ ' + due.toLocaleString('en-BD', {minimumFractionDigits:2}));
        $('#pm_amount').val(due);
        $('#payForm').attr('action', '/payments/invoice/' + id);
        $('#payModal').modal('show');
    });

    // ── Pay form submit ───────────────────────
    $('#payForm').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btnPaySubmit').prop('disabled', true)
                       .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
        $.ajax({
            url:    $(this).attr('action'),
            method: 'POST',
            data:   $(this).serialize(),
            success: function (res) {
                $('#payModal').modal('hide');
                toastOk('Payment saved successfully.');
                setTimeout(() => window.location.reload(), 1000);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Something went wrong.';
                toastErr(msg);
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Payment');
            }
        });
    });

    // ── Delete single ─────────────────────────
    $(document).on('click', '.btnDelete', function () {
        var id = $(this).data('id');
        var no = $(this).data('no');
        Swal.fire({
            title: 'Delete Invoice?',
            text:  'Invoice ' + no + ' will be permanently deleted.',
            icon:  'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete',
        }).then(res => {
            if (res.isConfirmed) {
                var form = $('<form method="POST" action="/invoices/' + id + '">'
                    + '@csrf @method("DELETE")</form>');
                $('body').append(form);
                form.submit();
            }
        });
    });

    // ── Bulk Delete ───────────────────────────
    $('#btnBulkDelete').on('click', function () {
        var ids = getCheckedIds();
        if (!ids.length) { toastErr('কোনো invoice select করা হয়নি।'); return; }
        Swal.fire({
            title: ids.length + ' invoice delete করবেন?',
            text:  'Paid invoices automatically skip হবে।',
            icon:  'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'হ্যাঁ, Delete করুন',
        }).then(res => {
            if (res.isConfirmed) {
                $('#bulkDeleteIds').val(ids.join(','));
                $('#bulkDeleteForm').submit();
            }
        });
    });

    // ── Bulk SMS ──────────────────────────────
    $('#btnBulkSms').on('click', function () {
        var ids = getCheckedIds();
        if (!ids.length) { toastErr('কোনো invoice select করা হয়নি।'); return; }
        Swal.fire({
            title: ids.length + ' customer কে SMS পাঠাবেন?',
            icon:  'question',
            showCancelButton: true,
            confirmButtonText: 'হ্যাঁ, পাঠান',
        }).then(res => {
            if (res.isConfirmed) {
                $('#bulkSmsIds').val(ids.join(','));
                $('#bulkSmsForm').submit();
            }
        });
    });

});

// ── Helpers ───────────────────────────────────
function getCheckedIds() {
    return $('.rowCheck:checked').map(function () { return $(this).val(); }).get();
}

function toastOk(msg) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ toast:true, position:'top-end', icon:'success', title:msg,
                    showConfirmButton:false, timer:2500 });
    } else { alert(msg); }
}

function toastErr(msg) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ toast:true, position:'top-end', icon:'error', title:msg,
                    showConfirmButton:false, timer:3500 });
    } else { alert(msg); }
}
</script>
@endsection
