@extends('adminlte::page')
@section('title', 'Purchase Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-shopping-cart mr-2 text-primary"></i>Purchase Report
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
            <div class="col-md-3">
                <label class="small font-weight-bold">Vendor</label>
                <select name="vendor_id" class="form-control form-control-sm">
                    <option value="">-- All Vendors --</option>
                    @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">-- All --</option>
                    <option value="draft"     {{ request('status') == 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="received"  {{ request('status') == 'received'  ? 'selected' : '' }}>Received</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                    <i class="fas fa-sync mr-1"></i> Filter
                </button>
                <a href="{{ route('inventory.reports.purchase') }}" class="btn btn-light btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-primary"><i class="fas fa-file-invoice"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Purchases</span>
                <span class="info-box-number">{{ $purchases->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-info"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Amount</span>
                <span class="info-box-number" style="font-size:16px;">৳{{ number_format($summary['total_amount'], 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Paid</span>
                <span class="info-box-number" style="font-size:16px;">৳{{ number_format($summary['total_paid'], 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Due</span>
                <span class="info-box-number" style="font-size:16px;">৳{{ number_format($summary['total_due'], 2) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-shopping-cart mr-1"></i> Purchase List ({{ $purchases->count() }} records)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="th">SL</th>
                        <th class="th">Purchase No</th>
                        <th class="th">Date</th>
                        <th class="th">Vendor</th>
                        <th class="th">Location</th>
                        <th class="th text-right">Total</th>
                        <th class="th text-right">Paid</th>
                        <th class="th text-right">Due</th>
                        <th class="th text-center">Status</th>
                        <th class="th text-center">Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $i => $purchase)
                    <tr>
                        <td class="td text-muted small">{{ $i + 1 }}</td>
                        <td class="td font-weight-bold">{{ $purchase->purchase_no }}</td>
                        <td class="td small text-muted">{{ $purchase->purchase_date->format('d M Y') }}</td>
                        <td class="td">{{ $purchase->vendor?->name ?? '—' }}</td>
                        <td class="td small text-muted">{{ $purchase->location?->name ?? '—' }}</td>
                        <td class="td text-right font-weight-bold">৳{{ number_format($purchase->total_amount, 2) }}</td>
                        <td class="td text-right text-success">৳{{ number_format($purchase->paid_amount, 2) }}</td>
                        <td class="td text-right {{ $purchase->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                            {{ $purchase->due_amount > 0 ? '৳'.number_format($purchase->due_amount, 2) : '—' }}
                        </td>
                        <td class="td text-center">
                            @if($purchase->status === 'received')
                            <span class="badge badge-success">Received</span>
                            @elseif($purchase->status === 'draft')
                            <span class="badge badge-warning text-dark">Draft</span>
                            @else
                            <span class="badge badge-secondary">Cancelled</span>
                            @endif
                        </td>
                        <td class="td text-center">
                            @if($purchase->payment_status === 'paid')
                            <span class="badge badge-success">Paid</span>
                            @elseif($purchase->payment_status === 'partial')
                            <span class="badge badge-info">Partial</span>
                            @else
                            <span class="badge badge-danger">Unpaid</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No purchases found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($purchases->count() > 0)
                <tfoot style="background:#f8f9fa; border-top:2px solid #dee2e6;">
                    <tr>
                        <td colspan="5" class="font-weight-bold pl-3">Total</td>
                        <td class="text-right font-weight-bold">৳{{ number_format($summary['total_amount'], 2) }}</td>
                        <td class="text-right font-weight-bold text-success">৳{{ number_format($summary['total_paid'], 2) }}</td>
                        <td class="text-right font-weight-bold text-danger">৳{{ number_format($summary['total_due'], 2) }}</td>
                        <td colspan="2"></td>
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
