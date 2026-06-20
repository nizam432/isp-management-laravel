@extends('layouts.app')
@section('page_title', 'Customers')
@section('page_actions')
    <button class="btn btn-info btn-sm" onclick="startSync()">
        <i class="fas fa-sync-alt mr-1"></i> Sync M.Status
    </button>
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Add Customer
    </a>
@endsection
@section('page_content')

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
                <div class="sc-label"><i class="fas fa-users mr-1"></i> Total Customers</div>
                <div class="sc-value">{{ $totalCustomers }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-check mr-1"></i> Active</div>
                <div class="sc-value">{{ $activeCustomers }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-check"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#f39c12;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-slash mr-1"></i> Suspended</div>
                <div class="sc-value">{{ $suspendedCustomers }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-slash"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#dd4b39;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-times mr-1"></i> Expired</div>
                <div class="sc-value">{{ $expiredCustomers }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-times"></i></div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Search & Filter</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Search</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Name / Phone / Code"
                                   value="{{ request('search') }}">
                        </div>
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
                        <label class="small font-weight-bold">Router</label>
                        <select name="router_id" class="form-control form-control-sm">
                            <option value="">All Routers</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>
                                    {{ $router->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Package</label>
                        <select name="package_id" class="form-control form-control-sm">
                            <option value="">All Packages</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>
                                    {{ $pkg->name }}
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
                        <select name="zone_id" class="form-control form-control-sm" id="zoneFilter">
                            <option value="">All Zones</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Sub Zone</label>
                        <select name="sub_zone_id" class="form-control form-control-sm" id="subZoneFilter">
                            <option value="">All Sub Zones</option>
                            @foreach($subZones as $sz)
                                <option value="{{ $sz->id }}" {{ request('sub_zone_id') == $sz->id ? 'selected' : '' }}>
                                    {{ $sz->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Protocol Type</label>
                        <select name="protocol_type_id" class="form-control form-control-sm">
                            <option value="">All Protocols</option>
                            @foreach($protocolTypes as $pt)
                                <option value="{{ $pt->id }}" {{ request('protocol_type_id') == $pt->id ? 'selected' : '' }}>
                                    {{ $pt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Connection Type</label>
                        <select name="connection_type_id" class="form-control form-control-sm">
                            <option value="">All Connections</option>
                            @foreach($connectionTypes as $ct)
                                <option value="{{ $ct->id }}" {{ request('connection_type_id') == $ct->id ? 'selected' : '' }}>
                                    {{ $ct->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Agent</label>
                        <select name="agent_id" class="form-control form-control-sm">
                            <option value="">All Agents</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
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
                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary ml-1">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
                @if(request()->hasAny(['search','status','router_id','package_id','client_type_id','zone_id','sub_zone_id','protocol_type_id','connection_type_id','agent_id','billing_date']))
                    <span class="badge badge-warning ml-2">Filtered: {{ $customers->total() }} results</span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Customer Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Customer List</h3>
        <div>
            <a href="{{ route('import.index') }}" class="btn btn-xs btn-success mr-1">
                <i class="fas fa-file-import mr-1"></i> Import
            </a>
            <span class="badge badge-info">{{ $customers->total() }} customers</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Package</th>
                    <th>Zone / Agent</th>
                    <th>MikroTik</th>
                    <th>Billing</th>
                    <th>Status</th>
                    <th>M.Status</th>
                    <th style="width:130px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $i => $customer)
                @if(!$customer || !$customer->id) @continue @endif
                <tr id="row-{{ $customer->id }}">
                    <td class="text-muted small">{{ $customers->firstItem() + $i }}</td>

                    {{-- Customer Info --}}
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="font-weight-bold d-block">
                            {{ $customer->name }}
                        </a>
                        <small class="text-muted"><code>{{ $customer->customer_code }}</code></small>
                        <br>
                        <small>
                            <a href="tel:{{ $customer->phone }}">
                                <i class="fas fa-phone-alt mr-1"></i>{{ $customer->phone }}
                            </a>
                            @if($customer->phone)
                                <a href="https://wa.me/88{{ ltrim($customer->phone, '0') }}"
                                   target="_blank" class="text-success ml-1" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            @endif
                        </small>
                        @if($customer->email)
                            <br><small class="text-muted"><i class="fas fa-envelope mr-1"></i>{{ $customer->email }}</small>
                        @endif
                        <br><small class="text-muted">
                            <i class="fas fa-calendar-plus mr-1"></i>
                            Joined: {{ $customer->connection_date ? \Carbon\Carbon::parse($customer->connection_date)->format('d M Y') : '—' }}
                        </small>
                    </td>

                    {{-- Package --}}
                    <td>
                        @if($customer->package)
                            <span class="font-weight-bold d-block">{{ $customer->package->name }}</span>
                            <small class="text-muted">
                                {{ $customer->clientType->name ?? 'All' }} |
                                ৳ {{ number_format($customer->package->price) }}/mo
                            </small>
                            <br>
                            <small class="text-muted">
                                {{ $customer->connectionType->name ?? '' }}
                                {{ $customer->protocolType->name ?? '' }}
                            </small>
                        @else
                            <small class="text-muted">N/A</small>
                        @endif
                    </td>

                    {{-- Zone / Agent --}}
                    <td>
                        @if($customer->zone)
                            <span class="d-block">
                                <i class="fas fa-map-marker-alt mr-1 text-danger"></i>{{ $customer->zone->name }}
                            </span>
                            @if($customer->subZone)
                                <small class="text-muted ml-3">{{ $customer->subZone->name }}</small>
                            @endif
                        @else
                            <small class="text-muted">—</small>
                        @endif
                        @if($customer->agent)
                            <br><small><i class="fas fa-user-tie mr-1"></i>{{ $customer->agent->name }}</small>
                        @endif
                    </td>

                    {{-- MikroTik --}}
                    <td>
                        @if($customer->router)
                            <span class="d-block small font-weight-bold">{{ $customer->router->name }}</span>
                        @endif
                        <button class="btn btn-xs btn-outline-secondary mk-info-btn"
                                data-id="{{ $customer->id }}"
                                data-name="{{ $customer->name }}"
                                title="View MikroTik Info">
                            <i class="fas fa-network-wired"></i>
                        </button>
                        <br>
                        <small class="text-muted">
                            {{ $customer->pppoe_username ?? '—' }} <b>-</b>
                            {{ $customer->pppoe_password }}
                          {{--    @if($customer->pppoe_password)
                               | <span class="pppoe-pass" data-pass="{{ $customer->pppoe_password }}">••••••</span>
                                <i class="fas fa-eye toggle-pass" style="cursor:pointer; font-size:11px; padding:6px 8px; display:inline-block; min-width:16px; min-height:16px;" title="Show password"></i>
                            @endif--}}
                        </small>
                    </td>

                    {{-- Billing --}}
                    <td>
                        <span class="badge badge-{{
                            $customer->status === 'active'    ? 'success'  :
                            ($customer->status === 'suspended' ? 'warning' :
                            ($customer->status === 'expired'   ? 'danger'  : 'secondary'))
                        }} d-block mb-1">{{ ucfirst($customer->status) }}</span>
                        <small class="text-muted d-block">
                            <i class="fas fa-calendar mr-1"></i>Bill: {{ $customer->billing_date }} of month
                        </small>
                        @if($customer->expire_date)
                            <small class="text-{{ \Carbon\Carbon::parse($customer->expire_date)->isPast() ? 'danger' : 'muted' }} d-block">
                                <i class="fas fa-calendar-times mr-1"></i>Expire: {{ \Carbon\Carbon::parse($customer->expire_date)->format('d M Y') }}
                            </small>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td>
                        <span class="badge badge-{{
                            $customer->status === 'active'    ? 'success'  :
                            ($customer->status === 'suspended' ? 'warning' :
                            ($customer->status === 'expired'   ? 'danger'  : 'secondary'))
                        }}">{{ ucfirst($customer->status) }}</span>
                    </td>

                    {{-- M.Status --}}
                    <td>
                        @php
                            $ms = $customer->mikrotik_status ?? 'pending';
                            $msBadge = match($ms) {
                                'active'    => 'success',
                                'suspended' => 'warning',
                                'removed'   => 'danger',
                                default     => 'secondary',
                            };
                            $msIcon = match($ms) {
                                'active'    => 'fa-check-circle',
                                'suspended' => 'fa-ban',
                                'removed'   => 'fa-times-circle',
                                default     => 'fa-clock',
                            };
                        @endphp
                        <span class="badge badge-{{ $msBadge }}" title="MikroTik: {{ ucfirst($ms) }}">
                            <i class="fas {{ $msIcon }} mr-1"></i>{{ ucfirst($ms) }}
                        </span>
                    </td>

                    {{-- Action --}}
                    <td style="white-space:nowrap;">
                        <a href="{{ route('customers.show', $customer) }}"
                           class="btn btn-xs btn-info mb-1" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('customers.edit', $customer) }}"
                           class="btn btn-xs btn-warning mb-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-xs btn-success mb-1"
                                onclick="openSmsModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', '{{ $customer->phone }}')"
                                title="Send SMS">
                            <i class="fas fa-sms"></i>
                        </button>
                        @if($customer->latitude && $customer->longitude)
                            <a href="https://www.google.com/maps?q={{ $customer->latitude }},{{ $customer->longitude }}"
                               target="_blank" class="btn btn-xs btn-secondary mb-1" title="View Map">
                                <i class="fas fa-map-marker-alt"></i>
                            </a>
                        @endif
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-xs btn-danger mb-1 swal-delete"
                                    data-message="Customer '{{ $customer->name }}' will be permanently deleted."
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x d-block mb-2"></i>
                        No customers found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            {{-- Left: row info + per page --}}
            <div class="d-flex align-items-center">
                <small class="text-muted mr-3">
                    Showing <strong>{{ $customers->firstItem() ?? 0 }}</strong>–<strong>{{ $customers->lastItem() ?? 0 }}</strong>
                    of <strong>{{ $customers->total() }}</strong> customers
                </small>
                <form method="GET" class="d-inline-flex align-items-center" id="perPageForm">
                    @foreach(request()->except('page', 'per_page') as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <select name="per_page" class="form-control form-control-sm" style="width:75px;"
                            onchange="document.getElementById('perPageForm').submit()">
                        @foreach([20, 50, 100] as $pp)
                            <option value="{{ $pp }}" {{ request('per_page', 20) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted ml-1">/ page</small>
                </form>
            </div>

            {{-- Right: pagination (always visible) --}}
            @if($customers->lastPage() > 1)
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        {{-- Previous --}}
                        <li class="page-item {{ $customers->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $customers->withQueryString()->previousPageUrl() ?? '#' }}">«</a>
                        </li>
                        {{-- Pages --}}
                        @for($p = 1; $p <= $customers->lastPage(); $p++)
                            @if($p == 1 || $p == $customers->lastPage() || abs($p - $customers->currentPage()) <= 2)
                                <li class="page-item {{ $p == $customers->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $customers->withQueryString()->url($p) }}">{{ $p }}</a>
                                </li>
                            @elseif(abs($p - $customers->currentPage()) == 3)
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            @endif
                        @endfor
                        {{-- Next --}}
                        <li class="page-item {{ !$customers->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $customers->withQueryString()->nextPageUrl() ?? '#' }}">»</a>
                        </li>
                    </ul>
                </nav>
            @else
                <small class="text-muted">Page 1 of 1</small>
            @endif

        </div>
    </div>
</div>

{{-- MikroTik Info Modal --}}
<div class="modal fade" id="mkInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">
                    <i class="fas fa-network-wired mr-1"></i> MikroTik Info — <span id="mkinfo-name"></span>
                </h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="mkinfo-body">
                <div class="text-center py-3">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
            <div class="modal-footer py-2" id="mkinfo-actions" style="display:none">
                <button class="btn btn-success btn-sm d-none" id="btn-provision" onclick="mkAction2('provision')">
                    <i class="fas fa-plus-circle mr-1"></i> Provision
                </button>
                <button class="btn btn-warning btn-sm d-none" id="btn-suspend" onclick="mkAction2('suspend')">
                    <i class="fas fa-ban mr-1"></i> Suspend
                </button>
                <button class="btn btn-primary btn-sm d-none" id="btn-restore" onclick="mkAction2('restore')">
                    <i class="fas fa-check-circle mr-1"></i> Restore
                </button>
              
            </div>
        </div>
    </div>
</div>

{{-- SMS Modal --}}
<div class="modal fade" id="smsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-sms mr-1"></i> Send SMS</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-2 small">
                    To: <strong id="sms-customer-name"></strong>
                    (<code id="sms-phone"></code>)
                </p>
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Template</label>
                    <select id="smsTemplate" class="form-control form-control-sm" onchange="fillTemplate()">
                        <option value="">-- Select Template --</option>
                        @foreach($smsTemplates as $tpl)
                            <option value="{{ $tpl->body }}">{{ $tpl->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold">Message</label>
                    <textarea id="smsMessage" class="form-control" rows="4"
                              maxlength="500" placeholder="Type message..."></textarea>
                    <small class="text-muted float-right"><span id="smsCount">0</span>/500</small>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" onclick="sendSms()">
                    <i class="fas fa-paper-plane mr-1"></i> Send
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Sync Progress Modal ──────────────────────────────── --}}
<div class="modal fade" id="syncModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:#001f3f;color:#fff;">
                <h6 class="modal-title mb-0"><i class="fas fa-sync-alt mr-1"></i> MikroTik Sync</h6>
            </div>
            <div class="modal-body text-center py-4">
                <div id="sync-spinner" class="mb-2">
                    <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
                </div>
                <div id="sync-progress-wrap" class="mb-2" style="display:none;">
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-info progress-bar-striped progress-bar-animated"
                             id="sync-progress-bar" style="width:0%"></div>
                    </div>
                </div>
                <p id="sync-message" class="mb-1 text-muted small">Starting sync...</p>
                <p id="sync-counter" class="font-weight-bold mb-0 text-info"></p>
            </div>
            <div class="modal-footer py-2 d-none" id="sync-done-footer">
                <div class="w-100 text-center">
                    <div id="sync-result" class="mb-2 small"></div>
                    <button class="btn btn-success btn-sm"
                            onclick="$('#syncModal').modal('hide'); location.reload();">
                        <i class="fas fa-check mr-1"></i> Done — Reload
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
var currentCustomerId = null;
var csrfToken         = '{{ csrf_token() }}';
var smsCustomerPhone  = '';

// ── PPPoE Password Toggle ─────────────────────────────
document.addEventListener('click', function (e) {

    let icon = e.target.closest('.toggle-pass');
    if (!icon) return;

    let container = icon.closest('small') || icon.parentElement;
    let span = container ? container.querySelector('.pppoe-pass') : null;

    if (!span) return;

    if (span.textContent.trim() === '••••••') {
        span.textContent = span.dataset.pass;
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        span.textContent = '••••••';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }

});
// ── Zone → Sub Zone filter ────────────────────────────
document.getElementById('zoneFilter').addEventListener('change', function() {
    var zoneId = this.value;
    var subZoneSelect = document.getElementById('subZoneFilter');
    subZoneSelect.innerHTML = '<option value="">All Sub Zones</option>';
    if (!zoneId) return;
    fetch('/zones/' + zoneId + '/sub-zones')
        .then(res => res.json())
        .then(data => {
            data.forEach(function(sz) {
                subZoneSelect.add(new Option(sz.name, sz.id));
            });
        });
});

// ── MikroTik Info Modal ───────────────────────────────
document.querySelectorAll('.mk-info-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id   = this.getAttribute('data-id');
        var name = this.getAttribute('data-name');
        currentCustomerId = id;
        document.getElementById('mkinfo-name').textContent     = name;
        document.getElementById('mkinfo-body').innerHTML       = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        document.getElementById('mkinfo-actions').style.display = 'none';
        $('#mkInfoModal').modal('show');

        fetch('/customers/' + id + '/mikrotik-info')
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    document.getElementById('mkinfo-body').innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                    return;
                }

                var acc    = data.account || {};
                var sess   = data.session || {};
                var online = Object.keys(sess).length > 0;

                var html = '<div class="row">';

                // Account Info
                html += '<div class="col-md-6">';
                html += '<h6 class="border-bottom pb-1"><i class="fas fa-user mr-1"></i> Account Info</h6>';
                html += '<table class="table table-sm table-borderless mb-0">';
                html += '<tr><td class="text-muted small">Router</td><td><strong>' + (data.router || '—') + '</strong></td></tr>';
                html += '<tr><td class="text-muted small">Username</td><td><code>' + (acc['name'] || '—') + '</code></td></tr>';
                html += '<tr><td class="text-muted small">Profile</td><td><span class="badge badge-info">' + (acc['profile'] || '—') + '</span></td></tr>';
                html += '<tr><td class="text-muted small">Status</td><td><span class="badge badge-' + (acc['disabled'] === 'true' ? 'danger' : 'success') + '">' + (acc['disabled'] === 'true' ? 'Disabled' : 'Active') + '</span></td></tr>';
                html += '<tr><td class="text-muted small">Comment</td><td><small>' + (acc['comment'] || '—') + '</small></td></tr>';
                html += '</table></div>';

                // Session Info
                html += '<div class="col-md-6">';
                html += '<h6 class="border-bottom pb-1"><i class="fas fa-wifi mr-1"></i> Live Session</h6>';
                if (online) {
                    html += '<table class="table table-sm table-borderless mb-0">';
                    html += '<tr><td class="text-muted small">Status</td><td><span class="badge badge-success"><i class="fas fa-circle mr-1"></i>Online</span></td></tr>';
                    html += '<tr><td class="text-muted small">IP</td><td><code>' + (sess['address'] || '—') + '</code></td></tr>';
                    html += '<tr><td class="text-muted small">MAC</td><td><small>' + (sess['caller-id'] || '—') + '</small></td></tr>';
                    html += '<tr><td class="text-muted small">Uptime</td><td><span class="badge badge-secondary">' + (sess['uptime'] || '—') + '</span></td></tr>';
                    html += '<tr><td class="text-muted small">Download</td><td><span class="text-success">' + formatBytes(sess['rx-bytes'] || 0) + '</span></td></tr>';
                    html += '<tr><td class="text-muted small">Upload</td><td><span class="text-danger">' + formatBytes(sess['tx-bytes'] || 0) + '</span></td></tr>';
                    html += '</table>';
                } else {
                    html += '<div class="text-center text-muted py-3"><i class="fas fa-times-circle fa-2x d-block mb-2"></i>Offline</div>';
                }
                html += '</div></div>';

                document.getElementById('mkinfo-body').innerHTML        = html;
                document.getElementById('mkinfo-actions').style.display = 'block';

                var status = acc['disabled'] === 'true' ? 'suspended' : (Object.keys(acc).length === 0 ? 'pending' : 'active');
                document.getElementById('btn-provision').classList.toggle('d-none', !(status === 'pending'));
                document.getElementById('btn-suspend').classList.toggle('d-none',   !(status === 'active'));
                document.getElementById('btn-restore').classList.toggle('d-none',   !(status === 'suspended'));
                //document.getElementById('btn-remove').classList.toggle('d-none',    !(status === 'active' || status === 'suspended'));
            })
            .catch(function() {
                document.getElementById('mkinfo-body').innerHTML = '<div class="alert alert-danger">Connection failed.</div>';
            });
    });
});

function mkAction2(action) {
    if (!currentCustomerId) return;
    var urls    = {
        provision: '/customers/' + currentCustomerId + '/mikrotik/provision',
        suspend:   '/customers/' + currentCustomerId + '/mikrotik/suspend',
        restore:   '/customers/' + currentCustomerId + '/mikrotik/restore',
        remove:    '/customers/' + currentCustomerId + '/mikrotik/',
    };
    var methods = { provision: 'POST', suspend: 'POST', restore: 'POST', remove: 'DELETE' };
    $.ajax({
        url: urls[action], method: methods[action], data: { _token: csrfToken },
        success: function() { $('#mkInfoModal').modal('hide'); },
        error: function(xhr) {
            alert('Failed: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Error'));
        }
    });
}

function formatBytes(bytes) {
    bytes = parseInt(bytes) || 0;
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576)    return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024)       return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' B';
}

// ── SMS Modal ─────────────────────────────────────────
function openSmsModal(customerId, name, phone) {
    currentCustomerId = customerId;
    smsCustomerPhone  = phone;
    document.getElementById('sms-customer-name').textContent = name;
    document.getElementById('sms-phone').textContent         = phone;
    document.getElementById('smsMessage').value              = '';
    document.getElementById('smsCount').textContent          = '0';
    document.getElementById('smsTemplate').value             = '';
    $('#smsModal').modal('show');
}

function fillTemplate() {
    var msg = document.getElementById('smsTemplate').value;
    document.getElementById('smsMessage').value     = msg;
    document.getElementById('smsCount').textContent = msg.length;
}

document.getElementById('smsMessage').addEventListener('input', function() {
    document.getElementById('smsCount').textContent = this.value.length;
});

function sendSms() {
    var message = document.getElementById('smsMessage').value.trim();
    if (!message) { alert('Please enter a message.'); return; }
    $.ajax({
        url:    '{{ route("sms.test") }}',
        method: 'POST',
        data:   { _token: csrfToken, mobile: smsCustomerPhone, message: message },
        success: function() { $('#smsModal').modal('hide'); },
        error:   function() { alert('Failed to send SMS.'); }
    });
}

// ── MikroTik Sync All ─────────────────────────────────
var syncPollInterval = null;

function startSync() {
    if (!confirm('Sync all customers MikroTik status? This may take a few minutes.')) return;

    // Reset modal
    document.getElementById('sync-spinner').style.display    = '';
    document.getElementById('sync-progress-wrap').style.display = 'none';
    document.getElementById('sync-message').textContent      = 'Starting sync...';
    document.getElementById('sync-counter').textContent      = '';
    document.getElementById('sync-done-footer').classList.add('d-none');
    $('#syncModal').modal('show');

    fetch('{{ route("mikrotik.sync.all") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            document.getElementById('sync-message').textContent = data.message;
            return;
        }
        // Start polling
        syncPollInterval = setInterval(pollSyncStatus, 2000);
    })
    .catch(() => {
        document.getElementById('sync-message').textContent = 'Failed to start sync.';
    });
}

function pollSyncStatus() {
    fetch('{{ route("mikrotik.sync.status") }}')
    .then(r => r.json())
    .then(data => {
        if (data.status === 'running') {
            document.getElementById('sync-spinner').style.display       = 'none';
            document.getElementById('sync-progress-wrap').style.display = '';
            document.getElementById('sync-message').textContent         = data.message ?? 'Processing...';

            if (data.total > 0) {
                var pct = Math.round((data.done / data.total) * 100);
                document.getElementById('sync-progress-bar').style.width = pct + '%';
                document.getElementById('sync-counter').textContent      = data.done + ' / ' + data.total;
            }

        } else if (data.status === 'completed') {
            clearInterval(syncPollInterval);
            document.getElementById('sync-spinner').style.display       = 'none';
            document.getElementById('sync-progress-wrap').style.display = '';
            document.getElementById('sync-progress-bar').style.width    = '100%';
            document.getElementById('sync-progress-bar').classList.remove('progress-bar-animated');
            document.getElementById('sync-message').textContent         = 'Sync complete!';
            document.getElementById('sync-counter').textContent         = '';
            document.getElementById('sync-result').innerHTML =
                '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>Active: <strong>' + (data.active ?? 0) + '</strong></span> &nbsp; ' +
                '<span class="text-secondary"><i class="fas fa-clock mr-1"></i>Pending: <strong>' + (data.pending ?? 0) + '</strong></span> &nbsp; ' +
                '<span class="text-info"><i class="fas fa-network-wired mr-1"></i>IPs updated: <strong>' + (data.ip_updated ?? 0) + '</strong></span>';
            document.getElementById('sync-done-footer').classList.remove('d-none');

        } else if (data.status === 'failed') {
            clearInterval(syncPollInterval);
            document.getElementById('sync-message').textContent = 'Sync failed: ' + data.message;
            document.getElementById('sync-done-footer').classList.remove('d-none');
        }
    });
}

</script>
@endpush