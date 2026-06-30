<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice — {{ $sale->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #1a237e; padding-bottom: 12px; }
        .header h1 { font-size: 20px; color: #1a237e; }
        .header p { font-size: 10px; color: #666; margin-top: 4px; }
        .invoice-title { text-align: center; margin: 12px 0; }
        .invoice-title h2 { font-size: 16px; color: #333; }
        .meta-table { width: 100%; margin-bottom: 16px; }
        .meta-table td { padding: 3px 0; font-size: 11px; vertical-align: top; }
        .meta-table .label { color: #888; width: 100px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items thead tr { background: #1a237e; color: #fff; }
        table.items th { padding: 7px 8px; text-align: left; font-size: 10px; }
        table.items th.right { text-align: right; }
        table.items td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 10px; }
        table.items td.right { text-align: right; }
        table.items tbody tr:nth-child(even) { background: #f8f9fa; }
        .summary-table { width: 280px; margin-left: auto; }
        .summary-table td { padding: 4px 8px; font-size: 11px; }
        .summary-table td.right { text-align: right; }
        .summary-table tr.total td { font-weight: bold; font-size: 13px; border-top: 2px solid #1a237e; color: #1a237e; }
        .summary-table tr.due td { font-weight: bold; color: #c62828; }
        .payments-section { margin-top: 20px; }
        .payments-section h3 { font-size: 12px; color: #1a237e; margin-bottom: 6px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        table.payments { width: 100%; border-collapse: collapse; }
        table.payments th { background: #f8f9fa; padding: 5px 8px; text-align: left; font-size: 10px; border-bottom: 1px solid #ddd; }
        table.payments td { padding: 5px 8px; font-size: 10px; border-bottom: 1px solid #eee; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; color: #fff; }
        .badge-success { background: #28a745; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-danger { background: #dc3545; }
    </style>
</head>
<body>

<div class="header">
    <h1>{{ \App\Models\Setting::get('company_name', 'My Company') ?? 'My Company' }}</h1>
    <p>{{ \App\Models\Setting::get('company_address', '') }}</p>
</div>

<div class="invoice-title">
    <h2>SALES INVOICE</h2>
</div>

<table class="meta-table">
    <tr>
        <td class="label">Invoice No</td>
        <td><strong>{{ $sale->invoice_no }}</strong></td>
        <td class="label">Date</td>
        <td>{{ $sale->sale_date->format('d M Y') }}</td>
    </tr>
    <tr>
        <td class="label">Customer</td>
        <td>{{ $sale->customer_name }}</td>
        <td class="label">Location</td>
        <td>{{ $sale->location->name ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Sale No</td>
        <td>{{ $sale->sale_no }}</td>
        <td class="label">Sale Type</td>
        <td>{{ ucfirst($sale->sale_type) }}</td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th class="right">Qty</th>
            <th class="right">Unit Price</th>
            <th class="right">Discount</th>
            <th class="right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sale->items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->product->name ?? '—' }}</td>
            <td class="right">{{ $item->quantity }} {{ $item->product->unit ?? '' }}</td>
            <td class="right">Tk {{ number_format($item->unit_price, 2) }}</td>
            <td class="right">Tk {{ number_format($item->discount, 2) }}</td>
            <td class="right">Tk {{ number_format($item->total_price, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="summary-table">
    <tr><td>Subtotal</td><td class="right">Tk {{ number_format($sale->subtotal, 2) }}</td></tr>
    <tr><td>Discount</td><td class="right">- Tk {{ number_format($sale->discount, 2) }}</td></tr>
    @if($sale->tax > 0)
    <tr><td>Tax</td><td class="right">+ Tk {{ number_format($sale->tax, 2) }}</td></tr>
    @endif
    <tr class="total"><td>Grand Total</td><td class="right">Tk {{ number_format($sale->total_amount, 2) }}</td></tr>
    <tr><td>Paid</td><td class="right" style="color:#28a745;">Tk {{ number_format($sale->paid_amount, 2) }}</td></tr>
    <tr class="due"><td>Due</td><td class="right">Tk {{ number_format($sale->due_amount, 2) }}</td></tr>
</table>

<div class="payments-section">
    <h3>PAYMENT HISTORY</h3>
    @if($sale->payments->count())
    <table class="payments">
        <thead>
            <tr>
                <th>Date</th>
                <th>Method</th>
                <th>Reference</th>
                <th style="text-align:right;">Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->payments as $payment)
            <tr>
                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                <td>{{ $payment->reference_no ?? '—' }}</td>
                <td style="text-align:right;">Tk {{ number_format($payment->amount, 2) }}</td>
                <td>
                    @if($payment->is_void)
                        <span class="badge badge-danger">Void</span>
                    @else
                        <span class="badge badge-success">Active</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:#999; font-size:10px;">No payments recorded yet.</p>
    @endif
</div>

@if($sale->note)
<div style="margin-top:16px; font-size:10px; color:#666;">
    <strong>Note:</strong> {{ $sale->note }}
</div>
@endif

<div class="footer">
    <p>{{ \App\Models\Setting::get('invoice_footer_text', 'Thank you for your business.') }}</p>
    <p style="margin-top:4px;">Generated: {{ now()->format('d M Y, h:i A') }}</p>
</div>

</body>
</html>
