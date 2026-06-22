@extends('layouts.app')
@section('page_title', 'Profit & Loss Report')
@section('page_actions')
    <a href="{{ route('reports.bill.profit.pdf', request()->query()) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> Generate PDF
    </a>
    <a href="{{ route('reports.bill.profit.xlsx', request()->query()) }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-excel mr-1"></i> Generate Excel
    </a>
@endsection
@section('page_content')
<style>
.cust-stat-card { border-radius:4px;color:#fff;padding:14px 16px;margin-bottom:16px;height:80px;display:flex;align-items:center;justify-content:space-between;overflow:hidden; }
.cust-stat-card .sc-left .sc-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:rgba(255,255,255,.85);margin-bottom:4px; }
.cust-stat-card .sc-left .sc-value { font-size:22px;font-weight:700;line-height:1;color:#fff; }
.cust-stat-card .sc-icon { font-size:52px;color:rgba(255,255,255,.18); }
</style>

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-hand-holding-usd mr-1"></i> Total Income</div>
                <div class="sc-value">৳ {{ number_format($totalIncome, 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-hand-holding-usd"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#dd4b39;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-money-bill-wave mr-1"></i> Total Expense</div>
                <div class="sc-value">৳ {{ number_format($totalExpense, 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:{{ $netProfit >= 0 ? '#00a65a' : '#dd4b39' }};">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-chart-line mr-1"></i> Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</div>
                <div class="sc-value">৳ {{ number_format(abs($netProfit), 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-chart-line"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-percentage mr-1"></i> Profit Margin</div>
                <div class="sc-value">{{ $totalIncome > 0 ? number_format(($netProfit / $totalIncome) * 100, 1) : 0 }}%</div>
            </div>
            <div class="sc-icon"><i class="fas fa-percentage"></i></div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Date Range</h3>
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
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $from }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">To Date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $to }}">
                    </div>
                </div>
            </div>
            <div class="mt-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search mr-1"></i> Apply</button>
                <a href="{{ route('reports.bill.profit') }}" class="btn btn-sm btn-secondary ml-1"><i class="fas fa-redo mr-1"></i> Reset</a>
                <span class="badge badge-info ml-2">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</span>
            </div>
        </form>
    </div>
</div>

<div class="row">
    {{-- P&L Summary --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-balance-scale mr-1"></i> P&L Summary</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr class="table-success">
                            <td class="font-weight-bold"><i class="fas fa-plus-circle mr-1 text-success"></i> Monthly Bill (Payments)</td>
                            <td class="text-right font-weight-bold text-success">৳ {{ number_format($paymentIncome, 2) }}</td>
                        </tr>
                        <tr class="table-success">
                            <td><i class="fas fa-plus-circle mr-1 text-success"></i> Other Manual Income</td>
                            <td class="text-right text-success">৳ {{ number_format($manualIncome, 2) }}</td>
                        </tr>
                        <tr style="background:#d5f5e3;">
                            <td class="font-weight-bold">TOTAL INCOME</td>
                            <td class="text-right font-weight-bold">৳ {{ number_format($totalIncome, 2) }}</td>
                        </tr>
                        <tr><td colspan="2"></td></tr>
                        <tr class="table-danger">
                            <td class="font-weight-bold"><i class="fas fa-minus-circle mr-1 text-danger"></i> TOTAL EXPENSE</td>
                            <td class="text-right font-weight-bold text-danger">৳ {{ number_format($totalExpense, 2) }}</td>
                        </tr>
                        <tr><td colspan="2"></td></tr>
                        <tr style="background:{{ $netProfit >= 0 ? '#d5f5e3' : '#fadbd8' }};">
                            <td class="font-weight-bold" style="font-size:15px;">NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}</td>
                            <td class="text-right font-weight-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:15px;">৳ {{ number_format(abs($netProfit), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Monthly Breakdown --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Monthly Breakdown</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Month</th>
                            <th class="text-right">Income</th>
                            <th class="text-right">Expense</th>
                            <th class="text-right">Profit/Loss</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($months as $m)
                        <tr>
                            <td class="font-weight-bold">{{ $m['month'] }}</td>
                            <td class="text-right text-success">৳ {{ number_format($m['income'], 0) }}</td>
                            <td class="text-right text-danger">৳ {{ number_format($m['expense'], 0) }}</td>
                            <td class="text-right font-weight-bold {{ $m['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $m['profit'] >= 0 ? '+' : '-' }}৳ {{ number_format(abs($m['profit']), 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Income by Category --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title"><i class="fas fa-arrow-up mr-1"></i> Income by Category</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Category</th><th class="text-right">Amount</th></tr></thead>
                    <tbody>
                        @forelse($incomeByCategory as $cat)
                        <tr>
                            <td>{{ $cat['name'] }}</td>
                            <td class="text-right text-success font-weight-bold">৳ {{ number_format($cat['amount'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">No data.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td>Total</td>
                            <td class="text-right text-success">৳ {{ number_format($totalIncome, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Expense by Category --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title"><i class="fas fa-arrow-down mr-1"></i> Expense by Category</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Category</th><th class="text-right">Amount</th></tr></thead>
                    <tbody>
                        @forelse($expenseByCategory as $cat)
                        <tr>
                            <td>{{ $cat['name'] }}</td>
                            <td class="text-right text-danger font-weight-bold">৳ {{ number_format($cat['amount'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">No data.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td>Total</td>
                            <td class="text-right text-danger">৳ {{ number_format($totalExpense, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
