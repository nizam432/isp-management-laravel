@extends('adminlte::page')
@section('title', 'Device Assignments')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-laptop mr-2 text-primary"></i>Device Assignments
            </h4>
            <small class="text-muted">Manage device assignments to customers</small>
        </div>
        <a href="{{ route('inventory.assignments.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> New Assignment
        </a>
    </div>
@endsection

@section('content')
@include('inventory._partials.alerts')
<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-list mr-1"></i> Assignment List</h6>
    </div>
    <div class="card-body">
        <p class="text-muted mb-0">List view content here.</p>
    </div>
</div>
@endsection
