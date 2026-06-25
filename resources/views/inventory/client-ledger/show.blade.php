@extends('layouts.app')
@section('title', 'Client Ledger')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Ledger — {{ $customer->name }}</h4>
        <a href="{{ route('inventory.client-ledger.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body"><div class="text-muted small">Total Sale</div><div class="fs-5 fw-bold">৳{{ number_format($summary['total_credit'],2) }}</div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body"><div class="text-muted small">Total Paid</div><div class="fs-5 fw-bold text-success">৳{{ number_format($summary['total_debit'],2) }}</div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body"><div class="text-muted small">Balance Due</div><div class="fs-5 fw-bold {{ $summary['balance'] > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($summary['balance'],2) }}</div></div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}"></div>
                <div class="col-md-3"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}"></div>
                <div class="col-auto"><button class="btn btn-sm btn-secondary">Filter</button></div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Date</th><th>Type</th><th>Note</th><th class="text-end">Debit (Paid)</th><th class="text-end">Credit (Sale)</th><th class="text-end">Balance</th></tr>
                </thead>
                <tbody>
                    @forelse($ledger as $row)
                    <tr>
                        <td>{{ $row->date->format('d M Y') }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($row->type) }}</span></td>
                        <td>{{ $row->note }}</td>
                        <td class="text-end text-success">{{ $row->debit > 0 ? '৳'.number_format($row->debit,2) : '—' }}</td>
                        <td class="text-end text-danger">{{ $row->credit > 0 ? '৳'.number_format($row->credit,2) : '—' }}</td>
                        <td class="text-end fw-semibold">৳{{ number_format($row->balance,2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No ledger entries</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
