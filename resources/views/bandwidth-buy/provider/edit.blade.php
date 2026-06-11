@extends('adminlte::page')
@section('title', 'Provider Edit')

@section('content_header')
    <h1 class="m-0 text-dark">Provider Edit</h1>
@endsection

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span class="font-weight-bold">Provider Edit</span>
        <a href="{{ route('bandwidth-buy.provider.index') }}" class="btn btn-primary btn-sm">Back</a>
    </div>
    <div class="card-body">

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('bandwidth-buy.provider.update', $provider) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Company Name <span class="text-danger">(require)</span></label>
                        <input type="text" name="company_name" class="form-control"
                               value="{{ old('company_name', $provider->company_name) }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Contact Person <span class="text-danger">(require)</span></label>
                        <input type="text" name="contact_person" class="form-control"
                               value="{{ old('contact_person', $provider->contact_person) }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Email <span class="text-danger">(require)</span></label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $provider->email) }}" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Phone No <span class="text-danger">(require and 11 digits)</span></label>
                        <input type="text" name="phone_no" class="form-control"
                               value="{{ old('phone_no', $provider->phone_no) }}" maxlength="11" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Document <span class="text-success">(optional)</span></label>
                        @if($provider->document)
                            <div class="mb-1">
                                <img src="{{ asset('storage/'.$provider->document) }}"
                                     style="height:50px;" alt="current doc">
                                <small class="text-muted d-block">Current file</small>
                            </div>
                        @endif
                        <input type="file" name="document" class="form-control-file"
                               accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Address <span class="text-success">(optional)</span></label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address', $provider->address) }}</textarea>
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
