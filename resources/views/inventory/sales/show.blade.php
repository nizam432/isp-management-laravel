@extends('adminlte::page')
@section('title', 'Sale Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-file-invoice mr-2 text-primary"></i>{{ $sale->invoice_no }}
            </h4>
            <small class="text-muted">Sale details &amp; payment history</small>
        </div>
        <div>
            @if($sale->isDraft())
            <form action="{{ route('inventory.sales.confirm', $sale) }}" method="POST"
                  class="d-inline" onsubmit="return confirm('Confirm this sale? Stock will be deducted.')">
                @csrf
                <button class="btn btn-success btn-sm px-3 mr-1">
                    <i class="fas fa-check mr-1"></i> Confirm
                </button>
            </form>
            <form action="{{ route('inventory.sales.cancel', $sale) }}" method="POST"
                  class="d-inline" onsubmit="return confirm('Cancel this sale?')">
                @csrf
                <button class="btn btn-danger btn-sm px-3 mr-1">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
            </form>
            @endif
            @if($sale->isConfirmed())
            <a href="{{ route('inventory.sale-returns.create', ['sale_id' => $sale->id]) }}"
               class="btn btn-warning btn-sm px-3 mr-1">
                <i class="fas fa-undo mr-1"></i> Return
            </a>
            @endif
            <a href="{{ route('inventory.sales.index') }}" class="btn btn-secondary btn-sm px-3">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="row">
    <div class="col-md-4">

        {{-- Sale Info --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-info-circle mr-1"></i> Sale Info
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr><td class="small text-muted pl-3" style="width:40%">Customer</td><td class="pr-3 font-weight-bold">{{ $sale->customer_name }}</td></tr>
                        <tr><td class="small text-muted pl-3">Location</td><td class="pr-3">{{ $sale->location->name }}</td></tr>
                        <tr><td class="small text-muted pl-3">Date</td><td class="pr-3">{{ $sale->sale_date->format('d M Y') }}</td></tr>
                        <tr><td class="small text-muted pl-3">Sale Type</td><td class="pr-3"><span class="badge badge-light border">{{ ucfirst($sale->sale_type) }}</span></td></tr>
                        <tr>
                            <td class="small text-muted pl-3">Status</td>
                            <td class="pr-3">
                                <span class="badge badge-{{ $sale->status == 'confirmed' ? 'success' : ($sale->status == 'draft' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr><td class="small text-muted pl-3">Subtotal</td><td class="pr-3">৳{{ number_format($sale->subtotal, 2) }}</td></tr>
                        <tr><td class="small text-muted pl-3">Discount</td><td class="pr-3 text-warning">- ৳{{ number_format($sale->discount, 2) }}</td></tr>
                        <tr style="border-top:2px solid #dee2e6;">
                            <td class="small font-weight-bold pl-3">Total</td>
                            <td class="pr-3 font-weight-bold" style="font-size:15px;">৳{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                        <tr><td class="small text-muted pl-3">Paid</td><td class="pr-3 text-success font-weight-bold">৳{{ number_format($sale->paid_amount, 2) }}</td></tr>
                        <tr>
                            <td class="small text-muted pl-3">Due</td>
                            <td class="pr-3 {{ $sale->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                ৳{{ number_format($sale->due_amount, 2) }}
                            </td>
                        </tr>
                        @if($sale->refund_due > 0)
                        <tr style="background:#fff3e0;">
                            <td class="small font-weight-bold pl-3" style="color:#e65100;">
                                <i class="fas fa-exclamation-circle mr-1"></i>Refund Due
                            </td>
                            <td class="pr-3 font-weight-bold" style="color:#e65100; font-size:14px;">
                                ৳{{ number_format($sale->refund_due, 2) }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Add Payment --}}
        @if($sale->isConfirmed() && $sale->due_amount > 0)
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#00695c 0%,#00897b 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-money-bill-wave mr-1"></i> Add Payment
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.sales.payment.store', $sale) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold small">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                            <input type="number" name="amount" class="form-control"
                                   max="{{ $sale->due_amount }}" step="0.01"
                                   placeholder="{{ number_format($sale->due_amount, 2) }}" required>
                        </div>
                        <small class="text-muted">Max: ৳{{ number_format($sale->due_amount, 2) }}</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Reference No</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="Txn / cheque no">
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-check mr-1"></i> Add Payment
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Payment History --}}
        <div class="card shadow-sm">
            <div class="card-header py-2 bg-light">
                <h6 class="m-0 font-weight-bold text-muted">
                    <i class="fas fa-history mr-1"></i> Payment History
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($sale->payments as $payment)
                        <tr style="{{ $payment->is_void ? 'opacity:.5;' : '' }}">
                            <td class="pl-3">
                                <span style="{{ $payment->is_void ? 'text-decoration:line-through;' : '' }}">
                                    {{ $payment->payment_date->format('d M Y') }}
                                </span>
                                <br><small class="text-muted">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</small>
                            </td>
                            <td class="font-weight-bold text-right" style="width:100px;">
                                ৳{{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="text-center pr-2" style="width:60px;">
                                @if(!$payment->is_void)
                                <button class="btn btn-xs btn-outline-danger"
                                        data-toggle="modal" data-target="#voidModal{{ $payment->id }}">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @else
                                <span class="badge badge-secondary">Void</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3 small">No payments yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-boxes mr-1"></i> Sale Items
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                                <th style="font-size:12px;color:#555;padding:10px 12px;">#</th>
                                <th style="font-size:12px;color:#555;padding:10px 12px;">Product</th>
                                <th style="font-size:12px;color:#555;padding:10px 12px;">Quantity</th>
                                <th class="text-right" style="font-size:12px;color:#555;padding:10px 12px;">Unit Price</th>
                                <th class="text-right" style="font-size:12px;color:#555;padding:10px 12px;">Discount</th>
                                <th class="text-right" style="font-size:12px;color:#555;padding:10px 12px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td style="padding:10px 12px;" class="text-muted small">{{ $loop->iteration }}</td>
                                <td style="padding:10px 12px;" class="font-weight-bold">{{ $item->product->name }}</td>
                                <td style="padding:10px 12px;"><span class="badge badge-light border">{{ $item->quantity }} {{ $item->product->unit }}</span></td>
                                <td style="padding:10px 12px;" class="text-right text-muted">৳{{ number_format($item->unit_price, 2) }}</td>
                                <td style="padding:10px 12px;" class="text-right text-warning">৳{{ number_format($item->discount, 2) }}</td>
                                <td style="padding:10px 12px;" class="text-right font-weight-bold">৳{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="border-top:2px solid #dee2e6; background:#f8f9fa;">
                            <tr>
                                <td colspan="5" class="text-right font-weight-bold pr-3" style="padding:10px 12px;">Grand Total</td>
                                <td class="text-right font-weight-bold" style="padding:10px 12px; font-size:15px; color:#1a237e;">
                                    ৳{{ number_format($sale->total_amount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Void Payment Modals --}}
@foreach($sale->payments as $payment)
@if(!$payment->is_void)
<div class="modal fade" id="voidModal{{ $payment->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('inventory.sales.payment.void', [$sale, $payment]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-ban mr-1 text-danger"></i> Void Payment
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Payment of <strong>৳{{ number_format($payment->amount, 2) }}</strong> will be voided.
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Void Reason <span class="text-danger">*</span></label>
                        <textarea name="void_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban mr-1"></i> Void Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

@endsection

@section('css')
<style>
    .card-header h6 { font-size: 13px; }
    .table tbody td { vertical-align: middle; }
    .btn-xs { padding: 2px 6px; font-size: 11px; }
    .input-group-text { background:#f4f6f9; border-color:#ced4da; }
</style>
@stop
