@extends('adminlte::page')
@section('title', 'Edit Product')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-edit mr-2 text-warning"></i>Edit Product
            </h4>
            <small class="text-muted">Update details for <strong>{{ $product->name }}</strong></small>
        </div>
        <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back to Products
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="row">
    <div class="col-lg-8 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-box mr-1"></i> Product Information
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.products.update', $product) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $product->name) }}"
                                   placeholder="Enter product name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Model / Brand</label>
                            <input type="text" name="model" class="form-control"
                                   value="{{ old('model', $product->model) }}"
                                   placeholder="e.g. TP-Link TL-WR841N">
                        </div>

                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-control @error('unit') is-invalid @enderror"
                                    required id="unitSelect">
                                @foreach(['pcs', 'meter', 'roll', 'box'] as $u)
                                <option value="{{ $u }}"
                                    {{ old('unit', $product->unit) == $u ? 'selected' : '' }}>
                                    {{ ucfirst($u) }}
                                </option>
                                @endforeach
                            </select>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 form-group" id="meterPerRollDiv"
                             style="{{ old('unit', $product->unit) === 'roll' ? '' : 'display:none' }}">
                            <label class="font-weight-bold small">Meter / Roll</label>
                            <input type="number" name="meter_per_roll" class="form-control"
                                   value="{{ old('meter_per_roll', $product->meter_per_roll) }}"
                                   min="1" placeholder="e.g. 305">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small">Low Stock Alert <span class="text-danger">*</span></label>
                            <input type="number" name="low_stock_alert"
                                   class="form-control @error('low_stock_alert') is-invalid @enderror"
                                   value="{{ old('low_stock_alert', $product->low_stock_alert) }}"
                                   min="0" required>
                            @error('low_stock_alert')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Alert when stock falls below this</small>
                        </div>

                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small">Purchase Price</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="purchase_price" class="form-control"
                                       value="{{ old('purchase_price', $product->purchase_price) }}"
                                       min="0" step="0.01">
                            </div>
                        </div>

                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small">Sell Price</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="sell_price" class="form-control"
                                       value="{{ old('sell_price', $product->sell_price) }}"
                                       min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <hr class="mt-1 mb-3">

                    <div class="d-flex">
                        <button type="submit" class="btn btn-warning px-4 mr-2">
                            <i class="fas fa-save mr-1"></i> Update Product
                        </button>
                        <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary px-4">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Current Stock Info --}}
    <div class="col-lg-4 col-xl-5">
        <div class="card shadow-sm">
            <div class="card-header py-2 bg-light">
                <h6 class="m-0 font-weight-bold text-muted">
                    <i class="fas fa-chart-bar mr-1"></i> Current Stock Info
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="small text-muted pl-3">Stock Quantity</td>
                            <td class="text-right pr-3">
                                <span class="badge {{ $product->is_low_stock ? 'badge-danger' : 'badge-success' }}">
                                    {{ $product->stock_quantity }} {{ $product->unit }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Low Stock Alert</td>
                            <td class="text-right pr-3 font-weight-bold">{{ $product->low_stock_alert }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Purchase Price</td>
                            <td class="text-right pr-3 font-weight-bold">৳{{ number_format($product->purchase_price, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Sell Price</td>
                            <td class="text-right pr-3 font-weight-bold text-success">৳{{ number_format($product->sell_price, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Category</td>
                            <td class="text-right pr-3">
                                <span class="badge badge-light border">{{ $product->category->name }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    .card-header h6 { font-size: 13px; letter-spacing: .3px; }
    .form-group label { color: #555; }
    .input-group-text { background: #f4f6f9; border-color: #ced4da; }
</style>
@stop

@section('js')
@parent
<script>
$(function () {
    $('#unitSelect').on('change', function () {
        $('#meterPerRollDiv').toggle(this.value === 'roll');
    });
});
</script>
@stop
