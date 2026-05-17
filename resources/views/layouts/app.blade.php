{{-- resources/views/layouts/app.blade.php --}}
@extends('adminlte::page')

@section('title', config('adminlte.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">@yield('page_title')</h1>
        <div>@yield('page_actions')</div>
    </div>
@stop

@section('content')
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @yield('page_content')
@stop

@section('css')
    <style>
        .badge-active    { background-color: #28a745; }
        .badge-inactive  { background-color: #6c757d; }
        .badge-suspended { background-color: #ffc107; color: #000; }
        .badge-expired   { background-color: #dc3545; }
        .card-header { font-weight: 600; }
    </style>
    @yield('extra_css')
@stop

@section('js')
    @yield('extra_js')
@stop
