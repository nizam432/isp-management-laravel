@extends('reseller.layout')

@section('title', $pageTitle ?? 'Coming Soon')

@section('content')
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
        <h4 class="mb-2">{{ $pageTitle ?? 'This page' }}</h4>
        <p class="text-muted mb-0">This page is coming soon. It will be built out with real functionality shortly.</p>
    </div>
</div>
@endsection
