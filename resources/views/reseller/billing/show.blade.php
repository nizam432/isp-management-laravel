@extends('reseller.layouts.app')

@section('title', 'Invoice Details')

@section('content')

<div class="mb-3">
    <a href="{{ route('reseller.billing.index') }}" class="btn btn-sm btn-light">
        <i class="fas fa-arrow-left"></i> Back to Billing
    </a>
</div>

@php
    $total = $invoice->total ?? $invoice->amount ?? 0;
    $paid  = $invoice->paid_amount ?? 0;
    $due   = $total - $paid;
    $badgeColor = match($invoice->status) {
        'paid' => 'success', 'unpaid' => 'danger',
        'partial' => 'warning', default => 'secondary',
    };
@endphp

<div class="row">
    <div class="col-md-8 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold mb-0">{{ $invoice->invoice_number ?? ('INV-' . $invoice->id) }}</h5>
                    <span class="badge badge-{{ $badgeColor }} px-3 py-2">{{ ucfirst($invoice->status ?? 'unpaid') }}</span>
                </div>
                <table class="table table-sm table-borderless mb-0" style="font-size:.875rem">
                    <tr><td class="text-muted" style="width:180px">Client</td><td>{{ $invoice->customer?->name }} ({{ $invoice->customer?->customer_code }})</td></tr>
                    <tr><td class="text-muted">Invoice Date</td><td>{{ $invoice->created_at?->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Total Amount</td><td class="font-weight-bold">{{ number_format($total, 2) }}</td></tr>
                    <tr><td class="text-muted">Paid Amount</td><td class="text-success">{{ number_format($paid, 2) }}</td></tr>
                    <tr><td class="text-muted">Due Amount</td><td class="text-danger font-weight-bold">{{ number_format($due, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body text-center">
                <div style="width:70px;height:70px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                    <i class="fas fa-file-invoice-dollar fa-2x text-primary"></i>
                </div>
                <h6 class="font-weight-bold mb-1">{{ number_format($total, 2) }}</h6>
                <p class="text-muted small mb-0">Invoice Total</p>
            </div>
        </div>
    </div>
</div>

@if($invoice->payments->isNotEmpty())
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body">
        <h6 class="font-weight-bold mb-3"><i class="fas fa-history text-info mr-1"></i> Payment History</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered" style="font-size:.85rem">
                <thead style="background:#f4f6f9">
                    <tr><th>Payment ID</th><th>Amount</th><th>Method</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $p)
                    <tr>
                        <td>{{ $p->id }}</td>
                        <td>{{ number_format($p->amount ?? 0, 2) }}</td>
                        <td>{{ ucfirst($p->payment_method ?? $p->method ?? '—') }}</td>
                        <td>{{ $p->created_at?->format('d M Y h:i A') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@stop
