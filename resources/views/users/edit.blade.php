{{-- resources/views/users/edit.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Edit User — ' . $user->name)

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
                <h3 class="card-title">
                    <i class="fas fa-user-edit mr-1"></i> Edit: {{ $user->name }}
                </h3>
            </div>
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">

                    {{-- Name --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Email (readonly) --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Email</label>
                        <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly>
                        <small class="text-muted">Email cannot be changed.</small>
                    </div>

                    {{-- Phone --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $user->phone) }}" placeholder="01XXXXXXXXX">
                    </div>

                    {{-- Role --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-control @error('role') is-invalid @enderror" required>
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}"
                                {{ (old('role', $currentRole) == $role->name) ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr>
                    <p class="text-muted small mb-2">Leave password blank to keep unchanged.</p>

                    {{-- Password --}}
                    <div class="form-group">
                        <label class="font-weight-bold">New Password</label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <form action="{{ route('users.toggle', $user) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="btn btn-{{ $user->is_active ? 'warning' : 'success' }}">
                            <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }} mr-1"></i>
                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <div>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Update User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
