@extends('layouts.app')
@section('title', 'Store Locations')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Store Locations</h4>
        <a href="{{ route('inventory.locations.create') }}" class="btn btn-primary btn-sm">+ Add Location</a>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Name</th><th>Contact Person</th><th>Phone</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($locations as $location)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $location->name }}<br><small class="text-muted">{{ $location->address }}</small></td>
                        <td>{{ $location->contact_person ?? '—' }}</td>
                        <td>{{ $location->phone ?? '—' }}</td>
                        <td><span class="badge {{ $location->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $location->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <a href="{{ route('inventory.locations.edit', $location) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('inventory.locations.toggle', $location) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-{{ $location->is_active ? 'warning' : 'success' }}">
                                    {{ $location->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No locations found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $locations->links() }}</div>
    </div>
</div>
@endsection
