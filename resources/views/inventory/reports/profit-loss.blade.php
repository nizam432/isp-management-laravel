@extends('adminlte::page')
@section('title', 'Profit & Loss')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-chart-pie mr-2 text-primary"></i>Profit &amp; Loss Report
            </h4>
        </div>
        <button onclick="window.print()" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
@endsection

@section('content')

<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="fas fa-sync mr-1"></i> Generate
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:#00a65a;">
                <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-arrow-up mr-1"></i> Revenue</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="pl-3 text-muted small">Sale Revenue</td>
                            <td class="text-right pr-3 font-weight-bold">৳{{ number_format($totalRevenue, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot style="background:#f8f9fa; border-top:2px solid #dee2e6;">
                        <tr>
                            <td class="pl-3 font-weight-bold">Total Revenue</td>
                            <td class="text-right pr-3 font-weight-bold text-success" style="font-size:15px;">৳{{ number_format($totalRevenue, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:#dd4b39;">
                <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-arrow-down mr-1"></i> Expenses</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="pl-3 text-muted small">Cost of Goods Sold</td>
                            <td class="text-right pr-3">৳{{ number_format($totalCogs, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="pl-3 text-muted small">Consumption Expense</td>
                            <td class="text-right pr-3">৳{{ number_format($consumptionExpense, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot style="background:#f8f9fa; border-top:2px solid #dee2e6;">
                        <tr>
                            <td class="pl-3 font-weight-bold">Total Expenses</td>
                            <td class="text-right pr-3 font-weight-bold text-danger" style="font-size:15px;">৳{{ number_format($totalCogs + $consumptionExpense, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-3" style="border-left:4px solid {{ $netProfit >= 0 ? '#00a65a' : '#dd4b39' }};">
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing:.5px;">Gross Profit</div>
                <div class="font-weight-bold mt-1" style="font-size:22px;">৳{{ number_format($grossProfit, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing:.5px;">Consumption Expense</div>
                <div class="text-danger font-weight-bold mt-1" style="font-size:22px;">- ৳{{ number_format($consumptionExpense, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing:.5px;">Net Profit</div>
                <div class="font-weight-bold mt-1 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:28px;">
                    ৳{{ number_format($netProfit, 2) }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
