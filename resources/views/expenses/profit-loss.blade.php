{{-- resources/views/expenses/profit-loss.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Profit & Loss Report')
@section('page_actions')
    <a href="{{ route('expenses.profit-loss.pdf', ['month' => $month]) }}"
       class="btn btn-danger btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> PDF Export
    </a>
@endsection
@section('page_content')

{{-- Month Filter --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline">
            <label class="mr-2 font-weight-bold">Month:</label>
            <input type="month" name="month" class="form-control form-control-sm mr-2"
                   value="{{ $month }}">
            <button class="btn btn-sm btn-primary">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <span class="ml-3 text-muted">
                {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
            </span>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">মোট আয় (Income)</span>
                <span class="info-box-number">৳{{ number_format($totalIncome) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">মোট ব্যয় (Expense)</span>
                <span class="info-box-number">৳{{ number_format($totalExpense) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box {{ $netProfit >= 0 ? 'bg-primary' : 'bg-warning' }}">
            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">নেট মুনাফা (Net Profit)</span>
                <span class="info-box-number">৳{{ number_format($netProfit) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-percent"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Profit Margin</span>
                <span class="info-box-number">{{ $profitMargin }}%</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- P&L Statement --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice-dollar mr-1"></i>
                    P&L Statement — {{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">

                    {{-- Income section --}}
                    <thead class="thead-light">
                        <tr>
                            <th colspan="2" class="text-success">
                                <i class="fas fa-plus-circle mr-1"></i> আয় (Income)
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incomeByMethod as $method => $amount)
                        <tr>
                            <td class="pl-4 text-muted">{{ strtoupper($method) }} collections</td>
                            <td class="text-right text-success">৳{{ number_format($amount, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="font-weight-bold bg-light">
                            <td class="pl-2">মোট আয়</td>
                            <td class="text-right text-success">৳{{ number_format($totalIncome, 2) }}</td>
                        </tr>
                    </tbody>

                    {{-- Expense section --}}
                    <thead class="thead-light">
                        <tr>
                            <th colspan="2" class="text-danger">
                                <i class="fas fa-minus-circle mr-1"></i> ব্যয় (Expense)
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenseBreakdown as $item)
                        <tr>
                            <td class="pl-4 text-muted">
                                @if($item['category'])
                                    <span class="badge" style="{{ $item['category']->badgeStyle }}">
                                        {{ $item['category']->name }}
                                    </span>
                                @else
                                    Uncategorized
                                @endif
                                <small class="ml-1">({{ $item['count'] }} items)</small>
                            </td>
                            <td class="text-right text-danger">৳{{ number_format($item['total'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted">কোনো expense নেই।</td>
                        </tr>
                        @endforelse
                        <tr class="font-weight-bold bg-light">
                            <td class="pl-2">মোট ব্যয়</td>
                            <td class="text-right text-danger">৳{{ number_format($totalExpense, 2) }}</td>
                        </tr>
                    </tbody>

                    {{-- Net Profit --}}
                    <tfoot>
                        <tr style="border-top:2px solid #dee2e6;">
                            <td class="font-weight-bold" style="font-size:16px">নেট মুনাফা</td>
                            <td class="text-right font-weight-bold {{ $netProfit >= 0 ? 'text-primary' : 'text-warning' }}"
                                style="font-size:18px">
                                ৳{{ number_format($netProfit, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Profit Margin</td>
                            <td class="text-right text-muted">{{ $profitMargin }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- 6-month trend chart --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-1"></i> ৬ মাসের Income vs Expense
                </h3>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="250"></canvas>
            </div>
        </div>

        {{-- Expense category pie --}}
        @if($expenseBreakdown->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i> Expense Breakdown
                </h3>
            </div>
            <div class="card-body">
                <canvas id="pieChart" height="200"></canvas>
            </div>
        </div>
        @endif
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ── Trend chart (bar) ──────────────────────────────────────────
const trendData = @json($trend);

new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: trendData.map(d => d.month),
        datasets: [
            {
                label: 'Income',
                data: trendData.map(d => d.income),
                backgroundColor: 'rgba(40,167,69,0.75)',
                borderRadius: 3,
            },
            {
                label: 'Expense',
                data: trendData.map(d => d.expense),
                backgroundColor: 'rgba(220,53,69,0.75)',
                borderRadius: 3,
            },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { grid: { display: false } },
            y: {
                ticks: {
                    callback: v => '৳' + Number(v).toLocaleString()
                }
            }
        }
    }
});

// ── Pie chart ─────────────────────────────────────────────────
@if($expenseBreakdown->count() > 0)
const pieData = @json($expenseBreakdown);

new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: pieData.map(d => d.category ? d.category.name : 'Other'),
        datasets: [{
            data: pieData.map(d => d.total),
            backgroundColor: [
                '#534AB7','#185FA5','#0F6E56','#BA7517',
                '#993C1D','#5F5E5A','#993556','#888780',
            ],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'right' },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.label + ': ৳' + Number(ctx.raw).toLocaleString()
                }
            }
        }
    }
});
@endif
</script>
@endpush

@endsection
