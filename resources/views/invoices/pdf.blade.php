{{-- resources/views/invoices/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #3c8dbc; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #3c8dbc; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px 8px; }
        .info-table .label { font-weight: bold; width: 35%; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #3c8dbc; color: #fff; padding: 8px; text-align: left; }
        table.items td { padding: 8px; border-bottom: 1px solid #ddd; }
        table.items tfoot td { font-weight: bold; background: #f4f4f4; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; }
        .badge-success { background: #28a745; color: #fff; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-danger  { background: #dc3545; color: #fff; }
        .badge-secondary { background: #6c757d; color: #fff; }
        .footer { text-align: center; margin-top: 40px; font-size: 11px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h2>ISP Management Software</h2>
        <p>Invoice / Bill</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Invoice No:</td>
            <td><strong>{{ $invoice->invoice_no }}</strong></td>
            <td class="label">Status:</td>
            <td>
                <span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') }}">
                    {{ strtoupper($invoice->status) }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="label">Customer:</td>
            <td>{{ $invoice->customer->name }}</td>
            <td class="label">Phone:</td>
            <td>{{ $invoice->customer->phone }}</td>
        </tr>
        <tr>
            <td class="label">Address:</td>
            <td>{{ $invoice->customer->address ?? '-' }}</td>
            <td class="label">Area:</td>
            <td>{{ $invoice->customer->area ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Billing Month:</td>
            <td>{{ $invoice->month }}</td>
            <td class="label">Due Date:</td>
            <td>{{ $invoice->due_date?->format('d M Y') ?? '-' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr><th>Description</th><th>Package</th><th>Amount</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Internet Service — {{ $invoice->month }}</td>
                <td>{{ $invoice->package->name ?? '-' }}</td>
                <td>{{ number_format($invoice->amount) }} BDT</td>
            </tr>
            @if($invoice->discount > 0)
            <tr>
                <td colspan="2">Discount</td>
                <td>- {{ number_format($invoice->discount) }} BDT</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Payable</td>
                <td>{{ number_format($invoice->amount - $invoice->discount) }} BDT</td>
            </tr>
            @foreach($invoice->payments as $pay)
            <tr>
                <td colspan="2">Paid ({{ strtoupper($pay->method) }} — {{ $pay->paid_at->format('d M Y') }})</td>
                <td>- {{ number_format($pay->amount) }} BDT</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="2">Due Amount</td>
                <td>{{ number_format($invoice->due_amount) }} BDT</td>
            </tr>
        </tfoot>
    </table>

    @if($invoice->notes)
    <p><strong>Notes:</strong> {{ $invoice->notes }}</p>
    @endif

    <div class="footer">
        <p>Thank you for your payment. | Generated on {{ now()->format('d M Y h:i A') }}</p>
    </div>
</body>
</html>
