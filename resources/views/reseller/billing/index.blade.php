@extends('reseller.layouts.app')

@section('title', 'Billing')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Billing</h4>
    <div>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#newInvoiceModal">
            <i class="fas fa-plus"></i> New Invoice
        </button>
        <button type="button" class="btn btn-success btn-sm ml-1" data-toggle="modal" data-target="#bulkGenerateModal">
            <i class="fas fa-cogs"></i> Bulk Generate
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- ── Stats Cards ─────────────────────────────── --}}
@php
    $paidChange     = $stats['paid_clients']['last'] > 0    ? round((($stats['paid_clients']['current']    - $stats['paid_clients']['last'])    / $stats['paid_clients']['last'])    * 100) : 0;
    $unpaidChange   = $stats['unpaid_clients']['last'] > 0  ? round((($stats['unpaid_clients']['current']  - $stats['unpaid_clients']['last'])  / $stats['unpaid_clients']['last'])  * 100) : 0;
    $receivedChange = $stats['received_bill']['last'] > 0   ? round((($stats['received_bill']['current']   - $stats['received_bill']['last'])   / $stats['received_bill']['last'])   * 100) : 0;
    $genChange      = $stats['generated_bill']['last'] > 0  ? round((($stats['generated_bill']['current']  - $stats['generated_bill']['last'])  / $stats['generated_bill']['last'])  * 100) : 0;
    $billChange     = $stats['monthly_bill']['last'] > 0    ? round((($stats['monthly_bill']['current']    - $stats['monthly_bill']['last'])    / $stats['monthly_bill']['last'])    * 100) : 0;
@endphp

<style>
.stat-card { border-radius:6px; color:#fff; overflow:hidden; margin-bottom:12px; }
.stat-card .sc-top { padding:10px 14px 6px; }
.stat-card .sc-label { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px; color:#fff; }
.stat-card .sc-value { font-size:28px; font-weight:700; line-height:1.2; color:#fff; }
.sc-badge { font-size:10px; padding:2px 7px; border-radius:20px; background:rgba(255,255,255,.25); font-weight:500; }
</style>

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#00a65a;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-user-check mr-1"></i> Paid Clients</div>
                    <span class="sc-badge">{{ $paidChange >= 0 ? '+' : '' }}{{ $paidChange }}%</span>
                </div>
                <div class="sc-value">{{ $stats['paid_clients']['current'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#dd4b39;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-user-times mr-1"></i> Unpaid Clients</div>
                    <span class="sc-badge">{{ $unpaidChange >= 0 ? '+' : '' }}{{ $unpaidChange }}%</span>
                </div>
                <div class="sc-value">{{ $stats['unpaid_clients']['current'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#0073b7;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-money-bill-wave mr-1"></i> Received</div>
                    <span class="sc-badge">{{ $receivedChange >= 0 ? '+' : '' }}{{ $receivedChange }}%</span>
                </div>
                <div class="sc-value">৳{{ number_format($stats['received_bill']['current'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#f39c12;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-exclamation-circle mr-1"></i> Total Due</div>
                    <span class="sc-badge">All time</span>
                </div>
                <div class="sc-value">৳{{ number_format($stats['total_due'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#00a65a;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-file-invoice mr-1"></i> Generated</div>
                    <span class="sc-badge">{{ $genChange >= 0 ? '+' : '' }}{{ $genChange }}%</span>
                </div>
                <div class="sc-value">{{ $stats['generated_bill']['current'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#dd4b39;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-wallet mr-1"></i> Advance</div>
                    <span class="sc-badge">Total</span>
                </div>
                <div class="sc-value">৳{{ number_format($stats['advance_amount'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#0073b7;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-chart-bar mr-1"></i> Monthly Bill</div>
                    <span class="sc-badge">{{ $billChange >= 0 ? '+' : '' }}{{ $billChange }}%</span>
                </div>
                <div class="sc-value">৳{{ number_format($stats['monthly_bill']['current'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background:#f39c12;">
            <div class="sc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="sc-label"><i class="fas fa-percentage mr-1"></i> Collection Rate</div>
                </div>
                <div class="sc-value">{{ $stats['collection_rate']['current'] }}%</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filter ───────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-header py-2" style="cursor:pointer;" id="filterToggle">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-filter mr-1"></i> Search & Filter</h6>
            <button type="button" class="btn btn-tool"><i class="fas fa-minus" id="filterIcon"></i></button>
        </div>
    </div>
    <div class="card-body pt-3" id="filterBody">
        <form method="GET" action="{{ route('reseller.billing.index') }}" id="filterForm">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="col-form-label-sm font-weight-bold mb-1">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Name / Phone / Code / Invoice No" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Month</label>
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}">
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
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>{{ $pkg->package->name ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="col-form-label-sm font-weight-bold mb-1">Zone</label>
                    <select name="zone_id" class="form-control form-control-sm select2">
                        <option value="">All Zones</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-search"></i> Search</button>
                    <a href="{{ route('reseller.billing.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-redo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Bulk Actions + Per Page ─────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <div id="bulkActions" class="d-none">
        <button type="button" class="btn btn-danger btn-sm mr-1" id="bulkDelete">
            <i class="fas fa-trash mr-1"></i> Delete Selected
        </button>
    </div>
    <div class="ml-auto d-flex align-items-center">
        <label class="mr-2 mb-0 text-muted" style="font-size:13px;">Show</label>
        <select id="perPage" class="form-control form-control-sm" style="width:80px;">
            <option value="20"  {{ request('per_page', 20) == 20  ? 'selected' : '' }}>20</option>
            <option value="50"  {{ request('per_page', 20) == 50  ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
        </select>
        <label class="ml-2 mb-0 text-muted" style="font-size:13px;">records</label>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead class="bg-light">
                <tr>
                    <th style="width:30px;"><input type="checkbox" id="selectAll"></th>
                    <th>Invoice No</th>
                    <th>Customer</th>
                    <th>Month</th>
                    <th>Amount</th>
                    <th>Discount</th>
                    <th>Due</th>
                    <th>Total Due</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td><input type="checkbox" class="invoice-check" value="{{ $invoice->id }}" data-status="{{ $invoice->status }}"></td>
                    <td><a href="{{ route('reseller.billing.show', $invoice) }}" class="text-danger font-weight-bold">{{ $invoice->invoice_no }}</a></td>
                    <td>
                        {{ $invoice->customer->name ?? '—' }}
                        <br><small class="text-muted">{{ $invoice->customer->phone ?? '' }}</small>
                        <br><small class="text-muted text-primary">{{ $invoice->customer->customer_code ?? '' }}</small>
                    </td>
                    <td><small class="text-muted">{{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->month)->format('F Y') }}</small></td>
                    <td>৳{{ number_format($invoice->amount, 0) }}</td>
                    <td>
                        @if($invoice->discount > 0)
                            <span class="text-warning">৳{{ number_format($invoice->discount, 0) }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($invoice->due_amount > 0)
                            {{ number_format($invoice->due_amount, 0) }}
                        @else
                            <span class="text-success">0</span>
                        @endif
                    </td>
                    <td><span class="text-danger font-weight-bold">৳{{ number_format($totalDueMap[$invoice->customer_id] ?? 0, 2) }}</span></td>
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
                        @if($invoice->status !== 'paid')
                            <a href="{{ route('reseller.payment.collect') }}?customer_id={{ $invoice->customer_id }}" class="btn btn-sm btn-success" title="Pay Now">
                                <i class="fas fa-money-bill-wave mr-1"></i> Pay
                            </a>
                        @endif
                        <a href="{{ route('reseller.billing.show', $invoice) }}" class="btn btn-sm btn-info" title="View"><i class="fas fa-file-invoice mr-1"></i> View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center text-muted py-4">No invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} records</small>
        {{ $invoices->appends(request()->query())->links() }}
    </div>
</div>

{{-- ── New Invoice Modal ──────────────────────── --}}
<div class="modal fade" id="newInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-file-invoice mr-1"></i> New Invoice</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="newInvoiceForm" action="{{ route('reseller.billing.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="invoiceError" class="alert alert-danger d-none"></div>
                    <div class="form-group">
                        <label>Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="inv_customer" class="form-control select2" required>
                            <option value="">— Select Customer —</option>
                            @foreach(\App\Models\Customer::forReseller(auth('mac_reseller')->id())->get() as $c)
                                <option value="{{ $c->id }}"
                                    data-price="{{ $c->monthly_bill_amount ?? 0 }}"
                                    data-advance="{{ $c->advance_balance ?? 0 }}">
                                    {{ $c->name }} ({{ $c->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="inv_customer_info" class="d-none">
                        <div class="bg-light rounded px-3 py-2 mb-3" style="font-size:13px;">
                            <span class="text-muted">Total Due:</span> <strong class="text-danger" id="inv_total_due"></strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Month <span class="text-danger">*</span></label>
                                <input type="month" name="month" class="form-control" value="{{ date('Y-m') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" name="due_date" class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="inv_amount" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Discount</label>
                                <input type="number" name="discount" id="inv_discount" class="form-control" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Payable Amount</label>
                                <input type="text" id="inv_payable" class="form-control" readonly style="background:#e9f7ef; color:#155724; font-weight:600;">
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
                    <button type="submit" class="btn btn-primary btn-sm" id="newInvoiceSubmit"><i class="fas fa-save mr-1"></i> Create Invoice</button>
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
            <form action="{{ route('reseller.billing.bulk-generate') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Select Month</label>
                        <input type="month" name="month" class="form-control" value="{{ date('Y-m') }}" required>
                        <small class="text-muted">Invoices will be generated for all your active clients (using each client's Monthly Bill Amount).</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-cogs mr-1"></i> Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    $('#inv_customer').on('change', function () {
        var opt        = $(this).find('option:selected');
        var customerId = $(this).val();
        var price      = parseFloat(opt.data('price')) || 0;

        if (customerId) {
            $('#inv_amount').val(price.toFixed(2));
            $('#inv_payable').val(price.toFixed(2));
            $('#inv_discount').val(0);
            $('#inv_total_due').text('Loading...');
            $('#inv_customer_info').removeClass('d-none');

            $.get('/reseller/billing/customer-due/' + customerId, function (data) {
                $('#inv_total_due').text('৳' + parseFloat(data.total_due).toFixed(2));
            });
        } else {
            $('#inv_customer_info').addClass('d-none');
            $('#inv_amount').val('');
            $('#inv_payable').val('');
        }
    });

    $('#inv_amount, #inv_discount').on('input', function () {
        var amount   = parseFloat($('#inv_amount').val()) || 0;
        var discount = parseFloat($('#inv_discount').val()) || 0;
        var payable  = amount - discount;
        $('#inv_payable').val(payable >= 0 ? payable.toFixed(2) : '0.00');
    });

    $('#newInvoiceForm').on('submit', function (e) {
        e.preventDefault();
        $('#invoiceError').addClass('d-none');
        $('#newInvoiceSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Creating...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                $('#newInvoiceModal').modal('hide');
                setTimeout(function () { window.location.reload(); }, 500);
            },
            error: function (xhr) {
                var msg = 'Something went wrong.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                else if (xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                $('#invoiceError').removeClass('d-none').text(msg);
                $('#newInvoiceSubmit').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Create Invoice');
            }
        });
    });

    $('#filterToggle').on('click', function () {
        $('#filterBody').slideToggle();
        $('#filterIcon').toggleClass('fa-minus fa-plus');
    });

    $('.select2').not('#inv_customer').select2({ width: '100%' });
    $('#inv_customer').select2({ width: '100%', dropdownParent: $('#newInvoiceModal') });

    $('#perPage').on('change', function () {
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', $(this).val());
        window.location.href = url.toString();
    });

    $('#selectAll').on('change', function () {
        $('.invoice-check').prop('checked', this.checked);
        toggleBulkActions();
    });
    $(document).on('change', '.invoice-check', function () {
        toggleBulkActions();
        if (!this.checked) $('#selectAll').prop('checked', false);
    });
    function toggleBulkActions() {
        $('#bulkActions').toggleClass('d-none', $('.invoice-check:checked').length === 0);
    }

    $('#bulkDelete').on('click', function () {
        var ids = $('.invoice-check:checked').filter(function() { return $(this).data('status') === 'unpaid'; })
            .map(function() { return $(this).val(); }).get();

        if (ids.length === 0) { alert('No unpaid invoices selected for deletion.'); return; }
        if (!confirm('Delete ' + ids.length + ' unpaid invoice(s)?')) return;

        $.ajax({
            url: '{{ route("reseller.billing.bulk-delete") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', ids: ids },
            success: function () { window.location.reload(); }
        });
    });
});
</script>
@endsection