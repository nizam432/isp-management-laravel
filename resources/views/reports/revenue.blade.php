{{-- resources/views/reports/revenue.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Revenue Report')
@section('page_actions')
    <a href="{{ route('reports.export.pdf', 'revenue') }}" class="btn btn-secondary btn-sm"><i class="fas fa-file-pdf"></i> Export PDF</a>
@endsection
@section('page_content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label class="mr-2">Month:</label>
            <input type="month" name="month" class="form-control form-control-sm mr-2" value="{{ $month }}">
            <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Filter</button>
        </form>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Revenue</span>
                        <span class="info-box-number">{{ number_format($total) }} BDT</span>
                    </div>
                </div>
            </div>
            @foreach($byMethod as $method => $amount)
            <div class="col-md-2">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-wallet"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ strtoupper($method) }}</span>
                        <span class="info-box-number">{{ number_format($amount) }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <table class="table table-striped table-sm">
            <thead class="thead-light">
                <tr><th>#</th><th>Customer</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Trx ID</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse($payments as $i => $pay)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $pay->customer->name }}</td>
                    <td><code>{{ $pay->invoice->invoice_no }}</code></td>
                    <td>{{ number_format($pay->amount) }}</td>
                    <td><span class="badge badge-info">{{ strtoupper($pay->method) }}</span></td>
                    <td>{{ $pay->transaction_id ?? '-' }}</td>
                    <td>{{ $pay->paid_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">No payments for this month.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="font-weight-bold">
                    <td colspan="3">Total</td>
                    <td>{{ number_format($total) }} BDT</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
