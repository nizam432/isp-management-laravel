@extends('reseller.layouts.app')

@section('title', 'Fund History')

@section('content')

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Remaining Fund</p>
                <h4 class="font-weight-bold mb-0 text-success">{{ number_format($stats['remaining_fund'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Total Paid</p>
                <h4 class="font-weight-bold mb-0 text-primary">{{ number_format($stats['total_paid'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Total Due</p>
                <h4 class="font-weight-bold mb-0 text-danger">{{ number_format($stats['total_due'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Total Records</p>
                <h4 class="font-weight-bold mb-0">{{ $stats['total_records'] }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body">
        <form method="GET" class="row mb-3">
            <div class="col-md-3 mb-2">
                <select name="transaction_status" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    <option value="paid"    {{ request('transaction_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="due"     {{ request('transaction_status') == 'due' ? 'selected' : '' }}>Due</option>
                    <option value="partial" {{ request('transaction_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3 mb-2">
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3 mb-2">
                <button type="submit" class="btn btn-sm btn-success w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered" style="font-size:.85rem">
                <thead style="background:#f4f6f9">
                    <tr>
                        <th>Invoice Number</th>
                        <th>Fund Amount</th>
                        <th>Payment</th>
                        <th>Due Amount</th>
                        <th>Funding Date</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fundings as $f)
                    @php
                        $badgeColor = match($f->transaction_status) {
                            'paid' => 'success', 'due' => 'danger',
                            'partial' => 'warning', default => 'secondary',
                        };
                    @endphp
                    <tr>
                        <td>{{ $f->invoice_number }}</td>
                        <td>{{ number_format($f->fund_amount, 2) }}</td>
                        <td>{{ number_format($f->payment, 2) }}</td>
                        <td>{{ number_format($f->due_amount, 2) }}</td>
                        <td>{{ $f->funding_date?->format('d M Y') }}</td>
                        <td><span class="badge badge-{{ $badgeColor }}">{{ ucfirst($f->transaction_status) }}</span></td>
                        <td>{{ $f->remarks ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-wallet mr-1"></i> No funding records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $fundings->links() }}
    </div>
</div>
@stop
