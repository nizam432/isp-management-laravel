{{-- resources/views/accounting/profit-loss.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Profit & Loss Report')
@section('page_actions')
    <a href="{{ route('expenses.profit-loss.pdf', ['from_date' => $from, 'to_date' => $to]) }}"
       class="btn btn-danger btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> PDF Export
    </a>
@endsection
@section('page_content')

{{-- Month Range Filter --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline flex-wrap">
            <label class="mr-2 font-weight-bold">From:</label>
            <input type="date" name="from_date" class="form-control form-control-sm mr-3"
                   value="{{ $from }}">
            <label class="mr-2 font-weight-bold">To:</label>
            <input type="date" name="to_date" class="form-control form-control-sm mr-3"
                   value="{{ $to }}">
            <button class="btn btn-sm btn-primary mr-2">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="{{ route('expenses.profit-loss') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-redo mr-1"></i> Reset
            </a>
            <span class="ml-3 text-muted small">
                {{ \Carbon\Carbon::parse($from)->format('d M Y') }}
                — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                ({{ count($rows) }} month{{ count($rows) > 1 ? 's' : '' }})
            </span>
        </form>
    </div>
</div>

{{-- Grand Total Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Income</span>
                <span class="info-box-number">BDT {{ number_format($grandIncome) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Expense</span>
                <span class="info-box-number">BDT {{ number_format($grandExpense) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box {{ $grandProfit >= 0 ? 'bg-primary' : 'bg-warning' }}">
            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Net Profit</span>
                <span class="info-box-number">BDT {{ number_format($grandProfit) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-percent"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Profit Margin</span>
                <span class="info-box-number">{{ $grandMargin }}%</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- P&L Table --}}
    <div class="{{ count($rows) > 1 ? 'col-md-7' : 'col-md-6' }}">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table mr-1"></i> Monthly Breakdown
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Month</th>
                            <th class="text-right">Income</th>
                            <th class="text-right">Expense</th>
                            <th class="text-right">Net Profit</th>
                            <th class="text-right">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                        <tr>
                            <td class="font-weight-bold">{{ $row['month_label'] }}</td>
                            <td class="text-right text-success">
                                BDT {{ number_format($row['total_income']) }}
                                @if($row['monthly_bill'] > 0 && $row['manual_income'] > 0)
                                    <br><small class="text-muted">
                                        Bill: {{ number_format($row['monthly_bill']) }}
                                        + Manual: {{ number_format($row['manual_income']) }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-right text-danger">
                                BDT {{ number_format($row['total_expense']) }}
                            </td>
                            <td class="text-right font-weight-bold {{ $row['net_profit'] >= 0 ? 'text-primary' : 'text-warning' }}">
                                BDT {{ number_format($row['net_profit']) }}
                            </td>
                            <td class="text-right">
                                <span class="badge {{ $row['margin'] >= 50 ? 'badge-success' : ($row['margin'] >= 20 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $row['margin'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if(count($rows) > 1)
                    <tfoot>
                        <tr class="font-weight-bold" style="border-top:2px solid #dee2e6; background:#f8f9fa;">
                            <td>Grand Total</td>
                            <td class="text-right text-success">BDT {{ number_format($grandIncome) }}</td>
                            <td class="text-right text-danger">BDT {{ number_format($grandExpense) }}</td>
                            <td class="text-right {{ $grandProfit >= 0 ? 'text-primary' : 'text-warning' }}">
                                BDT {{ number_format($grandProfit) }}
                            </td>
                            <td class="text-right">
                                <span class="badge {{ $grandMargin >= 50 ? 'badge-success' : ($grandMargin >= 20 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $grandMargin }}%
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="{{ count($rows) > 1 ? 'col-md-5' : 'col-md-6' }}">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-1"></i> Income vs Expense
                </h3>
            </div>
            <div class="card-body">
                <canvas id="plChart" height="{{ count($rows) > 3 ? '280' : '220' }}"></canvas>
            </div>
        </div>

        {{-- Single month detail --}}
        @if(count($rows) === 1)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    {{ $rows[0]['month_label'] }} — P&L Statement
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th colspan="2" class="text-success"><i class="fas fa-plus-circle mr-1"></i> Income</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="pl-4 text-muted">Monthly Bill (Payments)</td>
                            <td class="text-right text-success">BDT {{ number_format($rows[0]['monthly_bill'], 2) }}</td>
                        </tr>
                        @if($rows[0]['manual_income'] > 0)
                        <tr>
                            <td class="pl-4 text-muted">Other Income (Manual)</td>
                            <td class="text-right text-success">BDT {{ number_format($rows[0]['manual_income'], 2) }}</td>
                        </tr>
                        @endif
                        <tr class="font-weight-bold bg-light">
                            <td>Total Income</td>
                            <td class="text-right text-success">BDT {{ number_format($rows[0]['total_income'], 2) }}</td>
                        </tr>
                    </tbody>
                    <thead class="thead-light">
                        <tr><th colspan="2" class="text-danger"><i class="fas fa-minus-circle mr-1"></i> Expense</th></tr>
                    </thead>
                    <tbody>
                        @php
                            [$y, $mo] = explode('-', $rows[0]['month']);
                            $breakdown = \App\Models\Expense::breakdownForMonth($rows[0]['month']);
                        @endphp
                        @foreach($breakdown as $item)
                        <tr>
                            <td class="pl-4 text-muted">
                                @if($item['category'])
                                    <span class="badge" style="{{ $item['category']->badgeStyle }}">{{ $item['category']->name }}</span>
                                @else Uncategorized @endif
                            </td>
                            <td class="text-right text-danger">BDT {{ number_format($item['total'], 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="font-weight-bold bg-light">
                            <td>Total Expense</td>
                            <td class="text-right text-danger">BDT {{ number_format($rows[0]['total_expense'], 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid #dee2e6;">
                            <td class="font-weight-bold" style="font-size:15px">Net Profit</td>
                            <td class="text-right font-weight-bold {{ $rows[0]['net_profit'] >= 0 ? 'text-primary' : 'text-warning' }}"
                                style="font-size:17px">
                                BDT {{ number_format($rows[0]['net_profit'], 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Profit Margin</td>
                            <td class="text-right text-muted">{{ $rows[0]['margin'] }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartData = @json($chartData);

new Chart(document.getElementById('plChart'), {
    type: 'bar',
    data: {
        labels: chartData.map(d => d.month),
        datasets: [
            {
                label: 'Income',
                data: chartData.map(d => d.income),
                backgroundColor: 'rgba(40,167,69,0.75)',
                borderRadius: 3,
            },
            {
                label: 'Expense',
                data: chartData.map(d => d.expense),
                backgroundColor: 'rgba(220,53,69,0.75)',
                borderRadius: 3,
            },
            {
                label: 'Net Profit',
                data: chartData.map(d => d.profit),
                type: 'line',
                borderColor: '#007bff',
                backgroundColor: 'rgba(0,123,255,0.1)',
                borderWidth: 2,
                pointRadius: 4,
                fill: false,
                tension: 0.3,
            },
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index' },
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.dataset.label + ': BDT ' + Number(ctx.raw).toLocaleString()
                }
            }
        },
        scales: {
            x: { grid: { display: false } },
            y: {
                ticks: {
                    callback: v => 'BDT ' + (v >= 1000 ? Math.round(v/1000) + 'k' : v)
                }
            }
        }
    }
});
</script>
@endpush

@endsection
