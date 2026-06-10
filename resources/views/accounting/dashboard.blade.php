{{-- resources/views/accounting/dashboard.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Accounting Dashboard')
@section('page_actions')
    <a href="{{ route('expenses.profit-loss') }}" class="btn btn-info btn-sm">
        <i class="fas fa-chart-pie mr-1"></i> P&L Report
    </a>
@endsection
@section('page_content')

{{-- This Month Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-arrow-circle-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">This Month Income</span>
                <span class="info-box-number">BDT {{ number_format($thisMonthIncome) }}</span>
                <span class="info-box-text" style="font-size:11px">
                    Bill: {{ number_format($thisMonthBill) }} + Manual: {{ number_format($thisMonthManual) }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-arrow-circle-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">This Month Expense</span>
                <span class="info-box-number">BDT {{ number_format($thisMonthExpense) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box {{ $thisMonthProfit >= 0 ? 'bg-primary' : 'bg-warning' }}">
            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Net Profit</span>
                <span class="info-box-number">BDT {{ number_format($thisMonthProfit) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-percent"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Profit Margin</span>
                <span class="info-box-number">{{ $profitMargin }}%</span>
                <span class="info-box-text" style="font-size:11px">
                    {{ now()->format('F Y') }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- 6-month chart --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Last 6 Months Overview</h3>
                <div>
                    <span class="badge badge-success mr-1">Income</span>
                    <span class="badge badge-danger mr-1">Expense</span>
                    <span class="badge badge-primary">Profit</span>
                </div>
            </div>
            <div class="card-body">
                <canvas id="overviewChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- This month breakdown --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Expense Breakdown</h3>
                <span class="badge badge-light border ml-1">{{ now()->format('M Y') }}</span>
            </div>
            <div class="card-body">
                @if($expenseBreakdown->count() > 0)
                    <canvas id="expensePieChart" height="200"></canvas>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No expenses this month.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Recent Income --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-arrow-up text-success mr-1"></i> Recent Income</h3>
                <a href="{{ route('incomes.index') }}" class="btn btn-xs btn-light border">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentIncomes as $inc)
                        <tr>
                            <td>{{ $inc->income_date->format('d M') }}</td>
                            <td>
                                @if($inc->category)
                                    <span class="badge" style="{{ $inc->category->badgeStyle }}">
                                        {{ $inc->category->name }}
                                    </span>
                                @else <span class="text-muted">—</span> @endif
                            </td>
                            <td class="text-right text-success font-weight-bold">
                                BDT {{ number_format($inc->amount) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No recent income.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Expense --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-arrow-down text-danger mr-1"></i> Recent Expense</h3>
                <a href="{{ route('expenses.index') }}" class="btn btn-xs btn-light border">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentExpenses as $exp)
                        <tr>
                            <td>{{ $exp->expense_date->format('d M') }}</td>
                            <td>
                                @if($exp->category)
                                    <span class="badge" style="{{ $exp->category->badgeStyle }}">
                                        {{ $exp->category->name }}
                                    </span>
                                @else <span class="text-muted">—</span> @endif
                            </td>
                            <td class="text-right text-danger font-weight-bold">
                                BDT {{ number_format($exp->amount) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No recent expenses.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const trend = @json($trend);

// Overview bar + line chart
new Chart(document.getElementById('overviewChart'), {
    type: 'bar',
    data: {
        labels: trend.map(d => d.month),
        datasets: [
            { label: 'Income',  data: trend.map(d => d.income),  backgroundColor: 'rgba(40,167,69,0.7)',  borderRadius: 3 },
            { label: 'Expense', data: trend.map(d => d.expense), backgroundColor: 'rgba(220,53,69,0.7)',  borderRadius: 3 },
            { label: 'Profit',  data: trend.map(d => d.profit),  type: 'line', borderColor: '#007bff',
              backgroundColor: 'rgba(0,123,255,0.08)', borderWidth: 2, pointRadius: 4, fill: false, tension: 0.3 },
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index' },
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { grid: { display: false } },
            y: { ticks: { callback: v => 'BDT ' + (v >= 1000 ? Math.round(v/1000)+'k' : v) } }
        }
    }
});

@if($expenseBreakdown->count() > 0)
const pieData = @json($expenseBreakdown);
new Chart(document.getElementById('expensePieChart'), {
    type: 'doughnut',
    data: {
        labels: pieData.map(d => d.category ? d.category.name : 'Other'),
        datasets: [{
            data: pieData.map(d => d.total),
            backgroundColor: ['#534AB7','#185FA5','#0F6E56','#BA7517','#993C1D','#5F5E5A','#993556','#888780'],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'right', labels: { font: { size: 11 } } },
            tooltip: { callbacks: { label: ctx => ctx.label + ': BDT ' + Number(ctx.raw).toLocaleString() } }
        }
    }
});
@endif
</script>
@endpush

@endsection
