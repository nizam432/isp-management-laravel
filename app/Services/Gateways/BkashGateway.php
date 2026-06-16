<?php

namespace App\Services\Gateways;

use App\Models\PaymentGatewayTransaction;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * bKash Checkout URL API v1.2.0-beta
 * Flow: Grant Token → Create Payment (get bkashURL) → Execute Payment
 */
class BkashGateway
{
    private string $appKey;
    private string $appSecret;
    private string $username;
    private string $password;
    private bool   $sandbox;
    private string $baseUrl;
    private string $tokenCacheKey;

    public function __construct(PaymentGatewaySetting $setting)
    {
        $this->appKey        = $setting->cfg('app_key',    '');
        $this->appSecret     = $setting->cfg('app_secret', '');
        $this->username      = $setting->cfg('username',   '');
        $this->password      = $setting->cfg('password',   '');
        $this->sandbox       = $setting->sandbox;
        $this->baseUrl       = $this->sandbox
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta';
        $this->tokenCacheKey = 'bkash_token_' . md5($this->appKey);
    }

    private function getToken(): string
    {
        return Cache::remember($this->tokenCacheKey, 3300, function () {
            $res = Http::withHeaders([
                'Content-Type' => 'application/json',
                'username'     => $this->username,
                'password'     => $this->password,
            ])->post("{$this->baseUrl}/tokenized/checkout/token/grant", [
                'app_key'    => $this->appKey,
                'app_secret' => $this->appSecret,
            ]);

            if (!$res->successful() || empty($res->json('id_token'))) {
                throw new \Exception('bKash token grant failed: ' . $res->body());
            }
            return $res->json('id_token');
        });
    }

    public function initiate(PaymentGatewayTransaction $txn, array $customer): array
    {
        $token = $this->getToken();

        $res = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'authorization' => $token,
            'x-app-key'     => $this->appKey,
        ])->post("{$this->baseUrl}/tokenized/checkout/create", [
            'mode'                  => '0011',
            'payerReference'        => (string) $txn->customer_id,
            'callbackURL'           => route('client.payment.callback', ['gateway' => 'bkash']),
            'amount'                => number_format($txn->amount, 2, '.', ''),
            'currency'              => 'BDT',
            'intent'                => 'sale',
            'merchantInvoiceNumber' => $txn->txn_ref,
        ]);

        $data = $res->json();

        if (($data['statusCode'] ?? '') !== '0000') {
            Cache::forget($this->tokenCacheKey);
            throw new \Exception($data['statusMessage'] ?? 'bKash create payment failed.');
        }

        $txn->update(['gateway_txn_id' => $data['paymentID']]);
        return ['redirect_url' => $data['bkashURL']];
    }

    public function verify(Request $request, PaymentGatewayTransaction $txn): array
    {
        $status    = $request->input('status');
        $paymentId = $request->input('paymentID') ?? $txn->gateway_txn_id;

        if ($status === 'cancel') return ['success' => false, 'gateway_txn_id' => $paymentId, 'message' => 'Payment cancelled.'];
        if ($status === 'failure') return ['success' => false, 'gateway_txn_id' => $paymentId, 'message' => 'Payment failed.'];

        try {
            $token = $this->getToken();

            $res = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'authorization' => $token,
                'x-app-key'     => $this->appKey,
            ])->post("{$this->baseUrl}/tokenized/checkout/execute", [
                'paymentID' => $paymentId,
            ]);

            $data = $res->json();

            if (($data['statusCode'] ?? '') !== '0000') {
                return ['success' => false, 'gateway_txn_id' => $paymentId, 'message' => $data['statusMessage'] ?? 'bKash execute failed.'];
            }

            if (floatval($data['amount'] ?? 0) < $txn->amount) {
                Log::warning("bKash amount mismatch txn:{$txn->txn_ref}");
                return ['success' => false, 'gateway_txn_id' => $paymentId, 'message' => 'Amount mismatch.'];
            }

            return ['success' => true, 'gateway_txn_id' => $data['trxID'] ?? $paymentId, 'message' => 'bKash payment successful.', 'raw' => $data];

        } catch (\Exception $e) {
            Log::error('bKash execute error: ' . $e->getMessage());
            return ['success' => false, 'gateway_txn_id' => $paymentId, 'message' => $e->getMessage()];
        }
    }
}
