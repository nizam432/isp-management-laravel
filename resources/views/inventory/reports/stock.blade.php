@extends('layouts.app')
@section('title', 'Stock Report')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Stock Report</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">🖨 Print</button>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm">
        <div class="card-body"><p class="text-muted">Report content here.</p></div>
    </div>
</div>
@endsection
