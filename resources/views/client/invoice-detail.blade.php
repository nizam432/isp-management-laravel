{{-- resources/views/client/invoice-detail.blade.php --}}
@extends('client.layout')
@section('title', 'Invoice Detail')

@section('content')

<div style="margin-bottom:1rem; display:flex; align-items:center; justify-content:space-between;">
    <a href="{{ route('client.invoices') }}" style="color:#6b7280; font-size:13px; text-decoration:none;">
        <i class="fas fa-arrow-left"></i> Back to Invoices
    </a>
    <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
       style="background:#e74c3c; color:#fff; border-radius:7px; padding:7px 16px; font-size:13px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
        <i class="fas fa-file-pdf"></i> Download PDF
    </a>
</div>

<div class="card">
    <div class="card-header" style="justify-content:space-between;">
        <span><i class="fas fa-file-invoice" style="color:#1a56db;"></i> {{ $invoice->invoice_no }}</span>
        @php
            $statusMap = [
                'paid'    => ['badge-success', 'Paid'],
                'partial' => ['badge-warning', 'Partial'],
                'overdue' => ['badge-danger',  'Overdue'],
                'unpaid'  => ['badge-danger',  'Unpaid'],
            ];
            [$badgeClass, $statusBn] = $statusMap[$invoice->status] ?? ['badge-secondary', ucfirst($invoice->status)];
        @endphp
        <span class="badge {{ $badgeClass }}">{{ $statusBn }}</span>
    </div>
    <div class="card-body">

        {{-- Invoice Info --}}
        <table style="width:100%; font-size:13px; margin-bottom:1rem;">
            <tr>
                <td style="color:#6b7280; padding:5px 0; width:45%;">Customer Name</td>
                <td>{{ $customer->name }}</td>
            </tr>
            <tr>
                <td style="color:#6b7280; padding:5px 0;">Customer ID</td>
                <td>{{ $customer->customer_code }}</td>
            </tr>
            <tr>
                <td style="color:#6b7280; padding:5px 0;">Package</td>
                <td>{{ $invoice->package->name ?? '—' }}</td>
            </tr>
            <tr>
                <td style="color:#6b7280; padding:5px 0;">Period</td>
                <td>{{ $invoice->period_label }}</td>
            </tr>
            <tr>
                <td style="color:#6b7280; padding:5px 0;">Due Date</td>
                <td>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
            </tr>
        </table>

        <hr style="border:none; border-top:1px solid #e5e7eb; margin:1rem 0;">

        {{-- Amount breakdown --}}
        <table style="width:100%; font-size:13px;">
            <tr>
                <td style="padding:5px 0; color:#374151;">Invoice Amount</td>
                <td style="text-align:right;">৳{{ number_format($invoice->amount, 2) }}</td>
            </tr>
            @if($invoice->discount > 0)
            <tr>
                <td style="padding:5px 0; color:#374151;">Discount</td>
                <td style="text-align:right; color:#16a34a;">- ৳{{ number_format($invoice->discount, 2) }}</td>
            </tr>
            @endif
            <tr style="border-top:2px solid #111;">
                <td style="padding:8px 0 5px; font-weight:600;">Due Amount</td>
                <td style="text-align:right; font-weight:600; font-size:18px; color:{{ $invoice->due_amount > 0 ? '#dc2626' : '#16a34a' }};">
                    ৳{{ number_format($invoice->due_amount, 2) }}
                </td>
            </tr>
        </table>

        {{-- Payment history --}}
        @if($invoice->payments->where('status', 'active')->count() > 0)
        <div style="margin-top:1.25rem;">
            <div style="font-size:13px; font-weight:500; margin-bottom:8px; color:#374151;">
                <i class="fas fa-history" style="color:#1a56db;"></i> Payment History
            </div>
            <table style="width:100%; font-size:12px;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:7px 10px; text-align:left; color:#6b7280; font-weight:500;">Amount</th>
                        <th style="padding:7px 10px; text-align:left; color:#6b7280; font-weight:500;">Method</th>
                        <th style="padding:7px 10px; text-align:left; color:#6b7280; font-weight:500;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments->where('status', 'active') as $pay)
                    <tr>
                        <td style="padding:7px 10px; color:#16a34a; font-weight:500;">৳{{ number_format($pay->amount, 2) }}</td>
                        <td style="padding:7px 10px;">{{ strtoupper($pay->method) }}</td>
                        <td style="padding:7px 10px;">{{ optional($pay->paid_at)->format('d M Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($invoice->due_amount > 0)
        <div style="margin-top:1.25rem;">
            @include('client.payment._pay-button', ['invoice' => $invoice])
        </div>
        @endif

        @if($footerText)
        <div style="margin-top:1rem; font-size:12px; color:#9ca3af; text-align:center;">
            {{ $footerText }}
        </div>
        @endif
    </div>
</div>

@endsection
