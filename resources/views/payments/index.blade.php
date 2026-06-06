@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@extends('layouts.app')

@section('title', 'Payments')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Payments</h1>
    </div>
@endsection

@section('content')

{{-- Stats Cards --}}
<style>
.pay-card { border-radius:6px; color:#fff; overflow:hidden; margin-bottom:12px; }
.pay-card .pc-top { padding:10px 14px 6px; }
.pay-card .pc-label { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px; color:#fff; }
.pay-card .pc-value { font-size:22px; font-weight:700; line-height:1.2; color:#fff; }
.pay-card .pc-badge { font-size:10px; padding:2px 7px; border-radius:20px; background:rgba(255,255,255,.25); font-weight:500; }
</style>

<div class="row">

    <div class="col-xl-3 col-md-6">
        <div class="pay-card" style="background:#00a65a;">
            <div class="pc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="pc-label"><i class="fas fa-money-bill-wave mr-1"></i> Collected (This Month)</div>
                </div>
                <div class="pc-value">&#2547;{{ number_format($totalThisMonth, 0) }}</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pay-card" style="background:#0073b7;">
            <div class="pc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="pc-label"><i class="fas fa-chart-line mr-1"></i> Collected (All Time)</div>
                </div>
                <div class="pc-value">&#2547;{{ number_format($totalAllTime, 0) }}</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pay-card" style="background:#dd4b39;">
            <div class="pc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="pc-label"><i class="fas fa-hand-holding-usd mr-1"></i> Cash (This Month)</div>
                </div>
                <div class="pc-value">&#2547;{{ number_format($cashThisMonth, 0) }}</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pay-card" style="background:#f39c12;">
            <div class="pc-top">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="pc-label"><i class="fas fa-mobile-alt mr-1"></i> Mobile Banking (This Month)</div>
                </div>
                <div class="pc-value">&#2547;{{ number_format($mobileThisMonth, 0) }}</div>
            </div>
        </div>
    </div>

</div>

{{-- Filter --}}
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
        <form method="GET" action="{{ route('payments.index') }}">

            {{-- Row 1 --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="col-form-label-sm font-weight-bold mb-1">Search</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control"
                            placeholder="Name / Phone" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                        value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                        value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Method</label>
                    <select name="method" class="form-control form-control-sm select2">
                        <option value="">All Methods</option>
                        <option value="cash"   {{ request('method') == 'cash'   ? 'selected' : '' }}>Cash</option>
                        <option value="bkash"  {{ request('method') == 'bkash'  ? 'selected' : '' }}>bKash</option>
                        <option value="nagad"  {{ request('method') == 'nagad'  ? 'selected' : '' }}>Nagad</option>
                        <option value="rocket" {{ request('method') == 'rocket' ? 'selected' : '' }}>Rocket</option>
                        <option value="bank"   {{ request('method') == 'bank'   ? 'selected' : '' }}>Bank</option>
                        <option value="card"   {{ request('method') == 'card'   ? 'selected' : '' }}>Card</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Collector</label>
                    <select name="received_by" class="form-control form-control-sm select2">
                        <option value="">All Collectors</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('received_by') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Row 2 --}}
            <div class="row">
                <div class="col-md-2">
                    <label class="col-form-label-sm font-weight-bold mb-1">Status</label>
                    <select name="status" class="form-control form-control-sm select2">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="void"   {{ request('status') == 'void'   ? 'selected' : '' }}>Void</option>
                    </select>
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
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm mr-1">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>

        </form>
    </div>
</div>

{{-- Payments Table --}}
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Invoice No</th>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Transaction No</th>
                    <th>Received By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr class="{{ $payment->isVoid() ? 'table-secondary text-muted' : '' }}">
                    <td>{{ $payments->firstItem() + $loop->index }}</td>
                    <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '-' }}</td>
                    <td>
                        @if($payment->invoice)
                            <a href="{{ route('invoices.show', $payment->invoice) }}">
                                {{ $payment->invoice->invoice_no }}
                            </a>
                        @else
                            <span class="text-muted">Advance</span>
                        @endif
                    </td>
                    <td>{{ $payment->customer->name }}</td>
                    <td>{{ $payment->customer->phone }}</td>
                    <td>
                        <strong class="{{ $payment->isVoid() ? 'text-muted' : 'text-success' }}">
                            &#2547;{{ number_format($payment->amount, 2) }}
                        </strong>
                    </td>
                    <td>
                        @php
                            $methodColors = [
                                'cash'   => 'success',
                                'bkash'  => 'danger',
                                'nagad'  => 'warning',
                                'rocket' => 'info',
                                'bank'   => 'primary',
                                'card'   => 'secondary',
                                'advance'=> 'dark',
                            ];
                            $color = $methodColors[$payment->method] ?? 'secondary';
                        @endphp
                        <span class="badge badge-{{ $color }}">
                            {{ ucfirst($payment->method) }}
                        </span>
                    </td>
                    <td>{{ $payment->transaction_id ?? '-' }}</td>
                    <td>{{ $payment->receivedBy->name ?? '-' }}</td>
                    <td>
                        @if($payment->isVoid())
                            <span class="badge badge-danger">Void</span>
                        @else
                            <span class="badge badge-success">Active</span>
                        @endif
                    </td>
                    <td>
                        {{-- View Invoice --}}
                        @if($payment->invoice)
                            <a href="{{ route('invoices.show', $payment->invoice) }}"
                               class="btn btn-xs btn-info" title="View Invoice">
                                <i class="fas fa-eye"></i>
                            </a>
                        @endif

                        {{-- Void — ISP Admin only --}}
                        {{-- @if($payment->isActive() && auth()->user()->hasRole('isp-admin')) --}}
                        @if($payment->isActive())
                            <button type="button"
                                class="btn btn-xs btn-danger void-btn"
                                title="Void Payment"
                                data-payment-id="{{ $payment->id }}"
                                data-amount="{{ $payment->amount }}"
                                data-toggle="modal"
                                data-target="#voidModal">
                                <i class="fas fa-ban"></i>
                            </button>
                        @endif

                        {{-- Void info --}}
                        @if($payment->isVoid() && $payment->voidLog)
                            <span class="text-muted" style="font-size:11px;"
                                title="Voided by {{ $payment->voidLog->voidedBy->name ?? '-' }} — {{ $payment->voidLog->reason }}">
                                <i class="fas fa-info-circle"></i>
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">No payments found.</td>
                </tr>
                @endforelse
            </tbody>
            @if($payments->count())
            <tfoot class="bg-light">
                <tr>
                    <td colspan="5" class="text-right"><strong>Total (this page):</strong></td>
                    <td><strong class="text-success">&#2547;{{ number_format($payments->where('status', 'active')->sum('amount'), 2) }}</strong></td>
                    <td colspan="5"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if($payments->hasPages())
    <div class="card-footer">
        {{ $payments->links() }}
    </div>
    @endif
</div>

{{-- Void Modal --}}
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white"><i class="fas fa-ban mr-1"></i> Void Payment</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="voidForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Amount: <strong class="text-danger" id="void_amount"></strong></p>
                    <p class="text-muted" style="font-size:13px;">
                        The payment will be voided and the amount will be added to the customer's advance balance.
                    </p>
                    <div class="form-group mb-0">
                        <label>Reason <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control form-control-sm"
                            placeholder="Enter reason for void" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-ban mr-1"></i> Void Payment
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

    // Void button click
    $('.void-btn').on('click', function () {
        var paymentId = $(this).data('payment-id');
        var amount    = parseFloat($(this).data('amount')).toFixed(2);
        $('#void_amount').text('BDT ' + amount);
        $('#voidForm').attr('action', '/payments/' + paymentId + '/void');
    });

    // Select2
    // Filter toggle
    $('#filterToggle').on('click', function () {
        $('#filterBody').slideToggle();
        $('#filterIcon').toggleClass('fa-minus fa-plus');
    });

    $('.select2').select2({ width: '100%' });

    // Toast notifications
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif

});
</script>
@endpush