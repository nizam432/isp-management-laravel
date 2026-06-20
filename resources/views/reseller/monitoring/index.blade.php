@extends('reseller.layouts.app')

@section('title', 'Monitoring')

@section('content')

<div class="row mb-3">
    <div class="col-md-4 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Total Clients</p>
                <h4 class="font-weight-bold mb-0">{{ $stats['total'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Online</p>
                <h4 class="font-weight-bold mb-0 text-success">{{ $stats['online'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Offline</p>
                <h4 class="font-weight-bold mb-0 text-secondary">{{ $stats['offline'] }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3"><i class="fas fa-wifi text-success mr-1"></i> Online Clients ({{ $onlineClients->count() }})</h6>
                <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                    <table class="table table-sm table-bordered" style="font-size:.82rem">
                        <thead style="background:#f4f6f9"><tr><th>Name</th><th>Username</th><th>IP</th><th>Uptime</th></tr></thead>
                        <tbody>
                            @forelse($onlineClients as $c)
                            <tr>
                                <td>{{ $c->name }}</td>
                                <td>{{ $c->pppoe_username }}</td>
                                <td>{{ $c->live_ip ?? '—' }}</td>
                                <td>{{ $c->live_uptime ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No clients online right now.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3"><i class="fas fa-chart-pie text-info mr-1"></i> Online by Package</h6>
                @forelse($packageBreakdown as $pkg => $count)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small">{{ $pkg }}</span>
                    <span class="badge badge-primary">{{ $count }}</span>
                </div>
                @empty
                <p class="text-muted small text-center py-3">No data available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@stop
