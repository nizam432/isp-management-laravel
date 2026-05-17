{{-- resources/views/customers/edit.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Edit Customer: ' . $customer->name)

@section('page_actions')
    <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@section('page_content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Edit Customer Information</h3></div>
    <form action="{{ route('customers.update', $customer) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                    </div>
                    <div class="form-group">
                        <label>NID Number</label>
                        <input type="text" name="nid_number" class="form-control" value="{{ old('nid_number', $customer->nid_number) }}">
                    </div>
                    <div class="form-group">
                        <label>NID Photo</label>
                        <input type="file" name="nid_photo" class="form-control-file">
                        @if($customer->nid_photo)
                            <small><a href="{{ asset('storage/'.$customer->nid_photo) }}" target="_blank">View Current</a></small>
                        @endif
                    </div>
                    <div class="form-group">
                        <label>Customer Photo</label>
                        <input type="file" name="photo" class="form-control-file">
                        @if($customer->photo)
                            <img src="{{ asset('storage/'.$customer->photo) }}" height="50" class="mt-1 rounded">
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Package <span class="text-danger">*</span></label>
                        <select name="package_id" class="form-control" required>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" {{ old('package_id', $customer->package_id) == $pkg->id ? 'selected' : '' }}>
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
                                <option value="{{ $agent->id }}" {{ old('agent_id', $customer->agent_id) == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }} ({{ $agent->area }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Area</label>
                        <input type="text" name="area" class="form-control" value="{{ old('area', $customer->area) }}">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Connection Date</label>
                        <input type="date" name="connection_date" class="form-control" value="{{ old('connection_date', $customer->connection_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label>Billing Date <span class="text-danger">*</span></label>
                        <input type="number" name="billing_date" class="form-control" min="1" max="28" value="{{ old('billing_date', $customer->billing_date) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            @foreach(['active','inactive','suspended','expired'] as $s)
                                <option value="{{ $s }}" {{ old('status', $customer->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12"><h5 class="border-bottom pb-1 mt-2">Network / PPPoE</h5></div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>PPPoE Username</label>
                        <input type="text" name="pppoe_username" class="form-control" value="{{ old('pppoe_username', $customer->pppoe_username) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>PPPoE Password</label>
                        <input type="text" name="pppoe_password" class="form-control" value="{{ old('pppoe_password', $customer->pppoe_password) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>IP Address</label>
                        <input type="text" name="ip_address" class="form-control" value="{{ old('ip_address', $customer->ip_address) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>MAC Address</label>
                        <input type="text" name="mac_address" class="form-control" value="{{ old('mac_address', $customer->mac_address) }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $customer->remarks) }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Customer</button>
            <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
