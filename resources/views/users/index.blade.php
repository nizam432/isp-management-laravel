{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'User Management')

@section('page_actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-user-plus mr-1"></i> Add User
    </a>
@endsection

@section('page_content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Staff Users</h3>
        <div class="card-tools">
            <input type="text" id="searchInput" class="form-control form-control-sm"
                   placeholder="Search name / email..." style="width:220px">
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0" id="usersTable">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="{{ $user->adminlte_image() }}"
                                 class="img-circle mr-2" width="32" height="32"
                                 style="object-fit:cover">
                            <strong>{{ $user->name }}</strong>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?? '—' }}</td>
                    <td>
                        @php
                            $role = $user->roles->first()?->name ?? 'no role';
                            $colors = [
                                'manager'    => 'primary',
                                'staff'      => 'info',
                                'agent'      => 'warning',
                                'accountant' => 'success',
                                'support'    => 'secondary',
                            ];
                            $color = $colors[$role] ?? 'dark';
                        @endphp
                        <span class="badge badge-{{ $color }}">{{ ucfirst($role) }}</span>
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td><small>{{ $user->created_at->format('d M Y') }}</small></td>
                    <td class="text-right">
                        <a href="{{ route('users.edit', $user) }}"
                           class="btn btn-sm btn-warning mr-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form action="{{ route('users.toggle', $user) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit"
                                    class="btn btn-sm btn-{{ $user->is_active ? 'secondary' : 'success' }} mr-1"
                                    title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>

                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete user {{ $user->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        No staff users found. <a href="{{ route('users.create') }}">Add one</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer">
        {{ $users->links() }}
    </div>
    @endif
</div>

@endsection

@push('js')
<script>
document.getElementById('searchInput').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>
@endpush
