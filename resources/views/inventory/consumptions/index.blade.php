@extends('layouts.app')
@section('title', 'Consumption List')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Consumption</h4>
        <a href="{{ route('inventory.consumptions.create') }}" class="btn btn-primary btn-sm">+ New Consumption</a>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm">
        <div class="card-body"><p class="text-muted">List view content here.</p></div>
    </div>
</div>
@endsection
