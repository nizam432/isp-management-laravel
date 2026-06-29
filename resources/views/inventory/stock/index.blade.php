@extends('adminlte::page')
@section('title', 'Current Stock')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-layer-group mr-2 text-primary"></i>Current Stock
            </h4>
            <small class="text-muted">Real-time stock levels across all products</small>
        </div>
        <a href="{{ route('inventory.stock.transactions') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-history mr-1"></i> Transaction History
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Stock List
        </h6>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Quick search..." style="width:220px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="stockTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Product</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Category</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Unit</th>
                        <th class="text-center" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Stock</th>
                        <th class="text-center" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Alert</th>
                        <th class="text-center" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Status</th>
                    </tr>
                </thead>
                <tbody id="stockTableBody">
                    @forelse($products as $product)
                    <tr>
                        <td style="padding:10px 12px;" class="font-weight-bold">{{ $product->name }}</td>
                        <td style="padding:10px 12px;"><span class="badge badge-light border">{{ $product->category->name }}</span></td>
                        <td style="padding:10px 12px;" class="text-muted">{{ $product->unit }}</td>
                        <td style="padding:10px 12px;" class="text-center font-weight-bold">{{ $product->stock_quantity }}</td>
                        <td style="padding:10px 12px;" class="text-center text-muted">{{ $product->low_stock_alert }}</td>
                        <td style="padding:10px 12px;" class="text-center">
                            @if($product->stock_quantity <= 0)
                                <span class="badge badge-danger">Out of Stock</span>
                            @elseif($product->is_low_stock)
                                <span class="badge badge-warning">Low Stock</span>
                            @else
                                <span class="badge badge-success">In Stock</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-layer-group fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No products found.
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
    #stockTable tbody td { vertical-align: middle; }
    #stockTable tbody tr:hover { background:#f0f4ff !important; }
    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color:#1a237e; }
</style>
@stop

@section('js')
@parent
<script>
$(function () {
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#stockTableBody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });
});
</script>
@stop
