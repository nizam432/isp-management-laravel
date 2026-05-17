{{-- resources/views/payments/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Payments')
@section('page_content')
<div class="row">
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($todayTotal) }}</h3><p>Today's Collection</p></div>
            <div class="icon"><i class="fas fa-money-bill"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($monthTotal) }}</h3><p>This Month</p></div>
            <div class="icon"><i class="fas fa-chart-line"></i></div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline flex-wrap gap-2">
            <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Customer name / phone" value="{{ request('search') }}">
            <select name="method" class="form-control form-control-sm mr-2">
                <option value="">All Methods</option>
                @foreach(['cash','bkash','nagad','rocket','card','bank'] as $m)
                    <option value="{{ $m }}" {{ request('method') == $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                @endforeach
            </select>
            <input type="date" name="date" class="form-control form-control-sm mr-2" value="{{ request('date') }}">
            <button class="btn btn-sm btn-default mr-1"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('payments.index') }}" class="btn btn-sm btn-secondary">Reset</a>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead class="thead-light">
                <tr><th>Customer</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Trx ID</th><th>Received By</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($payments as $pay)
                <tr>
                    <td>{{ $pay->customer->name }}</td>
                    <td><code>{{ $pay->invoice->invoice_no }}</code></td>
                    <td><strong>{{ number_format($pay->amount) }}</strong></td>
                    <td><span class="badge badge-info">{{ strtoupper($pay->method) }}</span></td>
                    <td>{{ $pay->transaction_id ?? '-' }}</td>
                    <td>{{ $pay->receivedBy->name ?? '-' }}</td>
                    <td>{{ $pay->paid_at->format('d M Y') }}</td>
                    <td>
                        <form action="{{ route('payments.destroy', $pay) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this payment?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $payments->withQueryString()->links() }}</div>
</div>
@endsection
