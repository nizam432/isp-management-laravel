{{-- resources/views/reports/bill/daily-collection.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Daily Collection Report')
@section('page_content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label class="mr-2 mb-0">Date:</label>
            <input type="date" name="date" class="form-control form-control-sm mr-2" value="{{ $date->format('Y-m-d') }}">

            <label class="mr-2 mb-0">Method:</label>
            <select name="method" class="form-control form-control-sm mr-2">
                <option value="">All</option>
                @foreach(['cash','bkash','nagad','rocket','bank','card','advance'] as $m)
                    <option value="{{ $m }}" {{ request('method') === $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                @endforeach
            </select>

            <label class="mr-2 mb-0">Collector:</label>
            <select name="received_by" class="form-control form-control-sm mr-2">
                <option value="">All</option>
                @foreach($collectors as $collector)
                    <option value="{{ $collector->id }}" {{ request('received_by') == $collector->id ? 'selected' : '' }}>{{ $collector->name }}</option>
                @endforeach
            </select>

            <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Filter</button>
        </form>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Collected</span>
                        <span class="info-box-number">{{ number_format($total, 2) }} BDT</span>
                    </div>
                </div>
            </div>
            @foreach($byMethod as $method => $data)
            <div class="col-md-2">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-wallet"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ strtoupper($method) }}</span>
                        <span class="info-box-number">{{ number_format($data['amount'], 0) }}</span>
                        <span class="d-block small text-muted">{{ $data['count'] }} txns</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <h6>Collection by Collector</h6>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light"><tr><th>Collector</th><th>Transactions</th><th>Amount</th></tr></thead>
                    <tbody>
                        @forelse($byCollector as $name => $data)
                        <tr>
                            <td>{{ $name }}</td>
                            <td>{{ $data['count'] }}</td>
                            <td>{{ number_format($data['amount'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <h6>Transaction Details — {{ $date->format('d M Y') }}</h6>
        <table class="table table-striped table-sm">
            <thead class="thead-light">
                <tr><th>#</th><th>Customer</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Trx ID</th><th>Collector</th><th>Time</th></tr>
            </thead>
            <tbody>
                @forelse($payments as $i => $pay)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $pay->customer->name ?? '-' }}</td>
                    <td>{{ $pay->invoice->invoice_no ?? '-' }}</td>
                    <td>{{ number_format($pay->amount, 2) }}</td>
                    <td><span class="badge badge-info">{{ strtoupper($pay->method) }}</span></td>
                    <td>{{ $pay->transaction_id ?? '-' }}</td>
                    <td>{{ $pay->receivedBy->name ?? '-' }}</td>
                    <td>{{ $pay->paid_at ? $pay->paid_at->format('h:i A') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No collections found for this date.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="font-weight-bold">
                    <td colspan="3">Total</td>
                    <td>{{ number_format($total, 2) }} BDT</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
