@extends('layouts.app')

@section('title', 'Inventory Dashboard')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Inventory Dashboard</h4>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total Products</div>
                    <div class="fs-4 fw-bold">{{ $total_products }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-warning border-3">
                <div class="card-body">
                    <div class="text-muted small">Low Stock</div>
                    <div class="fs-4 fw-bold text-warning">{{ $low_stock_products }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-danger border-3">
                <div class="card-body">
                    <div class="text-muted small">Out of Stock</div>
                    <div class="fs-4 fw-bold text-danger">{{ $out_of_stock }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-info border-3">
                <div class="card-body">
                    <div class="text-muted small">Purchase Due</div>
                    <div class="fs-4 fw-bold text-info">৳{{ number_format($purchase_due, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-3">
                <div class="card-body">
                    <div class="text-muted small">Monthly Sale</div>
                    <div class="fs-4 fw-bold text-success">৳{{ number_format($monthly_sale, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-3">
                <div class="card-body">
                    <div class="text-muted small">Monthly Purchase</div>
                    <div class="fs-4 fw-bold text-primary">৳{{ number_format($monthly_purchase, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-danger border-3">
                <div class="card-body">
                    <div class="text-muted small">Sale Due</div>
                    <div class="fs-4 fw-bold text-danger">৳{{ number_format($sale_due, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Low Stock Alert --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold text-warning">
                    ⚠ Low Stock Products
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Alert</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($low_stock_list as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name }}</td>
                                <td><span class="badge bg-danger">{{ $product->stock_quantity }} {{ $product->unit }}</span></td>
                                <td>{{ $product->low_stock_alert }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No low stock products</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Recent Stock Transactions</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_transactions as $tx)
                            <tr>
                                <td>{{ $tx->product->name }}</td>
                                <td>
                                    @if($tx->type === 'in')
                                        <span class="badge bg-success">IN</span>
                                    @else
                                        <span class="badge bg-danger">OUT</span>
                                    @endif
                                </td>
                                <td>{{ $tx->quantity }}</td>
                                <td>{{ ucfirst($tx->reason) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No transactions</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
