@extends('adminlte::page')
@section('title', 'Profit & Loss')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h4 class="mb-0 font-weight-bold"><i class="fas fa-chart-pie mr-2"></i>Profit & Loss Report</h4>
    <a href="{{ route('expenses.profit-loss.pdf', request()->query()) }}" class="btn btn-danger btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> PDF Export
    </a>
</div>
@endsection

@section('content')

{{-- Filter --}}
<div class="card mb-3 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" class="form-inline flex-wrap gap-2">
            <label class="mr-1 font-weight-bold small">From:</label>
            <input type="date" name="from_date" class="form-control form-control-sm mr-2" value="{{ $from }}">
            <label class="mr-1 font-weight-bold small">To:</label>
            <input type="date" name="to_date" class="form-control form-control-sm mr-2" value="{{ $to }}">
            <button type="submit" class="btn btn-primary btn-sm mr-1">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="{{ route('expenses.profit-loss') }}" class="btn btn-secondary btn-sm mr-3">
                <i class="fas fa-redo mr-1"></i> Reset
            </a>
            <span class="text-muted small">
                {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                ({{ \Carbon\Carbon::parse($from)->diffInMonths(\Carbon\Carbon::parse($to)) + 1 }} month)
            </span>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="info-box bg-success shadow-sm mb-3">
            <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Income</span>
                <span class="info-box-number">BDT {{ number_format($grandIncome) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="info-box bg-danger shadow-sm mb-3">
            <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Expense</span>
                <span class="info-box-number">BDT {{ number_format($grandExpense) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="info-box bg-primary shadow-sm mb-3">
            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Net Profit</span>
                <span class="info-box-number">BDT {{ number_format($grandProfit) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="info-box bg-info shadow-sm mb-3">
            <span class="info-box-icon"><i class="fas fa-percent"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Profit Margin</span>
                <span class="info-box-number">{{ $grandMargin }}%</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Monthly Breakdown --}}
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-table mr-1"></i> Monthly Breakdown</h6>
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
                                BDT {{ number_format($row['total_income']) }}
                            </td>
                            <td class="text-right text-danger" style="padding:10px 12px;">
                                BDT {{ number_format($row['total_expense']) }} 
                            </td>
                            <td class="text-right font-weight-bold {{ $row['net_profit'] >= 0 ? 'text-primary' : 'text-danger' }}" style="padding:10px 12px;">
                                BDT {{ number_format($row['net_profit']) }}
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
                            <td class="text-right font-weight-bold text-success" style="padding:10px 12px;">BDT {{ number_format($grandIncome) }}</td>
                            <td class="text-right font-weight-bold text-danger" style="padding:10px 12px;">BDT {{ number_format($grandExpense) }}</td>
                            <td class="text-right font-weight-bold text-primary" style="padding:10px 12px;">BDT {{ number_format($grandProfit) }}</td>
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
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Income vs Expense</h6>
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
                backgroundColor: 'rgba(40,167,69,0.7)',
                borderColor: '#28a745',
                borderWidth: 1,
            },
            {
                label: 'Expense',
                data: chartData.map(r => r.expense),
                backgroundColor: 'rgba(220,53,69,0.7)',
                borderColor: '#dc3545',
                borderWidth: 1,
            },
            {
                label: 'Net Profit',
                data: chartData.map(r => r.profit),
                type: 'line',
                fill: false,
                borderColor: '#007bff',
                borderWidth: 2,
                pointBackgroundColor: '#007bff',
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
                    label: function(ctx) {
                        return ctx.dataset.label + ': BDT ' + Number(ctx.parsed.y).toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(v) {
                        return 'BDT ' + Number(v).toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@stop