<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #333; background: #fff; }

        /* Header */
        .header { padding: 20px 28px 14px; border-bottom: 3px solid #00a65a; }
        .header-inner { display: table; width: 100%; }
        .header-left { display: table-cell; vertical-align: top; width: 60%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .company-name { font-size: 20px; font-weight: bold; color: #333; margin-bottom: 3px; }
        .company-info { font-size: 11px; color: #777; line-height: 1.6; }
        .invoice-title { font-size: 26px; font-weight: bold; color: #00a65a; letter-spacing: 2px; }
        .invoice-no { font-size: 12px; color: #777; margin-top: 3px; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; margin-top: 4px; }
        .badge-paid { background: #d4edda; color: #155724; }
        .badge-unpaid { background: #e2e3e5; color: #383d41; }
        .badge-partial { background: #fff3cd; color: #856404; }
        .badge-overdue { background: #f8d7da; color: #721c24; }

        /* Info Section */
        .info-section { display: table; width: 100%; border-bottom: 1px solid #eee; }
        .info-left { display: table-cell; width: 50%; padding: 16px 28px; border-right: 1px solid #eee; vertical-align: top; }
        .info-right { display: table-cell; width: 50%; padding: 16px 28px; vertical-align: top; }
        .section-label { color: #00a65a; font-size: 10px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; margin-bottom: 8px; }
        .customer-name { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
        .info-text { font-size: 12px; color: #666; line-height: 1.7; }
        .info-row { display: table; width: 100%; margin-bottom: 3px; }
        .info-row-label { display: table-cell; color: #777; font-size: 12px; width: 50%; }
        .info-row-value { display: table-cell; font-size: 12px; text-align: right; }

        /* Items Table */
        .items-wrap { padding: 16px 28px; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items thead tr { border-bottom: 2px solid #00a65a; }
        table.items thead th { padding: 8px 0; font-size: 12px; color: #666; font-weight: bold; text-align: left; }
        table.items thead th.right { text-align: right; }
        table.items tbody tr { border-bottom: 1px solid #eee; }
        table.items tbody td { padding: 9px 0; font-size: 13px; }
        table.items tbody td.right { text-align: right; }
        table.items tbody td.muted { color: #777; }
        table.items tfoot tr { border-top: 2px solid #00a65a; }
        table.items tfoot td { padding: 9px 0; font-weight: bold; }
        table.items tfoot td.total { text-align: right; color: #00a65a; font-size: 16px; }

        /* Payment History */
        .payment-wrap { padding: 0 28px 16px; }
        table.payments { width: 100%; border-collapse: collapse; }
        table.payments thead tr { background: #f5f5f5; }
        table.payments thead th { padding: 7px 8px; font-size: 11px; color: #666; font-weight: bold; text-align: left; }
        table.payments tbody td { padding: 7px 8px; font-size: 12px; border-bottom: 1px solid #eee; }
        table.payments tbody td.right { text-align: right; }

        /* Footer */
        .footer { display: table; width: 100%; padding: 12px 28px; background: #f9f9f9; border-top: 1px solid #eee; margin-top: 8px; }
        .footer-left { display: table-cell; font-size: 11px; color: #777; }
        .footer-right { display: table-cell; text-align: right; font-size: 11px; color: #777; }
    </style>
</head>
<body>

<?php
    $companyName    = \App\Models\Setting::get('company_name', config('app.name'));
    $companyPhone   = \App\Models\Setting::get('company_phone', '');
    $companyEmail   = \App\Models\Setting::get('company_email', '');
    $companyAddress = \App\Models\Setting::get('company_address', '');
    $companyLogo    = \App\Models\Setting::get('company_logo', '');
?>

<!-- Header -->
<div class="header">
    <div class="header-inner">
        <div class="header-left">
            <?php if($companyLogo): ?>
                <img src="{{ public_path('storage/' . $companyLogo) }}" style="height:40px; margin-bottom:6px;"><br>
            <?php endif; ?>
            <div class="company-name">{{ $companyName }}</div>
            <div class="company-info">
                <?php if($companyAddress): ?>{{ $companyAddress }}<br><?php endif; ?>
                <?php if($companyPhone): ?>Phone: {{ $companyPhone }}<?php endif; ?>
                <?php if($companyEmail): ?> | Email: {{ $companyEmail }}<?php endif; ?>
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-no">{{ $invoice->invoice_no }}</div>
            @if($invoice->status === 'paid')
                <span class="badge badge-paid">Paid</span>
            @elseif($invoice->status === 'partial')
                <span class="badge badge-partial">Partial</span>
            @elseif($invoice->status === 'overdue')
                <span class="badge badge-overdue">Overdue</span>
            @else
                <span class="badge badge-unpaid">Unpaid</span>
            @endif
        </div>
    </div>
</div>

<!-- Customer + Invoice Info -->
<div class="info-section">
    <div class="info-left">
        <div class="section-label">Bill To</div>
        <div class="customer-name">{{ $invoice->customer->name }}</div>
        <div class="info-text">
            {{ $invoice->customer->phone }}<br>
            Username: {{ $invoice->customer->username ?? '-' }}<br>
            Package: {{ $invoice->package->name ?? '-' }}
        </div>
    </div>
    <div class="info-right">
        <div class="section-label">Invoice Info</div>
        <div class="info-row">
            <span class="info-row-label">Month:</span>
            <span class="info-row-value">{{ $invoice->month }}</span>
        </div>
        <div class="info-row">
            <span class="info-row-label">Issue Date:</span>
            <span class="info-row-value">{{ $invoice->created_at->format('d M Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-row-label">Due Date:</span>
            <span class="info-row-value">{{ $invoice->due_date?->format('d M Y') ?? '-' }}</span>
        </div>
    </div>
</div>

<!-- Items Table -->
<div class="items-wrap">
    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Monthly Internet Bill — {{ $invoice->package->name ?? 'Internet Service' }}</td>
                <td class="right">BDT {{ number_format($invoice->amount, 2) }}</td>
            </tr>
            @if($invoice->discount > 0)
            <tr>
                <td class="muted">Discount</td>
                <td class="right muted">- BDT {{ number_format($invoice->discount, 2) }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>Total Due</td>
                <td class="total">BDT {{ number_format($invoice->due_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Payment History -->
@if($invoice->payments->count() > 0)
<div class="payment-wrap">
    <div class="section-label" style="padding: 0 0 8px;">Payment History</div>
    <table class="payments">
        <thead>
            <tr>
                <th>Date</th>
                <th>Method</th>
                <th>Transaction ID</th>
                <th>Received By</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->payments as $pay)
            @if(!$pay->isVoid())
            <tr>
                <td>{{ optional($pay->paid_at)->format('d M Y') ?? optional($pay->payment_date)->format('d M Y') ?? '-' }}</td>
                <td>{{ strtoupper($pay->method) }}</td>
                <td>{{ $pay->transaction_id ?? '-' }}</td>
                <td>{{ $pay->receivedBy->name ?? '-' }}</td>
                <td class="right">BDT {{ number_format($pay->amount, 2) }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if($invoice->notes)
<div style="padding: 0 28px 12px; font-size: 12px; color: #666;">
    <strong>Notes:</strong> {{ $invoice->notes }}
</div>
@endif

<!-- Footer -->
<div class="footer">
    <div class="footer-left">Thank you for your payment!</div>
    <div class="footer-right">Generated on {{ now()->format('d M Y h:i A') }}</div>
</div>

</body>
</html>