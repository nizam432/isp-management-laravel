@extends('adminlte::page')
@section('title', 'Edit Category')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-edit mr-2 text-warning"></i>Edit Category
            </h4>
            <small class="text-muted">Update details for <strong>{{ $category->name }}</strong></small>
        </div>
        <a href="{{ route('inventory.categories.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back to Categories
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="row">
    <div class="col-lg-6 col-xl-5">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-tag mr-1"></i> Category Information
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.categories.update', $category) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="form-group">
                        <label class="font-weight-bold small">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $category->name) }}"
                               placeholder="e.g. Networking, Cables, Tools" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Optional description for this category">{{ old('description', $category->description) }}</textarea>
                    </div>

                    <hr class="mt-1 mb-3">

                    <div class="d-flex">
                        <button type="submit" class="btn btn-warning px-4 mr-2">
                            <i class="fas fa-save mr-1"></i> Update Category
                        </button>
                        <a href="{{ route('inventory.categories.index') }}" class="btn btn-secondary px-4">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-header py-2 bg-light">
                <h6 class="m-0 font-weight-bold text-muted">
                    <i class="fas fa-chart-bar mr-1"></i> Category Info
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="small text-muted pl-3">Name</td>
                            <td class="pr-3 font-weight-bold">{{ $category->name }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Description</td>
                            <td class="pr-3 text-muted">{{ $category->description ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Total Products</td>
                            <td class="pr-3">
                                <span class="badge badge-secondary">{{ $category->products_count ?? $category->products()->count() }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    .card-header h6 { font-size: 13px; letter-spacing: .3px; }
    .form-group label { color: #555; }
</style>
@stop
