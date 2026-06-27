<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment History</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h2 { font-size: 16px; color: #2c3e50; }
        .header p { font-size: 10px; color: #666; margin-top: 3px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        thead tr { background: #2c3e50; color: #fff; }
        thead th { padding: 5px 4px; text-align: left; white-space: nowrap; }
        thead th.text-right { text-align: right; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tbody tr.void-row { color: #999; }
        tbody td { padding: 4px; border-bottom: 1px solid #e9ecef; }
        tbody td.text-right { text-align: right; }
        tfoot tr { background: #2c3e50; color: #fff; font-weight: bold; }
        tfoot td { padding: 5px 4px; }
        tfoot td.text-right { text-align: right; }
        .badge-void { background: #6c757d; color: #fff; padding: 1px 5px; border-radius: 3px; }
        .badge-active { background: #28a745; color: #fff; padding: 1px 5px; border-radius: 3px; }
    </style>
</head>
<body>

<div class="header">
    <h2>Bandwidth Sale — Payment History</h2>
    <p>Generated: {{ now()->format('d M Y, h:i A') }}</p>
</div>

<div class="meta">
    <span>Total Records: {{ $payments->count() }}</span>
    <span>Total Received: ৳ {{ number_format($payments->where('status', 'active')->sum('received_amount'), 2) }}</span>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>R.Date</th>
            <th>Customer</th>
            <th>Contact</th>
            <th>Invoice No</th>
            <th>Bill Month</th>
            <th class="text-right">Bill Amount</th>
            <th class="text-right">Received</th>
            <th class="text-right">Discount</th>
            <th class="text-right">Balance Due</th>
            <th>Received By</th>
            <th>Created By</th>
            <th>Remarks</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @php $totalBill = 0; $totalReceived = 0; @endphp
        @forelse($payments as $i => $pay)
        @php
            $totalBill     += $pay->bwsInvoice->grand_total ?? 0;
            $totalReceived += $pay->status === 'active' ? $pay->received_amount : 0;
        @endphp
        <tr class="{{ $pay->isVoid() ? 'void-row' : '' }}">
            <td>{{ $i + 1 }}</td>
            <td>{{ optional($pay->received_date)->format('d-m-Y') }}</td>
            <td>{{ $pay->bwsCustomer->customer_name ?? '—' }}</td>
            <td>{{ $pay->bwsCustomer->contact_person ?? '—' }}</td>
            <td>{{ $pay->bwsInvoice->invoice_no ?? '—' }}</td>
            <td>{{ $pay->bwsInvoice->billing_month ?? '—' }}</td>
            <td class="text-right">{{ number_format($pay->bwsInvoice->grand_total ?? 0, 2) }}</td>
            <td class="text-right">{{ number_format($pay->received_amount, 2) }}</td>
            <td class="text-right">{{ number_format($pay->discount, 2) }}</td>
            <td class="text-right">{{ number_format($pay->bwsInvoice->due_amount ?? 0, 2) }}</td>
            <td>{{ $pay->receivedBy->name ?? '—' }}</td>
            <td>{{ $pay->createdBy->name ?? '—' }}</td>
            <td>{{ Str::limit($pay->remarks, 20) }}</td>
            <td>
                @if($pay->isVoid())
                    <span class="badge-void">Void</span>
                @else
                    <span class="badge-active">Active</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="14" style="text-align:center; padding:10px; color:#999;">
                No records found.
            </td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align:right; padding-right:6px;">Total</td>
            <td class="text-right">{{ number_format($totalBill, 2) }}</td>
            <td class="text-right">{{ number_format($totalReceived, 2) }}</td>
            <td colspan="6"></td>
        </tr>
    </tfoot>
</table>

</body>
</html>
