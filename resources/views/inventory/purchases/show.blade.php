@extends('layouts.app')
@section('title', 'Purchase Details')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ $purchase->purchase_no }}</h4>
        <div class="d-flex gap-2">
            @if($purchase->isDraft())
            <form action="{{ route('inventory.purchases.receive', $purchase) }}" method="POST" onsubmit="return confirm('Receive this purchase? Stock will be updated.')">
                @csrf <button class="btn btn-success btn-sm">✔ Receive</button>
            </form>
            <form action="{{ route('inventory.purchases.cancel', $purchase) }}" method="POST" onsubmit="return confirm('Cancel this purchase?')">
                @csrf <button class="btn btn-danger btn-sm">✕ Cancel</button>
            </form>
            @endif
            <a href="{{ route('inventory.purchases.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
        </div>
    </div>
    @include('inventory._partials.alerts')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Purchase Info</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Vendor</td><td>{{ $purchase->vendor->name }}</td></tr>
                        <tr><td class="text-muted">Location</td><td>{{ $purchase->location->name }}</td></tr>
                        <tr><td class="text-muted">Date</td><td>{{ $purchase->purchase_date->format('d M Y') }}</td></tr>
                        <tr><td class="text-muted">Invoice No</td><td>{{ $purchase->invoice_no ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Status</td><td><span class="badge bg-{{ $purchase->status == 'received' ? 'success' : ($purchase->status == 'draft' ? 'warning' : 'secondary') }}">{{ ucfirst($purchase->status) }}</span></td></tr>
                        <tr><td class="text-muted">Subtotal</td><td>৳{{ number_format($purchase->subtotal,2) }}</td></tr>
                        <tr><td class="text-muted">Discount</td><td>৳{{ number_format($purchase->discount,2) }}</td></tr>
                        <tr><td class="text-muted">Tax</td><td>৳{{ number_format($purchase->tax,2) }}</td></tr>
                        <tr><td class="text-muted fw-bold">Total</td><td class="fw-bold">৳{{ number_format($purchase->total_amount,2) }}</td></tr>
                        <tr><td class="text-muted">Paid</td><td class="text-success">৳{{ number_format($purchase->paid_amount,2) }}</td></tr>
                        <tr><td class="text-muted">Due</td><td class="{{ $purchase->due_amount > 0 ? 'text-danger fw-bold' : '' }}">৳{{ number_format($purchase->due_amount,2) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Payment --}}
            @if($purchase->isReceived() && $purchase->due_amount > 0)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Add Payment</div>
                <div class="card-body">
                    <form action="{{ route('inventory.purchases.payment.store', $purchase) }}" method="POST">
                        @csrf
                        <div class="mb-2"><label class="form-label">Amount *</label>
                            <input type="number" name="amount" class="form-control" max="{{ $purchase->due_amount }}" step="0.01" required>
                        </div>
                        <div class="mb-2"><label class="form-label">Date *</label><input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                        <div class="mb-2">
                            <label class="form-label">Method *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="mobile_banking">Mobile Banking</option>
                            </select>
                        </div>
                        <div class="mb-2"><label class="form-label">Reference No</label><input type="text" name="reference_no" class="form-control"></div>
                        <button type="submit" class="btn btn-primary w-100">Add Payment</button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Payment History --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Payment History</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @forelse($purchase->payments as $payment)
                            <tr class="{{ $payment->is_void ? 'text-muted text-decoration-line-through' : '' }}">
                                <td>{{ $payment->payment_date->format('d M Y') }}<br><small>{{ ucfirst($payment->payment_method) }}</small></td>
                                <td class="fw-semibold">৳{{ number_format($payment->amount,2) }}</td>
                                <td>
                                    @if(!$payment->is_void)
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#voidPayModal{{ $payment->id }}">Void</button>
                                    @else
                                    <span class="badge bg-secondary">Void</span>
                                    @endif
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

        {{-- Items --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Purchase Items</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Product</th><th>Qty</th><th>Unit Price</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }} {{ $item->product->unit }}</td>
                                <td>৳{{ number_format($item->unit_price,2) }}</td>
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

{{-- Void Payment Modals --}}
@foreach($purchase->payments as $payment)
@if(!$payment->is_void)
<div class="modal fade" id="voidPayModal{{ $payment->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('inventory.purchases.payment.void', [$purchase, $payment]) }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Void Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p>Payment Amount: <strong>৳{{ number_format($payment->amount,2) }}</strong></p>
                    <div class="mb-3"><label class="form-label">Void Reason *</label><textarea name="void_reason" class="form-control" rows="2" required></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-danger">Void Payment</button></div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection
