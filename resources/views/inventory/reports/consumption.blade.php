@extends('adminlte::page')
@section('title', 'Consumption Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-tools mr-2 text-primary"></i>Consumption Report
            </h4>
        </div>
        <button onclick="window.print()" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
@endsection

@section('content')

{{-- Filter --}}
<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
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
                    <i class="fas fa-sync mr-1"></i> Filter
                </button>
                <a href="{{ route('inventory.reports.consumption') }}" class="btn btn-light btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Summary --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-warning"><i class="fas fa-tools"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Records</span>
                <span class="info-box-number">{{ $consumptions->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-danger"><i class="fas fa-dollar-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Amount</span>
                <span class="info-box-number" style="font-size:16px;">৳{{ number_format($summary['total_amount'], 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm mb-0 h-100">
            <div class="card-header py-2 bg-light">
                <span class="font-weight-bold small"><i class="fas fa-chart-pie mr-1"></i> By Purpose</span>
            </div>
            <div class="card-body py-2 px-3">
                @if($summary['by_purpose']->isEmpty())
                <span class="text-muted small">—</span>
                @else
                @foreach($summary['by_purpose'] as $purpose => $amount)
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">{{ $purpose ?: 'General' }}</span>
                    <span class="font-weight-bold">৳{{ number_format($amount, 2) }}</span>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-tools mr-1"></i> Consumption List ({{ $consumptions->count() }} records)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="th">SL</th>
                        <th class="th">Consumption No</th>
                        <th class="th">Date</th>
                        <th class="th">Location</th>
                        <th class="th">Purpose</th>
                        <th class="th">Reference</th>
                        <th class="th text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consumptions as $i => $consumption)
                    <tr>
                        <td class="td text-muted small">{{ $i + 1 }}</td>
                        <td class="td font-weight-bold">{{ $consumption->consumption_no }}</td>
                        <td class="td small text-muted">{{ $consumption->consumption_date->format('d M Y') }}</td>
                        <td class="td small text-muted">{{ $consumption->location?->name ?? '—' }}</td>
                        <td class="td">
                            @if($consumption->purpose)
                            <span class="badge badge-light border">{{ $consumption->purpose }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="td small text-muted">{{ $consumption->reference_note ?? '—' }}</td>
                        <td class="td text-right font-weight-bold">৳{{ number_format($consumption->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No consumption records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($consumptions->count() > 0)
                <tfoot style="background:#f8f9fa; border-top:2px solid #dee2e6;">
                    <tr>
                        <td colspan="6" class="font-weight-bold pl-3">Total</td>
                        <td class="text-right font-weight-bold text-danger">৳{{ number_format($summary['total_amount'], 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

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
