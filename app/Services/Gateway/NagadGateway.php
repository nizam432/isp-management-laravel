<?php

namespace App\Services\Gateways;

use App\Models\PaymentGatewayTransaction;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Nagad Merchant API
 * Flow: Initialize Order → Complete Order (get redirectURL) → Verify via status API
 */
class NagadGateway
{
    private string $merchantId;
    private string $merchantNumber;
    private string $publicKey;
    private string $privateKey;
    private bool   $sandbox;
    private string $baseUrl;

    public function __construct(PaymentGatewaySetting $setting)
    {
        $this->merchantId     = $setting->cfg('merchant_id',     '');
        $this->merchantNumber = $setting->cfg('merchant_number', '');
        $this->publicKey      = $setting->cfg('public_key',      '');
        $this->privateKey     = $setting->cfg('private_key',     '');
        $this->sandbox        = $setting->sandbox;
        $this->baseUrl        = $this->sandbox
            ? 'https://apis.sandbox.mynagad.com/remote-payment-gateway-1.0/api/dfs'
            : 'https://api.mynagad.com/api/dfs';
    }

    private function encrypt(string $data): string
    {
        $pubKey = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($this->publicKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        openssl_public_encrypt($data, $encrypted, $pubKey, OPENSSL_PKCS1_PADDING);
        return base64_encode($encrypted);
    }

    private function sign(string $data): string
    {
        $privKey = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($this->privateKey, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        openssl_sign($data, $signature, $privKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    public function initiate(PaymentGatewayTransaction $txn, array $customer): array
    {
        $datetime  = now()->format('YmdHis');
        $orderId   = $txn->txn_ref;
        $challenge = bin2hex(random_bytes(16));

        $sensitivePayload = json_encode([
            'merchantId' => $this->merchantId,
            'datetime'   => $datetime,
            'orderId'    => $orderId,
            'challenge'  => $challenge,
        ]);

        // Step 1 — Initialize
        $initRes = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/check-out/initialize/{$this->merchantId}/{$orderId}", [
                'dateTime'      => $datetime,
                'sensitiveData' => $this->encrypt($sensitivePayload),
                'signature'     => $this->sign($sensitivePayload),
            ]);

        $initData = $initRes->json();
        if (empty($initData['sensitiveData'])) {
            throw new \Exception('Nagad initialize failed: ' . json_encode($initData));
        }

        // Step 2 — Complete
        $callbackUrl     = route('client.payment.callback', ['gateway' => 'nagad']);
        $completePayload = json_encode([
            'merchantId'   => $this->merchantId,
            'orderId'      => $orderId,
            'currencyCode' => '050',
            'amount'       => number_format($txn->amount, 2, '.', ''),
            'challenge'    => $initData['sensitiveData'],
        ]);

        $completeRes = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/check-out/complete/{$this->merchantId}/{$orderId}", [
                'sensitiveData'       => $this->encrypt($completePayload),
                'signature'           => $this->sign($completePayload),
                'merchantCallbackURL' => $callbackUrl,
            ]);

        $completeData = $completeRes->json();
        if (empty($completeData['callBackUrl'])) {
            throw new \Exception('Nagad complete failed: ' . json_encode($completeData));
        }

        return ['redirect_url' => $completeData['callBackUrl']];
    }

    public function verify(Request $request, PaymentGatewayTransaction $txn): array
    {
        $status     = $request->input('status');
        $nagadTxnId = $request->input('payment_ref_id', '');

        if ($status !== 'Success') {
            return ['success' => false, 'gateway_txn_id' => $nagadTxnId, 'message' => 'Nagad payment not successful.'];
        }

        $res  = Http::get("{$this->baseUrl}/verify/payment/{$nagadTxnId}");
        $data = $res->json();

        if (($data['status'] ?? '') !== 'Success') {
            return ['success' => false, 'gateway_txn_id' => $nagadTxnId, 'message' => 'Nagad verification failed.'];
        }

        if (floatval($data['amount'] ?? 0) < $txn->amount) {
            Log::warning("Nagad amount mismatch txn:{$txn->txn_ref}");
            return ['success' => false, 'gateway_txn_id' => $nagadTxnId, 'message' => 'Amount mismatch.'];
        }

        return ['success' => true, 'gateway_txn_id' => $nagadTxnId, 'message' => 'Nagad payment verified.', 'raw' => $data];
    }
}
