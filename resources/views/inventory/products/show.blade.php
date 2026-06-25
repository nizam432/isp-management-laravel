@extends('layouts.app')
@section('title', 'Product Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ $product->name }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.products.edit', $product) }}" class="btn btn-outline-primary btn-sm">Edit</a>
            <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
        </div>
    </div>

    <div class="row g-3">
        {{-- Product Info --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Product Info</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Category</td><td>{{ $product->category->name }}</td></tr>
                        <tr><td class="text-muted">Model</td><td>{{ $product->model ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Unit</td><td>{{ $product->unit }}</td></tr>
                        @if($product->unit === 'roll')
                        <tr><td class="text-muted">Meter/Roll</td><td>{{ $product->meter_per_roll }}</td></tr>
                        @endif
                        <tr>
                            <td class="text-muted">Stock</td>
                            <td>
                                <span class="badge {{ $product->is_low_stock ? 'bg-danger' : 'bg-success' }} fs-6">
                                    {{ $product->stock_quantity }} {{ $product->unit }}
                                </span>
                            </td>
                        </tr>
                        <tr><td class="text-muted">Low Stock Alert</td><td>{{ $product->low_stock_alert }}</td></tr>
                        <tr><td class="text-muted">Purchase Price</td><td>৳{{ number_format($product->purchase_price, 2) }}</td></tr>
                        <tr><td class="text-muted">Sell Price</td><td>৳{{ number_format($product->sell_price, 2) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Location Stock --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white fw-semibold">Stock by Location</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Location</th><th>Quantity</th></tr>
                        </thead>
                        <tbody>
                            @forelse($product->locationStocks as $ls)
                            <tr>
                                <td>{{ $ls->location->name }}</td>
                                <td>{{ $ls->quantity }} {{ $product->unit }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted">No location stock</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Stock Transactions --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Stock Transaction History</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Reason</th>
                                <th>Qty</th>
                                <th>Location</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $tx)
                            <tr>
                                <td>{{ $tx->created_at->format('d M Y') }}</td>
                                <td>
                                    @if($tx->type === 'in')
                                        <span class="badge bg-success">IN</span>
                                    @else
                                        <span class="badge bg-danger">OUT</span>
                                    @endif
                                </td>
                                <td>{{ ucfirst($tx->reason) }}</td>
                                <td>{{ $tx->quantity }}</td>
                                <td>{{ $tx->location->name }}</td>
                                <td>{{ $tx->createdBy->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No transactions</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">{{ $transactions->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
