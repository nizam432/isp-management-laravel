{{-- resources/views/super-admin/tenants/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'ISP Management')
@section('page_actions')
    <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Add ISP
    </a>
@endsection
@section('page_content')

{{-- Filter --}}
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline flex-wrap gap-2">
            <input type="text" name="search" class="form-control form-control-sm mr-2"
                   placeholder="Company name / email" value="{{ request('search') }}">
            <select name="type" class="form-control form-control-sm mr-2">
                <option value="">All Types</option>
                <option value="1" {{ request('type') == 1 ? 'selected' : '' }}>Pure ISP</option>
                <option value="2" {{ request('type') == 2 ? 'selected' : '' }}>Master Reseller</option>
                <option value="3" {{ request('type') == 3 ? 'selected' : '' }}>Sub Reseller</option>
            </select>
            <select name="plan" class="form-control form-control-sm mr-2">
                <option value="">All Plans</option>
                @foreach($plans as $plan)
                <option value="{{ $plan->id }}" {{ request('plan') == $plan->id ? 'selected' : '' }}>
                    {{ $plan->name }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-default mr-1">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-sm btn-secondary">Reset</a>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Company</th>
                    <th>Type</th>
                    <th>Plan</th>
                    <th>Parent</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                <tr>
                    <td>
                        <strong>{{ $tenant->name }}</strong>
                        <br><small class="text-muted">{{ $tenant->email }}</small>
                        <br><small class="text-muted">{{ $tenant->phone }}</small>
                    </td>
                    <td>
                        @if($tenant->is_reseller == 1)
                            <span class="badge badge-info">Pure ISP</span>
                        @elseif($tenant->is_reseller == 2)
                            <span class="badge badge-warning">Master Reseller</span>
                        @else
                            <span class="badge badge-secondary">Sub Reseller</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-primary">{{ $tenant->plan->name ?? '—' }}</span>
                        <br><small>৳{{ number_format($tenant->plan->price ?? 0) }}/মাস</small>
                    </td>
                    <td>{{ $tenant->parent->name ?? '—' }}</td>
                    <td>
                        <span class="badge badge-{{ $tenant->is_active ? 'success' : 'danger' }}">
                            {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <small>
                            {{ $tenant->plan_expires_at
                                ? \Carbon\Carbon::parse($tenant->plan_expires_at)->format('d M Y')
                                : '—' }}
                        </small>
                    </td>
                    <td>
                        <a href="{{ route('super-admin.tenants.show', $tenant->id) }}"
                           class="btn btn-xs btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('super-admin.tenants.edit', $tenant->id) }}"
                           class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('super-admin.tenants.toggle', $tenant->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-{{ $tenant->is_active ? 'danger' : 'success' }}"
                                    title="{{ $tenant->is_active ? 'Disable' : 'Enable' }}">
                                <i class="fas fa-{{ $tenant->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">কোনো ISP নেই।</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $tenants->withQueryString()->links() }}
    </div>
</div>

@endsection