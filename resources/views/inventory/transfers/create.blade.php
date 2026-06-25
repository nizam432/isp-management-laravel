@extends('layouts.app')
@section('title', 'New Stock Transfer')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">New Stock Transfer</h4>
        <a href="{{ route('inventory.transfers.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm">
        <div class="card-body"><p class="text-muted">Create form here.</p></div>
    </div>
</div>
@endsection
