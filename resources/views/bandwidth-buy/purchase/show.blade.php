@extends('adminlte::page')
@section('title', 'Purchase — ' . $purchase->invoice_no)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-file-invoice-dollar mr-2 text-primary"></i>
                Purchase — <span class="text-primary">{{ $purchase->invoice_no }}</span>
            </h4>
            <small class="text-muted">{{ $purchase->provider->company_name ?? '—' }} | {{ optional($purchase->billing_date)->format('d M Y') }}</small>
        </div>
        <a href="{{ route('bandwidth-buy.purchase.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@endsection

@section('content')

<div class="row">
    {{-- ── Invoice Info ── --}}
    <div class="col-md-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e,#283593);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-info-circle mr-1"></i> Invoice Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted" style="width:140px;">Invoice No</td>
                                <td class="font-weight-bold text-primary">{{ $purchase->invoice_no }}</td></tr>
                            <tr><td class="text-muted">Provider</td>
                                <td>{{ $purchase->provider->company_name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Billing Date</td>
                                <td>{{ optional($purchase->billing_date)->format('d M Y') }}</td></tr>
                            <tr><td class="text-muted">Bank Account</td>
                                <td>{{ $purchase->bank_account ?? '—' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted" style="width:120px;">Sub Total</td>
                                <td class="font-weight-bold">৳ {{ number_format($purchase->sub_total, 2) }}</td></tr>
                            <tr><td class="text-muted">Total Paid</td>
                                <td class="font-weight-bold text-success">৳ {{ number_format($purchase->paid, 2) }}</td></tr>
                            <tr><td class="text-muted">Balance Due</td>
                                <td class="font-weight-bold {{ $purchase->due > 0 ? 'text-danger' : 'text-success' }}">
                                    ৳ {{ number_format($purchase->due, 2) }}
                                </td></tr>
                            <tr><td class="text-muted">Status</td>
                                <td>{!! $purchase->statusBadge !!}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Service Lines ── --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:#2e7d32;">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-list mr-1"></i> Service Lines
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th class="text-right">Qty (MB)</th>
                            <th class="text-right">Rate</th>
                            <th class="text-right">VAT %</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->lines as $i => $line)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $line->service->name ?? '—' }}</td>
                            <td>{{ optional($line->from_date)->format('d M Y') }}</td>
                            <td>{{ optional($line->to_date)->format('d M Y') }}</td>
                            <td class="text-right">{{ number_format($line->quantity_mb, 2) }}</td>
                            <td class="text-right">{{ number_format($line->rate, 2) }}</td>
                            <td class="text-right">{{ number_format($line->vat_percent, 2) }}%</td>
                            <td class="text-right font-weight-bold">৳ {{ number_format($line->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background:#f8f9fa;">
                        <tr>
                            <td colspan="7" class="text-right font-weight-bold">Grand Total</td>
                            <td class="text-right font-weight-bold text-primary">৳ {{ number_format($purchase->sub_total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Payment History ── --}}
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-history mr-1"></i> Payment History
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>Date</th>
                            <th class="text-right">Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchase->payments as $pay)
                        <tr class="{{ $pay->status === 'void' ? 'text-muted' : '' }}">
                            <td>{{ optional($pay->payment_date)->format('d M Y') }}</td>
                            <td class="text-right font-weight-bold {{ $pay->status === 'void' ? 'text-muted' : 'text-success' }}">
                                ৳ {{ number_format($pay->amount, 2) }}
                            </td>
                            <td><span class="badge badge-light border" style="font-size:10px;">{{ strtoupper($pay->payment_method) }}</span></td>
                            <td>
                                @if($pay->status === 'void')
                                    <span class="badge badge-secondary" style="font-size:10px;">Void</span>
                                @else
                                    <span class="badge badge-success" style="font-size:10px;">Active</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No payments yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($purchase->payments->count())
                    <tfoot style="background:#e8f5e9;">
                        <tr>
                            <td class="text-right font-weight-bold" colspan="1">Total Paid</td>
                            <td class="text-right font-weight-bold text-success">৳ {{ number_format($purchase->paid, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
