@extends('layouts.app')
@section('title', 'Vendor Ledger')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Ledger — {{ $vendor->name }}</h4>
        <a href="{{ route('inventory.vendors.show', $vendor) }}" class="btn btn-outline-secondary btn-sm">← Back</a>
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
                    <tr><th>Date</th><th>Type</th><th>Note</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th></tr>
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
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3">Total</td>
                        <td class="text-end text-success">৳{{ number_format($ledger->sum('debit'),2) }}</td>
                        <td class="text-end text-danger">৳{{ number_format($ledger->sum('credit'),2) }}</td>
                        <td class="text-end">৳{{ number_format($ledger->sum('credit') - $ledger->sum('debit'),2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $ledger->links() }}</div>
    </div>
</div>
@endsection
