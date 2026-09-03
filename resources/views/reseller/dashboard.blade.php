@extends('reseller.layouts.app')

@section('title', 'Dashboard')

@section('content')

<style>
.dash-card { border-radius: 10px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); overflow: hidden; }
.dash-stat { padding: 18px 20px; }
.dash-stat .icon-wrap { width: 46px; height: 46px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.dash-stat .label { font-size: 12px; color: #8898aa; text-transform: uppercase; letter-spacing: .5px; font-weight: 600; }
.dash-stat .value { font-size: 26px; font-weight: 700; color: #32325d; }
.section-title { font-size: 14px; font-weight: 700; color: #32325d; margin-bottom: 14px; }
.mini-list-item { padding: 10px 0; border-bottom: 1px solid #f0f2f5; }
.mini-list-item:last-child { border-bottom: none; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="m-0">Welcome, {{ $reseller->contact_person ?? $reseller->business_name }} 👋</h4>
        <small class="text-muted">Here's what's happening with your ISP business today.</small>
    </div>
</div>

{{-- ── Client Stats ─────────────────────────────── --}}
<div class="row">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card dash-card">
            <div class="dash-stat d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Total Clients</div>
                    <div class="value">{{ $stats['total_clients'] }}</div>
                </div>
                <div class="icon-wrap" style="background:#e8f4fd; color:#0073b7;"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card dash-card">
            <div class="dash-stat d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Active Clients</div>
                    <div class="value text-success">{{ $stats['active_clients'] }}</div>
                </div>
                <div class="icon-wrap" style="background:#e6f7ec; color:#00a65a;"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card dash-card">
            <div class="dash-stat d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Suspended</div>
                    <div class="value text-warning">{{ $stats['suspended_clients'] }}</div>
                </div>
                <div class="icon-wrap" style="background:#fff6e5; color:#f39c12;"><i class="fas fa-user-clock"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card dash-card">
            <div class="dash-stat d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Expired</div>
                    <div class="value text-danger">{{ $stats['expired_clients'] }}</div>
                </div>
                <div class="icon-wrap" style="background:#fdeceb; color:#dd4b39;"><i class="fas fa-user-times"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- ── Billing + Support Stats ─────────────────── --}}
<div class="row">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card dash-card">
            <div class="dash-stat d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Collected (This Month)</div>
                    <div class="value" style="font-size:20px;">৳{{ number_format($billing['collected_this_month']) }}</div>
                </div>
                <div class="icon-wrap" style="background:#e6f7ec; color:#00a65a;"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card dash-card">
            <div class="dash-stat d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Total Due</div>
                    <div class="value text-danger" style="font-size:20px;">৳{{ number_format($billing['total_due']) }}</div>
                </div>
                <div class="icon-wrap" style="background:#fdeceb; color:#dd4b39;"><i class="fas fa-exclamation-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card dash-card">
            <div class="dash-stat d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Unpaid Invoices</div>
                    <div class="value">{{ $billing['unpaid_invoices'] }}</div>
                </div>
                <div class="icon-wrap" style="background:#fff6e5; color:#f39c12;"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card dash-card">
            <div class="dash-stat d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Open Tickets</div>
                    <div class="value">{{ $support['pending'] + $support['processing'] }}</div>
                </div>
                <div class="icon-wrap" style="background:#f0ebfa; color:#6f42c1;"><i class="fas fa-headset"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- ── Account Info ─────────────────────────── --}}
    <div class="col-lg-4 mb-3">
        <div class="card dash-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    @if($reseller->logo)
                        <img src="{{ asset('storage/' . $reseller->logo) }}" style="width:44px;height:44px;border-radius:8px;object-fit:cover;margin-right:10px;">
                    @else
                        <div class="icon-wrap mr-2" style="background:#e8f4fd;color:#0073b7;"><i class="fas fa-building"></i></div>
                    @endif
                    <div>
                        <div class="font-weight-bold">{{ $reseller->business_name }}</div>
                        <small class="text-muted">POP Code: {{ $reseller->code }}</small>
                    </div>
                </div>
                <table class="table table-sm table-borderless mb-0" style="font-size:13px;">
                    <tr><td class="text-muted pl-0">Contact Person</td><td class="text-right pr-0">{{ $reseller->contact_person }}</td></tr>
                    <tr><td class="text-muted pl-0">Mobile</td><td class="text-right pr-0">{{ $reseller->mobile }}</td></tr>
                    <tr><td class="text-muted pl-0">POP Type</td><td class="text-right pr-0"><span class="badge badge-info">{{ ucfirst($reseller->pop_type) }}</span></td></tr>
                    <tr><td class="text-muted pl-0">Tariff</td><td class="text-right pr-0">{{ $reseller->tariff->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted pl-0">Remaining Fund</td><td class="text-right pr-0 font-weight-bold text-success">৳{{ number_format($reseller->fund_balance ?? 0, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Recent Payments ──────────────────────── --}}
    <div class="col-lg-4 mb-3">
        <div class="card dash-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="section-title mb-0">Recent Payments</div>
                    <a href="{{ route('reseller.payment.index') }}" class="small">View all →</a>
                </div>
                @forelse($recentPayments as $p)
                <div class="mini-list-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="font-weight-bold" style="font-size:13px;">{{ $p->customer->name ?? '—' }}</div>
                        <small class="text-muted">{{ optional($p->payment_date)->format('d M, h:i A') }}</small>
                    </div>
                    <span class="text-success font-weight-bold">৳{{ number_format($p->amount) }}</span>
                </div>
                @empty
                <p class="text-muted text-center py-4 mb-0"><i class="fas fa-receipt fa-2x d-block mb-2"></i>No payments yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Recent Support Tickets ───────────────── --}}
    <div class="col-lg-4 mb-3">
        <div class="card dash-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="section-title mb-0">Recent Tickets</div>
                    <a href="{{ route('reseller.client-support.index') }}" class="small">View all →</a>
                </div>
                @forelse($recentTickets as $t)
                <div class="mini-list-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="font-weight-bold" style="font-size:13px;">{{ $t->customer->name ?? '—' }}</div>
                        <small class="text-muted"><code>{{ $t->ticket_no }}</code></small>
                    </div>
                    <span class="badge badge-{{ $t->status_badge ?? 'secondary' }}">{{ ucfirst($t->status) }}</span>
                </div>
                @empty
                <p class="text-muted text-center py-4 mb-0"><i class="fas fa-headset fa-2x d-block mb-2"></i>No tickets yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ── Recently Added Clients ───────────────────── --}}
<div class="card dash-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div class="section-title mb-0">Recently Added Clients</div>
            <a href="{{ route('reseller.client.index') }}" class="small">View all →</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr><th>Client</th><th>Package</th><th>Status</th><th>Joined</th></tr>
                </thead>
                <tbody>
                    @forelse($recentClients as $c)
                    <tr>
                        <td>
                            <a href="{{ route('reseller.client.show', $c) }}" class="font-weight-bold">{{ $c->name }}</a>
                            <br><small class="text-muted">{{ $c->phone }}</small>
                        </td>
                        <td>{{ $c->resellerTariffPackage->package->name ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $c->status === 'active' ? 'success' : ($c->status === 'suspended' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $c->connection_date ? \Carbon\Carbon::parse($c->connection_date)->format('d M Y') : '—' }}</small></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No clients added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection