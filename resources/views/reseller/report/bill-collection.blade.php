@extends('reseller.layouts.app')

@section('title', 'Bill Collection')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Bill Collection Report</h4>
    <a href="{{ route('reseller.report.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <label class="small font-weight-bold">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}">
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm mb-2"><i class="fas fa-search mr-1"></i> Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card text-center py-3" style="border-left:4px solid #00a65a;">
            <div class="text-success font-weight-bold" style="font-size:24px;">৳{{ number_format($total) }}</div>
            <small class="text-muted">Total Collected ({{ $from }} → {{ $to }})</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Daily Summary</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="bg-light"><tr><th>Date</th><th>Amount</th></tr></thead>
                    <tbody>
                        @forelse($byDay as $date => $amount)
                        <tr><td>{{ $date }}</td><td>৳{{ number_format($amount) }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No payments in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">All Payments</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="bg-light"><tr><th>Date</th><th>Client</th><th>Amount</th><th>Method</th></tr></thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr>
                            <td>{{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('d M Y') : '' }}</td>
                            <td>{{ $p->customer->name ?? '—' }}</td>
                            <td>৳{{ number_format($p->amount) }}</td>
                            <td><span class="badge badge-secondary">{{ ucfirst($p->method) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No payments in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection