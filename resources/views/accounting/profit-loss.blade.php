@extends('adminlte::page')
@section('title', 'Profit & Loss Report')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h4 class="mb-0 font-weight-bold text-dark">
            <i class="fas fa-chart-pie mr-2 text-primary"></i>Profit & Loss Report
        </h4>
        <small class="text-muted">Financial summary by period</small>
    </div>
    <a href="{{ route('expenses.profit-loss.pdf', request()->query()) }}" class="btn btn-danger btn-sm px-3">
        <i class="fas fa-file-pdf mr-1"></i> PDF Export
    </a>
</div>
@endsection

@section('content')

{{-- Filter --}}
<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="form-inline">
            <label class="mr-2 font-weight-bold small">From:</label>
            <input type="date" name="from_date" class="form-control form-control-sm mr-3" value="{{ $from }}">
            <label class="mr-2 font-weight-bold small">To:</label>
            <input type="date" name="to_date" class="form-control form-control-sm mr-3" value="{{ $to }}">
            <button type="submit" class="btn btn-primary btn-sm mr-2">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="{{ route('expenses.profit-loss') }}" class="btn btn-secondary btn-sm mr-3">
                <i class="fas fa-redo mr-1"></i> Reset
            </a>
            <span class="text-muted small">
                {{ \Carbon\Carbon::parse($from)->format('d M Y') }} —
                {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </span>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<style>
.pl-stat-card {
    border-radius: 8px;
    color: #fff;
    padding: 14px 16px;
    margin-bottom: 16px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    overflow: hidden;
}
.pl-stat-card .sc-left .sc-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(255,255,255,.85);
    margin-bottom: 4px;
}
.pl-stat-card .sc-left .sc-value {
    font-size: 22px;
    font-weight: 700;
    line-height: 1;
}
.pl-stat-card .sc-icon {
    font-size: 44px;
    color: rgba(255,255,255,.18);
}
</style>

<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="pl-stat-card" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-arrow-up mr-1"></i>Total Income</div>
                <div class="sc-value">৳{{ number_format($grandIncome) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-arrow-up"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="pl-stat-card" style="background:linear-gradient(135deg,#b71c1c,#c62828);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-arrow-down mr-1"></i>Total Expense</div>
                <div class="sc-value">৳{{ number_format($grandExpense) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-arrow-down"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="pl-stat-card" style="background:linear-gradient(135deg,#1a237e,#283593);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-chart-line mr-1"></i>Net Profit</div>
                <div class="sc-value">৳{{ number_format($grandProfit) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-chart-line"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="pl-stat-card" style="background:linear-gradient(135deg,#006064,#00838f);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-percent mr-1"></i>Profit Margin</div>
                <div class="sc-value">{{ $grandMargin }}%</div>
            </div>
            <div class="sc-icon"><i class="fas fa-percent"></i></div>
        </div>
    </div>
</div>
{{-- Category Breakdown --}}
<div class="row mt-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-plus-circle mr-1"></i> Income Breakdown
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($incomeByCategory as $cat)
                        <tr>
                            <td style="padding:8px 12px;">
                                <i class="fas fa-circle text-success mr-1" style="font-size:8px;"></i>
                                {{ $cat->category_name }}
                            </td>
                            <td class="text-right font-weight-bold text-success" style="padding:8px 12px;">
                                ৳{{ number_format($cat->total, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-3 text-muted">No income data.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot style="background:#e8f5e9; border-top:2px solid #2e7d32;">
                        <tr>
                            <td class="font-weight-bold" style="padding:10px 12px;">TOTAL INCOME</td>
                            <td class="text-right font-weight-bold text-success" style="padding:10px 12px; font-size:15px;">
                                ৳{{ number_format($grandIncome, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#b71c1c,#c62828);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-minus-circle mr-1"></i> Expense Breakdown
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($expenseByCategory as $cat)
                        <tr>
                            <td style="padding:8px 12px;">
                                <i class="fas fa-circle text-danger mr-1" style="font-size:8px;"></i>
                                {{ $cat->category_name }}
                            </td>
                            <td class="text-right font-weight-bold text-danger" style="padding:8px 12px;">
                                ৳{{ number_format($cat->total, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-3 text-muted">No expense data.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot style="background:#ffebee; border-top:2px solid #c62828;">
                        <tr>
                            <td class="font-weight-bold" style="padding:10px 12px;">TOTAL EXPENSE</td>
                            <td class="text-right font-weight-bold text-danger" style="padding:10px 12px; font-size:15px;">
                                ৳{{ number_format($grandExpense, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Net Profit Box --}}
        <div class="card shadow-sm mt-3" style="background:{{ $grandProfit >= 0 ? '#e8f5e9' : '#ffebee' }}; border:2px solid {{ $grandProfit >= 0 ? '#2e7d32' : '#c62828' }};">
            <div class="card-body py-3 text-center">
                <div class="small font-weight-bold text-muted mb-1">NET PROFIT</div>
                <h3 class="mb-0 font-weight-bold {{ $grandProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    ৳{{ number_format($grandProfit, 2) }}
                </h3>
                <small class="{{ $grandMargin >= 0 ? 'text-success' : 'text-danger' }}">
                    Margin: {{ $grandMargin }}%
                </small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Monthly Breakdown --}}
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e,#283593);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-table mr-1"></i> Monthly Breakdown
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <tr>
                            <th style="padding:10px 12px;">Month</th>
                            <th class="text-right" style="padding:10px 12px;">Income</th>
                            <th class="text-right" style="padding:10px 12px;">Expense</th>
                            <th class="text-right" style="padding:10px 12px;">Net Profit</th>
                            <th class="text-center" style="padding:10px 12px;">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                        <tr>
                            <td style="padding:10px 12px;" class="font-weight-bold">{{ $row['month_label'] }}</td>
                            <td class="text-right text-success font-weight-bold" style="padding:10px 12px;">
                                ৳{{ number_format($row['total_income']) }}
                            </td>
                            <td class="text-right text-danger" style="padding:10px 12px;">
                                ৳{{ number_format($row['total_expense']) }}
                            </td>
                            <td class="text-right font-weight-bold {{ $row['net_profit'] >= 0 ? 'text-primary' : 'text-danger' }}" style="padding:10px 12px;">
                                ৳{{ number_format($row['net_profit']) }}
                            </td>
                            <td class="text-center" style="padding:10px 12px;">
                                <span class="badge badge-{{ $row['margin'] >= 50 ? 'success' : ($row['margin'] >= 20 ? 'warning' : 'danger') }}">
                                    {{ $row['margin'] }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No data found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($rows) > 1)
                    <tfoot style="background:#f8f9fa; border-top:2px solid #dee2e6;">
                        <tr>
                            <td class="font-weight-bold" style="padding:10px 12px;">Total</td>
                            <td class="text-right font-weight-bold text-success" style="padding:10px 12px;">৳{{ number_format($grandIncome) }}</td>
                            <td class="text-right font-weight-bold text-danger" style="padding:10px 12px;">৳{{ number_format($grandExpense) }}</td>
                            <td class="text-right font-weight-bold text-primary" style="padding:10px 12px;">৳{{ number_format($grandProfit) }}</td>
                            <td class="text-center" style="padding:10px 12px;">
                                <span class="badge badge-{{ $grandMargin >= 50 ? 'success' : ($grandMargin >= 20 ? 'warning' : 'danger') }}">{{ $grandMargin }}%</span>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e,#283593);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-chart-bar mr-1"></i> Income vs Expense
                </h6>
            </div>
            <div class="card-body">
                <canvas id="plChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>


@endsection

@section('js')
@parent
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var chartData = @json($chartData);
var ctx = document.getElementById('plChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(r => r.month),
        datasets: [
            {
                label: 'Income',
                data: chartData.map(r => r.income),
                backgroundColor: 'rgba(46,125,50,0.7)',
                borderColor: '#2e7d32',
                borderWidth: 1,
            },
            {
                label: 'Expense',
                data: chartData.map(r => r.expense),
                backgroundColor: 'rgba(198,40,40,0.7)',
                borderColor: '#c62828',
                borderWidth: 1,
            },
            {
                label: 'Net Profit',
                data: chartData.map(r => r.profit),
                type: 'line',
                fill: false,
                borderColor: '#1a237e',
                borderWidth: 2,
                pointBackgroundColor: '#1a237e',
                tension: 0.3,
            },
        ],
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: function(c) {
                        return c.dataset.label + ': ৳' + Number(c.parsed.y).toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(v) { return '৳' + Number(v).toLocaleString(); }
                }
            }
        }
    }
});
</script>
@stop