{{-- resources/views/roles/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Role Management')

@section('page_actions')
    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> নতুন Role
    </a>
@endsection

@section('page_content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-tag mr-1"></i> Roles</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Role Name</th>
                    <th>Permissions</th>
                    <th>Users</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $i => $role)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ ucfirst($role->name) }}</strong></td>
                    <td>
                        <span class="badge badge-info">{{ $role->permissions_count }} permissions</span>
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $role->users_count }} users</span>
                    </td>
                    <td class="text-right">
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-warning mr-1">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('\'{{ $role->name }}\' delete করবেন?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        কোনো role নেই। <a href="{{ route('roles.create') }}">তৈরি করুন</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
