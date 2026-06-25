@extends('layouts.app')
@section('title', 'Profit & Loss')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Profit & Loss Report</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">🖨 Print</button>
    </div>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}"></div>
                <div class="col-md-3"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}"></div>
                <div class="col-auto"><button class="btn btn-sm btn-secondary">Generate</button></div>
            </form>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white fw-semibold">Revenue</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td>Sale Revenue</td><td class="text-end fw-semibold">৳{{ number_format($totalRevenue,2) }}</td></tr>
                        <tr class="table-light fw-bold"><td>Total Revenue</td><td class="text-end">৳{{ number_format($totalRevenue,2) }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white fw-semibold">Expenses</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td>Cost of Goods Sold</td><td class="text-end">৳{{ number_format($totalCogs,2) }}</td></tr>
                        <tr><td>Consumption Expense</td><td class="text-end">৳{{ number_format($consumptionExpense,2) }}</td></tr>
                        <tr class="table-light fw-bold"><td>Total Expenses</td><td class="text-end">৳{{ number_format($totalCogs + $consumptionExpense,2) }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm border-start border-4 border-{{ $netProfit >= 0 ? 'success' : 'danger' }}">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3"><div class="text-muted small">Gross Profit</div><div class="fs-5 fw-bold">৳{{ number_format($grossProfit,2) }}</div></div>
                        <div class="col-md-3"><div class="text-muted small">Consumption Expense</div><div class="fs-5 fw-bold text-danger">- ৳{{ number_format($consumptionExpense,2) }}</div></div>
                        <div class="col-md-3"><div class="text-muted small">Net Profit</div><div class="fs-4 fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">৳{{ number_format($netProfit,2) }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
