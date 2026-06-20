@extends('reseller.layouts.app')
@section('title', 'Report')
@section('content')

<div class="card border-0 shadow-sm mb-3" style="border-radius:12px">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4 mb-2">
                <label class="small font-weight-bold">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
            </div>
            <div class="col-md-4 mb-2">
                <label class="small font-weight-bold">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
            </div>
            <div class="col-md-4 mb-2">
                <button type="submit" class="btn btn-sm btn-success w-100"><i class="fas fa-filter mr-1"></i> Apply Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Client Summary --}}
<div class="card border-0 shadow-sm mb-3" style="border-radius:12px">
    <div class="card-body">
        <h6 class="font-weight-bold mb-3"><i class="fas fa-users text-primary mr-1"></i> Client Summary</h6>
        <div class="row text-center">
            <div class="col-md-3"><p class="text-muted small mb-1">Total</p><h5 class="font-weight-bold">{{ $clientStats['total'] }}</h5></div>
            <div class="col-md-3"><p class="text-muted small mb-1">Active</p><h5 class="font-weight-bold text-success">{{ $clientStats['active'] }}</h5></div>
            <div class="col-md-3"><p class="text-muted small mb-1">Expired</p><h5 class="font-weight-bold text-danger">{{ $clientStats['expired'] }}</h5></div>
            <div class="col-md-3"><p class="text-muted small mb-1">New (range)</p><h5 class="font-weight-bold text-info">{{ $clientStats['new_in_range'] }}</h5></div>
        </div>
    </div>
</div>

{{-- Billing Summary --}}
<div class="card border-0 shadow-sm mb-3" style="border-radius:12px">
    <div class="card-body">
        <h6 class="font-weight-bold mb-3"><i class="fas fa-file-invoice-dollar text-warning mr-1"></i> Billing Summary (Selected Range)</h6>
        <div class="row text-center">
            <div class="col-md-3"><p class="text-muted small mb-1">Invoices</p><h5 class="font-weight-bold">{{ $billingStats['invoice_count'] }}</h5></div>
            <div class="col-md-3"><p class="text-muted small mb-1">Total Invoiced</p><h5 class="font-weight-bold">{{ number_format($billingStats['total_invoiced'], 2) }}</h5></div>
            <div class="col-md-3"><p class="text-muted small mb-1">Paid</p><h5 class="font-weight-bold text-success">{{ number_format($billingStats['total_paid'], 2) }}</h5></div>
            <div class="col-md-3"><p class="text-muted small mb-1">Unpaid</p><h5 class="font-weight-bold text-danger">{{ number_format($billingStats['total_unpaid'], 2) }}</h5></div>
        </div>
    </div>
</div>

{{-- Fund Summary --}}
<div class="card border-0 shadow-sm mb-3" style="border-radius:12px">
    <div class="card-body">
        <h6 class="font-weight-bold mb-3"><i class="fas fa-wallet text-success mr-1"></i> Fund Summary (Selected Range)</h6>
        <div class="row text-center">
            <div class="col-md-6"><p class="text-muted small mb-1">Total Funded</p><h5 class="font-weight-bold text-success">{{ number_format($fundStats['total_funded'], 2) }}</h5></div>
            <div class="col-md-6"><p class="text-muted small mb-1">Total Due</p><h5 class="font-weight-bold text-danger">{{ number_format($fundStats['total_due'], 2) }}</h5></div>
        </div>
    </div>
</div>

{{-- Package Distribution --}}
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body">
        <h6 class="font-weight-bold mb-3"><i class="fas fa-box text-info mr-1"></i> Client Distribution by Package</h6>
        @forelse($packageDistribution as $pkg => $count)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small">{{ $pkg }}</span>
            <span class="badge badge-primary">{{ $count }}</span>
        </div>
        @empty
        <p class="text-muted small text-center py-3">No data available.</p>
        @endforelse
    </div>
</div>
@stop
