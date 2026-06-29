@extends('adminlte::page')
@section('title', 'Add Product')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-plus-circle mr-2 text-primary"></i>Add Product
            </h4>
            <small class="text-muted">Create a new inventory product</small>
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
                <form action="{{ route('inventory.products.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
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
                                   value="{{ old('name') }}"
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
                                   value="{{ old('model') }}" placeholder="e.g. TP-Link TL-WR841N">
                        </div>

                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold small">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-control @error('unit') is-invalid @enderror"
                                    required id="unitSelect">
                                <option value="pcs"   {{ old('unit') == 'pcs'   ? 'selected' : '' }}>Pcs</option>
                                <option value="meter" {{ old('unit') == 'meter' ? 'selected' : '' }}>Meter</option>
                                <option value="roll"  {{ old('unit') == 'roll'  ? 'selected' : '' }}>Roll</option>
                                <option value="box"   {{ old('unit') == 'box'   ? 'selected' : '' }}>Box</option>
                            </select>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 form-group" id="meterPerRollDiv" style="display:none">
                            <label class="font-weight-bold small">Meter / Roll</label>
                            <input type="number" name="meter_per_roll" class="form-control"
                                   value="{{ old('meter_per_roll') }}" min="1" placeholder="e.g. 305">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small">Low Stock Alert <span class="text-danger">*</span></label>
                            <input type="number" name="low_stock_alert"
                                   class="form-control @error('low_stock_alert') is-invalid @enderror"
                                   value="{{ old('low_stock_alert', 5) }}" min="0" required>
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
                                       value="{{ old('purchase_price', 0) }}" min="0" step="0.01">
                            </div>
                        </div>

                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small">Sell Price</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="sell_price" class="form-control"
                                       value="{{ old('sell_price', 0) }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <hr class="mt-1 mb-3">

                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary px-4 mr-2">
                            <i class="fas fa-save mr-1"></i> Save Product
                        </button>
                        <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary px-4">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Tips Panel --}}
    <div class="col-lg-4 col-xl-5">
        <div class="card shadow-sm">
            <div class="card-header py-2 bg-light">
                <h6 class="m-0 font-weight-bold text-muted">
                    <i class="fas fa-info-circle mr-1"></i> Tips
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        Select the correct <strong>category</strong> to keep your inventory organised.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        Choose <strong>Roll</strong> as unit if the product is measured per roll, then enter the metres per roll.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        Set a <strong>Low Stock Alert</strong> so you get notified before stock runs out.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        <strong>Purchase</strong> and <strong>Sell</strong> prices are used for profit/loss reports.
                    </li>
                </ul>
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
