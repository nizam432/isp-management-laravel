{{-- resources/views/client/invoices.blade.php --}}
@extends('client.layout')
@section('title', 'Invoice List')

@section('content')

<div class="page-title">Invoice List</div>

<div class="stats-row" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="stat-info">
            <div class="stat-value">Tk{{ number_format($totalDue, 0) }}</div>
            <div class="stat-label">Total Due</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-check-double"></i></div>
        <div class="stat-info">
            <div class="stat-value">Tk{{ number_format($totalPaid, 0) }}</div>
            <div class="stat-label">Total Paid</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="justify-content:space-between;">
        <span><i class="fas fa-list"></i> Invoice List</span>
        <form method="GET" style="display:flex; gap:8px; align-items:center;">
            <select name="status" class="form-control" style="width:auto; padding:5px 10px; font-size:12px;">
                <option value="">All</option>
                <option value="unpaid"  {{ request('status')=='unpaid'  ? 'selected':'' }}>Unpaid</option>
                <option value="paid"    {{ request('status')=='paid'    ? 'selected':'' }}>Paid</option>
                <option value="overdue" {{ request('status')=='overdue' ? 'selected':'' }}>Overdue</option>
                <option value="partial" {{ request('status')=='partial' ? 'selected':'' }}>Partial</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if(request('status'))
                <a href="{{ route('client.invoices') }}" class="btn btn-outline btn-sm">Clear</a>
            @endif
        </form>
    </div>

    @if($invoices->isEmpty())
        <div class="card-body" style="text-align:center; color:#aaa; padding:2.5rem;">
            <i class="fas fa-inbox" style="font-size:2.5rem; display:block; margin-bottom:.75rem;"></i>
            No invoices found.
        </div>
    @else
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice No</th>
                    <th>Period</th>
                    <th>Amount</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $i => $invoice)
                @php
                    $statusMap = [
                        'paid'    => ['badge-success', 'Paid'],
                        'partial' => ['badge-warning', 'Partial'],
                        'overdue' => ['badge-danger',  'Overdue'],
                        'unpaid'  => ['badge-danger',  'Unpaid'],
                    ];
                    [$badgeClass, $statusText] = $statusMap[$invoice->status] ?? ['badge-secondary', ucfirst($invoice->status)];
                @endphp
                <tr>
                    <td>{{ $invoices->firstItem() + $i }}</td>
                    <td><small style="font-family:monospace; font-weight:600;">{{ $invoice->invoice_no }}</small></td>
                    <td><small>{{ $invoice->period_label }}</small></td>
                    <td>Tk{{ number_format($invoice->amount, 0) }}</td>
                    <td style="{{ $invoice->due_amount > 0 ? 'color:#e74c3c; font-weight:600;' : 'color:#00c897;' }}">
                        Tk{{ number_format($invoice->due_amount, 0) }}
                    </td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statusText }}</span></td>
                    <td>
                        <small class="{{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'expire-urgent' : '' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                        </small>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;">
                            @include('client.payment._pay-button', ['invoice' => $invoice])
                            <a href="{{ route('client.invoices.show', $invoice) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $invoices->links() }}</div>
    @endif
</div>

@endsection
