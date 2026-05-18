{{--
    resources/views/customers/partials/mikrotik-card.blade.php
    Customer show page এ include করুন:
    @include('customers.partials.mikrotik-card', ['customer' => $customer])
--}}

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-network-wired mr-1"></i> MikroTik Connection
        </h3>
        <div class="card-tools">
            <span id="mk-status-badge" class="badge badge-secondary">
                <i class="fas fa-spinner fa-spin"></i> Checking...
            </span>
        </div>
    </div>
    <div class="card-body">

        {{-- PPPoE Info --}}
        <div class="row mb-3">
            <div class="col-sm-3">
                <strong>PPPoE Username</strong>
                <div><code>{{ $customer->pppoe_username ?? '—' }}</code></div>
            </div>
            <div class="col-sm-3">
                <strong>PPPoE Password</strong>
                <div>
                    <span id="pppoe-pass" style="filter:blur(4px)">
                        {{ $customer->pppoe_password ?? '—' }}
                    </span>
                    <button class="btn btn-xs btn-link p-0 ml-1" onclick="togglePass()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <strong>IP Address</strong>
                <div>{{ $customer->ip_address ?? '—' }}</div>
            </div>
            <div class="col-sm-3">
                <strong>MikroTik Status</strong>
                <div>
                    @php $mkStatus = $customer->mikrotik_status ?? 'pending' @endphp
                    <span class="badge badge-{{ match($mkStatus) {
                        'active'    => 'success',
                        'suspended' => 'warning',
                        'removed'   => 'danger',
                        default     => 'secondary',
                    } }}">{{ ucfirst($mkStatus) }}</span>
                </div>
            </div>
        </div>

        {{-- Live Session Info --}}
        <div id="session-box" class="alert alert-light border mb-3" style="display:none">
            <div class="row text-center">
                <div class="col">
                    <small class="text-muted d-block">IP Address</small>
                    <strong id="s-ip">—</strong>
                </div>
                <div class="col">
                    <small class="text-muted d-block">Uptime</small>
                    <strong id="s-uptime">—</strong>
                </div>
                <div class="col">
                    <small class="text-muted d-block">Interface</small>
                    <strong id="s-iface">—</strong>
                </div>
                <div class="col">
                    <small class="text-muted d-block">Encoding</small>
                    <strong id="s-encoding">—</strong>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="btn-group flex-wrap" role="group">

            @if(in_array($customer->mikrotik_status ?? 'pending', ['pending', 'removed']))
            <button class="btn btn-success btn-sm" onclick="mkAction('provision')">
                <i class="fas fa-plus-circle mr-1"></i>Provision
            </button>
            @endif

            @if(($customer->mikrotik_status ?? '') === 'active')
            <button class="btn btn-warning btn-sm" onclick="mkAction('suspend')">
                <i class="fas fa-ban mr-1"></i>Suspend
            </button>
            @endif

            @if(($customer->mikrotik_status ?? '') === 'suspended')
            <button class="btn btn-primary btn-sm" onclick="mkAction('restore')">
                <i class="fas fa-check-circle mr-1"></i>Restore
            </button>
            @endif

            <button class="btn btn-danger btn-sm" onclick="mkAction('kick')">
                <i class="fas fa-sign-out-alt mr-1"></i>Kick
            </button>

            <button class="btn btn-info btn-sm" onclick="loadSession()">
                <i class="fas fa-sync-alt mr-1"></i>Refresh
            </button>

            @if(($customer->mikrotik_status ?? '') === 'active')
            <button class="btn btn-secondary btn-sm" onclick="mkAction('change-package')">
                <i class="fas fa-exchange-alt mr-1"></i>Sync Package
            </button>
            @endif

        </div>

    </div>
</div>

<script>
const CUSTOMER_ID = {{ $customer->id }};
const MK_BASE     = `/customers/${CUSTOMER_ID}/mikrotik`;
const CSRF_TOKEN  = '{{ csrf_token() }}';

document.addEventListener('DOMContentLoaded', loadSession);

async function loadSession() {
    const badge = document.getElementById('mk-status-badge');
    badge.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
    badge.className = 'badge badge-secondary';

    try {
        const res  = await fetch(`${MK_BASE}/session`);
        const json = await res.json();

        if (json.success && json.online && json.session) {
            badge.className   = 'badge badge-success';
            badge.textContent = '🟢 Online';

            const s = json.session;
            document.getElementById('session-box').style.display = '';
            document.getElementById('s-ip').textContent       = s['address']       ?? '—';
            document.getElementById('s-uptime').textContent   = s['uptime']        ?? '—';
            document.getElementById('s-iface').textContent    = s['caller-id']     ?? '—';
            document.getElementById('s-encoding').textContent = s['encoding']      ?? '—';
        } else {
            badge.className   = 'badge badge-danger';
            badge.textContent = '🔴 Offline';
            document.getElementById('session-box').style.display = 'none';
        }
    } catch(e) {
        badge.className   = 'badge badge-warning';
        badge.textContent = '⚠️ Error';
    }
}

async function mkAction(action) {
    const labels = {
        provision:      'MikroTik এ add করবেন?',
        suspend:        'Customer suspend করবেন?',
        restore:        'Customer restore করবেন?',
        kick:           'Active session disconnect করবেন?',
        'change-package': 'Package sync করবেন?',
    };

    if (!confirm(labels[action] ?? 'Confirm?')) return;

    const method = 'POST';
    const url    = `${MK_BASE}/${action}`;

    try {
        const res  = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept':       'application/json',
            },
        });
        const json = await res.json();

        if (json.success) {
            toastr.success(json.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(json.message);
        }
    } catch(e) {
        toastr.error('Network error.');
    }
}

function togglePass() {
    const el = document.getElementById('pppoe-pass');
    el.style.filter = el.style.filter ? '' : 'blur(4px)';
}
</script>
