<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Income & Discount Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h2 { margin: 0 0 4px 0; font-size: 13px; }
        .meta { margin-bottom: 8px; color: #555; }
        .summary { display: table; width: 100%; margin-bottom: 10px; }
        .summary-box { display: table-cell; padding: 6px 10px; text-align: center; font-weight: bold; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 3px 5px; text-align: left; }
        th { background: #1f3864; color: #fff; font-size: 8px; }
        .text-right { text-align: right; }
        tfoot td { font-weight: bold; background: #dce6f1; }
    </style>
</head>
<body>
    <h2>Income & Discount Report</h2>
    <div class="meta">Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y h:i A') }}</div>

    <div class="summary">
        <div class="summary-box" style="background:#d5f5e3;">Total Billed<br>৳{{ number_format($grandTotal['billed'], 2) }}</div>
        <div class="summary-box" style="background:#fdebd0;">Discount<br>৳{{ number_format($grandTotal['discount'], 2) }}</div>
        <div class="summary-box" style="background:#d6eaf8;">Collected<br>৳{{ number_format($grandTotal['collected'], 2) }}</div>
        <div class="summary-box" style="background:#fadbd8;">Due<br>৳{{ number_format($grandTotal['due'], 2) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th><th>Invoice No</th><th>Customer</th><th>Package</th><th>Zone</th>
                <th>Due Date</th><th>Status</th>
                <th class="text-right">Billed</th><th class="text-right">Discount</th>
                <th class="text-right">Collected</th><th class="text-right">Due</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $i => $inv)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $inv->invoice_no }}</td>
                <td>{{ $inv->customer->name ?? '-' }}</td>
                <td>{{ $inv->customer->package->name ?? '-' }}</td>
                <td>{{ $inv->customer->zone->name ?? '-' }}</td>
                <td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '-' }}</td>
                <td>{{ ucfirst($inv->status) }}</td>
                <td class="text-right">{{ number_format($inv->amount, 2) }}</td>
                <td class="text-right">{{ number_format($inv->discount ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($inv->amount - $inv->due_amount, 2) }}</td>
                <td class="text-right">{{ number_format($inv->due_amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="11" style="text-align:center">No records found.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">Total</td>
                <td class="text-right">{{ number_format($grandTotal['billed'], 2) }}</td>
                <td class="text-right">{{ number_format($grandTotal['discount'], 2) }}</td>
                <td class="text-right">{{ number_format($grandTotal['collected'], 2) }}</td>
                <td class="text-right">{{ number_format($grandTotal['due'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
