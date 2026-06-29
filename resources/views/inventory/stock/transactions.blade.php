@extends('adminlte::page')
@section('title', 'Stock Transactions')

@section('content_header')
    <div>
        <h4 class="mb-0 font-weight-bold text-dark">
            <i class="fas fa-exchange-alt mr-2 text-primary"></i>Stock Transactions
        </h4>
        <small class="text-muted">All stock in/out movements</small>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold">Product</label>
                <select name="product_id" class="form-control form-control-sm">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Type</label>
                <select name="type" class="form-control form-control-sm">
                    <option value="">All Types</option>
                    <option value="in"  {{ request('type') == 'in'  ? 'selected' : '' }}>IN</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>OUT</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Reason</label>
                <select name="reason" class="form-control form-control-sm">
                    <option value="">All Reasons</option>
                    @foreach(['purchase','sale','consumption','transfer','return','damage','adjustment'] as $r)
                    <option value="{{ $r }}" {{ request('reason') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
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
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Transaction History
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Date</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Product</th>
                        <th class="text-center" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Type</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Reason</th>
                        <th class="text-center" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Qty</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Location</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr>
                        <td style="padding:10px 12px;" class="small text-muted">{{ $tx->created_at->format('d M Y H:i') }}</td>
                        <td style="padding:10px 12px;" class="font-weight-bold">{{ $tx->product->name }}</td>
                        <td style="padding:10px 12px;" class="text-center">
                            <span class="badge badge-{{ $tx->type == 'in' ? 'success' : 'danger' }}">
                                {{ strtoupper($tx->type) }}
                            </span>
                        </td>
                        <td style="padding:10px 12px;"><span class="badge badge-light border">{{ ucfirst($tx->reason) }}</span></td>
                        <td style="padding:10px 12px;" class="text-center font-weight-bold">{{ $tx->quantity }}</td>
                        <td style="padding:10px 12px;" class="text-muted">{{ $tx->location->name }}</td>
                        <td style="padding:10px 12px;" class="text-muted small">{{ $tx->createdBy->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-exchange-alt fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
    <div class="card-footer bg-light py-2">{{ $transactions->links() }}</div>
    @endif
</div>

@endsection

@section('css')
<style>
    .table tbody td { vertical-align: middle; }
    .table tbody tr:hover { background:#f0f4ff !important; }
</style>
@stop
