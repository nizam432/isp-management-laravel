@extends('adminlte::page')
@section('title', 'Sale Returns')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-undo-alt mr-2 text-primary"></i>Sale Returns
            </h4>
            <small class="text-muted">Manage sale return records</small>
        </div>
        <a href="{{ route('inventory.sale-returns.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> New Return
        </a>
    </div>
@endsection

@section('content')
@include('inventory._partials.alerts')

<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4">
                <label class="small font-weight-bold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Return No / Invoice No..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-3 mr-2">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('inventory.sale-returns.index') }}" class="btn btn-secondary btn-sm px-3">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-list mr-1"></i> Return List</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th style="padding:10px 12px;font-size:12px;color:#555;">Return No</th>
                        <th style="padding:10px 12px;font-size:12px;color:#555;">Sale Invoice</th>
                        <th style="padding:10px 12px;font-size:12px;color:#555;">Date</th>
                        <th style="padding:10px 12px;font-size:12px;color:#555;">Customer</th>
                        <th style="padding:10px 12px;font-size:12px;color:#555;" class="text-right">Amount</th>
                        <th style="padding:10px 12px;font-size:12px;color:#555;">Refund Type</th>
                        <th style="padding:10px 12px;font-size:12px;color:#555;" class="text-center">Status</th>
                        <th style="padding:10px 12px;font-size:12px;color:#555;" class="text-center" style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr>
                        <td style="padding:10px 12px;" class="font-weight-bold">{{ $return->return_no }}</td>
                        <td style="padding:10px 12px;">
                            <a href="{{ route('inventory.sales.show', $return->sale_id) }}">
                                {{ $return->sale->invoice_no ?? '—' }}
                            </a>
                        </td>
                        <td style="padding:10px 12px;" class="text-muted small">{{ $return->return_date->format('d M Y') }}</td>
                        <td style="padding:10px 12px;">{{ $return->client->name ?? $return->sale->walk_in_name ?? 'Walk-in' }}</td>
                        <td style="padding:10px 12px;" class="text-right font-weight-bold text-danger">
                            ৳{{ number_format($return->total_amount, 2) }}
                        </td>
                        <td style="padding:10px 12px;">
                            <span class="badge badge-light border">{{ ucfirst($return->refund_type) }}</span>
                        </td>
                        <td style="padding:10px 12px;" class="text-center">
                            <span class="badge badge-{{ $return->status === 'approved' ? 'success' : ($return->status === 'cancelled' ? 'secondary' : 'warning') }}">
                                {{ ucfirst($return->status) }}
                            </span>
                        </td>
                        <td style="padding:10px 12px;" class="text-center">
                            <a href="{{ route('inventory.sale-returns.show', $return) }}"
                               class="btn btn-sm btn-info px-2" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-undo-alt fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No returns found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($returns->hasPages())
    <div class="card-footer bg-light py-2">{{ $returns->links() }}</div>
    @endif
</div>
@endsection
