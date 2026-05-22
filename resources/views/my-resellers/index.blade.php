{{-- resources/views/my-resellers/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'My Resellers')
@section('page_actions')
    <a href="{{ route('my-resellers.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Add Sub Reseller
    </a>
@endsection
@section('page_content')

{{-- My Info --}}
@if($myTenant)
<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i>
    আপনি: <strong>{{ $myTenant->name }}</strong> —
    Plan: <strong>{{ $myTenant->plan->name ?? '—' }}</strong> —
    Type: <span class="badge badge-warning">Master Reseller</span>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-sitemap mr-1"></i> Sub Resellers</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($resellers as $reseller)
                <tr>
                    <td><strong>{{ $reseller->name }}</strong></td>
                    <td>{{ $reseller->email }}</td>
                    <td>{{ $reseller->phone ?? '—' }}</td>
                    <td>
                        <span class="badge badge-primary">{{ $reseller->plan->name ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $reseller->is_active ? 'success' : 'danger' }}">
                            {{ $reseller->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <small>
                            {{ $reseller->plan_expires_at
                                ? \Carbon\Carbon::parse($reseller->plan_expires_at)->format('d M Y')
                                : '—' }}
                        </small>
                    </td>
                    <td>
                        {{-- Edit Button --}}
                        <a href="{{ route('my-resellers.edit', $reseller->id) }}"
                           class="btn btn-xs btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>

                        {{-- Toggle Button --}}
                        <form action="{{ route('my-resellers.toggle', $reseller->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-{{ $reseller->is_active ? 'danger' : 'success' }}">
                                {{ $reseller->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        কোনো Sub Reseller নেই।
                        <a href="{{ route('my-resellers.create') }}">Add করুন</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $resellers->links() }}
    </div>
</div>

@endsection