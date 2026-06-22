@extends('layouts.app')
@section('page_title', 'Bill Collection')
@section('page_actions')
    <a href="{{ route('reports.bill.receive-history.pdf', request()->query()) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> Generate PDF
    </a>
    <a href="{{ route('reports.bill.receive-history.csv', request()->query()) }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-excel mr-1"></i> Generate Excel
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
    font-size: 28px;
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
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-hand-holding-usd mr-1"></i> Total Received</div>
                <div class="sc-value">৳ {{ number_format($grandTotal['received'], 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-hand-holding-usd"></i></div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-file-invoice-dollar mr-1"></i> Monthly Bill (Filtered)</div>
                <div class="sc-value">৳ {{ number_format($grandTotal['monthly_bill'], 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#605ca8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-receipt mr-1"></i> Transactions</div>
                <div class="sc-value">{{ number_format($payments->total()) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-receipt"></i></div>
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
                                   placeholder="Code / Name / Mobile / Username"
                                   value="{{ request('search') }}">
                        </div>
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
                        <select name="sub_zone_id" class="form-control form-control-sm">
                            <option value="">All Sub Zones</option>
                            @foreach($subZones as $sz)
                                <option value="{{ $sz->id }}" {{ request('sub_zone_id') == $sz->id ? 'selected' : '' }}>
                                    {{ $sz->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Billing Status</label>
                        <select name="billing_status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            @foreach($billingStatuses as $status)
                                <option value="{{ $status }}" {{ request('billing_status') === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Reseller / POP</label>
                        <select name="mac_reseller_id" class="form-control form-control-sm">
                            <option value="">All Resellers</option>
                            @foreach($resellers as $reseller)
                                <option value="{{ $reseller->id }}" {{ request('mac_reseller_id') == $reseller->id ? 'selected' : '' }}>
                                    {{ $reseller->business_name ?? $reseller->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Payment Gateway</label>
                        <select name="method" class="form-control form-control-sm">
                            <option value="">All Gateways</option>
                            @foreach($methods as $m)
                                <option value="{{ $m }}" {{ request('method') === $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Received By</label>
                        <select name="received_by" class="form-control form-control-sm">
                            <option value="">All Staff</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('received_by') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Creation From</label>
                        <input type="date" name="creation_from" class="form-control form-control-sm" value="{{ request('creation_from') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Creation To</label>
                        <input type="date" name="creation_to" class="form-control form-control-sm" value="{{ request('creation_to') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Recharge From</label>
                        <input type="date" name="paid_from" class="form-control form-control-sm" value="{{ request('paid_from') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Recharge To</label>
                        <input type="date" name="paid_to" class="form-control form-control-sm" value="{{ request('paid_to') }}">
                    </div>
                </div>
            </div>
            <div class="mt-1">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('reports.bill.receive-history') }}" class="btn btn-sm btn-secondary ml-1">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
                @if(request()->hasAny(['search','package_id','zone_id','sub_zone_id','billing_status','mac_reseller_id','method','received_by','creation_from','creation_to','paid_from','paid_to']))
                    <span class="badge badge-warning ml-2">Filtered: {{ $payments->total() }} results</span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Bill Collection Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-receipt mr-1"></i> Collection List</h3>
        <div>
            <form method="GET" class="form-inline d-inline-block mr-2">
                @foreach(request()->except(['show','page']) as $key => $val)
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endforeach
                <label class="mr-2 mb-0 small">Show</label>
                <select name="show" class="form-control form-control-sm mr-1" style="width:auto" onchange="this.form.submit()">
                    @foreach([10,25,50,100,500,1000,2000,5000] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            <span class="badge badge-info">{{ $payments->total() }} transactions</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>R.Date</th>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Zone</th>
                    <th>Package</th>
                    <th>Agent</th>
                    <th>TrxId</th>
                    <th>Monthly Bill</th>
                    <th>Received</th>
                    <th>Creation Date</th>
                    <th>Received By</th>
                    <th>Gateway</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $i => $pay)
                <tr>
                    <td class="text-muted small">{{ $payments->firstItem() + $i }}</td>
                    <td>
                        {{ $pay->paid_at ? \Carbon\Carbon::parse($pay->paid_at)->format('d M Y') : '-' }}
                        <br><small class="text-muted">{{ $pay->paid_at ? \Carbon\Carbon::parse($pay->paid_at)->format('h:i A') : '' }}</small>
                    </td>
                    <td>
                        <span class="font-weight-bold d-block">{{ $pay->customer->name ?? '-' }}</span>
                        <small class="text-muted"><code>{{ $pay->customer->customer_code ?? '-' }}</code></small>
                    </td>
                    <td>
                        <a href="tel:{{ $pay->customer->phone ?? '' }}">
                            <i class="fas fa-phone-alt mr-1"></i>{{ $pay->customer->phone ?? '-' }}
                        </a>
                    </td>
                    <td>
                        @if($pay->customer->zone ?? null)
                            <i class="fas fa-map-marker-alt mr-1 text-danger"></i>{{ $pay->customer->zone->name }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $pay->customer->package->name ?? '-' }}</td>
                    <td>{{ $pay->customer->agent->name ?? '-' }}</td>
                    <td><small>{{ $pay->transaction_id ?? '-' }}</small></td>
                    <td>৳{{ number_format($pay->customer->monthly_bill_amount ?? 0, 0) }}</td>
                    <td class="font-weight-bold text-success">৳{{ number_format($pay->amount, 0) }}</td>
                    <td>
                        {{ $pay->created_at ? \Carbon\Carbon::parse($pay->created_at)->format('d M Y') : '-' }}
                        <br><small class="text-muted">{{ $pay->created_at ? \Carbon\Carbon::parse($pay->created_at)->format('h:i A') : '' }}</small>
                    </td>
                    <td>{{ $pay->receivedBy->name ?? '-' }}</td>
                    <td>
                        @php
                            $badgeMap = [
                                'cash' => 'success', 'bkash' => 'danger', 'nagad' => 'warning',
                                'rocket' => 'primary', 'bank' => 'info', 'card' => 'secondary', 'advance' => 'dark',
                            ];
                        @endphp
                        <span class="badge badge-{{ $badgeMap[$pay->method] ?? 'secondary' }}">{{ strtoupper($pay->method) }}</span>
                    </td>
                    <td><small class="text-muted">{{ $pay->remarks ?? '-' }}</small></td>
                </tr>
                @empty
                <tr><td colspan="14" class="text-center text-muted py-4">No collections found for the selected filters.</td></tr>
                @endforelse
            </tbody>
            @if($payments->count())
            <tfoot>
                <tr class="font-weight-bold">
                    <td colspan="8" class="text-right">Total</td>
                    <td>৳{{ number_format($grandTotal['monthly_bill'], 0) }}</td>
                    <td>৳{{ number_format($grandTotal['received'], 0) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} entries</small>
        {{ $payments->links() }}
    </div>
</div>
@endsection
