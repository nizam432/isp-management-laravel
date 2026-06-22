{{-- resources/views/reports/bill/receive-history-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill Collection Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2 { margin: 0 0 4px 0; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; }
        tfoot td { font-weight: bold; background: #f7f7f7; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Bill Collection Report</h2>
    <div class="meta">Generated: {{ now()->format('d M Y h:i A') }} &nbsp; | &nbsp; Total Records: {{ $payments->count() }}</div>

    <table>
        <thead>
            <tr>
                <th>#</th><th>R.Date</th><th>C.Code</th><th>ID/IP</th><th>Name</th>
                <th>Mobile</th><th>Zone</th><th>SubZone</th><th>Package</th>
                <th>B.Status</th><th>Agent</th><th>TrxId</th><th>Monthly Bill</th><th>Received</th>
                <th>Received By</th><th>Gateway</th><th>Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $i => $pay)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $pay->paid_at ? \Carbon\Carbon::parse($pay->paid_at)->format('d M Y h:i A') : '-' }}</td>
                <td>{{ $pay->customer->customer_code ?? '-' }}</td>
                <td>{{ $pay->customer->pppoe_username ?? $pay->customer->ip_address ?? '-' }}</td>
                <td>{{ $pay->customer->name ?? '-' }}</td>
                <td>{{ $pay->customer->phone ?? '-' }}</td>
                <td>{{ $pay->customer->zone->name ?? '-' }}</td>
                <td>{{ $pay->customer->subZone->name ?? '-' }}</td>
                <td>{{ $pay->customer->package->name ?? '-' }}</td>
                <td>{{ ucfirst($pay->customer->billing_status ?? '-') }}</td>
                <td>{{ $pay->customer->agent->name ?? '-' }}</td>
                <td>{{ $pay->transaction_id ?? '-' }}</td>
                <td class="text-right">{{ number_format($pay->customer->monthly_bill_amount ?? 0, 0) }}</td>
                <td class="text-right">{{ number_format($pay->amount, 0) }}</td>
                <td>{{ $pay->receivedBy->name ?? '-' }}</td>
                <td>{{ strtoupper($pay->method) }}</td>
                <td>{{ $pay->remarks ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="17" style="text-align:center;">No records found.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="12" class="text-right">Total</td>
                <td class="text-right">{{ number_format($grandTotal['monthly_bill'], 0) }}</td>
                <td class="text-right">{{ number_format($grandTotal['received'], 0) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
