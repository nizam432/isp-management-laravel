@extends('reseller.layouts.app')

@section('title', 'Configuration')

@section('content')

<div class="row">
    <div class="col-md-7 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3"><i class="fas fa-user-edit text-primary mr-1"></i> Update Profile</h6>

                <form method="POST" action="{{ route('reseller.configuration.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="small font-weight-bold">Contact Person <span class="text-danger">*</span></label>
                        <input type="text" name="contact_person" class="form-control form-control-sm @error('contact_person') is-invalid @enderror"
                            value="{{ old('contact_person', $reseller->contact_person) }}" required>
                        @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-sm"
                            value="{{ old('email', $reseller->email) }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Mobile No. <span class="text-danger">*</span></label>
                                <input type="text" name="mobile" class="form-control form-control-sm @error('mobile') is-invalid @enderror"
                                    value="{{ old('mobile', $reseller->mobile) }}" required>
                                @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Phone No.</label>
                                <input type="text" name="phone" class="form-control form-control-sm"
                                    value="{{ old('phone', $reseller->phone) }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control form-control-sm" rows="2" required>{{ old('address', $reseller->address) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Logo</label>
                        <input type="file" name="logo" class="form-control-file form-control-sm" accept="image/*">
                        @if($reseller->logo)
                            <img src="{{ asset('storage/' . $reseller->logo) }}" style="max-width:80px;margin-top:8px;border-radius:6px">
                        @endif
                    </div>

                    <button type="submit" class="btn btn-success btn-sm px-4">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5 mb-3">
        {{-- Read-only account info --}}
        <div class="card border-0 shadow-sm mb-3" style="border-radius:12px">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3"><i class="fas fa-info-circle text-info mr-1"></i> Account Information</h6>
                <table class="table table-sm table-borderless mb-0" style="font-size:.85rem">
                    <tr><td class="text-muted" style="width:140px">POP Code</td><td class="font-weight-bold">{{ $reseller->code }}</td></tr>
                    <tr><td class="text-muted">Business Name</td><td>{{ $reseller->business_name }}</td></tr>
                    <tr><td class="text-muted">Username</td><td>{{ $reseller->username }}</td></tr>
                    <tr><td class="text-muted">POP Type</td><td><span class="badge badge-info">{{ ucfirst($reseller->pop_type) }}</span></td></tr>
                    <tr><td class="text-muted">Tariff</td><td>{{ $reseller->tariff?->name ?? 'N/A' }}</td></tr>
                </table>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3"><i class="fas fa-lock text-warning mr-1"></i> Change Password</h6>

                <form method="POST" action="{{ route('reseller.configuration.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="small font-weight-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control form-control-sm @error('current_password') is-invalid @enderror" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">New Password</label>
                        <input type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-sm" required>
                    </div>

                    <button type="submit" class="btn btn-warning btn-sm px-4">
                        <i class="fas fa-key mr-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
