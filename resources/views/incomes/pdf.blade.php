<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Income Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        h2 { margin-bottom: 2px; }
        .meta { color: #777; margin-bottom: 14px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px 7px; text-align: left; }
        th { background: #3c8dbc; color: #fff; font-size: 10px; text-transform: uppercase; }
        td.amount { text-align: right; font-weight: bold; }
        tfoot td { font-weight: bold; background: #f4f4f4; }
        .status-void { color: #999; text-decoration: line-through; }
    </style>
</head>
<body>
    <h2>Income Report</h2>
    <div class="meta">Generated: {{ now()->format('d M Y, h:i A') }} &middot; Total records: {{ $incomes->count() }}</div>

    <table>
        <thead>
            <tr>
                <th>Income No</th>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th>Payer</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incomes as $inc)
            <tr class="{{ $inc->status === 'void' ? 'status-void' : '' }}">
                <td>{{ $inc->income_no }}</td>
                <td>{{ $inc->income_date->format('d M Y') }}</td>
                <td>{{ $inc->category->name ?? '-' }}</td>
                <td>{{ $inc->description ?? '-' }}</td>
                <td>{{ $inc->payer ?? ($inc->customer->name ?? '-') }}</td>
                <td>{{ strtoupper($inc->payment_method) }}</td>
                <td class="amount">৳{{ number_format($inc->amount, 2) }}</td>
                <td>{{ ucfirst($inc->status) }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;">কোনো income পাওয়া যায়নি।</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">Total (Active only)</td>
                <td class="amount">৳{{ number_format($total, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
