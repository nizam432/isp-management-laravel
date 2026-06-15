{{-- resources/views/client/dashboard.blade.php --}}
@extends('client.layout')
@section('title', 'Dashboard')

@section('extra_css')
<style>
    .stat-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    @media (max-width: 900px) { .stat-cards { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .stat-cards { grid-template-columns: repeat(2, 1fr); } }

    .dash-stat {
        background: #fff; border-radius: 12px;
        border: 1px solid #eef0f5; padding: 18px 20px;
        display: flex; align-items: center; gap: 16px;
    }
    .dash-stat-icon {
        width: 52px; height: 52px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }
    .dash-stat-icon.teal   { background: #e0faf3; color: #00c897; }
    .dash-stat-icon.blue   { background: #e0eeff; color: #3a7bd5; }
    .dash-stat-icon.green  { background: #e8fbe8; color: #27ae60; }
    .dash-stat-icon.gray   { background: #f0f2f7; color: #6b7280; }
    .dash-stat-val  { font-size: 22px; font-weight: 700; color: #1a1f36; }
    .dash-stat-lbl  { font-size: 12px; color: #888; margin-top: 2px; }

    /* Two column layout */
    .dash-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 768px) { .dash-grid { grid-template-columns: 1fr; } }

    /* Customer card */
    .cust-card { background: #fff; border-radius: 12px; border: 1px solid #eef0f5; overflow: hidden; }
    .cust-card-header {
        padding: 16px 20px;
        display: flex; align-items: center; justify-content: space-between;
        border-bottom: 1px solid #f0f2f7;
    }
    .cust-avatar {
        width: 56px; height: 56px; border-radius: 50%;
        background: #1a1f36;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: 700; color: #00c897; flex-shrink: 0;
    }
    .cust-id   { font-size: 17px; font-weight: 700; color: #1a1f36; }
    .cust-body { padding: 16px 20px; }
    .cust-row  {
        display: flex; justify-content: space-between;
        font-size: 13px; padding: 6px 0;
        border-bottom: 1px solid #f4f6f9;
    }
    .cust-row:last-child { border-bottom: none; }
    .cust-lbl  { color: #888; }
    .cust-val  { color: #1a1f36; font-weight: 500; text-align: right; }
    .cust-val.danger  { color: #e74c3c; font-weight: 600; }
    .cust-val.success { color: #00c897; }

    /* Online/Offline dot */
    .dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 5px; }
    .dot.online  { background: #00c897; }
    .dot.offline { background: #e74c3c; }

    /* Payment history table */
    .pay-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .pay-table th { background: #f8f9fc; padding: 9px 14px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #eef0f5; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; }
    .pay-table td { padding: 10px 14px; border-bottom: 1px solid #f4f6f9; color: #444; vertical-align: middle; }
    .pay-table tr:last-child td { border-bottom: none; }
    .pay-table tr:hover td { background: #fafbfd; }

    /* Alert */
    .dash-alert {
        padding: 14px 18px; border-radius: 10px;
        font-size: 13px; margin-bottom: 16px;
        display: flex; align-items: center; gap: 12px;
        flex-wrap: wrap;
    }
    .dash-alert.danger  { background: #fff0f0; border: 1px solid #ffd0d0; color: #c0392b; border-left: 4px solid #e74c3c; }
    .dash-alert.warning { background: #fffbf0; border: 1px solid #ffe0a0; color: #b8860b; border-left: 4px solid #f39c12; }
    .dash-alert i { font-size: 18px; flex-shrink: 0; }
    .dash-alert .pay-btn {
        margin-left: auto; background: #e74c3c; color: #fff;
        border: none; border-radius: 6px; padding: 6px 16px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        text-decoration: none; white-space: nowrap;
    }
    .dash-alert.warning .pay-btn { background: #f39c12; }
</style>
@endsection

@section('content')

<div class="page-title">Dashboard</div>

{{-- Alert --}}
@if($customer->status === 'suspended')
<div class="dash-alert danger">
    <i class="fas fa-ban"></i>
    <div>
        <strong>Account Suspended</strong><br>
        <small>Your connection has been suspended. Please pay your dues to restore service.</small>
    </div>
    <a href="{{ route('client.invoices') }}" class="pay-btn">Pay Now</a>
</div>
@elseif($customer->expire_date && \Carbon\Carbon::parse($customer->expire_date)->isPast())
<div class="dash-alert danger">
    <i class="fas fa-exclamation-circle"></i>
    <div>
        <strong>Account Expired</strong>
        <span class="badge" style="background:#e74c3c; color:#fff; font-size:10px; padding:2px 8px; border-radius:4px; margin-left:6px;">URGENT</span><br>
        <small>Your account expired on {{ \Carbon\Carbon::parse($customer->expire_date)->format('d M Y h:i A') }}. Please renew to continue service.</small>
    </div>
    <a href="{{ route('client.invoices') }}" class="pay-btn">Pay Now</a>
</div>
@elseif($totalDue > 0)
<div class="dash-alert danger">
    <i class="fas fa-exclamation-circle"></i>
    <div>
        <strong>Payment Due</strong><br>
        <small>You have Tk{{ number_format($totalDue, 0) }} outstanding. Please pay to avoid service interruption.</small>
    </div>
    <a href="{{ route('client.invoices') }}" class="pay-btn">Pay Now</a>
</div>
@endif

{{-- Stat Cards --}}
<div class="stat-cards">
    <div class="dash-stat">
        <div class="dash-stat-icon teal"><i class="fas fa-heart"></i></div>
        <div>
            <div class="dash-stat-val">Tk{{ number_format($advanceBalance, 0) }}</div>
            <div class="dash-stat-lbl">Balance</div>
        </div>
    </div>
    <div class="dash-stat">
        <div class="dash-stat-icon blue"><i class="fas fa-ticket-alt"></i></div>
        <div>
            <div class="dash-stat-val">{{ $openTickets }}</div>
            <div class="dash-stat-lbl">Open Tickets</div>
        </div>
    </div>
    <div class="dash-stat">
        <div class="dash-stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="dash-stat-val">{{ $closedTickets }}</div>
            <div class="dash-stat-lbl">Close Tickets</div>
        </div>
    </div>
    <div class="dash-stat">
        <div class="dash-stat-icon {{ $customer->expire_date && \Carbon\Carbon::parse($customer->expire_date)->isPast() ? 'red' : 'gray' }}" style="{{ $customer->expire_date && \Carbon\Carbon::parse($customer->expire_date)->isPast() ? 'background:#fee2e2; color:#e74c3c;' : '' }}">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div>
            <div class="dash-stat-val" style="font-size:15px; {{ $customer->expire_date && \Carbon\Carbon::parse($customer->expire_date)->isPast() ? 'color:#e74c3c;' : '' }}">
                {{ $customer->expire_date ? \Carbon\Carbon::parse($customer->expire_date)->format('d M Y') : 'N/A' }}
            </div>
            <div class="dash-stat-lbl">Expire Date</div>
        </div>
    </div>
</div>

{{-- Two Column --}}
<div class="dash-grid">

    {{-- Customer Info --}}
    <div class="cust-card">
        <div class="cust-card-header">
            <div style="display:flex; align-items:center; gap:14px;">
                <div class="cust-avatar">{{ strtoupper(substr($customer->name, 0, 2)) }}</div>
                <div>
                    <div class="cust-id">ID #{{ $customer->customer_code }}</div>
                    <div style="font-size:12px; margin-top:3px;">
                        <span class="dot {{ $customer->mikrotik_status === 'active' ? 'online' : 'offline' }}"></span>
                        {{ $customer->mikrotik_status === 'active' ? 'Online' : 'Offline' }}
                    </div>
                </div>
            </div>
            @if($totalDue > 0)
            <a href="{{ route('client.invoices') }}" class="btn btn-danger btn-sm">Pay Now</a>
            @endif
        </div>
        <div class="cust-body">
            <div class="cust-row">
                <span class="cust-lbl">Full Name :</span>
                <span class="cust-val">{{ $customer->name }}</span>
            </div>
            <div class="cust-row">
                <span class="cust-lbl">Mobile :</span>
                <span class="cust-val">{{ $customer->phone }}</span>
            </div>
            <div class="cust-row">
                <span class="cust-lbl">Expire :</span>
                <span class="cust-val {{ $customer->expire_date && \Carbon\Carbon::parse($customer->expire_date)->isPast() ? 'danger' : '' }}">
                    {{ $customer->expire_date ? \Carbon\Carbon::parse($customer->expire_date)->format('d-m-Y h:i A') : 'N/A' }}
                </span>
            </div>
            <div class="cust-row">
                <span class="cust-lbl">User ID :</span>
                <span class="cust-val">{{ $customer->pppoe_username ?? '—' }}</span>
            </div>
            <div class="cust-row">
                <span class="cust-lbl">Package :</span>
                <span class="cust-val">{{ $customer->package->name ?? '—' }}</span>
            </div>
            <div class="cust-row">
                <span class="cust-lbl">Bill :</span>
                <span class="cust-val">{{ number_format($customer->monthly_bill_amount ?: ($customer->package->price ?? 0), 0) }} Tk</span>
            </div>
            @if($customer->email)
            <div class="cust-row">
                <span class="cust-lbl">Email :</span>
                <span class="cust-val">{{ $customer->email }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment History --}}
    <div class="cust-card">
        <div class="cust-card-header">
            <div style="display:flex; align-items:center; gap:8px; font-size:14px; font-weight:600; color:#1a1f36;">
                <i class="fas fa-history" style="color:#00c897;"></i> Payment History
            </div>
            <a href="{{ route('client.invoices') }}" style="font-size:12px; color:#00c897; text-decoration:none;">View All</a>
        </div>
        @if($recentPayments->count() > 0)
        <div style="overflow-x:auto;">
            <table class="pay-table">
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
                        <td><small>{{ optional($pay->created_at)->format('d-m-Y h:i A') }}</small></td>
                        <td><small>{{ optional($pay->invoice->due_date)->format('d-m-Y h:i A') ?? '—' }}</small></td>
                        <td><strong>{{ number_format($pay->amount, 0) }}</strong></td>
                        <td><span class="badge badge-success">Success</span></td>
                        <td>{{ ucfirst($pay->method) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="text-align:center; color:#aaa; padding:2.5rem;">
            <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
            No payment history found.
        </div>
        @endif
    </div>

</div>

@endsection
