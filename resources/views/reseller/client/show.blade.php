@extends('reseller.layouts.app')

@section('title', 'Client Details')

@section('content')

<div class="mb-3">
    <a href="{{ route('reseller.client.index') }}" class="btn btn-sm btn-light">
        <i class="fas fa-arrow-left"></i> Back to Clients
    </a>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body text-center">
                <div style="width:80px;height:80px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
                    <i class="fas fa-user fa-2x text-primary"></i>
                </div>
                <h5 class="font-weight-bold mb-1">{{ $client->name }}</h5>
                <p class="text-muted small mb-2">{{ $client->customer_code }}</p>
                @php
                    $badgeColor = match($client->status) {
                        'active' => 'success', 'expired' => 'danger',
                        'suspended' => 'warning', default => 'secondary',
                    };
                @endphp
                <span class="badge badge-{{ $badgeColor }} px-3 py-2">{{ ucfirst($client->status) }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3"><i class="fas fa-info-circle text-info mr-1"></i> Client Information</h6>
                <table class="table table-sm table-borderless mb-0" style="font-size:.875rem">
                    <tr><td class="text-muted" style="width:180px">Phone</td><td>{{ $client->phone }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $client->email ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Address</td><td>{{ $client->address ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Package</td><td>{{ $client->package?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Zone</td><td>{{ $client->zone?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Connection Type</td><td>{{ $client->connectionType?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Protocol</td><td>{{ $client->protocolType?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">PPPoE Username</td><td>{{ $client->pppoe_username ?? '—' }}</td></tr>
                    <tr><td class="text-muted">IP Address</td><td>{{ $client->ip_address ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Connection Date</td><td>{{ $client->connection_date?->format('d M Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Expire Date</td><td>{{ $client->expire_date?->format('d M Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Monthly Bill</td><td>{{ number_format($client->monthly_bill_amount ?? 0, 2) }}</td></tr>
                    <tr><td class="text-muted">Advance Balance</td><td>{{ number_format($client->advance_balance ?? 0, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

@if($client->invoices->isNotEmpty())
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body">
        <h6 class="font-weight-bold mb-3"><i class="fas fa-file-invoice text-warning mr-1"></i> Recent Invoices</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered" style="font-size:.85rem">
                <thead style="background:#f4f6f9">
                    <tr><th>Invoice</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @foreach($client->invoices->take(10) as $inv)
                    <tr>
                        <td>{{ $inv->invoice_number ?? $inv->id }}</td>
                        <td>{{ number_format($inv->total ?? $inv->amount ?? 0, 2) }}</td>
                        <td>{{ ucfirst($inv->status ?? '—') }}</td>
                        <td>{{ $inv->created_at?->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@stop
