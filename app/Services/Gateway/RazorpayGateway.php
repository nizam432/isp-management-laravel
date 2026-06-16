<?php

namespace App\Services\Gateways;

use App\Models\PaymentGatewayTransaction;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay Payment Links API
 * Flow: Create Payment Link (get short_url) → Redirect → Verify HMAC signature
 */
class RazorpayGateway
{
    private string $keyId;
    private string $keySecret;
    private string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct(PaymentGatewaySetting $setting)
    {
        $this->keyId     = $setting->cfg('key_id',     '');
        $this->keySecret = $setting->cfg('key_secret', '');
    }

    public function initiate(PaymentGatewayTransaction $txn, array $customer): array
    {
        $amountPaise = (int) round($txn->amount * 0.69 * 100); // BDT→INR→paise

        $res = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->timeout(30)
            ->post("{$this->baseUrl}/payment_links", [
                'amount'          => $amountPaise,
                'currency'        => 'INR',
                'accept_partial'  => false,
                'reference_id'    => $txn->txn_ref,
                'description'     => 'ISP Internet Bill Payment',
                'customer'        => [
                    'name'    => $customer['name']  ?? 'Customer',
                    'email'   => $customer['email'] ?? '',
                    'contact' => $customer['phone'] ?? '',
                ],
                'notify'          => ['sms' => false, 'email' => !empty($customer['email'])],
                'reminder_enable' => false,
                'callback_url'    => route('client.payment.callback', ['gateway' => 'razorpay']),
                'callback_method' => 'get',
                'notes'           => ['txn_ref' => $txn->txn_ref, 'customer_id' => (string) $txn->customer_id],
            ]);

        if (!$res->successful()) {
            throw new \Exception('Razorpay: ' . $res->json('error.description', 'API error.'));
        }

        $link = $res->json();
        $txn->update(['gateway_txn_id' => $link['id']]);

        return ['redirect_url' => $link['short_url']];
    }

    public function verify(Request $request, PaymentGatewayTransaction $txn): array
    {
        $paymentLinkId     = $request->input('razorpay_payment_link_id')             ?? $txn->gateway_txn_id;
        $paymentLinkRefId  = $request->input('razorpay_payment_link_reference_id')   ?? $txn->txn_ref;
        $paymentLinkStatus = $request->input('razorpay_payment_link_status');
        $razorpayPaymentId = $request->input('razorpay_payment_id',  '');
        $razorpaySignature = $request->input('razorpay_signature',   '');

        if ($paymentLinkStatus !== 'paid') {
            return ['success' => false, 'gateway_txn_id' => $razorpayPaymentId, 'message' => 'Razorpay status: ' . $paymentLinkStatus];
        }

        // HMAC-SHA256 signature verification
        $payload  = "{$paymentLinkId}|{$paymentLinkRefId}|{$paymentLinkStatus}|{$razorpayPaymentId}";
        $expected = hash_hmac('sha256', $payload, $this->keySecret);

        if (!hash_equals($expected, $razorpaySignature)) {
            Log::warning("Razorpay signature mismatch txn:{$txn->txn_ref}");
            return ['success' => false, 'gateway_txn_id' => $razorpayPaymentId, 'message' => 'Signature verification failed.'];
        }

        return ['success' => true, 'gateway_txn_id' => $razorpayPaymentId, 'message' => 'Razorpay payment verified.', 'raw' => $request->all()];
    }
}
