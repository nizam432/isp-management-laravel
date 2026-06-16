{{-- resources/views/client/payment/success.blade.php --}}
@extends('client.layout')
@section('title', 'Payment Successful')

@section('content')
<div style="max-width:480px; margin:40px auto;">
    <div class="card" style="text-align:center;">
        <div class="card-body" style="padding:40px 30px;">

            {{-- Animated checkmark --}}
            <div style="animation:pop .4s ease; margin-bottom:20px;">
                <div style="width:80px; height:80px; background:#28a745; border-radius:50%;
                            display:flex; align-items:center; justify-content:center; margin:0 auto;">
                    <i class="fas fa-check fa-2x" style="color:#fff;"></i>
                </div>
            </div>

            <h4 style="font-weight:700; color:#28a745; margin-bottom:6px;">Payment Successful!</h4>
            <p style="color:#888; margin-bottom:24px;">Thank you for paying your internet bill.</p>

            <div style="background:#f8f9fc; border-radius:10px; padding:16px; text-align:left; margin-bottom:24px;">
                <div style="display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #eef0f5; font-size:13px;">
                    <span style="color:#888;">Transaction Ref</span>
                    <span style="font-weight:600;">{{ $txn->txn_ref }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #eef0f5; font-size:13px;">
                    <span style="color:#888;">Gateway Txn ID</span>
                    <span>{{ $txn->gateway_txn_id ?? '—' }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #eef0f5; font-size:13px;">
                    <span style="color:#888;">Gateway</span>
                    <span style="background:#6c757d; color:#fff; border-radius:4px; padding:1px 8px; font-size:11px; font-weight:600;">
                        {{ strtoupper($txn->gateway) }}
                    </span>
                </div>
                <div style="display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #eef0f5; font-size:13px;">
                    <span style="color:#888;">Amount</span>
                    <span style="font-weight:700; color:#28a745; font-size:18px;">
                        Tk{{ number_format($txn->amount, 2) }}
                    </span>
                </div>
                <div style="display:flex; justify-content:space-between; padding:7px 0; font-size:13px;">
                    <span style="color:#888;">Paid At</span>
                    <span>{{ $txn->paid_at?->format('d M Y h:i A') }}</span>
                </div>
            </div>

            <a href="{{ route('client.dashboard') }}"
               style="display:block; padding:11px; background:#28a745; color:#fff; border-radius:8px; font-weight:600; text-decoration:none; margin-bottom:10px;">
                <i class="fas fa-home mr-2"></i> Go to Dashboard
            </a>
            <a href="{{ route('client.invoices') }}"
               style="display:block; padding:11px; background:#f8f9fc; color:#444; border-radius:8px; font-weight:600; text-decoration:none; border:1px solid #eef0f5;">
                <i class="fas fa-file-invoice mr-2"></i> View Invoices
            </a>
        </div>
    </div>
</div>

<style>
@keyframes pop {
    0%   { transform: scale(.5); opacity:0 }
    80%  { transform: scale(1.1) }
    100% { transform: scale(1);  opacity:1 }
}
</style>
@endsection
