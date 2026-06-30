@extends('adminlte::page')
@section('title', 'Client Ledger Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-users mr-2 text-primary"></i>Client Ledger Report
            </h4>
        </div>
        <button onclick="window.print()" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
@endsection

@section('content')

{{-- Client Selector --}}
<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4">
                <label class="small font-weight-bold">Client <span class="text-danger">*</span></label>
                <select name="client_id" class="form-control form-control-sm" required>
                    <option value="">-- Select Client --</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
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
                <button type="submit" class="btn btn-primary btn-sm px-3 mr-1">
                    <i class="fas fa-sync mr-1"></i> Generate
                </button>
                <a href="{{ route('inventory.reports.client-ledger') }}" class="btn btn-light btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

@isset($client)
@php
    $totalCredit  = $ledger->sum('credit');
    $totalDebit   = $ledger->sum('debit');
    $balance      = $ledger->last()?->balance ?? 0;
@endphp

{{-- Client summary --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-primary"><i class="fas fa-user"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Client</span>
                <span class="info-box-number" style="font-size:15px;">{{ $client->name }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-danger"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Sales (Credit)</span>
                <span class="info-box-number" style="font-size:16px;">৳{{ number_format($totalCredit, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-{{ $balance > 0 ? 'danger' : 'success' }}"><i class="fas fa-balance-scale"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Outstanding Balance</span>
                <span class="info-box-number {{ $balance > 0 ? 'text-danger' : '' }}" style="font-size:16px;">৳{{ number_format(abs($balance), 2) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> {{ $client->name }} — Ledger
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="th">Date</th>
                        <th class="th">Type</th>
                        <th class="th">Note</th>
                        <th class="th text-right">Debit (Paid)</th>
                        <th class="th text-right">Credit (Sale)</th>
                        <th class="th text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $row)
                    <tr>
                        <td class="td small text-muted">{{ $row->date->format('d M Y') }}</td>
                        <td class="td"><span class="badge badge-light border">{{ ucfirst($row->type) }}</span></td>
                        <td class="td text-muted small">{{ $row->note }}</td>
                        <td class="td text-right text-success font-weight-bold">{{ $row->debit > 0 ? '৳'.number_format($row->debit, 2) : '—' }}</td>
                        <td class="td text-right text-danger">{{ $row->credit > 0 ? '৳'.number_format($row->credit, 2) : '—' }}</td>
                        <td class="td text-right font-weight-bold {{ $row->balance > 0 ? 'text-danger' : 'text-success' }}">
                            ৳{{ number_format($row->balance, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No entries found.</td></tr>
                    @endforelse
                </tbody>
                @if($ledger->count() > 0)
                <tfoot style="background:#f8f9fa; border-top:2px solid #dee2e6;">
                    <tr>
                        <td colspan="3" class="font-weight-bold pl-3">Total</td>
                        <td class="text-right font-weight-bold text-success">৳{{ number_format($totalDebit, 2) }}</td>
                        <td class="text-right font-weight-bold text-danger">৳{{ number_format($totalCredit, 2) }}</td>
                        <td class="text-right font-weight-bold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($balance, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endisset

@endsection

@section('css')
<style>
    .th { font-size:12px; font-weight:700; text-transform:uppercase; color:#555; padding:10px 12px; }
    .td { padding:10px 12px; vertical-align:middle; }
    .table tbody tr:hover { background:#f0f4ff !important; }
    @media print {
        .content-header, .main-header, .main-sidebar, .main-footer, form, .btn { display:none !important; }
        .content-wrapper { margin-left:0 !important; }
        .card { box-shadow: none !important; border:1px solid #dee2e6 !important; }
    }
</style>
@stop
