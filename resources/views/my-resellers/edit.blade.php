{{-- resources/views/my-resellers/edit.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Edit Sub Reseller')
@section('page_actions')
    <a href="{{ route('my-resellers.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit — {{ $reseller->name }}</h3>
    </div>
    <form action="{{ route('my-resellers.update', $reseller->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control"
                               value="{{ old('company_name', $reseller->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $reseller->phone) }}">
                    </div>
                    <div class="form-group">
                        <label>New Password <small class="text-muted">(খালি রাখলে পরিবর্তন হবে না)</small></label>
                        <input type="password" name="password" class="form-control"
                               placeholder="নতুন password দিন">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Plan <span class="text-danger">*</span></label>
                        <select name="plan_id" class="form-control" required>
                            @foreach($plans as $plan)
                            <option value="{{ $plan->id }}"
                                {{ old('plan_id', $reseller->plan_id) == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} — ৳{{ number_format($plan->price) }}/মাস
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1" {{ $reseller->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$reseller->is_active ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Email: <strong>{{ $reseller->email }}</strong>
                        <br>Subdomain: <strong>{{ $reseller->id }}.{{ env('APP_DOMAIN') }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save mr-1"></i> Update
            </button>
        </div>
    </form>
</div>

@endsection
