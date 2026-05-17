{{-- resources/views/invoices/show.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Invoice: ' . $invoice->invoice_no)
@section('page_actions')
    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-secondary btn-sm"><i class="fas fa-file-pdf"></i> Download PDF</a>
    <a href="{{ route('invoices.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
@endsection
@section('page_content')
<div class="row">
    <div class="col-md-7">
        {{-- Invoice Details --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Invoice Details</h3></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th width="40%">Invoice No</th><td><code>{{ $invoice->invoice_no }}</code></td></tr>
                    <tr><th>Customer</th><td><a href="{{ route('customers.show', $invoice->customer) }}">{{ $invoice->customer->name }}</a></td></tr>
                    <tr><th>Phone</th><td>{{ $invoice->customer->phone }}</td></tr>
                    <tr><th>Package</th><td>{{ $invoice->package->name ?? '-' }}</td></tr>
                    <tr><th>Month</th><td>{{ $invoice->month }}</td></tr>
                    <tr><th>Amount</th><td>{{ number_format($invoice->amount) }} BDT</td></tr>
                    <tr><th>Discount</th><td>{{ number_format($invoice->discount) }} BDT</td></tr>
                    <tr><th>Due Amount</th><td class="text-danger font-weight-bold">{{ number_format($invoice->due_amount) }} BDT</td></tr>
                    <tr><th>Due Date</th><td>{{ $invoice->due_date?->format('d M Y') ?? '-' }}</td></tr>
                    <tr><th>Status</th><td>
                        <span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td></tr>
                </table>
            </div>
        </div>

        {{-- Payment History --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Payment History</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Amount</th><th>Method</th><th>Trx ID</th><th>Received By</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                        @forelse($invoice->payments as $pay)
                        <tr>
                            <td>{{ number_format($pay->amount) }}</td>
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
                        <tr><td colspan="6" class="text-center text-muted">No payments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Receive Payment Form --}}
    @if($invoice->status !== 'paid')
    <div class="col-md-5">
        <div class="card card-success">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-money-bill mr-1"></i> Receive Payment</h3></div>
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                <div class="card-body">
                    <div class="form-group">
                        <label>Amount (BDT) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" value="{{ $invoice->due_amount }}" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method <span class="text-danger">*</span></label>
                        <select name="method" class="form-control" required>
                            @foreach(['cash','bkash','nagad','rocket','card','bank'] as $m)
                                <option value="{{ $m }}">{{ strtoupper($m) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Transaction ID</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="bKash / Nagad TrxID">
                    </div>
                    <div class="form-group">
                        <label>Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="paid_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <input type="text" name="remarks" class="form-control">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-block"><i class="fas fa-check"></i> Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
