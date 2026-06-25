@extends('layouts.app')
@section('title', 'Purchases')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Purchases</h4>
        <a href="{{ route('inventory.purchases.create') }}" class="btn btn-primary btn-sm">+ New Purchase</a>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="vendor_id" class="form-select form-select-sm">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}"></div>
                <div class="col-md-2"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}"></div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-secondary">Filter</button>
                    <a href="{{ route('inventory.purchases.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Purchase No</th><th>Date</th><th>Vendor</th><th>Location</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->purchase_no }}</td>
                        <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                        <td>{{ $purchase->vendor->name }}</td>
                        <td>{{ $purchase->location->name }}</td>
                        <td>৳{{ number_format($purchase->total_amount,2) }}</td>
                        <td>৳{{ number_format($purchase->paid_amount,2) }}</td>
                        <td class="{{ $purchase->due_amount > 0 ? 'text-danger fw-semibold' : '' }}">৳{{ number_format($purchase->due_amount,2) }}</td>
                        <td><span class="badge bg-{{ $purchase->status == 'received' ? 'success' : ($purchase->status == 'draft' ? 'warning' : 'secondary') }}">{{ ucfirst($purchase->status) }}</span></td>
                        <td><a href="{{ route('inventory.purchases.show', $purchase) }}" class="btn btn-sm btn-outline-info">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No purchases found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $purchases->links() }}</div>
    </div>
</div>
@endsection
