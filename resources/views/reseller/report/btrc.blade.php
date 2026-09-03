@extends('reseller.layouts.app')

@section('title', 'BTRC Report')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">BTRC Report — Subscriber Summary</h4>
    <a href="{{ route('reseller.report.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i>
    এটি একটি সরলীকৃত সাবস্ক্রাইবার সামারি রিপোর্ট। BTRC-এর নির্দিষ্ট ফরম্যাট প্রয়োজন হলে জানান।
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center py-3" style="border-left:4px solid #17a2b8;">
            <div class="text-info font-weight-bold" style="font-size:24px;">{{ $totalClients }}</div>
            <small class="text-muted">Total Subscribers</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">By Status</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="bg-light"><tr><th>Status</th><th>Count</th></tr></thead>
                    <tbody>
                        @forelse($byStatus as $status => $count)
                        <tr><td>{{ ucfirst($status) }}</td><td>{{ $count }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">By Package</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="bg-light"><tr><th>Package</th><th>Count</th></tr></thead>
                    <tbody>
                        @forelse($byPackage as $pkg => $count)
                        <tr><td>{{ $pkg }}</td><td>{{ $count }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection