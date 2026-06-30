@extends('adminlte::page')
@section('title', 'Add Agent')
@section('page_title', 'Add Agent')
@section('page_actions')
    <a href="{{ route('agents.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection

@section('content')

<form action="{{ route('agents.store') }}" method="POST">
@csrf

<div class="card shadow-sm mb-3">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-user-shield mr-1"></i> Login Account
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="agent@example.com" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Agent এই email দিয়ে login করবে।</small>
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                       placeholder="Min 6 characters" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 form-group mb-0">
                <label class="font-weight-bold small">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Re-type password" required>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#2e7d32 0%,#43a047 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-id-card mr-1"></i> Agent Information
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold small">Agent Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold small">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="01XXXXXXXXX">
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold small">Area / Zone</label>
                <select name="area" class="form-control">
                    <option value="">-- Select Area --</option>
                    @foreach(\App\Models\Zone::all() as $zone)
                    <option value="{{ $zone->name }}" {{ old('area') == $zone->name ? 'selected' : '' }}>
                        {{ $zone->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold small">Commission Rate (%) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="commission_rate" class="form-control @error('commission_rate') is-invalid @enderror"
                           value="{{ old('commission_rate', 0) }}" min="0" max="100" step="0.01" required
                           oninput="updateCommissionPreview()">
                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                    @error('commission_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="text-muted" id="commissionPreview">Percentage of each payment collected by this agent.</small>
            </div>
            <div class="col-md-6 form-group mb-0">
                <label class="font-weight-bold small d-block">Status</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                    <label class="custom-control-label" for="is_active">Active</label>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-right">
        <a href="{{ route('agents.index') }}" class="btn btn-secondary mr-2">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Save Agent
        </button>
    </div>
</div>

</form>

@endsection

@section('js')
@parent
<script>
function updateCommissionPreview() {
    var rate = parseFloat(document.querySelector('[name=commission_rate]').value) || 0;
    var example = 1000;
    var commission = (example * rate / 100).toFixed(2);
    document.getElementById('commissionPreview').textContent =
        'Example: ৳' + example + ' বিলে এই agent পাবে ৳' + commission;
}
updateCommissionPreview();
</script>
@stop
