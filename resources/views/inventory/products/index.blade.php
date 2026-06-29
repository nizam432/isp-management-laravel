@extends('adminlte::page')
@section('title', 'Products')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-boxes mr-2 text-primary"></i>Products
            </h4>
            <small class="text-muted">Inventory product list</small>
        </div>
        <a href="{{ route('inventory.products.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> Add Product
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

{{-- ── Summary Cards ─────────────────────────────────────────────────────── --}}
<style>
.cust-stat-card {
    border-radius: 4px;
    color: #fff;
    padding: 14px 16px;
    margin-bottom: 16px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
}
.cust-stat-card .sc-left .sc-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(255,255,255,.85);
    margin-bottom: 4px;
}
.cust-stat-card .sc-left .sc-value {
    font-size: 32px;
    font-weight: 700;
    line-height: 1;
    color: #fff;
}
.cust-stat-card .sc-icon {
    font-size: 52px;
    color: rgba(255,255,255,.18);
}
</style>
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-boxes mr-1"></i> Total Products</div>
                <div class="sc-value">{{ $products->total() }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-boxes"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-check-circle mr-1"></i> In Stock</div>
                <div class="sc-value">{{ $products->getCollection()->where('is_low_stock', false)->count() }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="cust-stat-card" style="background:#dd4b39;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-exclamation-triangle mr-1"></i> Low Stock</div>
                <div class="sc-value">{{ $products->getCollection()->where('is_low_stock', true)->count() }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="cust-stat-card" style="background:#f39c12;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-tags mr-1"></i> Categories</div>
                <div class="sc-value">{{ $categories->count() }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-tags"></i></div>
        </div>
    </div>
</div>

{{-- ── Filter ────────────────────────────────────────────────────────────── --}}
<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold">Search Product</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search product..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold">Category</label>
                <select name="category_id" class="form-control form-control-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Stock Status</label>
                <select name="low_stock" class="form-control form-control-sm">
                    <option value="">All Stock</option>
                    <option value="1" {{ request('low_stock') ? 'selected' : '' }}>Low Stock Only</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-3 mr-2">
                    <i class="fas fa-search mr-1"></i>Search
                </button>
                <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary btn-sm px-3">
                    <i class="fas fa-redo mr-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Table Card ───────────────────────────────────────────────────────── --}}
<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Product List
        </h6>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Quick search..." style="width:220px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="productTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="text-center" style="width:50px;">#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th class="text-center">Stock</th>
                        <th class="text-right">Purchase Price</th>
                        <th class="text-right">Sell Price</th>
                        <th class="text-center" style="width:150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @forelse($products as $product)
                    <tr>
                        <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            <span class="font-weight-bold">{{ $product->name }}</span>
                            @if($product->model)
                                <br><small class="text-muted">{{ $product->model }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-light border">{{ $product->category->name }}</span>
                        </td>
                        <td class="text-muted">{{ $product->unit }}</td>
                        <td class="text-center">
                            <span class="badge {{ $product->is_low_stock ? 'badge-danger' : 'badge-success' }}">
                                {{ $product->stock_quantity }} {{ $product->unit }}
                            </span>
                            @if($product->is_low_stock)
                                <i class="fas fa-exclamation-triangle text-warning ml-1" title="Low stock!"></i>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold">৳{{ number_format($product->purchase_price, 2) }}</td>
                        <td class="text-right font-weight-bold text-success">৳{{ number_format($product->sell_price, 2) }}</td>
                        <td class="text-center">
                            <a href="{{ route('inventory.products.show', $product) }}"
                               class="btn btn-sm btn-info px-2" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('inventory.products.edit', $product) }}"
                               class="btn btn-sm btn-warning px-2" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($product->isDeletable())
                            <form action="{{ route('inventory.products.destroy', $product) }}" method="POST"
                                  class="d-inline" onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger px-2" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-boxes fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No products found. Click <strong>+ Add Product</strong> to add one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-light py-2">
        {{ $products->links() }}
    </div>
    @endif
</div>

@endsection

@section('css')
<style>
    #productTable thead th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #555;
        padding: 10px 12px;
    }
    #productTable tbody td { padding: 10px 12px; vertical-align: middle; }
    #productTable tbody tr:hover { background:#f0f4ff !important; }
    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color:#1a237e; }
</style>
@stop

@section('js')
@parent
<script>
$(function () {
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#productTableBody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });
});
</script>
@stop
