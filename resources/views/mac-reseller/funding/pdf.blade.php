<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MAC Reseller Funding Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2 { margin: 0 0 4px 0; font-size: 16px; }
        .muted { color: #666; font-size: 9px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #333; color: #fff; font-size: 9px; text-transform: uppercase; }
        td { font-size: 9px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        tfoot td { font-weight: bold; background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>MAC Reseller Funding Report</h2>
    <div class="muted">Generated on {{ now()->format('d M, Y h:i A') }}</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Reseller</th>
                <th>Invoice No.</th>
                <th class="text-right">Fund Amt</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Due</th>
                <th>Funding Date</th>
                <th>Given By</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fundings as $i => $f)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $f->reseller?->business_name }}</td>
                <td>{{ $f->invoice_number }}</td>
                <td class="text-right">{{ number_format($f->fund_amount, 2) }}</td>
                <td class="text-right">{{ number_format($f->payment, 2) }}</td>
                <td class="text-right">{{ number_format($f->due_amount, 2) }}</td>
                <td>{{ $f->funding_date?->format('d/m/Y') }}</td>
                <td>{{ $f->fundGivenBy?->name }}</td>
                <td class="text-center">{{ ucfirst($f->transaction_status) }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center">No funding records found.</td></tr>
            @endforelse
        </tbody>
        @if($fundings->count())
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">{{ number_format($fundings->sum('fund_amount'), 2) }}</td>
                <td class="text-right">{{ number_format($fundings->sum('payment'), 2) }}</td>
                <td class="text-right">{{ number_format($fundings->sum('due_amount'), 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>
