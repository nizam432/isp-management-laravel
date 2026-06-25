@extends('layouts.app')
@section('title', 'Edit Product')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Edit Product</h4>
        <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
    </div>

    <div class="card border-0 shadow-sm" style="max-width: 700px">
        <div class="card-body">
            <form action="{{ route('inventory.products.update', $product) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $product->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Model</label>
                        <input type="text" name="model" class="form-control" value="{{ old('model', $product->model) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                        <select name="unit" class="form-select" required id="unitSelect">
                            @foreach(['pcs','meter','roll','box'] as $u)
                            <option value="{{ $u }}" {{ old('unit', $product->unit) == $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3" id="meterPerRollDiv" style="{{ $product->unit === 'roll' ? '' : 'display:none' }}">
                        <label class="form-label fw-semibold">Meter/Roll</label>
                        <input type="number" name="meter_per_roll" class="form-control"
                               value="{{ old('meter_per_roll', $product->meter_per_roll) }}" min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Low Stock Alert <span class="text-danger">*</span></label>
                        <input type="number" name="low_stock_alert" class="form-control"
                               value="{{ old('low_stock_alert', $product->low_stock_alert) }}" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Purchase Price</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" name="purchase_price" class="form-control"
                                   value="{{ old('purchase_price', $product->purchase_price) }}" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sell Price</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" name="sell_price" class="form-control"
                                   value="{{ old('sell_price', $product->sell_price) }}" min="0" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.getElementById('unitSelect').addEventListener('change', function() {
        document.getElementById('meterPerRollDiv').style.display = this.value === 'roll' ? 'block' : 'none';
    });
</script>
@endpush
@endsection
