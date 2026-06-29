<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Bills</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h2 { font-size: 16px; color: #1a237e; }
        .header p { font-size: 10px; color: #666; margin-top: 3px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        thead tr { background: #1a237e; color: #fff; }
        thead th { padding: 5px 4px; text-align: left; }
        thead th.text-right { text-align: right; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tbody td { padding: 4px; border-bottom: 1px solid #e9ecef; }
        tbody td.text-right { text-align: right; }
        tfoot tr { background: #1a237e; color: #fff; font-weight: bold; }
        tfoot td { padding: 5px 4px; }
        tfoot td.text-right { text-align: right; }
        .badge-paid    { background: #28a745; color: #fff; padding: 1px 5px; border-radius: 3px; }
        .badge-partial { background: #ffc107; color: #000; padding: 1px 5px; border-radius: 3px; }
        .badge-due     { background: #dc3545; color: #fff; padding: 1px 5px; border-radius: 3px; }
    </style>
</head>
<body>
<div class="header">
    <h2>Bandwidth Purchase Bills</h2>
    <p>Generated: {{ now()->format('d M Y, h:i A') }}</p>
</div>

<div class="meta">
    <span>Total Bills: {{ $purchases->count() }}</span>
    <span>Grand Total: ৳ {{ number_format($purchases->sum('sub_total'), 2) }}</span>
    <span>Total Paid: ৳ {{ number_format($purchases->sum('paid'), 2) }}</span>
    <span>Total Due: ৳ {{ number_format($purchases->sum('due'), 2) }}</span>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Invoice No</th>
            <th>Provider</th>
            <th>Billing Date</th>
            <th class="text-right">Sub Total</th>
            <th class="text-right">Paid</th>
            <th class="text-right">Due</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchases as $i => $p)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $p->invoice_no }}</td>
            <td>{{ $p->provider->company_name ?? '—' }}</td>
            <td>{{ optional($p->billing_date)->format('d M Y') }}</td>
            <td class="text-right">{{ number_format($p->sub_total, 2) }}</td>
            <td class="text-right" style="color:#28a745;">{{ number_format($p->paid, 2) }}</td>
            <td class="text-right" style="color:{{ $p->due > 0 ? '#dc3545' : '#28a745' }};">{{ number_format($p->due, 2) }}</td>
            <td>
                @if($p->isPaid())
                    <span class="badge-paid">Paid</span>
                @elseif($p->isPartial())
                    <span class="badge-partial">Partial</span>
                @else
                    <span class="badge-due">Due</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" style="text-align:right; padding-right:6px;">Total</td>
            <td class="text-right">{{ number_format($purchases->sum('sub_total'), 2) }}</td>
            <td class="text-right">{{ number_format($purchases->sum('paid'), 2) }}</td>
            <td class="text-right">{{ number_format($purchases->sum('due'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
</body>
</html>
