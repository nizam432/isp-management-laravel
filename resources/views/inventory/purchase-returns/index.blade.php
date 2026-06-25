@extends('layouts.app')
@section('title', 'Purchase Return List')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Purchase Return</h4>
        <a href="{{ route('inventory.purchase-returns.create') }}" class="btn btn-primary btn-sm">+ New Purchase Return</a>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm">
        <div class="card-body"><p class="text-muted">List view content here.</p></div>
    </div>
</div>
@endsection
