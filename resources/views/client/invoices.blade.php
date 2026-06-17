{{-- resources/views/client/invoices.blade.php --}}
@extends('client.layout')
@section('title', 'Invoice List')

@section('content')

<div class="page-title">Invoice List</div>

@php
    $allowPartial = \App\Models\Setting::get('allow_partial_payment', '0') == '1';
    $unpaidInvoices = $invoices->filter(fn($i) => in_array($i->status, ['unpaid','partial','overdue']));
@endphp

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

{{-- Single Pay Now button for all dues --}}
@if($totalDue > 0)
<div style="background:#fff; border-radius:12px; border:1px solid #eef0f5; padding:16px 20px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
    <div>
        <div style="font-size:13px; color:#888; margin-bottom:3px;">Total Outstanding</div>
        <div style="font-size:24px; font-weight:700; color:#e74c3c;">Tk{{ number_format($totalDue, 0) }}</div>
        @if($allowPartial)
            <div style="font-size:11px; color:#aaa; margin-top:2px;">You can pay any amount</div>
        @else
            <div style="font-size:11px; color:#aaa; margin-top:2px;">Full amount required</div>
        @endif
    </div>
    <a href="{{ route('client.payment.select', ['invoice' => $unpaidInvoices->first()->id ?? 0, 'pay_all' => 1]) }}"
       style="background:#28a745; color:#fff; border-radius:8px; padding:12px 28px; font-weight:700; font-size:15px; text-decoration:none; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-credit-card"></i> Pay Now
    </a>
</div>
@endif

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
                        <a href="{{ route('client.invoice.pdf', $invoice) }}" target="_blank"
                           style="background:#e74c3c; color:#fff; border-radius:6px; padding:6px 12px; font-size:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
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
