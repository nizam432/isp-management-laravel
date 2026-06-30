<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
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
        .badge { padding: 1px 5px; border-radius: 3px; color:#fff; }
        .badge-success { background: #28a745; }
        .badge-warning { background: #ffc107; color:#000; }
        .badge-danger  { background: #dc3545; }
        .badge-secondary { background: #6c757d; }
    </style>
</head>
<body>
<div class="header">
    <h2>Sales Report</h2>
    <p>Generated: {{ now()->format('d M Y, h:i A') }}</p>
</div>
<div class="meta">
    <span>Total Sales: {{ $sales->count() }}</span>
    <span>Total Amount: Tk {{ number_format($sales->sum('total_amount'), 2) }}</span>
    <span>Total Paid: Tk {{ number_format($sales->sum('paid_amount'), 2) }}</span>
    <span>Total Due: Tk {{ number_format($sales->sum('due_amount'), 2) }}</span>
</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Invoice No</th>
            <th>Sale No</th>
            <th>Date</th>
            <th>Customer</th>
            <th class="right">Total (Tk)</th>
            <th class="right">Paid (Tk)</th>
            <th class="right">Due (Tk)</th>
            <th>Payment</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $i => $sale)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $sale->invoice_no }}</td>
            <td>{{ $sale->sale_no }}</td>
            <td>{{ $sale->sale_date->format('d M Y') }}</td>
            <td>{{ $sale->customer_name }}</td>
            <td class="right" style="font-weight:bold;">{{ number_format($sale->total_amount, 2) }}</td>
            <td class="right" style="color:#28a745;">{{ number_format($sale->paid_amount, 2) }}</td>
            <td class="right" style="color:{{ $sale->due_amount > 0 ? '#dc3545' : '#28a745' }};">{{ number_format($sale->due_amount, 2) }}</td>
            <td>
                @if($sale->payment_status === 'paid')    <span class="badge badge-success">Paid</span>
                @elseif($sale->payment_status === 'partial') <span class="badge badge-warning">Partial</span>
                @else <span class="badge badge-danger">Unpaid</span>
                @endif
            </td>
            <td>
                @if($sale->status === 'confirmed') <span class="badge badge-success">Confirmed</span>
                @elseif($sale->status === 'cancelled') <span class="badge badge-secondary">Cancelled</span>
                @else <span class="badge badge-warning">{{ ucfirst($sale->status) }}</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align:right; padding-right:6px;">Total</td>
            <td class="right">{{ number_format($sales->sum('total_amount'), 2) }}</td>
            <td class="right">{{ number_format($sales->sum('paid_amount'), 2) }}</td>
            <td class="right">{{ number_format($sales->sum('due_amount'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>
</body>
</html>
