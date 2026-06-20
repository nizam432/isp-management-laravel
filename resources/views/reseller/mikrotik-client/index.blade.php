@extends('reseller.layouts.app')

@section('title', 'Mikrotik Client')

@section('content')

<div class="row mb-3">
    <div class="col-md-4 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">Online Now</p>
                    <h4 class="font-weight-bold mb-0 text-success">{{ $onlineCount }}</h4>
                </div>
                <i class="fas fa-wifi fa-2x text-success" style="opacity:.3"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">Offline</p>
                    <h4 class="font-weight-bold mb-0 text-secondary">{{ $offlineCount }}</h4>
                </div>
                <i class="fas fa-wifi-slash fa-2x text-secondary" style="opacity:.3"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2 d-flex align-items-center">
        <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
            <i class="fas fa-sync-alt mr-1"></i> Refresh Status
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered" style="font-size:.85rem">
                <thead style="background:#f4f6f9">
                    <tr>
                        <th>Username</th>
                        <th>Client Name</th>
                        <th>Protocol</th>
                        <th>Status</th>
                        <th>IP Address</th>
                        <th>MAC Address</th>
                        <th>Uptime</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $c)
                    <tr>
                        <td>{{ $c->pppoe_username }}</td>
                        <td>{{ $c->name }}</td>
                        <td>{{ ucfirst($c->live_protocol ?? '—') }}</td>
                        <td>
                            @if($c->live_status === 'online')
                                <span class="badge badge-success"><i class="fas fa-circle" style="font-size:6px"></i> Online</span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-circle" style="font-size:6px"></i> Offline</span>
                            @endif
                        </td>
                        <td>{{ $c->live_ip ?? '—' }}</td>
                        <td>{{ $c->live_mac ?? '—' }}</td>
                        <td>{{ $c->live_uptime ?? '—' }}</td>
                        <td class="text-center">
                            @if($c->live_status === 'online')
                                <button class="btn btn-sm btn-outline-danger disconnect-btn"
                                    data-id="{{ $c->id }}"
                                    data-protocol="{{ $c->live_protocol }}"
                                    data-name="{{ $c->name }}">
                                    <i class="fas fa-unlink"></i> Disconnect
                                </button>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No clients found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $clients->links() }}
    </div>
</div>
@stop

@section('js')
<script>
$(document).on('click', '.disconnect-btn', function () {
    const id       = $(this).data('id');
    const protocol = $(this).data('protocol');
    const name     = $(this).data('name');

    if (!confirm(`Disconnect "${name}"? This will end their active session immediately.`)) return;

    $.ajax({
        url: `/reseller/mikrotik-client/${id}/disconnect`,
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', protocol: protocol },
        success: function (res) {
            if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 800); }
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON?.message ?? 'Failed to disconnect.');
        }
    });
});
</script>
@stop
