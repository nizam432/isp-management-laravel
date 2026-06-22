<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2 { margin: 0 0 4px 0; font-size: 16px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
        th { background: #1f3864; color: #fff; }
        .text-right { text-align: right; }
        .text-success { color: #1e8449; }
        .text-danger  { color: #c0392b; }
        .row-income { background: #d5f5e3; font-weight: bold; }
        .row-expense { background: #fadbd8; font-weight: bold; }
        .row-profit { font-weight: bold; font-size: 13px; }
        .row-profit.profit { background: #27ae60; color: #fff; }
        .row-profit.loss   { background: #c0392b; color: #fff; }
    </style>
</head>
<body>
    <h2>Profit & Loss Report</h2>
    <div class="meta">Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y h:i A') }}</div>

    <table>
        <thead><tr><th>Item</th><th class="text-right">Amount (BDT)</th></tr></thead>
        <tbody>
            <tr class="row-income">
                <td>Monthly Bill (Payments)</td>
                <td class="text-right">৳ {{ number_format($paymentIncome, 2) }}</td>
            </tr>
            <tr class="row-income">
                <td>Other Manual Income</td>
                <td class="text-right">৳ {{ number_format($manualIncome, 2) }}</td>
            </tr>
            <tr style="background:#1e8449;color:#fff;font-weight:bold;">
                <td>TOTAL INCOME</td>
                <td class="text-right">৳ {{ number_format($totalIncome, 2) }}</td>
            </tr>
            <tr><td colspan="2"></td></tr>
            <tr class="row-expense">
                <td>TOTAL EXPENSE</td>
                <td class="text-right">৳ {{ number_format($totalExpense, 2) }}</td>
            </tr>
            <tr><td colspan="2"></td></tr>
            <tr class="row-profit {{ $netProfit >= 0 ? 'profit' : 'loss' }}">
                <td>NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}</td>
                <td class="text-right">৳ {{ number_format(abs($netProfit), 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
