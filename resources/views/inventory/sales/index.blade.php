@extends('adminlte::page')
@section('title', 'Sales')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-receipt mr-2 text-primary"></i>Sales
            </h4>
            <small class="text-muted">Manage all inventory sales</small>
        </div>
        <a href="{{ route('inventory.sales.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> New Sale
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Sale / Invoice No..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    <option value="draft"     {{ request('status') == 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
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
                <button type="submit" class="btn btn-primary btn-sm px-3 mr-2">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('inventory.sales.index') }}" class="btn btn-secondary btn-sm px-3">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Sale List
        </h6>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Quick search..." style="width:220px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="saleTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Invoice No</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Date</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Customer</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Total</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Paid</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Due</th>
                        <th class="text-center" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Status</th>
                        <th class="text-center" style="width:70px;"></th>
                    </tr>
                </thead>
                <tbody id="saleTableBody">
                    @forelse($sales as $sale)
                    <tr>
                        <td style="padding:10px 12px;">
                            <span class="font-weight-bold">{{ $sale->invoice_no }}</span>
                            <br><small class="text-muted">{{ $sale->sale_no }}</small>
                        </td>
                        <td style="padding:10px 12px;" class="text-muted small">{{ $sale->sale_date->format('d M Y') }}</td>
                        <td style="padding:10px 12px;">{{ $sale->customer_name }}</td>
                        <td style="padding:10px 12px;" class="text-right font-weight-bold">৳{{ number_format($sale->total_amount, 2) }}</td>
                        <td style="padding:10px 12px;" class="text-right text-success">৳{{ number_format($sale->paid_amount, 2) }}</td>
                        <td style="padding:10px 12px;" class="text-right {{ $sale->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                            ৳{{ number_format($sale->due_amount, 2) }}
                        </td>
                        <td style="padding:10px 12px;" class="text-center">
                            <span class="badge badge-{{ $sale->status == 'confirmed' ? 'success' : ($sale->status == 'draft' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </td>
                        <td style="padding:10px 12px;" class="text-center">
                            <a href="{{ route('inventory.sales.show', $sale) }}"
                               class="btn btn-sm btn-info px-2" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No sales found. Click <strong>+ New Sale</strong> to add one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sales->hasPages())
    <div class="card-footer bg-light py-2">{{ $sales->links() }}</div>
    @endif
</div>

@endsection

@section('css')
<style>
    #saleTable tbody td { vertical-align: middle; }
    #saleTable tbody tr:hover { background:#f0f4ff !important; }
    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color:#1a237e; }
</style>
@stop

@section('js')
@parent
<script>
$(function () {
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#saleTableBody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });
});
</script>
@stop
