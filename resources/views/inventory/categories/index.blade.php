@extends('layouts.app')
@section('title', 'Product Categories')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Product Categories</h4>
        <a href="{{ route('inventory.categories.create') }}" class="btn btn-primary btn-sm">+ Add Category</a>
    </div>

    @include('inventory._partials.alerts')

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->description ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $category->products_count }}</span></td>
                        <td>
                            <a href="{{ route('inventory.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @if($category->isDeletable())
                            <form action="{{ route('inventory.categories.destroy', $category) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No categories found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $categories->links() }}</div>
    </div>
</div>
@endsection
