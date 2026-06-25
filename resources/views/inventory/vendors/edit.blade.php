@extends('layouts.app')
@section('title', 'Add Vendor')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Add Vendor</h4>
        <a href="{{ route('inventory.vendors.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('inventory.vendors.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Business Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Owner Name</label>
                        <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Vendor Type <span class="text-danger">*</span></label>
                        <select name="vendor_type" class="form-select" required>
                            <option value="supplier" {{ old('vendor_type') == 'supplier' ? 'selected' : '' }}>Supplier</option>
                            <option value="manufacturer" {{ old('vendor_type') == 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                            <option value="both" {{ old('vendor_type') == 'both' ? 'selected' : '' }}>Both</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Alternate Phone</label>
                        <input type="text" name="alternate_phone" class="form-control" value="{{ old('alternate_phone') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Area</label>
                        <input type="text" name="area" class="form-control" value="{{ old('area') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">District</label>
                        <input type="text" name="district" class="form-control" value="{{ old('district') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Business Type</label>
                        <input type="text" name="business_type" class="form-control" value="{{ old('business_type') }}" placeholder="e.g. ISP Equipment, Cable">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Trade License</label>
                        <input type="text" name="trade_license" class="form-control" value="{{ old('trade_license') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">TIN No</label>
                        <input type="text" name="tin_no" class="form-control" value="{{ old('tin_no') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">BIN No</label>
                        <input type="text" name="bin_no" class="form-control" value="{{ old('bin_no') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bank Account</label>
                        <input type="text" name="bank_account" class="form-control" value="{{ old('bank_account') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bank Branch</label>
                        <input type="text" name="bank_branch" class="form-control" value="{{ old('bank_branch') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Opening Balance</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" name="opening_balance" class="form-control" value="{{ old('opening_balance', 0) }}" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Credit Limit</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" name="credit_limit" class="form-control" value="{{ old('credit_limit') }}" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Note</label>
                        <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Save Vendor</button>
                    <a href="{{ route('inventory.vendors.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
