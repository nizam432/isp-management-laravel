{{-- resources/views/profile/show.blade.php --}}
@extends('layouts.app')
@section('page_title', 'My Profile')
@section('page_content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="row">

    {{-- Profile Info --}}
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user mr-1"></i> প্রোফাইল তথ্য</h3>
            </div>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-5x text-secondary"></i>
                        <h5 class="mt-2 mb-0">{{ $user->name }}</h5>
                        <small class="text-muted">{{ $user->email }}</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">নাম</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">ইমেইল</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">ফোন নম্বর</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $user->phone ?? '') }}" placeholder="01XXXXXXXXX">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> তথ্য সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="col-md-6">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-key mr-1"></i> পাসওয়ার্ড পরিবর্তন</h3>
            </div>
            <form action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">বর্তমান পাসওয়ার্ড</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">নতুন পাসওয়ার্ড</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">নতুন পাসওয়ার্ড (আবার লিখুন)</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key mr-1"></i> পাসওয়ার্ড পরিবর্তন করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
