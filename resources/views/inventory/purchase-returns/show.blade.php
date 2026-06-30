@extends('adminlte::page')
@section('title', 'Purchase Return — ' . $return->return_no)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-undo mr-2 text-primary"></i>{{ $return->return_no }}
            </h4>
            <small class="text-muted">Purchase return details</small>
        </div>
        <a href="{{ route('inventory.purchase-returns.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@endsection

@section('content')
@include('inventory._partials.alerts')

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-info-circle mr-1"></i> Return Info</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><td class="small text-muted pl-3" style="width:45%">Purchase No</td>
                        <td class="pr-3"><a href="{{ route('inventory.purchases.show', $return->purchase_id) }}" class="font-weight-bold">{{ $return->purchase->purchase_no ?? '—' }}</a></td></tr>
                    <tr><td class="small text-muted pl-3">Vendor</td>
                        <td class="pr-3">{{ $return->vendor->name ?? '—' }}</td></tr>
                    <tr><td class="small text-muted pl-3">Return Date</td>
                        <td class="pr-3">{{ $return->return_date->format('d M Y') }}</td></tr>
                    <tr><td class="small text-muted pl-3">Location</td>
                        <td class="pr-3">{{ $return->location->name ?? '—' }}</td></tr>
                    <tr><td class="small text-muted pl-3">Status</td>
                        <td class="pr-3">
                            <span class="badge badge-{{ $return->status === 'approved' ? 'success' : ($return->status === 'cancelled' ? 'secondary' : 'warning') }}">
                                {{ ucfirst($return->status) }}
                            </span>
                        </td></tr>
                    <tr style="border-top:2px solid #dee2e6;">
                        <td class="small font-weight-bold pl-3">Total Return</td>
                        <td class="pr-3 font-weight-bold text-danger" style="font-size:15px;">
                            ৳{{ number_format($return->total_amount, 2) }}
                        </td>
                    </tr>
                    @if($return->reason)
                    <tr><td class="small text-muted pl-3" colspan="2" style="padding-top:8px;">
                        <strong>Reason:</strong> {{ $return->reason }}
                    </td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#c62828 0%,#e53935 100%);">
                <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-boxes mr-1"></i> Returned Items</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                            <th style="padding:10px 12px;font-size:12px;color:#555;">#</th>
                            <th style="padding:10px 12px;font-size:12px;color:#555;">Product</th>
                            <th style="padding:10px 12px;font-size:12px;color:#555;">Quantity</th>
                            <th class="text-right" style="padding:10px 12px;font-size:12px;color:#555;">Unit Price</th>
                            <th class="text-right" style="padding:10px 12px;font-size:12px;color:#555;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->items as $i => $item)
                        <tr>
                            <td style="padding:10px 12px;" class="text-muted small">{{ $i + 1 }}</td>
                            <td style="padding:10px 12px;" class="font-weight-bold">{{ $item->product->name ?? '—' }}</td>
                            <td style="padding:10px 12px;"><span class="badge badge-light border">{{ $item->quantity }} {{ $item->product->unit ?? '' }}</span></td>
                            <td style="padding:10px 12px;" class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                            <td style="padding:10px 12px;" class="text-right font-weight-bold">৳{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="border-top:2px solid #dee2e6; background:#f8f9fa;">
                        <tr>
                            <td colspan="4" class="text-right font-weight-bold pr-3" style="padding:10px 12px;">Total Refund</td>
                            <td class="text-right font-weight-bold text-danger" style="padding:10px 12px; font-size:15px;">
                                ৳{{ number_format($return->total_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-body py-2 small text-muted">
                <i class="fas fa-info-circle mr-1"></i>
                Created by {{ $return->createdBy->name ?? '—' }} on {{ $return->created_at->format('d M Y, h:i A') }}
                @if($return->approvedBy)
                | Approved by {{ $return->approvedBy->name }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
