{{-- resources/views/customers/index.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Customers')

@section('page_actions')
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Add Customer
    </a>
@endsection

@section('page_content')
<div class="card">
    <div class="card-header">
        {{-- Search & Filter Form --}}
        <form method="GET" class="form-inline flex-wrap gap-2">
            <input type="text" name="search" class="form-control form-control-sm mr-2"
                   placeholder="Search name / phone / code" value="{{ request('search') }}">
            <select name="status" class="form-control form-control-sm mr-2">
                <option value="">All Status</option>
                <option value="active"    {{ request('status') == 'active'    ? 'selected' : '' }}>Active</option>
                <option value="inactive"  {{ request('status') == 'inactive'  ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="expired"   {{ request('status') == 'expired'   ? 'selected' : '' }}>Expired</option>
            </select>
            <button type="submit" class="btn btn-sm btn-default mr-1"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary">Reset</a>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead class="thead-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Package</th>
                    <th>Area</th>
                    <th>Status</th>
                    <th>Billing Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td><code>{{ $customer->customer_code }}</code></td>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a>
                    </td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->package->name ?? 'N/A' }}</td>
                    <td>{{ $customer->area ?? '-' }}</td>
                    <td>
                        <span class="badge badge-{{ $customer->status === 'active' ? 'success' : ($customer->status === 'suspended' ? 'warning' : ($customer->status === 'expired' ? 'danger' : 'secondary')) }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </td>
                    <td>{{ $customer->billing_date }}</td>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this customer?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $customers->withQueryString()->links() }}
    </div>
</div>
@endsection
