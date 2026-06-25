@extends('layouts.app')
@section('title', 'Stock Transactions')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Stock Transactions</h4>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="product_id" class="form-select form-select-sm">
                        <option value="">All Products</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>IN</option>
                        <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>OUT</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="reason" class="form-select form-select-sm">
                        <option value="">All Reasons</option>
                        @foreach(['purchase','sale','consumption','transfer','return','damage','adjustment'] as $r)
                        <option value="{{ $r }}" {{ request('reason') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}"></div>
                <div class="col-md-2"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}"></div>
                <div class="col-auto"><button class="btn btn-sm btn-secondary">Filter</button></div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Date</th><th>Product</th><th>Type</th><th>Reason</th><th>Qty</th><th>Location</th><th>By</th></tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr>
                        <td>{{ $tx->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $tx->product->name }}</td>
                        <td><span class="badge bg-{{ $tx->type == 'in' ? 'success' : 'danger' }}">{{ strtoupper($tx->type) }}</span></td>
                        <td>{{ ucfirst($tx->reason) }}</td>
                        <td>{{ $tx->quantity }}</td>
                        <td>{{ $tx->location->name }}</td>
                        <td>{{ $tx->createdBy->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No transactions</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
