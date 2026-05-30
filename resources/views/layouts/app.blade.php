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

    {{-- Global Toast Notification --}}
    @if(session('success') || session('error') || session('warning'))
    @php
        $type    = session('success') ? 'success' : (session('error') ? 'error' : 'warning');
        $message = session('success') ?? session('error') ?? session('warning');
        $color   = $type === 'success' ? '#28a745' : ($type === 'error' ? '#dc3545' : '#ffc107');
        $icon    = $type === 'success' ? 'check-circle' : ($type === 'error' ? 'times-circle' : 'exclamation-triangle');
        $title   = $type === 'success' ? 'Success' : ($type === 'error' ? 'সমস্যা' : 'সতর্কতা');
    @endphp
    <div style="position:fixed; top:20px; right:20px; z-index:9999; min-width:320px;">
        <div class="toast show" role="alert"
             style="border:none; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.15); overflow:hidden;">
            <div class="toast-header" style="background:{{ $color }}; color:#fff; border:none;">
                <i class="fas fa-{{ $icon }} mr-2"></i>
                <strong class="mr-auto">{{ $title }}</strong>
                <small style="opacity:0.8">Close</small>
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
        
    .swal-icon-sm {
        width: 30px !important;
        height: 30px !important;
        margin: 0.5rem auto !important;
        font-size: 1 rem !important;
    }
    .swal2-title {
        font-size: 1.1rem !important;
        padding: 0.5rem !important;
        color:red;
        margin-top:20px;
    }
    .swal2-html-container {
        font-size: 0.9rem !important;
        margin: 0.3rem !important;
    }        
        
    </style>
    @yield('extra_css')
@stop

@section('js')
<script>
// ── Global Toast auto-hide ─────────────────────────────
setTimeout(function() {
    document.querySelectorAll('.toast').forEach(function(t) {
        $(t).fadeOut(500, function() { $(this).remove(); });
    });
}, 4000);

// ── Global SweetAlert Delete Confirm ──────────────────
// ── Global SweetAlert Delete Confirm ──────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.swal-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var form = this.closest('form');
            var msg  = this.getAttribute('data-message') || 'Are you sure?';
            Swal.fire({
                title: 'Are you sure?',
                text: msg,
              //  icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                width: '350px',
                padding: '0.5 rem',
            }).then(function(result) {
                if (result.isConfirmed) form.submit();
            });
        });
    });
});
</script>
    @yield('extra_js')
@stop