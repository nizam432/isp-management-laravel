@extends('reseller.layouts.app')

@section('title', 'Clients')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Clients</h4>
    <a href="{{ route('reseller.client.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Add Client
    </a>
</div>

{{-- Stats --}}
<style>
.cust-stat-card {
    border-radius: 4px;
    color: #fff;
    padding: 14px 16px;
    margin-bottom: 16px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
}
.cust-stat-card .sc-left .sc-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(255,255,255,.85);
    margin-bottom: 4px;
}
.cust-stat-card .sc-left .sc-value {
    font-size: 32px;
    font-weight: 700;
    line-height: 1;
    color: #fff;
}
.cust-stat-card .sc-icon {
    font-size: 52px;
    color: rgba(255,255,255,.18);
}
</style>
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-users mr-1"></i> Total Clients</div>
                <div class="sc-value">{{ $stats['total'] }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-check mr-1"></i> Active</div>
                <div class="sc-value">{{ $stats['active'] }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-check"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#f39c12;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-slash mr-1"></i> Suspended</div>
                <div class="sc-value">{{ $stats['suspended'] }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-slash"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#dd4b39;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-times mr-1"></i> Expired</div>
                <div class="sc-value">{{ $stats['expired'] }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-times"></i></div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Search & Filter</h3>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Name / Phone / Code" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="active"    {{ request('status') == 'active'    ? 'selected' : '' }}>Active</option>
                            <option value="inactive"  {{ request('status') == 'inactive'  ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="expired"   {{ request('status') == 'expired'   ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Package</label>
                        <select name="mac_reseller_tariff_package_id" class="form-control form-control-sm">
                            <option value="">All Packages</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" {{ request('mac_reseller_tariff_package_id') == $pkg->id ? 'selected' : '' }}>
                                    {{ $pkg->package->name ?? 'Package #' . $pkg->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Client Type</label>
                        <select name="client_type_id" class="form-control form-control-sm">
                            <option value="">All Types</option>
                            @foreach($clientTypes as $ct)
                                <option value="{{ $ct->id }}" {{ request('client_type_id') == $ct->id ? 'selected' : '' }}>
                                    {{ ucfirst($ct->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Zone</label>
                        <select name="mac_reseller_zone_id" class="form-control form-control-sm" id="zoneFilter">
                            <option value="">All Zones</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ request('mac_reseller_zone_id') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Sub Zone</label>
                        <select name="mac_reseller_sub_zone_id" class="form-control form-control-sm" id="subZoneFilter">
                            <option value="">All Sub Zones</option>
                            @foreach($subZones as $sz)
                                <option value="{{ $sz->id }}" data-zone="{{ $sz->mac_reseller_zone_id }}"
                                        {{ request('mac_reseller_sub_zone_id') == $sz->id ? 'selected' : '' }}>
                                    {{ $sz->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Billing Date</label>
                        <select name="billing_date" class="form-control form-control-sm">
                            <option value="">All Dates</option>
                            @for($d = 1; $d <= 28; $d++)
                                <option value="{{ $d }}" {{ request('billing_date') == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-1">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('reseller.client.index') }}" class="btn btn-sm btn-secondary ml-1">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
                @if(request()->hasAny(['search','status','mac_reseller_tariff_package_id','client_type_id','mac_reseller_zone_id','mac_reseller_sub_zone_id','billing_date']))
                    <span class="badge badge-warning ml-2">Filtered: {{ $clients->total() }} results</span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Client Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Client List</h3>
        <span class="badge badge-info">{{ $clients->total() }} clients</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Package</th>
                    <th>Zone</th>
                    <th>Billing</th>
                    <th>Status</th>
                    <th>MikroTik / Server</th>
                    <th style="width:110px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $i => $client)
                <tr>
                    <td class="text-muted small">{{ $clients->firstItem() + $i }}</td>

                    <td>
                        <a href="{{ route('reseller.client.show', $client) }}" class="font-weight-bold d-block">
                            {{ $client->name }}
                        </a>
                        <small class="text-muted"><code>{{ $client->customer_code }}</code></small>
                        <br>
                        <small>
                            <a href="tel:{{ $client->phone }}"><i class="fas fa-phone-alt mr-1"></i>{{ $client->phone }}</a>
                        </small>
                        <br><small class="text-muted">
                            <i class="fas fa-calendar-plus mr-1"></i>
                            Joined: {{ $client->connection_date ? \Carbon\Carbon::parse($client->connection_date)->format('d M Y') : '—' }}
                        </small>
                    </td>

                    <td>
                        @if($client->resellerTariffPackage)
                            <span class="font-weight-bold d-block">{{ $client->resellerTariffPackage->package->name ?? '—' }}</span>
                            <small class="text-muted">
                                {{ $client->clientType->name ?? '' }} |
                                ৳{{ number_format($client->monthly_bill_amount) }}/mo
                            </small>
                            <br>
                            <small class="text-muted">
                                {{ $client->resellerTariffPackage->profile }}
                                ({{ strtoupper($client->resellerTariffPackage->protocol) }})
                            </small>
                        @else
                            <small class="text-muted">N/A</small>
                        @endif
                    </td>

                    <td>
                        @if($client->resellerZone)
                            <span class="d-block">
                                <i class="fas fa-map-marker-alt mr-1 text-danger"></i>{{ $client->resellerZone->name }}
                            </span>
                            @if($client->resellerSubZone)
                                <small class="text-muted ml-3">{{ $client->resellerSubZone->name }}</small>
                            @endif
                        @else
                            <small class="text-muted">—</small>
                        @endif
                    </td>

                    <td>
                        <span class="badge badge-{{
                            $client->status === 'active'    ? 'success'  :
                            ($client->status === 'suspended' ? 'warning' :
                            ($client->status === 'expired'   ? 'danger'  : 'secondary'))
                        }} d-block mb-1">{{ ucfirst($client->status) }}</span>
                        <small class="text-muted d-block">
                            <i class="fas fa-calendar mr-1"></i>Bill: {{ $client->billing_date }} of month
                        </small>
                    </td>

                    <td>
                        <span class="badge badge-{{
                            $client->status === 'active'    ? 'success'  :
                            ($client->status === 'suspended' ? 'warning' :
                            ($client->status === 'expired'   ? 'danger'  : 'secondary'))
                        }}">{{ ucfirst($client->status) }}</span>
                    </td>

                    <td>
                        @if($client->pppoe_username)
                        <a href="#" class="mikrotik-info-btn" data-id="{{ $client->id }}">
                            @if($client->mikrotik_status === 'active')
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Active</span>
                            @elseif($client->mikrotik_status === 'failed')
                                <span class="badge badge-danger" title="Provisioning failed — check router/server name">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Failed
                                </span>
                            @else
                                <span class="badge badge-secondary" title="Not yet provisioned on MikroTik">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                </span>
                            @endif
                        </a>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                        @if($client->resellerTariffPackage?->server_name)
                            <br><small class="text-muted"><i class="fas fa-server mr-1"></i>{{ $client->resellerTariffPackage->server_name }}</small>
                        @endif
                    </td>

                    <td style="white-space:nowrap;">
                        <a href="{{ route('reseller.client.show', $client) }}" class="btn btn-xs btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('reseller.client.edit', $client) }}" class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x d-block mb-2"></i>
                        No clients found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <small class="text-muted">
                Showing <strong>{{ $clients->firstItem() ?? 0 }}</strong>–<strong>{{ $clients->lastItem() ?? 0 }}</strong>
                of <strong>{{ $clients->total() }}</strong> clients
            </small>
            {{ $clients->links() }}
        </div>
    </div>
</div>

{{-- MikroTik Info Modal --}}
<div class="modal fade" id="mikrotikInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="fas fa-server mr-1"></i> MikroTik Info — <span id="mt_client_name"></span></h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="mikrotikModalBody">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
document.getElementById('zoneFilter')?.addEventListener('change', function () {
    var zoneId = this.value;
    var subSelect = document.getElementById('subZoneFilter');
    Array.from(subSelect.options).forEach(function (opt) {
        if (!opt.value) { opt.style.display = ''; return; }
        opt.style.display = (!zoneId || opt.dataset.zone === zoneId) ? '' : 'none';
    });
    subSelect.value = '';
});

$(document).on('click', '.mikrotik-info-btn', function (e) {
    e.preventDefault();
    const id = $(this).data('id');
    const name = $(this).closest('tr').find('td:eq(1) a').first().text().trim();

    $('#mt_client_name').text(name);
    $('#mikrotikModalBody').html('<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#mikrotikInfoModal').modal('show');

    $.get(`/reseller/client/${id}/mikrotik-info`, function (res) {
        if (!res.success) {
            $('#mikrotikModalBody').html(`<div class="alert alert-warning mb-0">${res.message}</div>`);
            return;
        }

        const a = res.account, s = res.session;
        const sessionHtml = s.online
            ? `<tr><td class="text-muted">Status</td><td><span class="badge badge-success"><i class="fas fa-circle mr-1"></i>Online</span></td></tr>
               <tr><td class="text-muted">IP</td><td class="text-danger">${s.ip}</td></tr>
               <tr><td class="text-muted">MAC</td><td>${s.mac}</td></tr>
               <tr><td class="text-muted">Uptime</td><td><span class="badge badge-secondary">${s.uptime}</span></td></tr>`
            : `<tr><td class="text-muted">Status</td><td><span class="badge badge-secondary"><i class="fas fa-circle mr-1"></i>Offline</span></td></tr>`;

        const suspendBtn = a.status === 'Disabled'
            ? `<button class="btn btn-success btn-sm btn-block mt-2" id="mtEnableBtn" data-id="${id}"><i class="fas fa-play mr-1"></i> Enable</button>`
            : `<button class="btn btn-warning btn-sm btn-block mt-2" id="mtSuspendBtn" data-id="${id}"><i class="fas fa-ban mr-1"></i> Suspend</button>`;

        $('#mikrotikModalBody').html(`
            <div class="row">
                <div class="col-6">
                    <h6 class="small text-muted text-uppercase mb-2"><i class="fas fa-user mr-1"></i> Account Info</h6>
                    <table class="table table-sm mb-0" style="font-size:13px;">
                        <tr><td class="text-muted">Router</td><td class="font-weight-bold">${res.router}</td></tr>
                        <tr><td class="text-muted">Username</td><td class="text-danger">${a.username}</td></tr>
                        <tr><td class="text-muted">Profile</td><td><span class="badge badge-info">${a.profile}</span></td></tr>
                        <tr><td class="text-muted">Status</td><td><span class="badge badge-${a.status === 'Active' ? 'success' : 'secondary'}">${a.status}</span></td></tr>
                    </table>
                </div>
                <div class="col-6">
                    <h6 class="small text-muted text-uppercase mb-2"><i class="fas fa-wifi mr-1"></i> Live Session</h6>
                    <table class="table table-sm mb-0" style="font-size:13px;">${sessionHtml}</table>
                </div>
            </div>
            ${suspendBtn}
        `);
    }).fail(function () {
        $('#mikrotikModalBody').html('<div class="alert alert-danger mb-0">Failed to load MikroTik info.</div>');
    });
});

$(document).on('click', '#mtSuspendBtn, #mtEnableBtn', function () {
    const id     = $(this).data('id');
    const action = this.id === 'mtSuspendBtn' ? 'mikrotik-suspend' : 'mikrotik-enable';
    const $btn   = $(this).prop('disabled', true);

    $.post(`/reseller/client/${id}/${action}`, { _token: '{{ csrf_token() }}' }, function (res) {
        if (res.success) {
            toastr?.success(res.message) ?? alert(res.message);
            $('#mikrotikInfoModal').modal('hide');
            setTimeout(() => location.reload(), 800);
        } else {
            toastr?.error(res.message) ?? alert(res.message);
            $btn.prop('disabled', false);
        }
    });
});
</script>
@endsection