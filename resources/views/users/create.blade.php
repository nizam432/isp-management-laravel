{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Add Staff User')

@section('page_actions')
    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection

@section('page_content')

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> New Staff User</h3>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="card-body">

                    {{-- Name --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
                        <small class="text-muted">Used for login</small>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Phone --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone') }}" placeholder="01XXXXXXXXX">
                    </div>

                    {{-- Role --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-control @error('role') is-invalid @enderror" required>
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr>

                    {{-- Password --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    {{-- Role Access Summary --}}
                    <div class="alert alert-info mt-3 mb-0 py-2" id="roleInfo" style="display:none">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="roleInfoText"></span>
                    </div>

                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
const roleDescriptions = {
    manager:    'Full access to customers, billing, reports. Cannot manage system settings.',
    staff:      'Can manage customers and collect payments. Limited reporting access.',
    agent:      'Can add customers and track own commissions only.',
    accountant: 'Access to billing, payments, and financial reports only.',
    support:    'Can view customers and manage support tickets only.',
};

document.querySelector('select[name="role"]').addEventListener('change', function () {
    const info = document.getElementById('roleInfo');
    const text = document.getElementById('roleInfoText');
    if (this.value && roleDescriptions[this.value]) {
        text.textContent = roleDescriptions[this.value];
        info.style.display = 'block';
    } else {
        info.style.display = 'none';
    }
});
</script>
@endpush
