{{-- ════════════════════════════════════════
     resources/views/reseller/support/index.blade.php
════════════════════════════════════════ --}}
@extends('reseller.layouts.app')
@section('title', 'Client Support')
@section('content')

<div class="row mb-3">
    <div class="col-md-4 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3"><p class="text-muted small mb-1">Total Tickets</p><h4 class="font-weight-bold mb-0">{{ $stats['total'] }}</h4></div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3"><p class="text-muted small mb-1">Open</p><h4 class="font-weight-bold mb-0 text-warning">{{ $stats['open'] }}</h4></div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3"><p class="text-muted small mb-1">Closed</p><h4 class="font-weight-bold mb-0 text-success">{{ $stats['closed'] }}</h4></div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body">
        <form method="GET" class="mb-3">
            <select name="status" class="form-control form-control-sm d-inline-block" style="width:200px" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered" style="font-size:.85rem">
                <thead style="background:#f4f6f9">
                    <tr><th>Subject</th><th>Client</th><th>Status</th><th>Created</th><th class="text-center">Action</th></tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                    <tr>
                        <td>{{ $t->subject ?? 'No subject' }}</td>
                        <td>{{ $t->customer?->name ?? '—' }}</td>
                        <td><span class="badge badge-{{ $t->status == 'open' ? 'warning' : 'success' }}">{{ ucfirst($t->status) }}</span></td>
                        <td>{{ $t->created_at?->format('d M Y') }}</td>
                        <td class="text-center">
                            <a href="{{ route('reseller.client-support.show', $t->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No support tickets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $tickets->links() }}
    </div>
</div>
@stop
