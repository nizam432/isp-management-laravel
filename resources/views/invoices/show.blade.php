@extends('layouts.app')

@section('title', 'Invoice: ' . $invoice->invoice_no)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Invoice: {{ $invoice->invoice_no }}</h1>
        <div>
            <a href="{{ route('invoices.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Go Back
            </a>
            <button onclick="window.print()" class="btn btn-info btn-sm ml-1">
                <i class="fas fa-print mr-1"></i> Print Receipt
            </button>
            {{-- <a href="#" class="btn btn-warning btn-sm ml-1">
                <i class="fas fa-sms mr-1"></i> Send SMS
            </a> --}}
            {{-- <a href="#" class="btn btn-secondary btn-sm ml-1">
                <i class="fas fa-envelope mr-1"></i> Send to Email
            </a> --}}
            <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-danger btn-sm ml-1">
                <i class="fas fa-file-pdf mr-1"></i> Download PDF
            </a>
        </div>
    </div>
@endsection

@section('content')

@push('css')
<style>
@media print {
    .main-header, .main-sidebar, .content-header, .main-footer, .no-print { display: none !important; }
    .content-wrapper { margin: 0 !important; padding: 0 !important; background: #fff !important; }
    .invoice-box { box-shadow: none !important; border: none !important; }
}
.invoice-accent { color: #00a65a; }
.invoice-border-top { border-top: 3px solid #00a65a; }
.invoice-border-bottom { border-bottom: 3px solid #00a65a; }
.invoice-table thead tr { border-bottom: 2px solid #00a65a; }
.invoice-table tfoot tr { border-top: 2px solid #00a65a; }
.invoice-total { color: #00a65a; font-size: 18px; font-weight: 600; }
.section-label { color: #00a65a; font-size: 11px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 8px; }
</style>
@endpush

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card invoice-box">

            {{-- Header --}}
            <div class="card-body pb-2 invoice-border-top" style="border-top: 3px solid #00a65a;">
                <div class="row align-items-start">
                    <div class="col-md-6">
                        @php
                            $companyName    = \App\Models\Setting::get('company_name', config('app.name'));
                            $companyPhone   = \App\Models\Setting::get('company_phone', '');
                            $companyEmail   = \App\Models\Setting::get('company_email', '');
                            $companyAddress = \App\Models\Setting::get('company_address', '');
                            $companyLogo    = \App\Models\Setting::get('company_logo', '');
                        @endphp
                        <div class="d-flex align-items-center mb-1">
                            @if($companyLogo)
                                <img src="{{ asset('storage/' . $companyLogo) }}" alt="Logo" style="height:40px; margin-right:10px;">
                            @endif
                            <h4 class="font-weight-500 mb-0">{{ $companyName }}</h4>
                        </div>
                        @if($companyAddress)
                        <p class="text-muted mb-0" style="font-size:13px;">{{ $companyAddress }}</p>
                        @endif
                        <p class="text-muted mb-0" style="font-size:13px;">
                            @if($companyPhone) Phone: {{ $companyPhone }} @endif
                            @if($companyEmail) | Email: {{ $companyEmail }} @endif
                        </p>
                    </div>
                    <div class="col-md-6 text-right">
                        <h2 class="invoice-accent mb-0" style="letter-spacing:2px;">INVOICE</h2>
                        <p class="text-muted mb-1" style="font-size:13px;">{{ $invoice->invoice_no }}</p>
                        @if($invoice->status === 'paid')
                            <span class="badge badge-success">Paid</span>
                        @elseif($invoice->status === 'partial')
                            <span class="badge badge-warning">Partial</span>
                        @elseif($invoice->status === 'overdue')
                            <span class="badge badge-danger">Overdue</span>
                        @else
                            <span class="badge badge-secondary">Unpaid</span>
                        @endif
                    </div>
                </div>
            </div>

            <hr class="mt-0 mb-0">

            {{-- Customer + Invoice Info --}}
            <div class="card-body py-3">
                <div class="row">
                    <div class="col-md-6 border-right">
                        <div class="section-label">Bill To</div>
                        <h6 class="font-weight-bold mb-1">{{ $invoice->customer->name }}</h6>
                        <p class="text-muted mb-0" style="font-size:13px;">{{ $invoice->customer->phone }}</p>
                        <p class="text-muted mb-0" style="font-size:13px;">Username: {{ $invoice->customer->username ?? '-' }}</p>
                        <p class="text-muted mb-0" style="font-size:13px;">Package: {{ $invoice->package->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <div class="section-label">Invoice Info</div>
                        <table class="table table-sm table-borderless mb-0" style="font-size:13px;">
                            <tr>
                                <td class="text-muted pl-0">Month:</td>
                                <td class="text-right pr-0">{{ $invoice->month }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Issue Date:</td>
                                <td class="text-right pr-0">{{ $invoice->created_at->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Due Date:</td>
                                <td class="text-right pr-0">{{ $invoice->due_date?->format('d M Y') ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <hr class="mt-0 mb-0">

            {{-- Items Table --}}
            <div class="card-body py-3">
                <table class="table table-sm invoice-table mb-0" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th class="pl-0 text-muted font-weight-500">Description</th>
                            <th class="pr-0 text-right text-muted font-weight-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="pl-0">Monthly Internet Bill — {{ $invoice->package->name ?? 'Internet Service' }}</td>
                            <td class="pr-0 text-right">BDT {{ number_format($invoice->amount, 2) }}</td>
                        </tr>
                        @if($invoice->discount > 0)
                        <tr>
                            <td class="pl-0 text-muted">Discount</td>
                            <td class="pr-0 text-right text-muted">- BDT {{ number_format($invoice->discount, 2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="pl-0 font-weight-bold">Total Due</td>
                            <td class="pr-0 text-right invoice-total">BDT {{ number_format($invoice->due_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <hr class="mt-0 mb-0">

            {{-- Payment History --}}
            <div class="card-body py-3">
                <div class="section-label">Payment History</div>
                <table class="table table-sm table-hover mb-0" style="font-size:13px;">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Transaction ID</th>
                            <th>Received By</th>
                            <th class="text-right">Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->payments as $pay)
                        <tr class="{{ $pay->isVoid() ? 'text-muted' : '' }}">
                            <td>{{ optional($pay->paid_at)->format('d M Y') ?? optional($pay->payment_date)->format('d M Y') ?? '-' }}</td>
                            <td><span class="badge badge-info">{{ strtoupper($pay->method) }}</span></td>
                            <td>{{ $pay->transaction_id ?? '-' }}</td>
                            <td>{{ $pay->receivedBy->name ?? '-' }}</td>
                            <td class="text-right">BDT {{ number_format($pay->amount, 2) }}</td>
                            <td>
                                @if($pay->isVoid())
                                    <span class="badge badge-danger">Void</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No payments yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer --}}
            <div class="card-footer d-flex justify-content-between align-items-center" style="background: var(--color-background-secondary);">
                <small class="text-muted">Thank you for your payment!</small>
                <small class="text-muted">Support: {{ $companyPhone }}</small>
            </div>

        </div>
    </div>
</div>


@endsection