@extends('reseller.layouts.app')

@section('title', 'Client: ' . $client->name)

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-times-circle mr-1"></i> {{ session('error') }}
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Client Details</h4>
    <div>
        <a href="{{ route('reseller.client.edit', $client) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit mr-1"></i> Edit
        </a>
        <a href="{{ route('reseller.client.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>

@php
    $statusColor = match($client->status) {
        'active'    => '#00a65a',
        'suspended' => '#f39c12',
        'expired'   => '#dd4b39',
        default     => '#6c757d',
    };
    $mkStatus = $client->mikrotik_status ?? 'pending';
@endphp

<div class="row">

    {{-- ── LEFT: Profile Card ─────────────────── --}}
    <div class="col-lg-4 col-md-4">

        <div class="card">
            <div class="card-body text-center py-4" style="background: linear-gradient(135deg, {{ $statusColor }}22, #fff);">
                @if($client->photo)
                    <img src="{{ asset('storage/' . $client->photo) }}"
                         class="img-circle elevation-2"
                         style="width:90px;height:90px;object-fit:cover;" alt="Photo">
                @else
                    <div class="img-circle elevation-2 d-inline-flex align-items-center justify-content-center"
                         style="width:90px;height:90px;background:{{ $statusColor }};">
                        <i class="fas fa-user fa-2x text-white"></i>
                    </div>
                @endif
                <h5 class="mt-3 mb-1 font-weight-bold">{{ $client->name }}</h5>
                <code class="text-muted small">{{ $client->customer_code }}</code>
                <div class="mt-2">
                    <span class="badge badge-pill px-3 py-2"
                          style="background:{{ $statusColor }};color:#fff;font-size:12px;">
                        {{ ucfirst($client->status) }}
                    </span>
                </div>
                @if($client->resellerTariffPackage?->package)
                    <div class="mt-2">
                        <span class="badge badge-info badge-pill">{{ $client->resellerTariffPackage->package->name }}</span>
                    </div>
                @endif
            </div>

            <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:13px;">
                    <tr>
                        <td class="text-muted pl-3" style="width:36%;white-space:nowrap;">
                            <i class="fas fa-phone fa-fw mr-1"></i>Phone
                        </td>
                        <td><a href="tel:{{ $client->phone }}">{{ $client->phone }}</a></td>
                    </tr>

                    @if($client->email)
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-envelope fa-fw mr-1"></i>Email</td>
                        <td><small>{{ $client->email }}</small></td>
                    </tr>
                    @endif

                    @if($client->gender)
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-venus-mars fa-fw mr-1"></i>Gender</td>
                        <td><small>{{ ucfirst($client->gender) }}</small></td>
                    </tr>
                    @endif

                    @if($client->occupation)
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-briefcase fa-fw mr-1"></i>Occupation</td>
                        <td><small>{{ $client->occupation }}</small></td>
                    </tr>
                    @endif

                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-box fa-fw mr-1"></i>Package</td>
                        <td><small><strong>{{ $client->resellerTariffPackage?->package->name ?? '—' }}</strong></small></td>
                    </tr>

                    @if($client->resellerTariffPackage)
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-tachometer-alt fa-fw mr-1"></i>Profile</td>
                        <td><small>{{ $client->resellerTariffPackage->profile }} ({{ strtoupper($client->resellerTariffPackage->protocol) }})</small></td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-server fa-fw mr-1"></i>Server</td>
                        <td><small>{{ $client->resellerTariffPackage->server_name }}</small></td>
                    </tr>
                    @endif

                    @if($client->monthly_bill_amount)
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-money-bill fa-fw mr-1"></i>Bill Amount</td>
                        <td><small><strong class="text-success">৳{{ number_format($client->monthly_bill_amount) }}</strong></small></td>
                    </tr>
                    @endif

                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-calendar fa-fw mr-1"></i>Billing Date</td>
                        <td><small>{{ $client->billing_date }} of month</small></td>
                    </tr>

                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-plug fa-fw mr-1"></i>Connected</td>
                        <td><small>{{ $client->connection_date ? \Carbon\Carbon::parse($client->connection_date)->format('d M Y') : '—' }}</small></td>
                    </tr>

                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-map-marker-alt fa-fw mr-1"></i>Zone</td>
                        <td><small>
                            {{ $client->resellerZone->name ?? '—' }}
                            @if($client->resellerSubZone), {{ $client->resellerSubZone->name }}@endif
                        </small></td>
                    </tr>

                    @if($client->clientType)
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-users fa-fw mr-1"></i>Client Type</td>
                        <td><small>{{ $client->clientType->name }}</small></td>
                    </tr>
                    @endif

                    @if($client->nid_number)
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-id-card fa-fw mr-1"></i>NID</td>
                        <td><small>{{ $client->nid_number }}</small></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Change Status --}}
        <div class="card">
            <div class="card-header py-2" style="background:#f8f9fa;">
                <h6 class="mb-0"><i class="fas fa-toggle-on mr-1 text-primary"></i> Change Status</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('reseller.client.status', $client) }}" method="POST" id="statusForm">
                    @csrf @method('PATCH')
                    <div class="input-group input-group-sm">
                        <select name="status" class="form-control">
                            @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'expired' => 'Expired'] as $val => $label)
                                <option value="{{ $val }}" {{ $client->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i></button>
                        </div>
                    </div>
                </form>
                <small class="text-muted">Current: <strong style="color:{{ $statusColor }}">{{ ucfirst($client->status) }}</strong></small>
            </div>
        </div>

    </div>

    {{-- ── RIGHT: Network + Financials ─────────────────── --}}
    <div class="col-lg-8 col-md-8">
        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-header py-2" style="background:linear-gradient(90deg,#001f3f,#003366);color:#fff;">
                        <h6 class="mb-0"><i class="fas fa-network-wired mr-1"></i> Network / PPPoE</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="p-2 rounded" style="background:#f8f9fa;">
                                    <small class="text-muted d-block">PPPoE Username</small>
                                    <code class="font-weight-bold">{{ $client->pppoe_username ?? '—' }}</code>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded" style="background:#f8f9fa;">
                                    <small class="text-muted d-block">IP Address</small>
                                    <code>{{ $client->ip_address ?? '—' }}</code>
                                </div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="p-2 rounded" style="background:#f8f9fa;">
                                    <small class="text-muted d-block">MAC Address</small>
                                    <code>{{ $client->mac_address ?? '—' }}</code>
                                </div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="p-2 rounded" style="background:#f8f9fa;">
                                    <small class="text-muted d-block">MikroTik Status</small>
                                    @php
                                        $mkBadge = match($mkStatus) {
                                            'active'    => 'success',
                                            'suspended' => 'warning',
                                            'removed'   => 'danger',
                                            default     => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $mkBadge }}">{{ ucfirst($mkStatus) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6">
                @php
                    $totalDue    = $client->invoices->whereIn('status', ['unpaid', 'partial', 'overdue'])->sum('due_amount');
                    $totalPaid   = $client->payments->where('status', 'active')->sum('amount');
                    $lastPayment = $client->payments->where('status', 'active')->sortByDesc('paid_at')->first();
                @endphp
                <div class="row">
                    <div class="col-6">
                        <div class="card text-center py-3" style="border-left:4px solid #dd4b39;">
                            <div class="text-danger font-weight-bold" style="font-size:22px;">৳{{ number_format($totalDue) }}</div>
                            <small class="text-muted">Total Due</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center py-3" style="border-left:4px solid #00a65a;">
                            <div class="text-success font-weight-bold" style="font-size:22px;">৳{{ number_format($totalPaid) }}</div>
                            <small class="text-muted">Total Paid</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center py-3" style="border-left:4px solid #17a2b8;">
                            <div class="text-info font-weight-bold" style="font-size:22px;">{{ $client->invoices->count() }}</div>
                            <small class="text-muted">Invoices</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center py-3" style="border-left:4px solid #6f42c1;">
                            <div class="font-weight-bold" style="font-size:22px;color:#6f42c1;">{{ $client->supportTickets->count() ?? 0 }}</div>
                            <small class="text-muted">Tickets</small>
                        </div>
                    </div>
                </div>

                @if($lastPayment)
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block mb-1"><i class="fas fa-money-bill-wave mr-1"></i> Last Payment</small>
                        <strong class="text-success">৳{{ number_format($lastPayment->amount) }}</strong>
                        <small class="text-muted ml-2">{{ $lastPayment->paid_at ? \Carbon\Carbon::parse($lastPayment->paid_at)->format('d M Y') : '' }}</small>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Invoices --}}
        <div class="card">
            <div class="card-header py-2">
                <h6 class="mb-0"><i class="fas fa-file-invoice mr-1 text-success"></i> Invoices</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr><th>Invoice No</th><th>Month</th><th>Amount</th><th>Due</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($client->invoices->sortByDesc('created_at') as $inv)
                        <tr>
                            <td><small><code>{{ $inv->invoice_no }}</code></small></td>
                            <td><small>{{ $inv->month }}</small></td>
                            <td><small>৳{{ number_format($inv->amount) }}</small></td>
                            <td><small class="{{ $inv->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">৳{{ number_format($inv->due_amount) }}</small></td>
                            <td>
                                <span class="badge badge-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'overdue' ? 'danger' : ($inv->status === 'partial' ? 'info' : 'warning')) }}">
                                    {{ ucfirst($inv->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Payment History --}}
        <div class="card">
            <div class="card-header py-2">
                <h6 class="mb-0"><i class="fas fa-money-bill-wave mr-1 text-success"></i> Payment History</h6>
            </div>
            <div class="card-body p-0">
                @php $payments = $client->payments->where('status', 'active')->sortByDesc('paid_at'); @endphp
                @if($payments->isEmpty())
                    <div class="text-center text-muted py-3">No payments yet.</div>
                @else
                <table class="table table-sm table-hover mb-0">
                    <thead style="background:#f8f9fa;"><tr><th>Date</th><th>Amount</th><th>Method</th></tr></thead>
                    <tbody>
                        @foreach($payments->take(10) as $pay)
                        <tr>
                            <td><small>{{ $pay->paid_at ? \Carbon\Carbon::parse($pay->paid_at)->format('d M Y') : '' }}</small></td>
                            <td><small class="text-success font-weight-bold">৳{{ number_format($pay->amount) }}</small></td>
                            <td><span class="badge badge-secondary">{{ ucfirst($pay->method ?? '—') }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection