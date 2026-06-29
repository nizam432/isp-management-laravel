@extends('adminlte::page')
@section('title', 'Add Category')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-plus-circle mr-2 text-primary"></i>Add Category
            </h4>
            <small class="text-muted">Create a new inventory category</small>
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
                <form action="{{ route('inventory.categories.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="font-weight-bold small">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="e.g. Networking, Cables, Tools" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Optional description for this category">{{ old('description') }}</textarea>
                    </div>

                    <hr class="mt-1 mb-3">

                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary px-4 mr-2">
                            <i class="fas fa-save mr-1"></i> Save Category
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
                    <i class="fas fa-info-circle mr-1"></i> Tips
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        Use clear, descriptive names like <strong>Networking Equipment</strong> or <strong>Fiber Cables</strong>.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        Categories help organise products and make stock reports easier to read.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        A category can only be deleted if it has no products assigned to it.
                    </li>
                </ul>
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
