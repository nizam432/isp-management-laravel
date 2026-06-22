<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Income Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h2 { margin: 0 0 4px 0; font-size: 14px; }
        .meta { margin-bottom: 10px; color: #555; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 3px 5px; text-align: left; }
        th { background: #1a5276; color: #fff; font-size: 9px; }
        tfoot td { font-weight: bold; background: #d5f5e3; }
        .text-right { text-align: right; }
        .text-success { color: #1e8449; }
    </style>
</head>
<body>
    <h2>Income Report</h2>
    <div class="meta">Generated: {{ now()->format('d M Y h:i A') }} &nbsp;|&nbsp; Total Records: {{ count($rows) }} &nbsp;|&nbsp; Total Amount: ৳{{ number_format($grandTotal['amount'], 2) }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Income Id</th><th>Name</th><th>Income Head</th>
                <th>Date</th><th>Invoice No</th><th>Description</th><th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item['id'] }}</td>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['head'] }}</td>
                <td>{{ $item['date'] }}</td>
                <td>{{ $item['invoice_no'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td class="text-right text-success">{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center">No records found.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">Total</td>
                <td class="text-right text-success">{{ number_format($grandTotal['amount'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
