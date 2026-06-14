{{-- resources/views/client/dashboard.blade.php --}}
@extends('client.layout')
@section('title', 'Dashboard')

@section('content')

<div class="page-title">Dashboard</div>

{{-- Expired / Suspended Alert --}}
@if($customer->status === 'expired' || ($customer->expire_date && \Carbon\Carbon::parse($customer->expire_date)->isPast()))
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <div>
        <strong>Account Expired</strong> <span class="badge badge-danger" style="font-size:10px; margin-left:6px;">URGENT</span><br>
        <small>Your account has expired on {{ $customer->expire_date ? \Carbon\Carbon::parse($customer->expire_date)->format('d M Y H:i') : 'N/A' }}. Please renew to continue service.</small>
    </div>
    <a href="{{ route('client.invoices') }}" class="btn-paynow">Pay Now</a>
</div>
@elseif($customer->status === 'suspended')
<div class="alert alert-warning">
    <i class="fas fa-ban"></i>
    <div>
        <strong>Account Suspended</strong><br>
        <small>Your account has been suspended. Please contact support or pay your dues.</small>
    </div>
    <a href="{{ route('client.invoices') }}" class="btn-paynow">Pay Now</a>
</div>
@endif

{{-- Stat Cards --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-heart"></i></div>
        <div class="stat-info">
            <div class="stat-value">Tk{{ number_format($advanceBalance, 0) }}</div>
            <div class="stat-label">Balance</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-ticket-alt"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $openTickets }}</div>
            <div class="stat-label">Open Tickets</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $closedTickets ?? 0 }}</div>
            <div class="stat-label">Close Tickets</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-chart-bar"></i></div>
        <div class="stat-info">
            <div class="stat-value">0Byte</div>
            <div class="stat-label">Data Used</div>
        </div>
    </div>
</div>

{{-- Two Column --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; flex-wrap:wrap;">

    {{-- Customer Info --}}
    <div class="card">
        <div class="card-body" style="position:relative;">
            @if($totalDue > 0)
            <a href="{{ route('client.invoices') }}" class="btn btn-danger btn-sm" style="position:absolute; top:16px; right:16px;">Pay Now</a>
            @endif

            <div class="customer-card">
                <div class="customer-avatar">
                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                </div>
                <div class="customer-info">
                    <div class="customer-id">ID #{{ $customer->customer_code }}</div>
                    <div style="font-size:12px; color:#888;">
                        <span class="online-dot offline"></span> Offline
                    </div>
                </div>
            </div>

            <table class="info-table" style="margin-top:16px;">
                <tr>
                    <td>Full Name :</td>
                    <td>{{ $customer->name }}</td>
                </tr>
                <tr>
                    <td>Mobile :</td>
                    <td>{{ $customer->phone }}</td>
                </tr>
                <tr>
                    <td>Expire :</td>
                    <td class="{{ $customer->expire_date && \Carbon\Carbon::parse($customer->expire_date)->isPast() ? 'expire-urgent' : '' }}">
                        {{ $customer->expire_date ? \Carbon\Carbon::parse($customer->expire_date)->format('Y-m-d H:i:s') : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td>User ID :</td>
                    <td>{{ $customer->pppoe_username ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Package :</td>
                    <td>{{ $customer->package->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Bill :</td>
                    <td>{{ number_format($customer->monthly_bill_amount ?: ($customer->package->price ?? 0), 0) }} Tk</td>
                </tr>
                @if($customer->email)
                <tr>
                    <td>Email :</td>
                    <td>{{ $customer->email }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Payment History --}}
    <div class="card">
        <div class="card-header">
            <i class="fas fa-history"></i> Payment History
        </div>
        @if($recentPayments->count() > 0)
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Create Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPayments as $i => $pay)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><small>{{ optional($pay->created_at)->format('d-m-Y h:i A') ?? '—' }}</small></td>
                        <td><small>{{ optional($pay->invoice->due_date)->format('d-m-Y h:i A') ?? '—' }}</small></td>
                        <td>{{ number_format($pay->amount, 0) }}</td>
                        <td><span class="badge badge-success">Success</span></td>
                        <td>{{ ucfirst($pay->method) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="card-body" style="text-align:center; color:#aaa; padding:2rem;">
            <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
            No payment history found.
        </div>
        @endif
    </div>

</div>

@endsection
