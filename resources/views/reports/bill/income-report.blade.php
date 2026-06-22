@extends('layouts.app')
@section('page_title', 'Income Report')
@section('page_actions')
    <a href="{{ route('reports.bill.income.pdf', request()->query()) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> Generate PDF
    </a>
    <a href="{{ route('reports.bill.income.xlsx', request()->query()) }}" class="btn btn-success btn-sm">
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
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-hand-holding-usd mr-1"></i> Total Income</div>
                <div class="sc-value">৳ {{ number_format($grandTotal['amount'], 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-hand-holding-usd"></i></div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-list mr-1"></i> Total Transactions</div>
                <div class="sc-value">{{ number_format($grandTotal['count']) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-list"></i></div>
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
                        <label class="small font-weight-bold">From Date</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">To Date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Income Head</label>
                        <select name="category_id" class="form-control form-control-sm">
                            <option value="">All Head</option>
                            <option value="monthly_bill" {{ request('category_id') === 'monthly_bill' ? 'selected' : '' }}>Monthly Bill</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search mr-1"></i> Search</button>
                <a href="{{ route('reports.bill.income') }}" class="btn btn-sm btn-secondary ml-1"><i class="fas fa-redo mr-1"></i> Reset</a>
                @if(request()->hasAny(['from_date','to_date','category_id']))
                    <span class="badge badge-warning ml-2">Filtered: {{ $paginated['total'] }} results</span>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-list mr-1"></i> Income List</h3>
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
            <span class="badge badge-info">{{ $paginated['total'] }} records</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Sl No</th><th>Income Id</th><th>Name</th><th>Income Head</th>
                    <th>Date</th><th>Invoice No</th><th>Description</th><th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paginated['data'] as $i => $item)
                <tr>
                    <td class="text-muted small">{{ $paginated['from'] + $i }}</td>
                    <td>{{ $item['id'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td><span class="badge badge-success">{{ $item['head'] }}</span></td>
                    <td>{{ $item['date'] }}</td>
                    <td><small>{{ $item['invoice_no'] }}</small></td>
                    <td><small>{{ $item['description'] }}</small></td>
                    <td class="text-right font-weight-bold text-success">{{ number_format($item['amount'], 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No income records found.</td></tr>
                @endforelse
            </tbody>
            @if(count($paginated['data']))
            <tfoot>
                <tr class="font-weight-bold">
                    <td colspan="7" class="text-right">Total</td>
                    <td class="text-right text-success">{{ number_format($grandTotal['amount'], 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $paginated['from'] }} to {{ $paginated['to'] }} of {{ $paginated['total'] }} entries</small>
        {{-- Manual pagination links --}}
        @if($paginated['last_page'] > 1)
        <nav>
            <ul class="pagination pagination-sm mb-0">
                @if($paginated['current_page'] > 1)
                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $paginated['current_page'] - 1]) }}">«</a></li>
                @endif
                @for($p = max(1, $paginated['current_page'] - 2); $p <= min($paginated['last_page'], $paginated['current_page'] + 2); $p++)
                    <li class="page-item {{ $p == $paginated['current_page'] ? 'active' : '' }}">
                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $p]) }}">{{ $p }}</a>
                    </li>
                @endfor
                @if($paginated['current_page'] < $paginated['last_page'])
                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $paginated['current_page'] + 1]) }}">»</a></li>
                @endif
            </ul>
        </nav>
        @endif
    </div>
</div>
@endsection
