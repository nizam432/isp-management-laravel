@extends('reseller.layouts.app')

@section('title', 'Reports')

@section('content')

<h4 class="mb-3">Reports</h4>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center py-3" style="border-left:4px solid #17a2b8;">
            <div class="text-info font-weight-bold" style="font-size:24px;">{{ $stats['total_clients'] }}</div>
            <small class="text-muted">Total Clients</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center py-3" style="border-left:4px solid #00a65a;">
            <div class="text-success font-weight-bold" style="font-size:24px;">{{ $stats['active_clients'] }}</div>
            <small class="text-muted">Active Clients</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center py-3" style="border-left:4px solid #6f42c1;">
            <div class="font-weight-bold" style="font-size:24px;color:#6f42c1;">৳{{ number_format($stats['this_month_paid']) }}</div>
            <small class="text-muted">Collected This Month</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <a href="{{ route('reseller.report.btrc') }}" class="card text-decoration-none h-100">
            <div class="card-body text-center">
                <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                <h6>BTRC Report</h6>
                <small class="text-muted">Subscriber summary</small>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('reseller.report.status-history') }}" class="card text-decoration-none h-100">
            <div class="card-body text-center">
                <i class="fas fa-history fa-2x text-warning mb-2"></i>
                <h6>Enable/Disable History</h6>
                <small class="text-muted">Client status overview</small>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('reseller.report.bill-collection') }}" class="card text-decoration-none h-100">
            <div class="card-body text-center">
                <i class="fas fa-hand-holding-usd fa-2x text-success mb-2"></i>
                <h6>Bill Collection</h6>
                <small class="text-muted">Payments by date</small>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('reseller.report.messages') }}" class="card text-decoration-none h-100">
            <div class="card-body text-center">
                <i class="fas fa-comment-dots fa-2x text-info mb-2"></i>
                <h6>Messages Report</h6>
                <small class="text-muted">SMS delivery log</small>
            </div>
        </a>
    </div>
</div>

@endsection