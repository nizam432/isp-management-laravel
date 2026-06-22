<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h2 { margin: 0 0 4px 0; font-size: 14px; }
        .meta { margin-bottom: 10px; color: #555; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 3px 5px; text-align: left; }
        th { background: #7b0000; color: #fff; font-size: 9px; }
        tfoot td { font-weight: bold; background: #fce4e4; }
        .text-right { text-align: right; }
        .text-danger { color: #c0392b; }
    </style>
</head>
<body>
    <h2>Expense Report</h2>
    <div class="meta">Generated: {{ now()->format('d M Y h:i A') }} &nbsp;|&nbsp; Total Records: {{ $expenses->count() }} &nbsp;|&nbsp; Total Amount: ৳{{ number_format($grandTotal['amount'], 2) }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Expense Id</th><th>Name</th><th>Expense Head</th>
                <th>Date</th><th>Invoice No</th><th>Employee</th><th>Description</th><th>Status</th><th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $i => $expense)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $expense->id }}</td>
                <td>{{ $expense->category->name ?? '-' }}</td>
                <td>{{ $expense->category->name ?? '-' }}</td>
                <td>{{ $expense->expense_date ? \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') : '-' }}</td>
                <td>{{ $expense->expense_no ?? '-' }}</td>
                <td>{{ $expense->payee ?? '-' }}</td>
                <td>{{ $expense->description ?? '-' }}</td>
                <td>{{ ucfirst($expense->status) }}</td>
                <td class="text-right text-danger">{{ number_format($expense->amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center">No records found.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="9" class="text-right">Total</td>
                <td class="text-right text-danger">{{ number_format($grandTotal['amount'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
