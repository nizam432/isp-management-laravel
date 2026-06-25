@extends('layouts.app')
@section('title', 'Sale Details')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ $sale->invoice_no }}</h4>
        <div class="d-flex gap-2">
            @if($sale->isDraft())
            <form action="{{ route('inventory.sales.confirm', $sale) }}" method="POST" onsubmit="return confirm('Confirm this sale? Stock will be deducted.')">
                @csrf <button class="btn btn-success btn-sm">✔ Confirm</button>
            </form>
            <form action="{{ route('inventory.sales.cancel', $sale) }}" method="POST" onsubmit="return confirm('Cancel this sale?')">
                @csrf <button class="btn btn-danger btn-sm">✕ Cancel</button>
            </form>
            @endif
            @if($sale->isConfirmed())
            <a href="{{ route('inventory.sale-returns.create', ['sale_id' => $sale->id]) }}" class="btn btn-warning btn-sm">Return</a>
            @endif
            <a href="{{ route('inventory.sales.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
        </div>
    </div>
    @include('inventory._partials.alerts')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Sale Info</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Customer</td><td>{{ $sale->customer_name }}</td></tr>
                        <tr><td class="text-muted">Location</td><td>{{ $sale->location->name }}</td></tr>
                        <tr><td class="text-muted">Date</td><td>{{ $sale->sale_date->format('d M Y') }}</td></tr>
                        <tr><td class="text-muted">Sale Type</td><td>{{ ucfirst($sale->sale_type) }}</td></tr>
                        <tr><td class="text-muted">Status</td><td><span class="badge bg-{{ $sale->status == 'confirmed' ? 'success' : ($sale->status == 'draft' ? 'warning' : 'secondary') }}">{{ ucfirst($sale->status) }}</span></td></tr>
                        <tr><td class="text-muted">Subtotal</td><td>৳{{ number_format($sale->subtotal,2) }}</td></tr>
                        <tr><td class="text-muted">Discount</td><td>৳{{ number_format($sale->discount,2) }}</td></tr>
                        <tr><td class="text-muted fw-bold">Total</td><td class="fw-bold">৳{{ number_format($sale->total_amount,2) }}</td></tr>
                        <tr><td class="text-muted">Paid</td><td class="text-success">৳{{ number_format($sale->paid_amount,2) }}</td></tr>
                        <tr><td class="text-muted">Due</td><td class="{{ $sale->due_amount > 0 ? 'text-danger fw-bold' : '' }}">৳{{ number_format($sale->due_amount,2) }}</td></tr>
                    </table>
                </div>
            </div>

            @if($sale->isConfirmed() && $sale->due_amount > 0)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Add Payment</div>
                <div class="card-body">
                    <form action="{{ route('inventory.sales.payment.store', $sale) }}" method="POST">
                        @csrf
                        <div class="mb-2"><label class="form-label">Amount *</label><input type="number" name="amount" class="form-control" max="{{ $sale->due_amount }}" step="0.01" required></div>
                        <div class="mb-2"><label class="form-label">Date *</label><input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                        <div class="mb-2">
                            <label class="form-label">Method *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="mobile_banking">Mobile Banking</option>
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                            </select>
                        </div>
                        <div class="mb-2"><label class="form-label">Reference No</label><input type="text" name="reference_no" class="form-control"></div>
                        <button type="submit" class="btn btn-primary w-100">Add Payment</button>
                    </form>
                </div>
            </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Payment History</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @forelse($sale->payments as $payment)
                            <tr class="{{ $payment->is_void ? 'text-muted text-decoration-line-through' : '' }}">
                                <td>{{ $payment->payment_date->format('d M Y') }}<br><small>{{ ucfirst($payment->payment_method) }}</small></td>
                                <td>৳{{ number_format($payment->amount,2) }}</td>
                                <td>
                                    @if(!$payment->is_void)
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#voidPayModal{{ $payment->id }}">Void</button>
                                    @else <span class="badge bg-secondary">Void</span> @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-2">No payments</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Sale Items</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Product</th><th>Qty</th><th>Unit Price</th><th>Discount</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }} {{ $item->product->unit }}</td>
                                <td>৳{{ number_format($item->unit_price,2) }}</td>
                                <td>৳{{ number_format($item->discount,2) }}</td>
                                <td class="text-end">৳{{ number_format($item->total_price,2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@foreach($sale->payments as $payment)
@if(!$payment->is_void)
<div class="modal fade" id="voidPayModal{{ $payment->id }}" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form action="{{ route('inventory.sales.payment.void', [$sale, $payment]) }}" method="POST">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Void Payment ৳{{ number_format($payment->amount,2) }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="mb-3"><label class="form-label">Void Reason *</label><textarea name="void_reason" class="form-control" required></textarea></div></div>
            <div class="modal-footer"><button type="submit" class="btn btn-danger">Void Payment</button></div>
        </form>
    </div></div>
</div>
@endif
@endforeach
@endsection
