<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expenses</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h2 { font-size: 16px; color: #c0392b; }
        .header p { font-size: 10px; color: #666; margin-top: 3px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        thead tr { background: #c0392b; color: #fff; }
        thead th { padding: 5px 4px; text-align: left; }
        thead th.right { text-align: right; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tbody tr.void { color: #999; }
        tbody td { padding: 4px; border-bottom: 1px solid #e9ecef; }
        tbody td.right { text-align: right; }
        tfoot tr { background: #c0392b; color: #fff; font-weight: bold; }
        tfoot td { padding: 5px 4px; }
        tfoot td.right { text-align: right; }
        .badge-approved { background: #28a745; color: #fff; padding: 1px 5px; border-radius: 3px; }
        .badge-pending  { background: #ffc107; color: #000; padding: 1px 5px; border-radius: 3px; }
        .badge-void     { background: #6c757d; color: #fff; padding: 1px 5px; border-radius: 3px; }
    </style>
</head>
<body>
<div class="header">
    <h2>Expense Report</h2>
    <p>Generated: {{ now()->format('d M Y, h:i A') }}</p>
</div>
<div class="meta">
    <span>Total Records: {{ $expenses->count() }}</span>
    <span>Total Amount: ৳ {{ number_format($expenses->where('status', '!=', 'void')->sum('amount'), 2) }}</span>
</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Expense No</th>
            <th>Date</th>
            <th>Category</th>
            <th>Description</th>
            <th>Payee</th>
            <th>Method</th>
            <th class="right">Amount (৳)</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($expenses as $i => $exp)
        <tr class="{{ $exp->isVoid() ? 'void' : '' }}">
            <td>{{ $i + 1 }}</td>
            <td>{{ $exp->expense_no }}</td>
            <td>{{ optional($exp->expense_date)->format('d M Y') }}</td>
            <td>{{ $exp->category->name ?? '—' }}</td>
            <td>{{ Str::limit($exp->description, 30) ?? '—' }}</td>
            <td>{{ $exp->payee ?? '—' }}</td>
            <td>{{ strtoupper($exp->payment_method) }}</td>
            <td class="right" style="color:{{ $exp->isVoid() ? '#999' : '#c0392b' }}; font-weight:bold;">
                {{ number_format($exp->amount, 2) }}
            </td>
            <td>
                @if($exp->isVoid())
                    <span class="badge-void">Void</span>
                @elseif($exp->isApproved())
                    <span class="badge-approved">Approved</span>
                @else
                    <span class="badge-pending">Pending</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" style="text-align:right; padding-right:6px;">Total</td>
            <td class="right">{{ number_format($expenses->where('status', '!=', 'void')->sum('amount'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
</body>
</html>
