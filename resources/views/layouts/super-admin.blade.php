{{-- resources/views/layouts/super-admin.blade.php --}}
@extends('adminlte::page')
@section('title', 'Super Admin — ' . config('adminlte.title'))

{{-- ── Custom user menu (top-right) ──────────────────────────────
     AdminLTE's default user-menu reads Auth::user() (default 'web' guard),
     which is always null for Super Admin (separate 'super_admin' guard).
     This replaces that section entirely with one that explicitly reads
     the correct guard, instead of relying on package internals that
     assume a single guard. --}}
@section('content_top_nav_right')
    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="fas fa-user-shield mr-1"></i>
            {{ Auth::guard('super_admin')->user()->name ?? 'Super Admin' }}
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <span class="dropdown-item-text text-muted small">
                {{ Auth::guard('super_admin')->user()->email }}
            </span>
            <div class="dropdown-divider"></div>
            <form action="{{ route('super-admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </button>
            </form>
        </div>
    </li>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">@yield('page_title')</h1>
        <div>@yield('page_actions')</div>
    </div>
@stop

@section('content')
    {{-- Global Toast Notification --}}
    @if(session('success') || session('error') || session('warning'))
    @php
        $type    = session('success') ? 'success' : (session('error') ? 'error' : 'warning');
        $message = session('success') ?? session('error') ?? session('warning');
        $color   = $type === 'success' ? '#28a745' : ($type === 'error' ? '#dc3545' : '#ffc107');
        $icon    = $type === 'success' ? 'check-circle' : ($type === 'error' ? 'times-circle' : 'exclamation-triangle');
        $title   = $type === 'success' ? 'Success' : 'Error';
    @endphp
    <div style="position:fixed; top:20px; right:20px; z-index:9999; min-width:320px;">
        <div class="toast show" role="alert"
             style="border:none; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.15); overflow:hidden;">
            <div class="toast-header" style="background:{{ $color }}; color:#fff; border:none;">
                <i class="fas fa-{{ $icon }} mr-2"></i>
                <strong class="mr-auto">{{ $title }}</strong>
                <button type="button" class="ml-2 close" data-dismiss="toast" style="color:#fff; opacity:1;">&times;</button>
            </div>
            <div class="toast-body" style="background:#fff; font-size:14px; padding:12px 16px;">
                {{ $message }}
            </div>
        </div>
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
        .card-header     { font-weight: 600; }
    </style>
    @yield('extra_css')
    @stack('css')
@stop

@section('js')
<script>
setTimeout(function() {
    document.querySelectorAll('.toast').forEach(function(t) {
        $(t).fadeOut(500, function() { $(this).remove(); });
    });
}, 4000);

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.swal-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var form = this.closest('form');
            var msg  = this.getAttribute('data-message') || 'Are you sure?';
            Swal.fire({
                title: 'Are you sure?',
                text: msg,
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            }).then(function(result) {
                if (result.isConfirmed) form.submit();
            });
        });
    });
});
</script>
    @yield('extra_js')
    @stack('js')
@stop
