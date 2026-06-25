@extends('layouts.app')
@section('title', 'Current Stock')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Current Stock</h4>
        <a href="{{ route('inventory.stock.transactions') }}" class="btn btn-outline-secondary btn-sm">Transaction History</a>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Product</th><th>Category</th><th>Unit</th><th>Stock</th><th>Low Alert</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ $product->unit }}</td>
                        <td>{{ $product->stock_quantity }}</td>
                        <td>{{ $product->low_stock_alert }}</td>
                        <td>
                            @if($product->stock_quantity <= 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->is_low_stock)
                                <span class="badge bg-warning">Low Stock</span>
                            @else
                                <span class="badge bg-success">In Stock</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No products</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
