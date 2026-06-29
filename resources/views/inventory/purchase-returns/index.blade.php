@extends('adminlte::page')
@section('title', 'Purchase Returns')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-undo mr-2 text-primary"></i>Purchase Returns
            </h4>
            <small class="text-muted">Manage purchase return records</small>
        </div>
        <a href="{{ route('inventory.purchase-returns.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> New Return
        </a>
    </div>
@endsection

@section('content')
@include('inventory._partials.alerts')
<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-list mr-1"></i> Return List</h6>
    </div>
    <div class="card-body">
        <p class="text-muted mb-0">List view content here.</p>
    </div>
</div>
@endsection
