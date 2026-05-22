{{-- resources/views/my-resellers/create.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Add Sub Reseller')
@section('page_actions')
    <a href="{{ route('my-resellers.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Sub Reseller Information</h3>
    </div>
    <form action="{{ route('my-resellers.store') }}" method="POST">
        @csrf
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
                               value="{{ old('company_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email') }}" required>
                        <small class="text-muted">এই email দিয়ে login করবে</small>
                    </div>
                    <div class="form-group">
                        <label>Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Subdomain <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="subdomain" class="form-control"
                                   value="{{ old('subdomain') }}"
                                   placeholder="mirpur-net" required>
                            <div class="input-group-append">
                                <span class="input-group-text">.{{ env('APP_DOMAIN', 'innovativeitbd.com') }}</span>
                            </div>
                        </div>
                        <small class="text-muted">শুধু lowercase, number, dash</small>
                    </div>
                    <div class="form-group">
                        <label>Plan <span class="text-danger">*</span></label>
                        <select name="plan_id" class="form-control" required>
                            <option value="">-- Plan Select --</option>
                            @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} — ৳{{ number_format($plan->price) }}/মাস
                                ({{ $plan->max_customers == -1 ? 'Unlimited' : $plan->max_customers }} customers)
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        এই Sub Reseller আপনার under এ কাজ করবে।
                        <br>Parent: <strong>{{ $myTenant->name ?? '—' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Create Sub Reseller
            </button>
        </div>
    </form>
</div>

@endsection
