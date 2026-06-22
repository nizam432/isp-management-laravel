@extends('layouts.app')
@section('page_title', 'Income & Discount Report')
@section('page_actions')
    <a href="{{ route('reports.bill.income-discount.pdf', request()->query()) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> Generate PDF
    </a>
    <a href="{{ route('reports.bill.income-discount.xlsx', request()->query()) }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-excel mr-1"></i> Generate Excel
    </a>
@endsection
@section('page_content')
<style>
.cust-stat-card { border-radius:4px;color:#fff;padding:14px 16px;margin-bottom:16px;height:80px;display:flex;align-items:center;justify-content:space-between;overflow:hidden; }
.cust-stat-card .sc-left .sc-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:rgba(255,255,255,.85);margin-bottom:4px; }
.cust-stat-card .sc-left .sc-value { font-size:24px;font-weight:700;line-height:1;color:#fff; }
.cust-stat-card .sc-icon { font-size:52px;color:rgba(255,255,255,.18); }
</style>

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-file-invoice-dollar mr-1"></i> Total Billed</div>
                <div class="sc-value">৳ {{ number_format($grandTotal['billed'], 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-hand-holding-usd mr-1"></i> Collected</div>
                <div class="sc-value">৳ {{ number_format($grandTotal['collected'], 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-hand-holding-usd"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#f39c12;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-tags mr-1"></i> Total Discount</div>
                <div class="sc-value">৳ {{ number_format($grandTotal['discount'], 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-tags"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#dd4b39;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-exclamation-circle mr-1"></i> Due</div>
                <div class="sc-value">৳ {{ number_format($grandTotal['due'], 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-exclamation-circle"></i></div>
        </div>
    </div>
</div>

{{-- Filter --}}
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
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">From Date</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $from }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">To Date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $to }}">
                    </div>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-3">
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
                        <label class="small font-weight-bold">Payment Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach(['paid','unpaid','partial','overdue'] as $s)
                                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search mr-1"></i> Search</button>
                <a href="{{ route('reports.bill.income-discount') }}" class="btn btn-sm btn-secondary ml-1"><i class="fas fa-redo mr-1"></i> Reset</a>
                <span class="badge badge-info ml-2">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</span>
                <span class="badge badge-secondary ml-1">{{ $grandTotal['count'] }} invoices</span>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-list mr-1"></i> Invoice List</h3>
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
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Invoice No</th>
                    <th>Customer</th>
                    <th>Package</th>
                    <th>Zone</th>
                    <th>Agent</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th class="text-right">Billed</th>
                    <th class="text-right text-warning">Discount</th>
                    <th class="text-right text-success">Collected</th>
                    <th class="text-right text-danger">Due</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $i => $inv)
                <tr>
                    <td class="text-muted small">{{ $invoices->firstItem() + $i }}</td>
                    <td><code>{{ $inv->invoice_no }}</code></td>
                    <td>
                        <span class="font-weight-bold">{{ $inv->customer->name ?? '-' }}</span>
                        <br><small class="text-muted">{{ $inv->customer->customer_code ?? '' }}</small>
                    </td>
                    <td>{{ $inv->customer->package->name ?? '-' }}</td>
                    <td>{{ $inv->customer->zone->name ?? '-' }}</td>
                    <td>{{ $inv->customer->agent->name ?? '-' }}</td>
                    <td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '-' }}</td>
                    <td>
                        @php $statusMap = ['paid'=>'badge-success','unpaid'=>'badge-danger','partial'=>'badge-warning','overdue'=>'badge-dark']; @endphp
                        <span class="badge {{ $statusMap[$inv->status] ?? 'badge-secondary' }}">{{ ucfirst($inv->status) }}</span>
                    </td>
                    <td class="text-right">{{ number_format($inv->amount, 2) }}</td>
                    <td class="text-right text-warning font-weight-bold">{{ number_format($inv->discount ?? 0, 2) }}</td>
                    <td class="text-right text-success font-weight-bold">{{ number_format($inv->amount - $inv->due_amount, 2) }}</td>
                    <td class="text-right {{ $inv->due_amount > 0 ? 'text-danger font-weight-bold' : '' }}">{{ number_format($inv->due_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center text-muted py-4">No invoices found for this date range.</td></tr>
                @endforelse
            </tbody>
            @if($invoices->count())
            <tfoot>
                <tr class="font-weight-bold">
                    <td colspan="8" class="text-right">Total</td>
                    <td class="text-right">{{ number_format($grandTotal['billed'], 2) }}</td>
                    <td class="text-right text-warning">{{ number_format($grandTotal['discount'], 2) }}</td>
                    <td class="text-right text-success">{{ number_format($grandTotal['collected'], 2) }}</td>
                    <td class="text-right text-danger">{{ number_format($grandTotal['due'], 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} entries</small>
        {{ $invoices->links() }}
    </div>
</div>
@endsection
