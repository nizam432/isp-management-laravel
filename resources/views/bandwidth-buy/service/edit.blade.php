@extends('adminlte::page')
@section('title', 'Service Edit')

@section('content_header')
    <h1 class="m-0 text-dark">Service Edit</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span class="font-weight-bold">Service Edit</span>
        <a href="{{ route('bandwidth-buy.service.index') }}" class="btn btn-primary btn-sm">Back</a>
    </div>
    <div class="card-body">

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('bandwidth-buy.service.update', $service) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Name <span class="text-danger">(require)</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $service->name) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $service->description) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <button type="submit" class="btn btn-warning">Update</button>
            </div>
        </form>

    </div>
</div>
@endsection
