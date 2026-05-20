{{-- resources/views/super-admin/dashboard.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Super Admin Dashboard')
@section('page_content')

{{-- Stats --}}
<div class="row">
    <div class="col-md-2">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ $stats['total_isp'] }}</h3><p>Total ISP</p></div>
            <div class="icon"><i class="fas fa-building"></i></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $stats['active_isp'] }}</h3><p>Active ISP</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $stats['pure_isp'] }}</h3><p>Pure ISP</p></div>
            <div class="icon"><i class="fas fa-network-wired"></i></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $stats['master_reseller'] }}</h3><p>Master Reseller</p></div>
            <div class="icon"><i class="fas fa-sitemap"></i></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ $stats['sub_reseller'] }}</h3><p>Sub Reseller</p></div>
            <div class="icon"><i class="fas fa-project-diagram"></i></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ $stats['total_plans'] }}</h3><p>Plans</p></div>
            <div class="icon"><i class="fas fa-tags"></i></div>
            <a href="{{ route('super-admin.plans.index') }}" class="small-box-footer">
                Manage <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- Recent ISPs --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-building mr-1"></i> Recent ISPs</h3>
        <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i> Add ISP
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Company</th>
                    <th>Type</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTenants as $tenant)
                <tr>
                    <td>
                        <strong>{{ $tenant->name }}</strong>
                        <br><small class="text-muted">{{ $tenant->email }}</small>
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
                    </td>
                    <td>
                        <span class="badge badge-{{ $tenant->is_active ? 'success' : 'danger' }}">
                            {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <small>{{ $tenant->plan_expires_at ? \Carbon\Carbon::parse($tenant->plan_expires_at)->format('d M Y') : '—' }}</small>
                    </td>
                    <td>
                        <a href="{{ route('super-admin.tenants.show', $tenant->id) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('super-admin.tenants.edit', $tenant->id) }}" class="btn btn-xs btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('super-admin.tenants.toggle', $tenant->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-{{ $tenant->is_active ? 'danger' : 'success' }}">
                                {{ $tenant->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">কোনো ISP নেই।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-sm btn-default">
            সব দেখুন →
        </a>
    </div>
</div>

@endsection
