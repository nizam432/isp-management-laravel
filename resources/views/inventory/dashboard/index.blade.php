@extends('adminlte::page')
@section('title', 'Inventory Dashboard')

@section('content_header')
    <div>
        <h4 class="mb-0 font-weight-bold text-dark">
            <i class="fas fa-tachometer-alt mr-2 text-primary"></i>Inventory Dashboard
        </h4>
        <small class="text-muted">Overview of inventory status</small>
    </div>
@endsection

@section('content')

<style>
.inv-stat-card {
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
.inv-stat-card .sc-left .sc-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(255,255,255,.85);
    margin-bottom: 4px;
}
.inv-stat-card .sc-left .sc-value {
    font-size: 30px;
    font-weight: 700;
    line-height: 1;
}
.inv-stat-card .sc-icon {
    font-size: 48px;
    color: rgba(255,255,255,.18);
}
</style>

<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="inv-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-boxes mr-1"></i> Total Products</div>
                <div class="sc-value">{{ $total_products }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-boxes"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="inv-stat-card" style="background:#f39c12;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-exclamation-triangle mr-1"></i> Low Stock</div>
                <div class="sc-value">{{ $low_stock_products }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="inv-stat-card" style="background:#dd4b39;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-times-circle mr-1"></i> Out of Stock</div>
                <div class="sc-value">{{ $out_of_stock }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="inv-stat-card" style="background:#6f42c1;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-clock mr-1"></i> Purchase Due</div>
                <div class="sc-value" style="font-size:22px;">৳{{ number_format($purchase_due, 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="inv-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-chart-line mr-1"></i> Monthly Sale</div>
                <div class="sc-value" style="font-size:22px;">৳{{ number_format($monthly_sale, 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-chart-line"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="inv-stat-card" style="background:#1a237e;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-shopping-cart mr-1"></i> Monthly Purchase</div>
                <div class="sc-value" style="font-size:22px;">৳{{ number_format($monthly_purchase, 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-shopping-cart"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="inv-stat-card" style="background:#c0392b;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-money-bill mr-1"></i> Sale Due</div>
                <div class="sc-value" style="font-size:22px;">৳{{ number_format($sale_due, 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-money-bill"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Low Stock Products
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                            <th style="font-size:12px;color:#555;padding:8px 12px;">Product</th>
                            <th style="font-size:12px;color:#555;padding:8px 12px;">Category</th>
                            <th class="text-center" style="font-size:12px;color:#555;padding:8px 12px;">Stock</th>
                            <th class="text-center" style="font-size:12px;color:#555;padding:8px 12px;">Alert</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($low_stock_list as $product)
                        <tr>
                            <td style="padding:8px 12px;" class="font-weight-bold">{{ $product->name }}</td>
                            <td style="padding:8px 12px;" class="text-muted small">{{ $product->category->name }}</td>
                            <td style="padding:8px 12px;" class="text-center">
                                <span class="badge badge-danger">{{ $product->stock_quantity }} {{ $product->unit }}</span>
                            </td>
                            <td style="padding:8px 12px;" class="text-center text-muted">{{ $product->low_stock_alert }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No low stock products</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-exchange-alt mr-1"></i> Recent Stock Transactions
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                            <th style="font-size:12px;color:#555;padding:8px 12px;">Product</th>
                            <th class="text-center" style="font-size:12px;color:#555;padding:8px 12px;">Type</th>
                            <th class="text-center" style="font-size:12px;color:#555;padding:8px 12px;">Qty</th>
                            <th style="font-size:12px;color:#555;padding:8px 12px;">Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_transactions as $tx)
                        <tr>
                            <td style="padding:8px 12px;" class="font-weight-bold">{{ $tx->product->name }}</td>
                            <td style="padding:8px 12px;" class="text-center">
                                <span class="badge badge-{{ $tx->type === 'in' ? 'success' : 'danger' }}">
                                    {{ strtoupper($tx->type) }}
                                </span>
                            </td>
                            <td style="padding:8px 12px;" class="text-center">{{ $tx->quantity }}</td>
                            <td style="padding:8px 12px;" class="text-muted small">{{ ucfirst($tx->reason) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No recent transactions</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
