<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #222; }
        h2 { margin: 0 0 4px 0; font-size: 13px; }
        .meta { margin-bottom: 8px; color: #555; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 2px 4px; text-align: left; }
        th { background: #1f3864; color: #fff; font-size: 8px; }
        tfoot td { font-weight: bold; background: #dce6f1; }
    </style>
</head>
<body>
    <h2>Customer Report</h2>
    <div class="meta">Generated: {{ now()->format('d M Y h:i A') }} &nbsp;|&nbsp; Total: {{ $customers->count() }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Code</th><th>Username</th><th>Name</th><th>Phone</th>
                <th>Client Type</th><th>Package</th><th>Server</th><th>Protocol</th>
                <th>Monthly Bill</th><th>B.Status</th><th>POP</th><th>M.Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $i => $cust)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $cust->customer_code }}</td>
                <td>{{ $cust->pppoe_username ?? '-' }}</td>
                <td>{{ $cust->name }}</td>
                <td>{{ $cust->phone ?? '-' }}</td>
                <td>{{ $cust->clientType->name ?? '-' }}</td>
                <td>{{ $cust->package->name ?? '-' }}</td>
                <td>{{ $cust->router->name ?? '-' }}</td>
                <td>{{ $cust->protocolType->name ?? '-' }}</td>
                <td>{{ number_format($cust->monthly_bill_amount ?? 0, 0) }}</td>
                <td>{{ ucfirst($cust->billing_status ?? $cust->status) }}</td>
                <td>{{ $cust->macReseller->business_name ?? '-' }}</td>
                <td>{{ ucfirst($cust->mikrotik_status ?? '-') }}</td>
            </tr>
            @empty
            <tr><td colspan="13" style="text-align:center">No records found.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="13">Total: {{ $grandTotal['total'] }} customers</td></tr>
        </tfoot>
    </table>
</body>
</html>
