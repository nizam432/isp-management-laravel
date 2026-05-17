{{-- resources/views/packages/create.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Add Package')
@section('page_actions')
    <a href="{{ route('packages.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
@endsection
@section('page_content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Package Information</h3></div>
    <form action="{{ route('packages.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Package Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. 10 Mbps Home" required>
                    </div>
                    <div class="form-group">
                        <label>Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="home"     {{ old('type') == 'home'     ? 'selected' : '' }}>Home</option>
                            <option value="business" {{ old('type') == 'business' ? 'selected' : '' }}>Business</option>
                            <option value="student"  {{ old('type') == 'student'  ? 'selected' : '' }}>Student</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Monthly Price (BDT) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" value="{{ old('price') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Connection Fee (BDT)</label>
                        <input type="number" name="connection_fee" class="form-control" value="{{ old('connection_fee', 0) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Download Speed (Mbps) <span class="text-danger">*</span></label>
                        <input type="number" name="speed_download" class="form-control" value="{{ old('speed_download') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Upload Speed (Mbps) <span class="text-danger">*</span></label>
                        <input type="number" name="speed_upload" class="form-control" value="{{ old('speed_upload') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Data Limit (GB)</label>
                        <input type="number" name="data_limit" class="form-control" value="{{ old('data_limit', 0) }}">
                        <small class="text-muted">0 = Unlimited</small>
                    </div>
                    <div class="form-group">
                        <label>MikroTik Profile</label>
                        <input type="text" name="mikrotik_profile" class="form-control" value="{{ old('mikrotik_profile') }}" placeholder="Queue profile name">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Package</button>
            <a href="{{ route('packages.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
