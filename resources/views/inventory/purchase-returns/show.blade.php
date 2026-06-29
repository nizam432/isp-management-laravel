@extends('adminlte::page')
@section('title', 'Purchase Return Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-undo mr-2 text-primary"></i>Purchase Return Details
            </h4>
        </div>
        <a href="{{ route('inventory.purchase-returns.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@endsection

@section('content')
@include('inventory._partials.alerts')
<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-info-circle mr-1"></i> Return Info</h6>
    </div>
    <div class="card-body">
        <p class="text-muted mb-0">Detail view here.</p>
    </div>
</div>
@endsection
