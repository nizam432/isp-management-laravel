@extends('layouts.app')
@section('title', 'Client Ledger Report')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Client Ledger Report</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">🖨 Print</button>
    </div>
    @isset($client)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">{{ $client->name }} — Ledger</div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Date</th><th>Type</th><th>Note</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th></tr>
                </thead>
                <tbody>
                    @forelse($ledger as $row)
                    <tr>
                        <td>{{ $row->date->format('d M Y') }}</td>
                        <td>{{ ucfirst($row->type) }}</td>
                        <td>{{ $row->note }}</td>
                        <td class="text-end text-success">{{ $row->debit > 0 ? '৳'.number_format($row->debit,2) : '—' }}</td>
                        <td class="text-end text-danger">{{ $row->credit > 0 ? '৳'.number_format($row->credit,2) : '—' }}</td>
                        <td class="text-end fw-semibold">৳{{ number_format($row->balance,2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No entries</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endisset
</div>
@endsection
