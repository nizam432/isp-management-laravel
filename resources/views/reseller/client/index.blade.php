@extends('reseller.layouts.app')

@section('title', 'My Clients')

@section('content')

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Total Clients</p>
                <h4 class="font-weight-bold mb-0">{{ $stats['total'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Active</p>
                <h4 class="font-weight-bold mb-0 text-success">{{ $stats['active'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Expired</p>
                <h4 class="font-weight-bold mb-0 text-danger">{{ $stats['expired'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Inactive</p>
                <h4 class="font-weight-bold mb-0 text-secondary">{{ $stats['inactive'] }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body">
        <form method="GET" class="row mb-3">
            <div class="col-md-4 mb-2">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search by name, code, phone, username..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3 mb-2">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    <option value="active"    {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive"  {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="expired"   {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-sm btn-success w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered" style="font-size:.85rem">
                <thead style="background:#f4f6f9">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Package</th>
                        <th>PPPoE Username</th>
                        <th>Status</th>
                        <th>Expire Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $c)
                    <tr>
                        <td>{{ $c->customer_code }}</td>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->phone }}</td>
                        <td>{{ $c->package?->name ?? '—' }}</td>
                        <td>{{ $c->pppoe_username ?? '—' }}</td>
                        <td>
                            @php
                                $badgeColor = match($c->status) {
                                    'active' => 'success',
                                    'expired' => 'danger',
                                    'suspended' => 'warning',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge badge-{{ $badgeColor }}">{{ ucfirst($c->status) }}</span>
                        </td>
                        <td>{{ $c->expire_date?->format('d M Y') ?? '—' }}</td>
                        <td class="text-center">
                            <a href="{{ route('reseller.client.show', $c->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-users-slash mr-1"></i> No clients found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $clients->links() }}
    </div>
</div>
@stop
