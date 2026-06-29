<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll — {{ $month }}</title>
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
        thead th.right { text-align: right; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tbody td { padding: 4px; border-bottom: 1px solid #e9ecef; }
        tbody td.right { text-align: right; }
        tfoot tr { background: #1a237e; color: #fff; font-weight: bold; }
        tfoot td { padding: 5px 4px; }
        tfoot td.right { text-align: right; }
        .badge-paid    { background: #28a745; color: #fff; padding: 1px 5px; border-radius: 3px; }
        .badge-partial { background: #ffc107; color: #000; padding: 1px 5px; border-radius: 3px; }
        .badge-pending { background: #6c757d; color: #fff; padding: 1px 5px; border-radius: 3px; }
        .badge-void    { background: #dc3545; color: #fff; padding: 1px 5px; border-radius: 3px; }
    </style>
</head>
<body>
<div class="header">
    <h2>Payroll — {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</h2>
    <p>Generated: {{ now()->format('d M Y, h:i A') }}</p>
</div>
<div class="meta">
    <span>Total Employees: {{ $payrolls->count() }}</span>
    <span>Total Net Salary: ৳ {{ number_format($payrolls->sum('net_salary'), 2) }}</span>
    <span>Total Paid: ৳ {{ number_format($payrolls->sum('paid_amount'), 2) }}</span>
    <span>Total Due: ৳ {{ number_format($payrolls->sum('due_amount'), 2) }}</span>
</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Employee</th>
            <th>Code</th>
            <th class="right">Basic (৳)</th>
            <th class="right">Gross (৳)</th>
            <th class="right">Deduction (৳)</th>
            <th class="right">Net Salary (৳)</th>
            <th class="right">Paid (৳)</th>
            <th class="right">Due (৳)</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payrolls as $i => $p)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $p->employee->name ?? '—' }}</td>
            <td>{{ $p->employee->employee_code ?? '—' }}</td>
            <td class="right">{{ number_format($p->basic_salary, 2) }}</td>
            <td class="right">{{ number_format($p->gross_salary, 2) }}</td>
            <td class="right" style="color:#dc3545;">{{ number_format($p->total_deduction, 2) }}</td>
            <td class="right" style="font-weight:bold;">{{ number_format($p->net_salary, 2) }}</td>
            <td class="right" style="color:#28a745;">{{ number_format($p->paid_amount, 2) }}</td>
            <td class="right" style="color:{{ $p->due_amount > 0 ? '#dc3545' : '#28a745' }};">{{ number_format($p->due_amount, 2) }}</td>
            <td>
                @if($p->isPaid())    <span class="badge-paid">Paid</span>
                @elseif($p->isPartial()) <span class="badge-partial">Partial</span>
                @elseif($p->isVoid()) <span class="badge-void">Void</span>
                @else <span class="badge-pending">Pending</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align:right; padding-right:6px;">Total</td>
            <td class="right">{{ number_format($payrolls->sum('basic_salary'), 2) }}</td>
            <td class="right">{{ number_format($payrolls->sum('gross_salary'), 2) }}</td>
            <td class="right">{{ number_format($payrolls->sum('total_deduction'), 2) }}</td>
            <td class="right">{{ number_format($payrolls->sum('net_salary'), 2) }}</td>
            <td class="right">{{ number_format($payrolls->sum('paid_amount'), 2) }}</td>
            <td class="right">{{ number_format($payrolls->sum('due_amount'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
</body>
</html>
