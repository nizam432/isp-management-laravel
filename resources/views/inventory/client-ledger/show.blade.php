@extends('adminlte::page')
@section('title', 'Client Ledger')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-book mr-2 text-primary"></i>{{ $customer->name }}
            </h4>
            <small class="text-muted">Client ledger details</small>
        </div>
        <a href="{{ route('inventory.client-ledger.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@endsection

@section('content')

<style>
.cust-stat-card { border-radius:4px; color:#fff; padding:14px 16px; height:80px; display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
.cust-stat-card .sc-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.85); margin-bottom:4px; }
.cust-stat-card .sc-value { font-size:22px; font-weight:700; line-height:1; }
.cust-stat-card .sc-icon { font-size:42px; color:rgba(255,255,255,.18); }
</style>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div><div class="sc-label">Total Sale</div><div class="sc-value">৳{{ number_format($summary['total_credit'], 0) }}</div></div>
            <div class="sc-icon"><i class="fas fa-receipt"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div><div class="sc-label">Total Paid</div><div class="sc-value">৳{{ number_format($summary['total_debit'], 0) }}</div></div>
            <div class="sc-icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="cust-stat-card" style="background:{{ $summary['balance'] > 0 ? '#dd4b39' : '#00a65a' }};">
            <div><div class="sc-label">Balance Due</div><div class="sc-value">৳{{ number_format($summary['balance'], 0) }}</div></div>
            <div class="sc-icon"><i class="fas fa-money-bill"></i></div>
        </div>
    </div>
</div>

<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-3 mr-2">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Ledger Entries
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Date</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Type</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Note</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Debit (Paid)</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Credit (Sale)</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $row)
                    <tr>
                        <td style="padding:10px 12px;" class="small text-muted">{{ $row->date->format('d M Y') }}</td>
                        <td style="padding:10px 12px;"><span class="badge badge-light border">{{ ucfirst($row->type) }}</span></td>
                        <td style="padding:10px 12px;" class="text-muted small">{{ $row->note }}</td>
                        <td style="padding:10px 12px;" class="text-right text-success font-weight-bold">
                            {{ $row->debit > 0 ? '৳'.number_format($row->debit, 2) : '—' }}
                        </td>
                        <td style="padding:10px 12px;" class="text-right text-danger">
                            {{ $row->credit > 0 ? '৳'.number_format($row->credit, 2) : '—' }}
                        </td>
                        <td style="padding:10px 12px;" class="text-right font-weight-bold">
                            ৳{{ number_format($row->balance, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-book fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No ledger entries found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    .table tbody td { vertical-align: middle; }
    .table tbody tr:hover { background:#f0f4ff !important; }
</style>
@stop
