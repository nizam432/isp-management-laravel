@extends('adminlte::page')
@section('title', 'Add Vendor')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-plus-circle mr-2 text-primary"></i>Add Vendor
            </h4>
            <small class="text-muted">Create a new supplier or manufacturer</small>
        </div>
        <a href="{{ route('inventory.vendors.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back to Vendors
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<form action="{{ route('inventory.vendors.store') }}" method="POST">
@csrf

{{-- Basic Info --}}
<div class="card shadow-sm mb-3">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-user-tie mr-1"></i> Basic Information
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Business Name <span class="text-danger">*</span></label>
                <input type="text" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="Vendor business name" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Owner Name</label>
                <input type="text" name="owner_name" class="form-control"
                       value="{{ old('owner_name') }}" placeholder="Owner / contact person">
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Vendor Type <span class="text-danger">*</span></label>
                <select name="vendor_type" class="form-control" required>
                    <option value="supplier"     {{ old('vendor_type') == 'supplier'     ? 'selected' : '' }}>Supplier</option>
                    <option value="manufacturer" {{ old('vendor_type') == 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                    <option value="both"         {{ old('vendor_type') == 'both'         ? 'selected' : '' }}>Both</option>
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Phone <span class="text-danger">*</span></label>
                <input type="text" name="phone"
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required>
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Alternate Phone</label>
                <input type="text" name="alternate_phone" class="form-control"
                       value="{{ old('alternate_phone') }}" placeholder="Optional">
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Email</label>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email') }}" placeholder="vendor@example.com">
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Area</label>
                <input type="text" name="area" class="form-control"
                       value="{{ old('area') }}" placeholder="e.g. Motijheel">
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">District</label>
                <input type="text" name="district" class="form-control"
                       value="{{ old('district') }}" placeholder="e.g. Dhaka">
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Business Type</label>
                <input type="text" name="business_type" class="form-control"
                       value="{{ old('business_type') }}" placeholder="e.g. ISP Equipment, Cable">
            </div>
            <div class="col-12 form-group">
                <label class="font-weight-bold small">Full Address</label>
                <textarea name="address" class="form-control" rows="2"
                          placeholder="Full address of the vendor">{{ old('address') }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- Legal Info --}}
<div class="card shadow-sm mb-3">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-file-alt mr-1"></i> Legal & Tax Information
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Trade License</label>
                <input type="text" name="trade_license" class="form-control"
                       value="{{ old('trade_license') }}">
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">TIN No</label>
                <input type="text" name="tin_no" class="form-control"
                       value="{{ old('tin_no') }}">
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">BIN No</label>
                <input type="text" name="bin_no" class="form-control"
                       value="{{ old('bin_no') }}">
            </div>
        </div>
    </div>
</div>

{{-- Bank & Financial Info --}}
<div class="card shadow-sm mb-3">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-university mr-1"></i> Bank & Financial Information
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Bank Name</label>
                <input type="text" name="bank_name" class="form-control"
                       value="{{ old('bank_name') }}" placeholder="e.g. Dutch Bangla Bank">
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Bank Account No</label>
                <input type="text" name="bank_account" class="form-control"
                       value="{{ old('bank_account') }}">
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Bank Branch</label>
                <input type="text" name="bank_branch" class="form-control"
                       value="{{ old('bank_branch') }}" placeholder="Branch name">
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Opening Balance</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">৳</span>
                    </div>
                    <input type="number" name="opening_balance" class="form-control"
                           value="{{ old('opening_balance', 0) }}" min="0" step="0.01">
                </div>
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Credit Limit</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">৳</span>
                    </div>
                    <input type="number" name="credit_limit" class="form-control"
                           value="{{ old('credit_limit') }}" min="0" step="0.01"
                           placeholder="0 = unlimited">
                </div>
            </div>
            <div class="col-12 form-group">
                <label class="font-weight-bold small">Note</label>
                <textarea name="note" class="form-control" rows="2"
                          placeholder="Any additional notes about this vendor">{{ old('note') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="d-flex mb-4">
    <button type="submit" class="btn btn-primary px-4 mr-2">
        <i class="fas fa-save mr-1"></i> Save Vendor
    </button>
    <a href="{{ route('inventory.vendors.index') }}" class="btn btn-secondary px-4">
        <i class="fas fa-times mr-1"></i> Cancel
    </a>
</div>

</form>

@endsection

@section('css')
<style>
    .card-header h6 { font-size: 13px; letter-spacing: .3px; }
    .form-group label { color: #555; }
    .input-group-text { background: #f4f6f9; border-color: #ced4da; }
</style>
@stop
