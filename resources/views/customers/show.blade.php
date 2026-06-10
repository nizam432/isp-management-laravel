{{-- resources/views/customers/show.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Customer: ' . $customer->name)

@section('page_actions')
    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-edit mr-1"></i> Edit
    </a>
    <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-invoice mr-1"></i> Create Invoice
    </a>
    <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection

@section('page_content')

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

@php
    $statusColor = match($customer->status) {
        'active'    => '#00a65a',
        'suspended' => '#f39c12',
        'expired'   => '#dd4b39',
        default     => '#6c757d',
    };
    $mkStatus = $customer->mikrotik_status ?? 'pending';
@endphp

{{-- ══════════════════════════════════════════════════════ --}}
{{-- ROW 1: Profile + Info + MikroTik                     --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="row">

    {{-- ── LEFT: Customer Profile Card ─────────────────── --}}
    <div class="col-lg-3 col-md-4">

        {{-- Profile --}}
        <div class="card">
            <div class="card-body text-center py-4" style="background: linear-gradient(135deg, {{ $statusColor }}22, #fff);">
                @if($customer->photo)
                    <img src="{{ asset('storage/' . $customer->photo) }}"
                         class="img-circle elevation-2"
                         style="width:90px;height:90px;object-fit:cover;" alt="Photo">
                @else
                    <div class="img-circle elevation-2 d-inline-flex align-items-center justify-content-center"
                         style="width:90px;height:90px;background:{{ $statusColor }};">
                        <i class="fas fa-user fa-2x text-white"></i>
                    </div>
                @endif
                <h5 class="mt-3 mb-1 font-weight-bold">{{ $customer->name }}</h5>
                <code class="text-muted small">{{ $customer->customer_code }}</code>
                <div class="mt-2">
                    <span class="badge badge-pill px-3 py-2"
                          style="background:{{ $statusColor }};color:#fff;font-size:12px;">
                        {{ ucfirst($customer->status) }}
                    </span>
                </div>
                @if($customer->package)
                    <div class="mt-2">
                        <span class="badge badge-info badge-pill">{{ $customer->package->name }}</span>
                    </div>
                @endif
            </div>

            {{-- Quick Info --}}
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted pl-3" style="width:35%"><i class="fas fa-phone fa-fw mr-1"></i></td>
                        <td>
                            <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                            @if($customer->phone)
                                <a href="https://wa.me/88{{ ltrim($customer->phone,'0') }}"
                                   target="_blank" class="text-success ml-1"><i class="fab fa-whatsapp"></i></a>
                            @endif
                        </td>
                    </tr>
                    @if($customer->email)
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-envelope fa-fw mr-1"></i></td>
                        <td><small>{{ $customer->email }}</small></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-map-marker-alt fa-fw mr-1"></i></td>
                        <td><small>{{ $customer->zone->name ?? '—' }} {{ $customer->subZone ? '/ '.$customer->subZone->name : '' }}</small></td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-calendar fa-fw mr-1"></i></td>
                        <td><small>Bill: <strong>{{ $customer->billing_date }}</strong> of month</small></td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-plug fa-fw mr-1"></i></td>
                        <td><small>{{ $customer->connection_date?->format('d M Y') ?? '—' }}</small></td>
                    </tr>
                    @if($customer->agent)
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-user-tie fa-fw mr-1"></i></td>
                        <td><small>{{ $customer->agent->name }}</small></td>
                    </tr>
                    @endif
                    @if($customer->nid_number)
                    <tr>
                        <td class="text-muted pl-3"><i class="fas fa-id-card fa-fw mr-1"></i></td>
                        <td><small>{{ $customer->nid_number }}</small></td>
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
                <form action="{{ route('customers.status', $customer) }}" method="POST" id="statusForm">
                    @csrf @method('PATCH')
                    <div class="input-group input-group-sm">
                        <select name="status" class="form-control" id="statusSelect">
                            @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'expired' => 'Expired'] as $val => $label)
                                <option value="{{ $val }}" {{ $customer->status === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                </form>
                <small class="text-muted">Current: <strong id="currentStatusLabel"
                    style="color:{{ $statusColor }}">{{ ucfirst($customer->status) }}</strong></small>
            </div>
        </div>

    </div>

    {{-- ── MIDDLE: MikroTik Card ────────────────────────── --}}
    <div class="col-lg-5 col-md-8">

        <div class="card">
            <div class="card-header py-2" style="background:linear-gradient(90deg,#001f3f,#003366);color:#fff;">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-network-wired mr-1"></i> MikroTik Connection</h6>
                    <div>
                        <span id="mk-live-badge" class="badge badge-secondary mr-1">
                            <i class="fas fa-spinner fa-spin fa-fw"></i> Checking...
                        </span>
                        <button class="btn btn-xs btn-outline-light" onclick="loadMkSession()" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">

                {{-- PPPoE Info Row --}}
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#f8f9fa;">
                            <small class="text-muted d-block">PPPoE Username</small>
                            <code class="font-weight-bold">{{ $customer->pppoe_username ?? '—' }}</code>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#f8f9fa;">
                            <small class="text-muted d-block">PPPoE Password</small>
                            <span id="pppoe-pass" style="filter:blur(4px);cursor:pointer;"
                                  onclick="togglePass()" title="Click to show">
                                {{ $customer->pppoe_password ?? '—' }}
                            </span>
                            <i class="fas fa-eye text-muted ml-1" style="cursor:pointer;font-size:11px;"
                               onclick="togglePass()"></i>
                        </div>
                    </div>
                    <div class="col-6 mt-2">
                        <div class="p-2 rounded" style="background:#f8f9fa;">
                            <small class="text-muted d-block">IP Address</small>
                            <code>{{ $customer->ip_address ?? '—' }}</code>
                        </div>
                    </div>
                    <div class="col-6 mt-2">
                        <div class="p-2 rounded" style="background:#f8f9fa;">
                            <small class="text-muted d-block">DB Status</small>
                            @php
                                $mkBadge = match($mkStatus) {
                                    'active'    => 'success',
                                    'suspended' => 'warning',
                                    'removed'   => 'danger',
                                    default     => 'secondary',
                                };
                            @endphp
                            <span class="badge badge-{{ $mkBadge }}" id="db-mk-status">
                                {{ ucfirst($mkStatus) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Live Session Box --}}
                <div id="mk-session-box" style="display:none;" class="mb-3">
                    <div class="alert alert-success py-2 mb-2">
                        <div class="row text-center">
                            <div class="col-3">
                                <small class="text-muted d-block" style="font-size:10px;">IP</small>
                                <strong id="s-ip" style="font-size:13px;">—</strong>
                            </div>
                            <div class="col-3">
                                <small class="text-muted d-block" style="font-size:10px;">Uptime</small>
                                <strong id="s-uptime" style="font-size:13px;">—</strong>
                            </div>
                            <div class="col-3">
                                <small class="text-muted d-block" style="font-size:10px;">Interface</small>
                                <strong id="s-iface" style="font-size:13px;">—</strong>
                            </div>
                            <div class="col-3">
                                <small class="text-muted d-block" style="font-size:10px;">Encoding</small>
                                <strong id="s-encoding" style="font-size:13px;">—</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MikroTik Action Buttons --}}
                <div class="d-flex flex-wrap gap-1" id="mk-action-btns">
                    {{-- Provision --}}
                    <button class="btn btn-success btn-sm mk-btn" id="btn-provision"
                            onclick="mkAction('provision')"
                            style="{{ in_array($mkStatus, ['pending','removed']) ? '' : 'display:none;' }}">
                        <i class="fas fa-plus-circle mr-1"></i>Provision
                    </button>
                    {{-- Suspend --}}
                    <button class="btn btn-warning btn-sm mk-btn" id="btn-suspend"
                            onclick="mkAction('suspend')"
                            style="{{ $mkStatus === 'active' ? '' : 'display:none;' }}">
                        <i class="fas fa-ban mr-1"></i>Suspend
                    </button>
                    {{-- Restore --}}
                    <button class="btn btn-primary btn-sm mk-btn" id="btn-restore"
                            onclick="mkAction('restore')"
                            style="{{ $mkStatus === 'suspended' ? '' : 'display:none;' }}">
                        <i class="fas fa-check-circle mr-1"></i>Restore
                    </button>
                    {{-- Kick --}}
                    <button class="btn btn-danger btn-sm" onclick="mkAction('kick')">
                        <i class="fas fa-sign-out-alt mr-1"></i>Kick
                    </button>
                    {{-- Sync Package --}}
                    <button class="btn btn-secondary btn-sm" onclick="mkAction('change-package')">
                        <i class="fas fa-exchange-alt mr-1"></i>Sync Package
                    </button>
                </div>

            </div>
        </div>

        {{-- Router Info --}}
        @if($customer->router)
        <div class="card">
            <div class="card-body py-2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-server text-info mr-2 fa-lg"></i>
                    <div>
                        <small class="text-muted d-block">Router</small>
                        <strong>{{ $customer->router->name }}</strong>
                        <small class="text-muted ml-1">({{ $customer->router->ip_address }})</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── RIGHT: Stats mini cards ─────────────────────── --}}
    <div class="col-lg-4 col-md-12">

        {{-- Due Amount Card --}}
        @php
            $totalDue = $customer->invoices->whereIn('status',['unpaid','partial','overdue'])->sum('due_amount');
            $totalPaid = $customer->payments->where('status','active')->sum('amount');
            $lastPayment = $customer->payments->where('status','active')->sortByDesc('paid_at')->first();
        @endphp

        <div class="row">
            <div class="col-6">
                <div class="card text-center py-3" style="border-left:4px solid #dd4b39;">
                    <div class="text-danger font-weight-bold" style="font-size:22px;">
                        ৳{{ number_format($totalDue) }}
                    </div>
                    <small class="text-muted">Total Due</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card text-center py-3" style="border-left:4px solid #00a65a;">
                    <div class="text-success font-weight-bold" style="font-size:22px;">
                        ৳{{ number_format($totalPaid) }}
                    </div>
                    <small class="text-muted">Total Paid</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card text-center py-3" style="border-left:4px solid #17a2b8;">
                    <div class="text-info font-weight-bold" style="font-size:22px;">
                        {{ $customer->invoices->count() }}
                    </div>
                    <small class="text-muted">Invoices</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card text-center py-3" style="border-left:4px solid #6f42c1;">
                    <div class="font-weight-bold" style="font-size:22px;color:#6f42c1;">
                        {{ $customer->tickets->count() }}
                    </div>
                    <small class="text-muted">Tickets</small>
                </div>
            </div>
        </div>

        {{-- Last Payment --}}
        @if($lastPayment)
        <div class="card">
            <div class="card-body py-2">
                <small class="text-muted d-block mb-1"><i class="fas fa-money-bill-wave mr-1"></i> Last Payment</small>
                <strong class="text-success">৳{{ number_format($lastPayment->amount) }}</strong>
                <small class="text-muted ml-2">{{ $lastPayment->paid_at?->format('d M Y') }}</small>
                <span class="badge badge-secondary ml-1">{{ ucfirst($lastPayment->method ?? '—') }}</span>
            </div>
        </div>
        @endif

        {{-- Advance Balance --}}
        @if($customer->advance_balance > 0)
        <div class="card">
            <div class="card-body py-2">
                <small class="text-muted d-block mb-1"><i class="fas fa-piggy-bank mr-1"></i> Advance Balance</small>
                <strong class="text-primary">৳{{ number_format($customer->advance_balance) }}</strong>
            </div>
        </div>
        @endif

    </div>

</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- ROW 2: Invoices + Tickets                            --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="row">

    {{-- Invoices --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-file-invoice mr-1 text-success"></i> Invoices</h6>
                <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}"
                   class="btn btn-xs btn-success">
                    <i class="fas fa-plus mr-1"></i>New
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>Invoice No</th>
                            <th>Month</th>
                            <th>Amount</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->invoices->sortByDesc('created_at') as $inv)
                        <tr>
                            <td><small><code>{{ $inv->invoice_no }}</code></small></td>
                            <td><small>{{ $inv->month }}</small></td>
                            <td><small>৳{{ number_format($inv->amount) }}</small></td>
                            <td>
                                <small class="{{ $inv->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                                    ৳{{ number_format($inv->due_amount) }}
                                </small>
                            </td>
                            <td>
                                <span class="badge badge-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'overdue' ? 'danger' : ($inv->status === 'partial' ? 'info' : 'warning')) }}">
                                    {{ ucfirst($inv->status) }}
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('invoices.show', $inv) }}" class="btn btn-xs btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('invoices.pdf', $inv) }}" class="btn btn-xs btn-secondary" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                <i class="fas fa-file-invoice fa-lg d-block mb-1"></i>
                                No invoices yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tickets --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-ticket-alt mr-1 text-warning"></i> Support Tickets</h6>
                <a href="{{ route('tickets.create', ['customer_id' => $customer->id]) }}"
                   class="btn btn-xs btn-warning">
                    <i class="fas fa-plus mr-1"></i>New
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>No</th>
                            <th>Subject</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->tickets->sortByDesc('created_at') as $ticket)
                        <tr>
                            <td><small><code>{{ $ticket->ticket_no }}</code></small></td>
                            <td><small>{{ Str::limit($ticket->subject, 30) }}</small></td>
                            <td>
                                <span class="badge badge-{{ match($ticket->priority) { 'urgent'=>'danger','high'=>'warning','medium'=>'info',default=>'secondary' } }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ match($ticket->status) { 'open'=>'danger','resolved','closed'=>'success',default=>'secondary' } }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-xs btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                <i class="fas fa-ticket-alt fa-lg d-block mb-1"></i>
                                No tickets yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection

@push('js')
<script>
const CUSTOMER_ID = {{ $customer->id }};
const MK_BASE     = `/customers/${CUSTOMER_ID}/mikrotik`;
const CSRF_TOKEN  = '{{ csrf_token() }}';

// ── Auto load session on page ready ─────────────────────
document.addEventListener('DOMContentLoaded', loadMkSession);

// ── Load live MikroTik session ───────────────────────────
async function loadMkSession() {
    const badge = document.getElementById('mk-live-badge');
    badge.innerHTML = '<i class="fas fa-spinner fa-spin fa-fw"></i> Checking...';
    badge.className = 'badge badge-secondary';

    try {
        const res  = await fetch(`${MK_BASE}/session`);
        const json = await res.json();

        if (json.success && json.online && json.session) {
            // ✅ Customer is online
            badge.className   = 'badge badge-success';
            badge.textContent = '🟢 Online';

            const s = json.session;
            document.getElementById('s-ip').textContent       = s['address']   ?? '—';
            document.getElementById('s-uptime').textContent   = s['uptime']    ?? '—';
            document.getElementById('s-iface').textContent    = s['caller-id'] ?? '—';
            document.getElementById('s-encoding').textContent = s['encoding']  ?? '—';
            document.getElementById('mk-session-box').style.display = '';

            updateDbStatusBadge('active');
            updateActionButtons('active');

        } else if (json.success && json.not_found) {
            // ⚠️ PPPoE user not on router — needs provisioning
            badge.className   = 'badge badge-warning';
            badge.textContent = '⚠️ Not Provisioned';
            document.getElementById('mk-session-box').style.display = 'none';
            updateDbStatusBadge('pending');
            updateActionButtons('pending');

        } else if (json.success && !json.online) {
            // 🔴 User exists on router but not connected
            badge.className   = 'badge badge-danger';
            badge.textContent = '🔴 Offline';
            document.getElementById('mk-session-box').style.display = 'none';

        } else {
            // ❌ Router connection error
            badge.className   = 'badge badge-warning';
            badge.textContent = '⚠️ Router Error';
            console.warn('MikroTik error:', json.message);
        }
    } catch(e) {
        badge.className   = 'badge badge-secondary';
        badge.textContent = '⚠️ Unreachable';
    }
}

// ── Update DB status badge dynamically ──────────────────
function updateDbStatusBadge(status) {
    const el = document.getElementById('db-mk-status');
    const colorMap = { active:'success', suspended:'warning', removed:'danger', pending:'secondary' };
    el.className = 'badge badge-' + (colorMap[status] ?? 'secondary');
    el.textContent = status.charAt(0).toUpperCase() + status.slice(1);
}

// ── Show/hide action buttons based on status ────────────
function updateActionButtons(status) {
    document.getElementById('btn-provision').style.display =
        ['pending','removed'].includes(status) ? '' : 'none';
    document.getElementById('btn-suspend').style.display =
        status === 'active' ? '' : 'none';
    document.getElementById('btn-restore').style.display =
        status === 'suspended' ? '' : 'none';
}

// ── MikroTik Actions ────────────────────────────────────
async function mkAction(action) {
    const labels = {
        provision:        'MikroTik এ add করবেন?',
        suspend:          'Customer suspend করবেন?',
        restore:          'Customer restore করবেন?',
        kick:             'Active session disconnect করবেন?',
        'change-package': 'Package sync করবেন?',
    };

    if (!confirm(labels[action] ?? 'Confirm?')) return;

    // Loading state
    const btn = event.currentTarget;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    try {
        const res  = await fetch(`${MK_BASE}/${action}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
        });
        const json = await res.json();

        if (json.success) {
            // Update UI instantly without reload
            const newStatus = {
                provision: 'active',
                suspend: 'suspended',
                restore: 'active',
                kick: null,
                'change-package': null,
            }[action];

            if (newStatus) {
                updateDbStatusBadge(newStatus);
                updateActionButtons(newStatus);
            }

            if (typeof toastr !== 'undefined') {
                toastr.success(json.message);
            } else {
                alert(json.message);
            }

            // Refresh session after action
            if (action === 'provision' || action === 'restore') {
                setTimeout(loadMkSession, 1500);
            }
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error(json.message ?? 'Failed.');
            } else {
                alert(json.message ?? 'Failed.');
            }
        }
    } catch(e) {
        alert('Network error: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

// ── PPPoE password toggle ────────────────────────────────
function togglePass() {
    const el = document.getElementById('pppoe-pass');
    el.style.filter = el.style.filter ? '' : 'blur(4px)';
}

// ── Change Status form — visual feedback ─────────────────
document.getElementById('statusForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type=submit]');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
});
</script>
@endpush
