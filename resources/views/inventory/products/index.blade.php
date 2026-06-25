@extends('layouts.app')
@section('title', 'Products')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Products</h4>
        <a href="{{ route('inventory.products.create') }}" class="btn btn-primary btn-sm">+ Add Product</a>
    </div>

    @include('inventory._partials.alerts')

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Search product..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="low_stock" class="form-select form-select-sm">
                        <option value="">All Stock</option>
                        <option value="1" {{ request('low_stock') ? 'selected' : '' }}>Low Stock Only</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-secondary">Filter</button>
                    <a href="{{ route('inventory.products.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Stock</th>
                        <th>Purchase Price</th>
                        <th>Sell Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            {{ $product->name }}
                            @if($product->model) <small class="text-muted">({{ $product->model }})</small> @endif
                        </td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ $product->unit }}</td>
                        <td>
                            <span class="badge {{ $product->is_low_stock ? 'bg-danger' : 'bg-success' }}">
                                {{ $product->stock_quantity }} {{ $product->unit }}
                            </span>
                        </td>
                        <td>৳{{ number_format($product->purchase_price, 2) }}</td>
                        <td>৳{{ number_format($product->sell_price, 2) }}</td>
                        <td>
                            <a href="{{ route('inventory.products.show', $product) }}" class="btn btn-sm btn-outline-info">View</a>
                            <a href="{{ route('inventory.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @if($product->isDeletable())
                            <form action="{{ route('inventory.products.destroy', $product) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No products found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $products->links() }}</div>
    </div>
</div>
@endsection
