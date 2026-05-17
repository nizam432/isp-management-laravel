{{-- resources/views/customers/create.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Add New Customer')

@section('page_actions')
    <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@section('page_content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Customer Information</h3></div>
    <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="row">
                {{-- Basic Info --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
                    <div class="form-group">
                        <label>NID Number</label>
                        <input type="text" name="nid_number" class="form-control" value="{{ old('nid_number') }}">
                    </div>
                    <div class="form-group">
                        <label>NID Photo</label>
                        <input type="file" name="nid_photo" class="form-control-file">
                    </div>
                    <div class="form-group">
                        <label>Customer Photo</label>
                        <input type="file" name="photo" class="form-control-file">
                    </div>
                </div>

                {{-- Connection Info --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Package <span class="text-danger">*</span></label>
                        <select name="package_id" class="form-control" required>
                            <option value="">-- Select Package --</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" {{ old('package_id') == $pkg->id ? 'selected' : '' }}>
                                    {{ $pkg->name }} — {{ number_format($pkg->price) }} BDT
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Agent</label>
                        <select name="agent_id" class="form-control">
                            <option value="">-- No Agent --</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }} ({{ $agent->area }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Area</label>
                        <input type="text" name="area" class="form-control" value="{{ old('area') }}">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Connection Date</label>
                        <input type="date" name="connection_date" class="form-control" value="{{ old('connection_date', date('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label>Billing Date <span class="text-danger">*</span></label>
                        <input type="number" name="billing_date" class="form-control" min="1" max="28" value="{{ old('billing_date', 1) }}" required>
                        <small class="text-muted">Day of month (1–28)</small>
                    </div>
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="active"   {{ old('status') == 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- PPPoE / Network --}}
            <div class="row">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-1 mt-2">Network / PPPoE</h5>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>PPPoE Username</label>
                        <input type="text" name="pppoe_username" class="form-control" value="{{ old('pppoe_username') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>PPPoE Password</label>
                        <input type="text" name="pppoe_password" class="form-control" value="{{ old('pppoe_password') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>IP Address</label>
                        <input type="text" name="ip_address" class="form-control" value="{{ old('ip_address') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>MAC Address</label>
                        <input type="text" name="mac_address" class="form-control" value="{{ old('mac_address') }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Customer</button>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
