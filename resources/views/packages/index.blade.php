{{-- resources/views/packages/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Packages')
@section('page_actions')
    <a href="{{ route('packages.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Package</a>
@endsection
@section('page_content')
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead class="thead-light">
                <tr><th>Name</th><th>Speed</th><th>Data</th><th>Price</th><th>Type</th><th>Customers</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($packages as $pkg)
                <tr>
                    <td><a href="{{ route('packages.show', $pkg) }}">{{ $pkg->name }}</a></td>
                    <td>{{ $pkg->speed_download }}↓ / {{ $pkg->speed_upload }}↑ Mbps</td>
                    <td>{{ $pkg->data_limit == 0 ? 'Unlimited' : $pkg->data_limit.' GB' }}</td>
                    <td>{{ number_format($pkg->price) }} BDT</td>
                    <td>{{ ucfirst($pkg->type) }}</td>
                    <td><span class="badge badge-info">{{ $pkg->customers_count }}</span></td>
                    <td>
                        <span class="badge badge-{{ $pkg->is_active ? 'success' : 'secondary' }}">
                            {{ $pkg->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('packages.edit', $pkg) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('packages.toggle', $pkg) }}" method="POST" class="d-inline">
                            @csrf @method('PATCH')
                            <button class="btn btn-xs btn-{{ $pkg->is_active ? 'secondary' : 'success' }}">
                                <i class="fas fa-{{ $pkg->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('packages.destroy', $pkg) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this package?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No packages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $packages->links() }}</div>
</div>
@endsection
