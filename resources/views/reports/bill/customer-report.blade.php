@extends('layouts.app')
@section('page_title', 'Customer Report')
@section('page_actions')
    <a href="{{ route('reports.bill.customer.pdf', request()->query()) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> Generate PDF
    </a>
    <a href="{{ route('reports.bill.customer.xlsx', request()->query()) }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-excel mr-1"></i> Generate Excel
    </a>
@endsection
@section('page_content')
<style>
.cust-stat-card { border-radius:4px;color:#fff;padding:14px 16px;margin-bottom:16px;height:80px;display:flex;align-items:center;justify-content:space-between;overflow:hidden; }
.cust-stat-card .sc-left .sc-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:rgba(255,255,255,.85);margin-bottom:4px; }
.cust-stat-card .sc-left .sc-value { font-size:26px;font-weight:700;line-height:1;color:#fff; }
.cust-stat-card .sc-icon { font-size:52px;color:rgba(255,255,255,.18); }
</style>

<div class="row mb-3">
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-users mr-1"></i> Total Customers</div>
                <div class="sc-value">{{ number_format($grandTotal['total']) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-check mr-1"></i> Active</div>
                <div class="sc-value">{{ number_format($grandTotal['active']) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-check"></i></div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#dd4b39;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-times mr-1"></i> Inactive/Expired</div>
                <div class="sc-value">{{ number_format($grandTotal['inactive']) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-times"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Search & Filter</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Search</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                            <input type="text" name="search" class="form-control" placeholder="Code / Name / Phone / Username" value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Package</label>
                        <select name="package_id" class="form-control form-control-sm">
                            <option value="">All Packages</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>{{ $pkg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Zone</label>
                        <select name="zone_id" class="form-control form-control-sm">
                            <option value="">All Zones</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
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
                                <option value="{{ $ct->id }}" {{ request('client_type_id') == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Protocol</label>
                        <select name="protocol_type_id" class="form-control form-control-sm">
                            <option value="">All Protocols</option>
                            @foreach($protocolTypes as $pt)
                                <option value="{{ $pt->id }}" {{ request('protocol_type_id') == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Server</label>
                        <select name="router_id" class="form-control form-control-sm">
                            <option value="">All Servers</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>{{ $router->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">POP / Reseller</label>
                        <select name="mac_reseller_id" class="form-control form-control-sm">
                            <option value="">All POPs</option>
                            @foreach($resellers as $reseller)
                                <option value="{{ $reseller->id }}" {{ request('mac_reseller_id') == $reseller->id ? 'selected' : '' }}>{{ $reseller->business_name ?? $reseller->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">B. Status</label>
                        <select name="billing_status" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach(['active','inactive','left','free'] as $s)
                                <option value="{{ $s }}" {{ request('billing_status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">From Date</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">To Date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                    </div>
                </div>
            </div>
            <div class="mt-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search mr-1"></i> Search</button>
                <a href="{{ route('reports.bill.customer') }}" class="btn btn-sm btn-secondary ml-1"><i class="fas fa-redo mr-1"></i> Reset</a>
                @if(request()->hasAny(['search','package_id','zone_id','client_type_id','protocol_type_id','router_id','mac_reseller_id','billing_status','from_date','to_date']))
                    <span class="badge badge-warning ml-2">Filtered: {{ $customers->total() }} results</span>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Customer List</h3>
        <div>
            <form method="GET" class="form-inline d-inline-block mr-2">
                @foreach(request()->except(['show','page']) as $key => $val)
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endforeach
                <label class="mr-1 mb-0 small">Show</label>
                <select name="show" class="form-control form-control-sm mr-1" style="width:auto" onchange="this.form.submit()">
                    @foreach([10,25,50,100,500,1000,2000,5000] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            <span class="badge badge-info">{{ $customers->total() }} customers</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mb-0" style="white-space:nowrap;">
                <thead class="thead-dark">
                    <tr>
                        <th>Client Code</th><th>Username</th><th>Customer Name</th>
                        <th>Contact Number</th><th>Client Type</th><th>Package</th>
                        <th>Server</th><th>Protocol</th>
                        <th>Monthly Bill</th><th>B.Status</th><th>POP Name</th><th>M.Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $cust)
                    <tr>
                        <td><code>{{ $cust->customer_code }}</code></td>
                        <td>{{ $cust->pppoe_username ?? '-' }}</td>
                        <td class="font-weight-bold">{{ $cust->name }}</td>
                        <td>{{ $cust->phone ?? '-' }}</td>
                        <td>{{ $cust->clientType->name ?? '-' }}</td>
                        <td>{{ $cust->package->name ?? '-' }}</td>
                        <td><small>{{ $cust->router->name ?? '-' }}</small></td>
                        <td>{{ $cust->protocolType->name ?? '-' }}</td>
                        <td>{{ number_format($cust->monthly_bill_amount ?? 0, 2) }}</td>
                        <td>
                            @php $bs = $cust->billing_status ?? $cust->status; @endphp
                            <span class="badge {{ $bs === 'active' ? 'badge-success' : ($bs === 'left' ? 'badge-danger' : 'badge-secondary') }}">{{ ucfirst($bs) }}</span>
                        </td>
                        <td>{{ $cust->macReseller->business_name ?? '-' }}</td>
                        <td>
                            @if($cust->mikrotik_status === 'active')
                                <span class="badge badge-success">Active</span>
                            @elseif($cust->mikrotik_status === 'suspended')
                                <span class="badge badge-warning">Suspended</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($cust->mikrotik_status ?? '-') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="text-center text-muted py-4">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} entries</small>
        {{ $customers->links() }}
    </div>
</div>
@endsection
