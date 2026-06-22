<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>POP Wise Clients</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2 { margin: 0 0 4px 0; font-size: 14px; }
        .meta { margin-bottom: 10px; color: #555; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: center; }
        th { background: #1f3864; color: #fff; }
        td:nth-child(2) { text-align: left; font-weight: bold; }
        tfoot td { font-weight: bold; background: #dce6f1; }
    </style>
</head>
<body>
    <h2>POP Wise Clients Report</h2>
    <div class="meta">Generated: {{ now()->format('d M Y h:i A') }}</div>
    <table>
        <thead>
            <tr>
                <th>SL</th><th>Reseller</th><th>Total</th><th>Active</th><th>Expired</th>
                <th>Left</th><th>Pending</th><th>PPPOE</th><th>Hotspot</th><th>Free</th><th>VIP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['reseller'] }}</td>
                <td>{{ $row['total'] }}</td>
                <td>{{ $row['active'] }}</td>
                <td>{{ $row['expired'] }}</td>
                <td>{{ $row['left'] }}</td>
                <td>{{ $row['pending'] }}</td>
                <td>{{ $row['pppoe'] }}</td>
                <td>{{ $row['hotspot'] }}</td>
                <td>{{ $row['free'] }}</td>
                <td>{{ $row['vip'] }}</td>
            </tr>
            @empty
            <tr><td colspan="11" style="text-align:center">No records.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total</td>
                <td>{{ $totals['total'] }}</td><td>{{ $totals['active'] }}</td><td>{{ $totals['expired'] }}</td>
                <td>{{ $totals['left'] }}</td><td>{{ $totals['pending'] }}</td><td>{{ $totals['pppoe'] }}</td>
                <td>{{ $totals['hotspot'] }}</td><td>{{ $totals['free'] }}</td><td>{{ $totals['vip'] }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
