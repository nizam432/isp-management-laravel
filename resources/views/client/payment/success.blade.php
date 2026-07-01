<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #fff; border-radius: 16px; padding: 40px 32px; max-width: 480px; width: 100%; text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,.08); }
        .check-circle { width: 80px; height: 80px; background: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; animation: pop .4s ease; }
        .check-circle i { color: #fff; font-size: 32px; }
        h2 { color: #28a745; font-size: 22px; margin-bottom: 8px; }
        p { color: #888; font-size: 14px; margin-bottom: 24px; }
        .details { background: #f8f9fc; border-radius: 10px; padding: 16px; text-align: left; margin-bottom: 24px; }
        .detail-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #eef0f5; font-size: 13px; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #888; }
        .detail-value { font-weight: 600; color: #333; }
        .amount { color: #28a745; font-size: 20px; font-weight: 700; }
        .btn { display: inline-block; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; text-decoration: none; margin: 4px; }
        .btn-success { background: #28a745; color: #fff; }
        .btn-outline { background: #f8f9fc; color: #444; border: 1px solid #eef0f5; }
        @keyframes pop { 0% { transform: scale(.5); opacity: 0; } 80% { transform: scale(1.1); } 100% { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>
<div class="card">
    <div class="check-circle">
        <i class="fas fa-check"></i>
    </div>

    <h2>Payment Successful!</h2>
    <p>Thank you for paying your internet bill.</p>

    <div class="details">
        <div class="detail-row">
            <span class="detail-label">Transaction Ref</span>
            <span class="detail-value">{{ $txn->txn_ref }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Gateway TxnID</span>
            <span class="detail-value">{{ $txn->gateway_txn_id ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Gateway</span>
            <span class="detail-value">{{ strtoupper($txn->gateway) }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Amount</span>
            <span class="detail-value amount">Tk{{ number_format($txn->amount, 2) }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Paid At</span>
            <span class="detail-value">{{ $txn->paid_at?->format('d M Y h:i A') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Customer</span>
            <span class="detail-value">{{ $txn->customer->name ?? '—' }}</span>
        </div>
    </div>

    <a href="{{ route('client.dashboard') }}" class="btn btn-success">
        <i class="fas fa-home mr-1"></i> Go to Dashboard
    </a>
    <a href="{{ route('client.invoices') }}" class="btn btn-outline">
        <i class="fas fa-file-invoice mr-1"></i> View Invoices
    </a>
</div>
</body>
</html>
