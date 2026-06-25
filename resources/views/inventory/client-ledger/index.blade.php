@extends('layouts.app')
@section('title', 'Client Ledger')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Client Ledger (Product Due)</h4>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2">
                <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search client..." value="{{ request('search') }}"></div>
                <div class="col-auto"><button class="btn btn-sm btn-secondary">Search</button></div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Client</th><th>Phone</th><th>Total Sale</th><th>Total Paid</th><th>Due</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                    @php
                        $due = ($client->total_credit ?? 0) - ($client->total_debit ?? 0);
                    @endphp
                    <tr>
                        <td>{{ $client->name }}<br><small class="text-muted">{{ $client->customer_code }}</small></td>
                        <td>{{ $client->phone }}</td>
                        <td>৳{{ number_format($client->total_credit ?? 0, 2) }}</td>
                        <td>৳{{ number_format($client->total_debit ?? 0, 2) }}</td>
                        <td class="{{ $due > 0 ? 'text-danger fw-bold' : 'text-success' }}">৳{{ number_format($due, 2) }}</td>
                        <td><a href="{{ route('inventory.client-ledger.show', $client) }}" class="btn btn-sm btn-outline-info">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No client ledger found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $clients->links() }}</div>
    </div>
</div>
@endsection
