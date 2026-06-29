@extends('adminlte::page')
@section('title', 'Edit Location')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-edit mr-2 text-warning"></i>Edit Location
            </h4>
            <small class="text-muted">Update details for <strong>{{ $location->name }}</strong></small>
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
                <form action="{{ route('inventory.locations.update', $location) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="form-group">
                        <label class="font-weight-bold small">Location Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $location->name) }}"
                               placeholder="e.g. Main Warehouse, Branch Office" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Address</label>
                        <textarea name="address" class="form-control" rows="2"
                                  placeholder="Full address of this location">{{ old('address', $location->address) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control"
                                   value="{{ old('contact_person', $location->contact_person) }}"
                                   placeholder="Responsible person name">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $location->phone) }}"
                                   placeholder="e.g. 01XXXXXXXXX">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="isActive"
                                   name="is_active" value="1"
                                   {{ old('is_active', $location->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold small" for="isActive">
                                Active Location
                            </label>
                        </div>
                        <small class="text-muted">Inactive locations will not appear in stock assignment forms.</small>
                    </div>

                    <hr class="mt-1 mb-3">

                    <div class="d-flex">
                        <button type="submit" class="btn btn-warning px-4 mr-2">
                            <i class="fas fa-save mr-1"></i> Update Location
                        </button>
                        <a href="{{ route('inventory.locations.index') }}" class="btn btn-secondary px-4">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Location Info Panel --}}
    <div class="col-lg-5 col-xl-6">
        <div class="card shadow-sm">
            <div class="card-header py-2 bg-light">
                <h6 class="m-0 font-weight-bold text-muted">
                    <i class="fas fa-info-circle mr-1"></i> Location Info
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="small text-muted pl-3">Name</td>
                            <td class="pr-3 font-weight-bold">{{ $location->name }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Address</td>
                            <td class="pr-3 text-muted">{{ $location->address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Contact Person</td>
                            <td class="pr-3">{{ $location->contact_person ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Phone</td>
                            <td class="pr-3">{{ $location->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Status</td>
                            <td class="pr-3">
                                <span class="badge {{ $location->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $location->is_active ? 'Active' : 'Inactive' }}
                                </span>
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
</style>
@stop
