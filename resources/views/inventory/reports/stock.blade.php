@extends('adminlte::page')
@section('title', 'Stock Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-chart-bar mr-2 text-primary"></i>Stock Report
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
                <label class="small font-weight-bold">Category</label>
                <select name="category_id" class="form-control form-control-sm">
                    <option value="">-- All Categories --</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <div class="form-check mb-1 mr-3">
                    <input type="checkbox" name="low_stock" value="1" id="lowStockFilter" class="form-check-input" {{ request('low_stock') ? 'checked' : '' }}>
                    <label for="lowStockFilter" class="form-check-label small font-weight-bold text-danger">Low Stock Only</label>
                </div>
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="fas fa-sync mr-1"></i> Filter
                </button>
                <a href="{{ route('inventory.reports.stock') }}" class="btn btn-light btn-sm ml-1">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Summary --}}
@php
    $totalProducts  = $products->count();
    $lowStockCount  = $products->filter(fn($p) => $p->is_low_stock)->count();
    $inStockCount   = $totalProducts - $lowStockCount;
    $totalStockVal  = $products->sum(fn($p) => $p->stock_quantity * $p->purchase_price);
@endphp

<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-primary"><i class="fas fa-boxes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Products</span>
                <span class="info-box-number">{{ $totalProducts }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">In Stock</span>
                <span class="info-box-number">{{ $inStockCount }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Low Stock</span>
                <span class="info-box-number">{{ $lowStockCount }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-info"><i class="fas fa-dollar-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Stock Value</span>
                <span class="info-box-number" style="font-size:16px;">৳{{ number_format($totalStockVal, 2) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Stock Report ({{ $totalProducts }} items)</h6>
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
                        <th class="th text-right">Stock Value</th>
                        <th class="th text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $i => $product)
                    <tr>
                        <td class="td text-muted small">{{ $i + 1 }}</td>
                        <td class="td">
                            <div class="font-weight-bold">{{ $product->name }}</div>
                            @if($product->model)
                            <div class="text-muted small">{{ $product->model }}</div>
                            @endif
                        </td>
                        <td class="td small text-muted">{{ $product->category?->name ?? '—' }}</td>
                        <td class="td small">{{ strtoupper($product->unit) }}</td>
                        <td class="td text-right font-weight-bold {{ $product->is_low_stock ? 'text-danger' : 'text-success' }}">
                            {{ number_format($product->stock_quantity, 2) }}
                        </td>
                        <td class="td text-right text-muted small">{{ $product->low_stock_alert }}</td>
                        <td class="td text-right small">৳{{ number_format($product->stock_quantity * $product->purchase_price, 2) }}</td>
                        <td class="td text-center">
                            @if($product->is_low_stock)
                            <span class="badge badge-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Low Stock</span>
                            @else
                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>OK</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No products found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($products->count() > 0)
                <tfoot style="background:#f8f9fa; border-top:2px solid #dee2e6;">
                    <tr>
                        <td colspan="6" class="font-weight-bold pl-3">Total Stock Value</td>
                        <td class="text-right font-weight-bold text-primary">৳{{ number_format($totalStockVal, 2) }}</td>
                        <td></td>
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
