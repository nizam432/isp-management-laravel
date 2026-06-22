@extends('layouts.app')
@section('page_title', 'Monthly Billing Report')
@section('page_actions')
    <a href="{{ route('reports.bill.monthly-billing.pdf', request()->query()) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> Generate PDF
    </a>
    <a href="{{ route('reports.bill.monthly-billing.csv', request()->query()) }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-excel mr-1"></i> Generate Excel
    </a>
@endsection
@section('page_content')

{{-- Stats --}}
<style>
.cust-stat-card {
    border-radius: 4px; color: #fff; padding: 14px 16px;
    margin-bottom: 16px; height: 80px;
    display: flex; align-items: center; justify-content: space-between; overflow: hidden;
}
.cust-stat-card .sc-left .sc-label {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: rgba(255,255,255,.85); margin-bottom: 4px;
}
.cust-stat-card .sc-left .sc-value { font-size: 26px; font-weight: 700; line-height: 1; color: #fff; }
.cust-stat-card .sc-icon { font-size: 52px; color: rgba(255,255,255,.18); }
</style>
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-file-invoice-dollar mr-1"></i> Generated</div>
                <div class="sc-value">৳ {{ number_format($grandTotal['generated'], 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-hand-holding-usd mr-1"></i> Received</div>
                <div class="sc-value">৳ {{ number_format($grandTotal['received'], 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-hand-holding-usd"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#dd4b39;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-exclamation-circle mr-1"></i> Balance Due</div>
                <div class="sc-value">৳ {{ number_format($grandTotal['due'], 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-exclamation-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#605ca8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-piggy-bank mr-1"></i> Advance</div>
                <div class="sc-value">৳ {{ number_format($grandTotal['advance'], 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-piggy-bank"></i></div>
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
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Month</label>
                        <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}">
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
                        <label class="small font-weight-bold">Client Type</label>
                        <select name="client_type_id" class="form-control form-control-sm">
                            <option value="">All Types</option>
                            @foreach($clientTypes as $ct)
                                <option value="{{ $ct->id }}" {{ request('client_type_id') == $ct->id ? 'selected' : '' }}>{{ ucfirst($ct->name) }}</option>
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
                                <option value="{{ $ct->id }}" {{ request('connection_type_id') == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
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
                                <option value="{{ $pt->id }}" {{ request('protocol_type_id') == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Billing Status</label>
                        <select name="billing_status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            @foreach(['active','inactive','left','free'] as $s)
                                <option value="{{ $s }}" {{ request('billing_status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Payment Status</label>
                        <select name="payment_status" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach(['paid','unpaid','partial','overdue'] as $s)
                                <option value="{{ $s }}" {{ request('payment_status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Mikrotik Status</label>
                        <select name="mikrotik_status" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach(['active','suspended','pending'] as $s)
                                <option value="{{ $s }}" {{ request('mikrotik_status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
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
            </div>
            <div class="mt-1">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('reports.bill.monthly-billing') }}" class="btn btn-sm btn-secondary ml-1">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
                @if(request()->hasAny(['zone_id','package_id','client_type_id','connection_type_id','protocol_type_id','billing_status','payment_status','mikrotik_status','router_id']))
                    <span class="badge badge-warning ml-2">Filtered: {{ $invoices->total() }} results</span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-list mr-1"></i> Billing List — {{ \Carbon\Carbon::parse($month)->format('F Y') }}</h3>
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
            <span class="badge badge-info">{{ $invoices->total() }} invoices</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>C.Code</th>
                        <th>ID/IP</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Zone</th>
                        <th>Cus.Type</th>
                        <th>Conn.Type</th>
                        <th>R.Address</th>
                        <th>Package</th>
                        <th>Speed</th>
                        <th>Generated</th>
                        <th>Received</th>
                        <th>Balance Due</th>
                        <th>Advance</th>
                        <th>Payment Date</th>
                        <th>Server</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $i => $inv)
                    @php $cust = $inv->customer; @endphp
                    <tr>
                        <td class="text-muted small">{{ $invoices->firstItem() + $i }}</td>
                        <td><code>{{ $cust->customer_code ?? '-' }}</code></td>
                        <td><small>{{ $cust->pppoe_username ?? $cust->ip_address ?? '-' }}</small></td>
                        <td>
                            <span class="font-weight-bold">{{ $cust->name ?? '-' }}</span>
                        </td>
                        <td>{{ $cust->phone ?? '-' }}</td>
                        <td>
                            @if($cust->zone ?? null)
                                <i class="fas fa-map-marker-alt mr-1 text-danger"></i>{{ $cust->zone->name }}
                            @else -
                            @endif
                        </td>
                        <td>{{ $cust->clientType->name ?? '-' }}</td>
                        <td>{{ $cust->connectionType->name ?? '-' }}</td>
                        <td><small>{{ $cust->address ?? '-' }}</small></td>
                        <td>{{ $cust->package->name ?? '-' }}</td>
                        <td>
                            @if($cust->package ?? null)
                                <small>{{ $cust->package->speed_download ?? '-' }}Mbps</small>
                            @else -
                            @endif
                        </td>
                        <td>{{ number_format($inv->amount, 0) }}</td>
                        <td class="text-success font-weight-bold">{{ number_format($inv->amount - $inv->due_amount, 0) }}</td>
                        <td class="{{ $inv->due_amount > 0 ? 'text-danger font-weight-bold' : '' }}">{{ number_format($inv->due_amount, 0) }}</td>
                        <td class="text-info">{{ number_format($cust->advance_balance ?? 0, 0) }}</td>
                        <td>
                            @if($cust->last_payment_date)
                                {{ \Carbon\Carbon::parse($cust->last_payment_date)->format('d M Y') }}
                            @else -
                            @endif
                        </td>
                        <td><small>{{ $cust->router->name ?? '-' }}</small></td>
                    </tr>
                    @empty
                    <tr><td colspan="17" class="text-center text-muted py-4">No billing records found for {{ \Carbon\Carbon::parse($month)->format('F Y') }}.</td></tr>
                    @endforelse
                </tbody>
                @if($invoices->count())
                <tfoot>
                    <tr class="font-weight-bold">
                        <td colspan="11" class="text-right">Total</td>
                        <td>{{ number_format($grandTotal['generated'], 0) }}</td>
                        <td class="text-success">{{ number_format($grandTotal['received'], 0) }}</td>
                        <td class="text-danger">{{ number_format($grandTotal['due'], 0) }}</td>
                        <td class="text-info">{{ number_format($grandTotal['advance'], 0) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} entries</small>
        {{ $invoices->links() }}
    </div>
</div>
@endsection
