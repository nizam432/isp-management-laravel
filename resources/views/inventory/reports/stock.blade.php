@extends('adminlte::page')
@section('title', 'Stock Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-chart-bar mr-2 text-primary"></i>Stock Report
            </h4>
        </div>
        <button onclick="window.print()" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
@endsection

@section('content')
@include('inventory._partials.alerts')
<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Stock Report</h6>
    </div>
    <div class="card-body">
        <p class="text-muted mb-0">Report content here.</p>
    </div>
</div>
@endsection
