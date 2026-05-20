{{-- resources/views/super-admin/tenants/create.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Add New ISP')
@section('page_actions')
    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<div class="card">
    <div class="card-header"><h3 class="card-title">ISP Information</h3></div>
    <form action="{{ route('super-admin.tenants.store') }}" method="POST">
        @csrf
        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
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
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Subdomain <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="subdomain" class="form-control"
                                   value="{{ old('subdomain') }}" placeholder="dhaka-fiber" required>
                            <div class="input-group-append">
                                <span class="input-group-text">.{{ env('APP_DOMAIN', 'innovativeitbd.com') }}</span>
                            </div>
                        </div>
                        <small class="text-muted">শুধু lowercase, number, dash ব্যবহার করুন</small>
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

                    <div class="form-group">
                        <label>ISP Type <span class="text-danger">*</span></label>
                        <select name="is_reseller" class="form-control" id="ispType"
                                onchange="toggleParent()" required>
                            <option value="1" {{ old('is_reseller') == 1 ? 'selected' : '' }}>
                                Pure ISP (Independent)
                            </option>
                            <option value="2" {{ old('is_reseller') == 2 ? 'selected' : '' }}>
                                Master Reseller (নিজেও ISP + Sub Reseller তৈরি করতে পারবে)
                            </option>
                            <option value="3" {{ old('is_reseller') == 3 ? 'selected' : '' }}>
                                Sub Reseller (কোনো Master Reseller এর under এ)
                            </option>
                        </select>
                    </div>

                    <div class="form-group" id="parentDiv" style="display:none">
                        <label>Parent ISP (Master Reseller) <span class="text-danger">*</span></label>
                        <select name="parent_id" class="form-control">
                            <option value="">-- Parent Select --</option>
                            @foreach($masterResellers as $mr)
                            <option value="{{ $mr->id }}" {{ old('parent_id') == $mr->id ? 'selected' : '' }}>
                                {{ $mr->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Create ISP
            </button>
        </div>
    </form>
</div>

<script>
function toggleParent() {
    const type = document.getElementById('ispType').value;
    document.getElementById('parentDiv').style.display = type == 3 ? '' : 'none';
}
// On page load
toggleParent();
</script>

@endsection
