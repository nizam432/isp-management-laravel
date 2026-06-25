@extends('layouts.app')
@section('title', 'Sales')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Sales</h4>
        <a href="{{ route('inventory.sales.create') }}" class="btn btn-primary btn-sm">+ New Sale</a>
    </div>
    @include('inventory._partials.alerts')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2"><input type="text" name="search" class="form-control form-control-sm" placeholder="Sale/Invoice No..." value="{{ request('search') }}"></div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}"></div>
                <div class="col-md-2"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}"></div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-secondary">Filter</button>
                    <a href="{{ route('inventory.sales.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Invoice No</th><th>Date</th><th>Customer</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_no }}<br><small class="text-muted">{{ $sale->sale_no }}</small></td>
                        <td>{{ $sale->sale_date->format('d M Y') }}</td>
                        <td>{{ $sale->customer_name }}</td>
                        <td>৳{{ number_format($sale->total_amount,2) }}</td>
                        <td>৳{{ number_format($sale->paid_amount,2) }}</td>
                        <td class="{{ $sale->due_amount > 0 ? 'text-danger fw-semibold' : '' }}">৳{{ number_format($sale->due_amount,2) }}</td>
                        <td><span class="badge bg-{{ $sale->status == 'confirmed' ? 'success' : ($sale->status == 'draft' ? 'warning' : 'secondary') }}">{{ ucfirst($sale->status) }}</span></td>
                        <td><a href="{{ route('inventory.sales.show', $sale) }}" class="btn btn-sm btn-outline-info">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No sales found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $sales->links() }}</div>
    </div>
</div>
@endsection
