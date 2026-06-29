@extends('adminlte::page')
@section('title', 'Add Location')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-plus-circle mr-2 text-primary"></i>Add Store Location
            </h4>
            <small class="text-muted">Create a new inventory store location</small>
        </div>
        <a href="{{ route('inventory.locations.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back to Locations
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="row">
    <div class="col-lg-7 col-xl-6">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-warehouse mr-1"></i> Location Details
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.locations.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="font-weight-bold small">Location Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="e.g. Main Warehouse, Branch Office" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Address</label>
                        <textarea name="address" class="form-control" rows="2"
                                  placeholder="Full address of this location">{{ old('address') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control"
                                   value="{{ old('contact_person') }}"
                                   placeholder="Responsible person name">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone') }}"
                                   placeholder="e.g. 01XXXXXXXXX">
                        </div>
                    </div>

                    <hr class="mt-1 mb-3">

                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary px-4 mr-2">
                            <i class="fas fa-save mr-1"></i> Save Location
                        </button>
                        <a href="{{ route('inventory.locations.index') }}" class="btn btn-secondary px-4">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5 col-xl-6">
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
                        Each <strong>location</strong> represents a physical store, warehouse, or branch.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        Stock can be tracked separately per location after purchase assignments.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        Add a <strong>contact person</strong> and <strong>phone</strong> for quick reference.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        Locations can be activated or deactivated from the list at any time.
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
</style>
@stop
