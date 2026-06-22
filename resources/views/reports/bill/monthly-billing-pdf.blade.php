{{-- resources/views/reports/bill/monthly-billing-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Billing Report — {{ \Carbon\Carbon::parse($month)->format('F Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h2 { margin: 0 0 4px 0; font-size: 14px; }
        .meta { margin-bottom: 10px; color: #555; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 3px 5px; text-align: left; }
        th { background: #f0f0f0; font-size: 9px; }
        tfoot td { font-weight: bold; background: #f7f7f7; }
        .text-right { text-align: right; }
        .text-danger { color: #dc3545; }
        .text-success { color: #28a745; }
    </style>
</head>
<body>
    <h2>Monthly Billing Report</h2>
    <div class="meta">
        Month: {{ \Carbon\Carbon::parse($month)->format('F Y') }} &nbsp;|&nbsp;
        Generated: {{ now()->format('d M Y h:i A') }} &nbsp;|&nbsp;
        Total Records: {{ $invoices->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th><th>C.Code</th><th>ID/IP</th><th>Name</th><th>Mobile</th>
                <th>Zone</th><th>Cus.Type</th><th>Conn.Type</th><th>Package</th><th>Speed</th>
                <th class="text-right">Generated</th><th class="text-right">Received</th>
                <th class="text-right">Due</th><th class="text-right">Advance</th>
                <th>Payment Date</th><th>Server</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $i => $inv)
            @php $cust = $inv->customer; @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $cust->customer_code ?? '-' }}</td>
                <td>{{ $cust->pppoe_username ?? $cust->ip_address ?? '-' }}</td>
                <td>{{ $cust->name ?? '-' }}</td>
                <td>{{ $cust->phone ?? '-' }}</td>
                <td>{{ $cust->zone->name ?? '-' }}</td>
                <td>{{ $cust->clientType->name ?? '-' }}</td>
                <td>{{ $cust->connectionType->name ?? '-' }}</td>
                <td>{{ $cust->package->name ?? '-' }}</td>
                <td>{{ ($cust->package->speed_download ?? '-') }}Mbps</td>
                <td class="text-right">{{ number_format($inv->amount, 0) }}</td>
                <td class="text-right text-success">{{ number_format($inv->amount - $inv->due_amount, 0) }}</td>
                <td class="text-right text-danger">{{ number_format($inv->due_amount, 0) }}</td>
                <td class="text-right">{{ number_format($cust->advance_balance ?? 0, 0) }}</td>
                <td>{{ $cust->last_payment_date ? \Carbon\Carbon::parse($cust->last_payment_date)->format('d M Y') : '-' }}</td>
                <td>{{ $cust->router->name ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="16" style="text-align:center;">No records found.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="10" class="text-right">Total</td>
                <td class="text-right">{{ number_format($grandTotal['generated'], 0) }}</td>
                <td class="text-right text-success">{{ number_format($grandTotal['received'], 0) }}</td>
                <td class="text-right text-danger">{{ number_format($grandTotal['due'], 0) }}</td>
                <td class="text-right">{{ number_format($grandTotal['advance'], 0) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
