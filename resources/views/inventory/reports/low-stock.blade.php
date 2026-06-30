@extends('adminlte::page')
@section('title', 'Low Stock Alert')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>Low Stock Alert
            </h4>
        </div>
        <button onclick="window.print()" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
@endsection

@section('content')

@if($products->count() > 0)
<div class="alert alert-warning shadow-sm mb-3">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    <strong>{{ $products->count() }} product(s)</strong> are at or below their low stock alert level. Please restock them soon.
</div>
@else
<div class="alert alert-success shadow-sm mb-3">
    <i class="fas fa-check-circle mr-2"></i>
    All products are well-stocked. No alerts at this time.
</div>
@endif

<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#c62828 0%,#b71c1c 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-exclamation-triangle mr-1"></i> Low Stock Products ({{ $products->count() }} items)
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="th">SL</th>
                        <th class="th">Product</th>
                        <th class="th">Category</th>
                        <th class="th">Unit</th>
                        <th class="th text-right">Current Stock</th>
                        <th class="th text-right">Alert Level</th>
                        <th class="th text-right">Deficit</th>
                        <th class="th text-right">Stock Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $i => $product)
                    @php $deficit = $product->low_stock_alert - $product->stock_quantity; @endphp
                    <tr class="{{ $product->stock_quantity <= 0 ? 'table-danger' : 'table-warning' }}" style="opacity:.9;">
                        <td class="td text-muted small">{{ $i + 1 }}</td>
                        <td class="td">
                            <div class="font-weight-bold">{{ $product->name }}</div>
                            @if($product->model)
                            <div class="text-muted small">{{ $product->model }}</div>
                            @endif
                        </td>
                        <td class="td small">{{ $product->category?->name ?? '—' }}</td>
                        <td class="td small">{{ strtoupper($product->unit) }}</td>
                        <td class="td text-right font-weight-bold text-danger" style="font-size:15px;">
                            {{ number_format($product->stock_quantity, 2) }}
                        </td>
                        <td class="td text-right text-muted small">{{ $product->low_stock_alert }}</td>
                        <td class="td text-right font-weight-bold text-danger">
                            @if($deficit > 0)
                            <i class="fas fa-arrow-down mr-1"></i>{{ number_format($deficit, 2) }}
                            @else
                            <span class="text-warning">0</span>
                            @endif
                        </td>
                        <td class="td text-right small">৳{{ number_format($product->stock_quantity * $product->purchase_price, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>No low stock products found.
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
    .th { font-size:12px; font-weight:700; text-transform:uppercase; color:#555; padding:10px 12px; }
    .td { padding:10px 12px; vertical-align:middle; }
    .table-warning td { background:rgba(255,193,7,.12) !important; }
    .table-danger td { background:rgba(220,53,69,.10) !important; }
    @media print {
        .content-header, .main-header, .main-sidebar, .main-footer, .btn { display:none !important; }
        .content-wrapper { margin-left:0 !important; }
        .card { box-shadow: none !important; border:1px solid #dee2e6 !important; }
    }
</style>
@stop
