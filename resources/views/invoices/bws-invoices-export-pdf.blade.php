{{-- resources/views/bandwidth-sale/invoices/export-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BWS Invoice List</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DejaVu Sans', Arial, sans-serif; font-size:10px; color:#2c3e50; }
        .page { padding:20px 25px; }

        .header { margin-bottom:15px; border-bottom:3px solid #0073b7; padding-bottom:10px; }
        .header h2 { font-size:16px; color:#0073b7; font-weight:700; }
        .header p  { font-size:10px; color:#666; margin-top:3px; }

        table { width:100%; border-collapse:collapse; }
        thead tr { background:#2c3e50; color:#fff; }
        th { padding:7px 6px; text-align:left; font-size:9px; font-weight:700; white-space:nowrap; }
        th.text-right, td.text-right { text-align:right; }
        th.text-center, td.text-center { text-align:center; }
        tbody tr { border-bottom:1px solid #e9ecef; }
        tbody tr:nth-child(even) { background:#f8f9fa; }
        td { padding:6px 6px; font-size:9px; }

        .badge { padding:2px 6px; border-radius:8px; color:#fff; font-size:8px; font-weight:700; }
        .badge-paid    { background:#00a65a; }
        .badge-partial { background:#17a2b8; }
        .badge-overdue { background:#dc3545; }
        .badge-unpaid  { background:#f39c12; }

        tfoot tr { background:#e8f4fd; font-weight:700; }
        tfoot td { padding:7px 6px; font-size:10px; }

        .text-danger { color:#dc3545; }
        .text-success { color:#00a65a; }
        .text-warning { color:#f39c12; }
        .text-primary { color:#0073b7; }

        .footer { margin-top:15px; padding-top:8px; border-top:1px solid #dee2e6;
                  font-size:9px; color:#888; text-align:center; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <h2>Bandwidth Sale — Invoice List</h2>
        <p>Generated: {{ now()->format('d M Y, h:i A') }} &nbsp;|&nbsp; Total: {{ count($invoices) }} invoices</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice No</th>
                <th>Customer</th>
                <th>Contact</th>
                <th class="text-center">Month</th>
                <th class="text-right">Sub Total</th>
                <th class="text-right">VAT</th>
                <th class="text-right">Invoice Total</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Grand Total</th>
                <th class="text-right">Received</th>
                <th class="text-right">Balance Due</th>
                <th class="text-center">Status</th>
                <th>Created By</th>
                <th class="text-center">Date</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSub=0; $totalVat=0; $totalGrand=0; $totalReceived=0; $totalDue=0; @endphp
            @foreach($invoices as $i => $inv)
            @php
                $invTotal = $inv->total_amount + $inv->vat_amount;
                $totalSub      += $inv->total_amount;
                $totalVat      += $inv->vat_amount;
                $totalGrand    += $inv->grand_total;
                $totalReceived += $inv->received_amount;
                $totalDue      += $inv->due_amount;
                $badgeClass = match($inv->status) {
                    'paid'    => 'badge-paid',
                    'partial' => 'badge-partial',
                    'overdue' => 'badge-overdue',
                    default   => 'badge-unpaid',
                };
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $inv->invoice_no }}</strong></td>
                <td>{{ $inv->bwsCustomer->customer_name ?? '—' }}</td>
                <td>{{ $inv->bwsCustomer->contact_person ?? '—' }}</td>
                <td class="text-center">
                    {{ \Carbon\Carbon::parse($inv->billing_month.'-01')->format('M Y') }}
                </td>
                <td class="text-right">{{ number_format($inv->total_amount, 2) }}</td>
                <td class="text-right">{{ number_format($inv->vat_amount, 2) }}</td>
                <td class="text-right"><strong>{{ number_format($invTotal, 2) }}</strong></td>
                <td class="text-right text-warning">
                    {{ $inv->discount > 0 ? '('.number_format($inv->discount, 2).')' : '—' }}
                </td>
                <td class="text-right font-weight-bold text-primary">
                    {{ number_format($inv->grand_total, 2) }}
                </td>
                <td class="text-right text-success">{{ number_format($inv->received_amount, 2) }}</td>
                <td class="text-right {{ $inv->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                    <strong>{{ number_format($inv->due_amount, 2) }}</strong>
                </td>
                <td class="text-center">
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($inv->status) }}</span>
                </td>
                <td>{{ $inv->createdBy->name ?? '—' }}</td>
                <td class="text-center">{{ optional($inv->created_at)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">Total</td>
                <td class="text-right">{{ number_format($totalSub, 2) }}</td>
                <td class="text-right">{{ number_format($totalVat, 2) }}</td>
                <td class="text-right">{{ number_format($totalSub + $totalVat, 2) }}</td>
                <td></td>
                <td class="text-right">{{ number_format($totalGrand, 2) }}</td>
                <td class="text-right text-success">{{ number_format($totalReceived, 2) }}</td>
                <td class="text-right text-danger">{{ number_format($totalDue, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ config('app.name') }} &nbsp;|&nbsp; Bandwidth Sale Invoice List &nbsp;|&nbsp;
        {{ now()->format('d M Y, h:i A') }}
    </div>

</div>
</body>
</html>
