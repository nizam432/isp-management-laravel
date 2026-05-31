{{-- resources/views/mikrotik/active-sessions.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Active Sessions')
@section('page_actions')
    <button class="btn btn-success btn-sm" onclick="refreshAll()">
        <i class="fas fa-sync mr-1"></i> Refresh
    </button>
    <span class="badge badge-info ml-2" id="totalOnline">Loading...</span>
@endsection
@section('page_content')

@foreach($routers as $router)
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-network-wired mr-1 text-success"></i>
            {{ $router->name }}
            <small class="text-muted ml-2">{{ $router->ip_address }}</small>
        </h3>
        <div>
            <span class="badge badge-success" id="count-{{ $router->id }}">Loading...</span>
            <span class="text-muted small ml-2">Auto refresh: <span id="timer-{{ $router->id }}">30</span>s</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>PPPoE Username</th>
                    <th>IP Address</th>
                    <th>MAC Address</th>
                    <th>Uptime</th>
                    <th>Download</th>
                    <th>Upload</th>
                    <th style="width:80px">Action</th>
                </tr>
            </thead>
            <tbody id="sessions-{{ $router->id }}">
                <tr>
                    <td colspan="9" class="text-center text-muted py-3">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Loading...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endforeach

@endsection

@push('js')
<script>
var customers = @json($customers);
var routers   = @json($routers->pluck('id'));
var timers    = {};
var countdown = {};

// ── Format bytes ──────────────────────────────────────
function formatBytes(bytes) {
    bytes = parseInt(bytes) || 0;
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576)    return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024)       return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' B';
}

// ── Load sessions for a router ────────────────────────
function loadSessions(routerId) {
    fetch('/mikrotik/' + routerId + '/active-sessions')
        .then(res => res.json())
        .then(data => {
            var tbody = document.getElementById('sessions-' + routerId);
            var count = document.getElementById('count-' + routerId);

            if (!data.success) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-3"><i class="fas fa-times-circle mr-1"></i> ' + data.message + '</td></tr>';
                count.textContent = 'Error';
                count.className = 'badge badge-danger';
                return;
            }

            count.textContent = data.count + ' Online';
            count.className = 'badge badge-success';

            if (data.count === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-3">No active sessions.</td></tr>';
                updateTotalCount();
                return;
            }

            var html = '';
            data.data.forEach(function(s, i) {
                var username    = s['name'] || '—';
                var customerName = customers[username] || '<span class="text-muted">—</span>';
                var ip          = s['address'] || '—';
                var mac         = s['caller-id'] || '—';
                var uptime      = s['uptime'] || '—';
                var rxBytes     = formatBytes(s['rx-bytes'] || 0);
                var txBytes     = formatBytes(s['tx-bytes'] || 0);

                html += '<tr>';
                html += '<td class="text-muted">' + (i + 1) + '</td>';
                html += '<td><strong>' + customerName + '</strong></td>';
                html += '<td><code>' + username + '</code></td>';
                html += '<td><code>' + ip + '</code></td>';
                html += '<td><small>' + mac + '</small></td>';
                html += '<td><span class="badge badge-secondary">' + uptime + '</span></td>';
                html += '<td><span class="text-success"><i class="fas fa-arrow-down mr-1"></i>' + rxBytes + '</span></td>';
                html += '<td><span class="text-danger"><i class="fas fa-arrow-up mr-1"></i>' + txBytes + '</span></td>';
                html += '<td>';
                html += '<button class="btn btn-xs btn-danger swal-kick" data-router="' + routerId + '" data-username="' + username + '" title="Kick"><i class="fas fa-times"></i></button>';
                html += '</td>';
                html += '</tr>';
            });

            tbody.innerHTML = html;
            updateTotalCount();
            bindKickButtons();
        })
        .catch(function(err) {
            document.getElementById('sessions-' + routerId).innerHTML =
                '<tr><td colspan="9" class="text-center text-danger py-3">Connection failed.</td></tr>';
        });
}

// ── Update total online count ─────────────────────────
function updateTotalCount() {
    var total = 0;
    document.querySelectorAll('[id^="count-"]').forEach(function(el) {
        var n = parseInt(el.textContent);
        if (!isNaN(n)) total += n;
    });
    document.getElementById('totalOnline').textContent = total + ' Total Online';
}

// ── Kick button ───────────────────────────────────────
function bindKickButtons() {
    document.querySelectorAll('.swal-kick').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var routerId = this.getAttribute('data-router');
            var username = this.getAttribute('data-username');
            Swal.fire({
                title: 'Kick User?',
                text: username + ' will be disconnected.',
                icon: false,
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Kick',
                cancelButtonText: 'Cancel',
                width: '320px',
                padding: '1rem',
            }).then(function(result) {
                if (result.isConfirmed) {
                    kickUser(routerId, username);
                }
            });
        });
    });
}

function kickUser(routerId, username) {
    // Find customer by pppoe_username
    var customerId = null;
    fetch('/customers?search=' + username)
        .then(() => {
            // Use customer mikrotik kick route
            return fetch('/customers/kick-by-username', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ username: username, router_id: routerId })
            });
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ title: 'Kicked!', text: username + ' has been disconnected.', icon: 'success', width: '320px', timer: 2000, showConfirmButton: false });
                setTimeout(() => loadSessions(routerId), 1000);
            } else {
                Swal.fire({ title: 'Failed', text: data.message, icon: 'error', width: '320px' });
            }
        });
}

// ── Auto refresh with countdown ───────────────────────
function startTimer(routerId) {
    var seconds = 30;
    countdown[routerId] = seconds;

    timers[routerId] = setInterval(function() {
        countdown[routerId]--;
        var el = document.getElementById('timer-' + routerId);
        if (el) el.textContent = countdown[routerId];

        if (countdown[routerId] <= 0) {
            loadSessions(routerId);
            countdown[routerId] = 30;
        }
    }, 1000);
}

function refreshAll() {
    routers.forEach(function(id) {
        clearInterval(timers[id]);
        loadSessions(id);
        startTimer(id);
    });
}

// ── Init ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    routers.forEach(function(id) {
        loadSessions(id);
        startTimer(id);
    });
});
</script>
@endpush