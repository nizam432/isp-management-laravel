{{-- resources/views/invoices/receipt.blade.php --}}
{{-- Thermal Receipt — 58mm / 80mm --}}
@extends('layouts.app')

@section('title', 'Receipt: ' . $invoice->invoice_no)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Receipt: {{ $invoice->invoice_no }}</h1>
        <div>
            <button onclick="printReceipt('58mm')" class="btn btn-secondary btn-sm">
                <i class="fas fa-print mr-1"></i> Print 58mm
            </button>
            <button onclick="printReceipt('80mm')" class="btn btn-info btn-sm ml-1">
                <i class="fas fa-print mr-1"></i> Print 80mm
            </button>
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-default btn-sm ml-1">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')

@php
    $companyName    = \App\Models\Setting::get('company_name', config('app.name'));
    $companyPhone   = \App\Models\Setting::get('company_phone', '');
    $companyAddress = \App\Models\Setting::get('company_address', '');
    $footerText     = \App\Models\Setting::get('invoice_footer_text', 'Thank you for your payment.');
    $currency       = \App\Models\Setting::get('currency', 'BDT');
    $advancePaid    = $invoice->payments->where('method', 'advance')->where('status', 'active')->sum('amount');
    $cashPaid       = $invoice->payments->where('method', '!=', 'advance')->where('status', 'active')->sum('amount');
@endphp

{{-- Preview Box --}}
<div class="row justify-content-center">
    <div id="receiptPreview" style="width: 72mm; font-family: monospace;">

        <div id="receiptContent" style="padding: 4px;">

            {{-- Header --}}
            <div style="text-align:center; border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px;">
                <strong style="font-size:14px;">{{ $companyName }}</strong><br>
                @if($companyAddress)
                <span style="font-size:10px;">{{ $companyAddress }}</span><br>
                @endif
                @if($companyPhone)
                <span style="font-size:10px;">Phone: {{ $companyPhone }}</span><br>
                @endif
                <strong style="font-size:12px; text-transform:uppercase;">PAYMENT RECEIPT</strong>
            </div>

            {{-- Invoice Info --}}
            <table style="width:100%; font-size:11px;">
                <tr>
                    <td style="width:45%;">Invoice No</td>
                    <td>: <strong>{{ $invoice->invoice_no }}</strong></td>
                </tr>
                <tr>
                    <td>Date</td>
                    <td>: {{ now()->format('d M Y h:i A') }}</td>
                </tr>
                <tr>
                    <td>Period</td>
                    <td>: {{ $invoice->period_label }}</td>
                </tr>
            </table>

            <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>

            {{-- Customer Info --}}
            <table style="width:100%; font-size:11px;">
                <tr>
                    <td style="width:45%;">Customer</td>
                    <td>: {{ $invoice->customer->name }}</td>
                </tr>
                <tr>
                    <td>Phone</td>
                    <td>: {{ $invoice->customer->phone }}</td>
                </tr>
                @if($invoice->customer->customer_code)
                <tr>
                    <td>ID</td>
                    <td>: {{ $invoice->customer->customer_code }}</td>
                </tr>
                @endif
                <tr>
                    <td>Package</td>
                    <td>: {{ $invoice->package->name ?? '-' }}</td>
                </tr>
            </table>

            <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>

            {{-- Amount Details --}}
            <table style="width:100%; font-size:11px;">
                <tr>
                    <td style="width:55%;">Bill Amount</td>
                    <td style="text-align:right;">{{ $currency }} {{ number_format($invoice->amount, 2) }}</td>
                </tr>
                @if($invoice->discount > 0)
                <tr>
                    <td>Discount</td>
                    <td style="text-align:right;">- {{ $currency }} {{ number_format($invoice->discount, 2) }}</td>
                </tr>
                @endif
                @if($advancePaid > 0)
                <tr>
                    <td>Advance Paid</td>
                    <td style="text-align:right;">- {{ $currency }} {{ number_format($advancePaid, 2) }}</td>
                </tr>
                @endif
                @if($cashPaid > 0)
                <tr>
                    <td>Cash Paid</td>
                    <td style="text-align:right;">{{ $currency }} {{ number_format($cashPaid, 2) }}</td>
                </tr>
                @endif
            </table>

            <div style="border-top: 2px solid #000; margin: 5px 0;"></div>

            {{-- Total --}}
            <table style="width:100%; font-size:12px;">
                <tr>
                    <td><strong>Total Due</strong></td>
                    <td style="text-align:right;"><strong>{{ $currency }} {{ number_format($invoice->due_amount, 2) }}</strong></td>
                </tr>
                <tr>
                    <td>
                        <span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'partial' ? 'warning' : 'danger') }}">
                            {{ strtoupper($invoice->status) }}
                        </span>
                    </td>
                </tr>
            </table>

            @if($invoice->payments->count() > 0)
            <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>

            {{-- Payment History --}}
            <div style="font-size:10px; font-weight:bold; text-align:center; margin-bottom:3px;">PAYMENT HISTORY</div>
            @foreach($invoice->payments->where('status', 'active') as $pay)
            <table style="width:100%; font-size:10px;">
                <tr>
                    <td>{{ optional($pay->paid_at)->format('d M Y') ?? '-' }}</td>
                    <td style="text-align:center;">{{ strtoupper($pay->method) }}</td>
                    <td style="text-align:right;">{{ $currency }} {{ number_format($pay->amount, 2) }}</td>
                </tr>
            </table>
            @endforeach
            @endif

            <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>

            {{-- Footer --}}
            <div style="text-align:center; font-size:10px; margin-top:4px;">
                {{ $footerText }}<br>
                <small>Printed: {{ now()->format('d M Y h:i A') }}</small>
            </div>

        </div>
    </div>
</div>

@push('css')
<style>
@media print {
    .main-header, .main-sidebar, .content-header, .main-footer, .no-print { display: none !important; }
    .content-wrapper { margin: 0 !important; padding: 0 !important; background: #fff !important; }
    #receiptPreview { margin: 0 auto; }
}
.receipt-58 { width: 58mm !important; }
.receipt-80 { width: 80mm !important; }
</style>
@endpush

@push('js')
<script>
function printReceipt(size) {
    var preview = document.getElementById('receiptPreview');
    preview.className = size === '58mm' ? 'receipt-58' : 'receipt-80';
    window.print();
}
</script>
@endpush

@endsection
