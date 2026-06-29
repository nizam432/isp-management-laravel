@extends('adminlte::page')
@section('title', 'Vendor Ledger Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-book mr-2 text-primary"></i>Vendor Ledger Report
            </h4>
        </div>
        <button onclick="window.print()" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
@endsection

@section('content')

<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold">Vendor <span class="text-danger">*</span></label>
                <select name="vendor_id" class="form-control form-control-sm" required>
                    <option value="">-- Select Vendor --</option>
                    @foreach($vendors as $v)
                    <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="fas fa-sync mr-1"></i> Generate
                </button>
            </div>
        </form>
    </div>
</div>

@isset($vendor)
<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> {{ $vendor->name }} — Ledger
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
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Debit</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Credit</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $row)
                    <tr>
                        <td style="padding:10px 12px;" class="small text-muted">{{ $row->date->format('d M Y') }}</td>
                        <td style="padding:10px 12px;"><span class="badge badge-light border">{{ ucfirst($row->type) }}</span></td>
                        <td style="padding:10px 12px;" class="text-muted small">{{ $row->note }}</td>
                        <td style="padding:10px 12px;" class="text-right text-success font-weight-bold">{{ $row->debit > 0 ? '৳'.number_format($row->debit, 2) : '—' }}</td>
                        <td style="padding:10px 12px;" class="text-right text-danger">{{ $row->credit > 0 ? '৳'.number_format($row->credit, 2) : '—' }}</td>
                        <td style="padding:10px 12px;" class="text-right font-weight-bold">৳{{ number_format($row->balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No entries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endisset

@endsection

@section('css')
<style>
    .table tbody td { vertical-align: middle; }
    .table tbody tr:hover { background:#f0f4ff !important; }
</style>
@stop
