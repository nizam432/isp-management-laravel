@extends('reseller.layouts.app')

@section('title', 'Billing')

@section('content')

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Total Invoices</p>
                <h4 class="font-weight-bold mb-0">{{ $stats['total_invoices'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Paid</p>
                <h4 class="font-weight-bold mb-0 text-success">{{ $stats['paid'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Unpaid</p>
                <h4 class="font-weight-bold mb-0 text-danger">{{ $stats['unpaid'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Total Due</p>
                <h4 class="font-weight-bold mb-0 text-warning">{{ number_format($stats['total_due'], 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body">
        <form method="GET" class="row mb-3">
            <div class="col-md-3 mb-2">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search invoice, name, code..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-2 mb-2">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    <option value="paid"   {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2 mb-2">
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-sm btn-success w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered" style="font-size:.85rem">
                <thead style="background:#f4f6f9">
                    <tr>
                        <th>Invoice #</th>
                        <th>Client</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    @php
                        $total = $inv->total ?? $inv->amount ?? 0;
                        $paid  = $inv->paid_amount ?? 0;
                        $due   = $total - $paid;
                        $badgeColor = match($inv->status) {
                            'paid' => 'success',
                            'unpaid' => 'danger',
                            'partial' => 'warning',
                            default => 'secondary',
                        };
                    @endphp
                    <tr>
                        <td>{{ $inv->invoice_number ?? ('INV-' . $inv->id) }}</td>
                        <td>{{ $inv->customer?->name ?? '—' }}</td>
                        <td>{{ number_format($total, 2) }}</td>
                        <td>{{ number_format($paid, 2) }}</td>
                        <td>{{ number_format($due, 2) }}</td>
                        <td><span class="badge badge-{{ $badgeColor }}">{{ ucfirst($inv->status ?? 'unpaid') }}</span></td>
                        <td>{{ $inv->created_at?->format('d M Y') }}</td>
                        <td class="text-center">
                            <a href="{{ route('reseller.billing.show', $inv->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-file-invoice mr-1"></i> No invoices found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $invoices->links() }}
    </div>
</div>
@stop
