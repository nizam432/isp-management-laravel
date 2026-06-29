@extends('adminlte::page')
@section('title', 'Product Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-box mr-2 text-primary"></i>{{ $product->name }}
            </h4>
            <small class="text-muted">Product details &amp; stock history</small>
        </div>
        <div>
            <a href="{{ route('inventory.products.edit', $product) }}" class="btn btn-warning btn-sm px-3 mr-1">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary btn-sm px-3">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="row">
    <div class="col-md-4">

        {{-- Product Info --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-info-circle mr-1"></i> Product Info
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="small text-muted pl-3" style="width:45%">Category</td>
                            <td class="pr-3"><span class="badge badge-light border">{{ $product->category->name }}</span></td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Model</td>
                            <td class="pr-3">{{ $product->model ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Unit</td>
                            <td class="pr-3">{{ $product->unit }}</td>
                        </tr>
                        @if($product->unit === 'roll')
                        <tr>
                            <td class="small text-muted pl-3">Meter/Roll</td>
                            <td class="pr-3">{{ $product->meter_per_roll }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="small text-muted pl-3">Stock</td>
                            <td class="pr-3">
                                <span class="badge badge-{{ $product->is_low_stock ? 'danger' : 'success' }}" style="font-size:13px;">
                                    {{ $product->stock_quantity }} {{ $product->unit }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Low Stock Alert</td>
                            <td class="pr-3">{{ $product->low_stock_alert }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Purchase Price</td>
                            <td class="pr-3 font-weight-bold">৳{{ number_format($product->purchase_price, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Sell Price</td>
                            <td class="pr-3 font-weight-bold text-success">৳{{ number_format($product->sell_price, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Stock by Location --}}
        <div class="card shadow-sm">
            <div class="card-header py-2 bg-light">
                <h6 class="m-0 font-weight-bold text-muted">
                    <i class="fas fa-warehouse mr-1"></i> Stock by Location
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                            <th style="font-size:12px;color:#555;padding:8px 12px;">Location</th>
                            <th style="font-size:12px;color:#555;padding:8px 12px;">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->locationStocks as $ls)
                        <tr>
                            <td style="padding:8px 12px;">{{ $ls->location->name }}</td>
                            <td style="padding:8px 12px;">
                                <span class="badge badge-light border">{{ $ls->quantity }} {{ $product->unit }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3 small">No location stock</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-history mr-1"></i> Stock Transaction History
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                                <th style="font-size:12px;color:#555;padding:10px 12px;">Date</th>
                                <th class="text-center" style="font-size:12px;color:#555;padding:10px 12px;">Type</th>
                                <th style="font-size:12px;color:#555;padding:10px 12px;">Reason</th>
                                <th class="text-center" style="font-size:12px;color:#555;padding:10px 12px;">Qty</th>
                                <th style="font-size:12px;color:#555;padding:10px 12px;">Location</th>
                                <th style="font-size:12px;color:#555;padding:10px 12px;">By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $tx)
                            <tr>
                                <td style="padding:10px 12px;" class="small text-muted">{{ $tx->created_at->format('d M Y') }}</td>
                                <td style="padding:10px 12px;" class="text-center">
                                    <span class="badge badge-{{ $tx->type === 'in' ? 'success' : 'danger' }}">
                                        {{ strtoupper($tx->type) }}
                                    </span>
                                </td>
                                <td style="padding:10px 12px;">{{ ucfirst($tx->reason) }}</td>
                                <td style="padding:10px 12px;" class="text-center font-weight-bold">{{ $tx->quantity }}</td>
                                <td style="padding:10px 12px;" class="text-muted">{{ $tx->location->name }}</td>
                                <td style="padding:10px 12px;" class="text-muted small">{{ $tx->createdBy->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-history fa-3x mb-3 d-block" style="opacity:.2;"></i>
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
    </div>
</div>

@endsection
