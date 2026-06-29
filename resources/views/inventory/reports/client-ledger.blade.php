@extends('adminlte::page')
@section('title', 'Client Ledger Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-users mr-2 text-primary"></i>Client Ledger Report
            </h4>
        </div>
        <button onclick="window.print()" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
@endsection

@section('content')

@isset($client)
<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> {{ $client->name }} — Ledger
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Date</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Type</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Note</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Debit (Paid)</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Credit (Sale)</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $row)
                    <tr>
                        <td style="padding:10px 12px;" class="small text-muted">{{ $row->date->format('d M Y') }}</td>
                        <td style="padding:10px 12px;"><span class="badge badge-light border">{{ ucfirst($row->type) }}</span></td>
                        <td style="padding:10px 12px;" class="text-muted small">{{ $row->note }}</td>
                        <td style="padding:10px 12px;" class="text-right text-success font-weight-bold">{{ $row->debit > 0 ? '৳'.number_format($row->debit, 2) : '—' }}</td>
                        <td style="padding:10px 12px;" class="text-right text-danger">{{ $row->credit > 0 ? '৳'.number_format($row->credit, 2) : '—' }}</td>
                        <td style="padding:10px 12px;" class="text-right font-weight-bold">৳{{ number_format($row->balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No entries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endisset

@endsection

@section('css')
<style>
    .table tbody td { vertical-align: middle; }
    .table tbody tr:hover { background:#f0f4ff !important; }
</style>
@stop
