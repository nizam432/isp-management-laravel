@extends('layouts.app')
@section('title', 'Vendors')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Vendors</h4>
        <a href="{{ route('inventory.vendors.create') }}" class="btn btn-primary btn-sm">+ Add Vendor</a>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name/phone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="blacklisted" {{ request('status') == 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-secondary">Filter</button>
                    <a href="{{ route('inventory.vendors.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Vendor No</th><th>Name</th><th>Phone</th><th>Type</th><th>Total Due</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $vendor->vendor_no }}</td>
                        <td>{{ $vendor->name }}<br><small class="text-muted">{{ $vendor->owner_name }}</small></td>
                        <td>{{ $vendor->phone }}</td>
                        <td>{{ ucfirst($vendor->vendor_type) }}</td>
                        <td class="{{ $vendor->total_due > 0 ? 'text-danger fw-semibold' : '' }}">৳{{ number_format($vendor->total_due, 2) }}</td>
                        <td><span class="badge bg-{{ $vendor->status == 'active' ? 'success' : ($vendor->status == 'blacklisted' ? 'danger' : 'secondary') }}">{{ ucfirst($vendor->status) }}</span></td>
                        <td>
                            <a href="{{ route('inventory.vendors.show', $vendor) }}" class="btn btn-sm btn-outline-info">View</a>
                            <a href="{{ route('inventory.vendors.edit', $vendor) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No vendors found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $vendors->links() }}</div>
    </div>
</div>
@endsection
