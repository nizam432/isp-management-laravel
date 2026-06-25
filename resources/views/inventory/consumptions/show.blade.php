@extends('layouts.app')
@section('title', 'Consumption Details')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Consumption Details</h4>
        <a href="{{ route('inventory.consumptions.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm">
        <div class="card-body"><p class="text-muted">Detail view here.</p></div>
    </div>
</div>
@endsection
