@extends('reseller.layouts.app')

@section('title', $title)

@section('content')
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body text-center py-5">
        <div style="width:80px;height:80px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;border:2px dashed #86efac">
            <i class="fas fa-tools" style="font-size:2rem;color:#22c55e"></i>
        </div>
        <h5 class="font-weight-bold mb-2">{{ $title }}</h5>
        <p class="text-muted">This section is coming soon. We're working on it!</p>
    </div>
</div>
@stop
