<?php

namespace App\Services\Gateways;

use App\Models\PaymentGatewayTransaction;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ShurjoPayGateway
{
    private string $username;
    private string $password;
    private string $prefix;
    private bool   $sandbox;
    private string $baseUrl;
    private string $tokenCacheKey;

    public function __construct(PaymentGatewaySetting $setting)
    {
        $this->username      = $setting->cfg('username', '');
        $this->password      = $setting->cfg('password', '');
        $this->prefix        = $setting->cfg('prefix',   'SP');
        $this->sandbox       = $setting->sandbox;
        $this->baseUrl       = $this->sandbox
            ? 'https://sandbox.shurjopayment.com'
            : 'https://engine.shurjopayment.com';
        $this->tokenCacheKey = 'shurjopay_token_' . md5($this->username);
    }

    // ── Step 1: Get Token — JSON body required ────────────────────
    private function getTokenData(): array
    {
        return Cache::remember($this->tokenCacheKey, 3300, function () {
            $res = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(20)
                ->post("{$this->baseUrl}/api/get_token", [
                    'username' => $this->username,
                    'password' => $this->password,
                ]);

            if (!$res->successful()) {
                throw new \Exception('ShurjoPay token request failed: ' . $res->body());
            }

            $data = $res->json();

            if (empty($data['token'])) {
                throw new \Exception('ShurjoPay token missing: ' . json_encode($data));
            }

            return [
                'token'       => $data['token'],
                'store_id'    => $data['store_id']    ?? '',
                'execute_url' => $data['execute_url'] ?? "{$this->baseUrl}/api/secret-pay",
            ];
        });
    }

    // ── Step 2: Make Payment ──────────────────────────────────────
    public function initiate(PaymentGatewayTransaction $txn, array $customer): array
    {
        $tokenData  = $this->getTokenData();
        $executeUrl = $tokenData['execute_url'];

        $res = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $tokenData['token'],
            ])
            ->timeout(30)
            ->post($executeUrl, [
                'prefix'            => $this->prefix,
                'token'             => $tokenData['token'],
                'store_id'          => $tokenData['store_id'],
                'order_id'          => $txn->txn_ref,
                'currency'          => 'BDT',
                'amount'            => number_format($txn->amount, 2, '.', ''),
                'discount_amount'   => 0,
                'disc_percent'      => 0,
                'client_ip'         => request()->ip() ?: '127.0.0.1',
                'customer_name'     => $customer['name']    ?? 'Customer',
                'customer_email'    => $customer['email']   ?? 'customer@isp.com',
                'customer_phone'    => $customer['phone']   ?? '01700000000',
                'customer_address'  => $customer['address'] ?? 'Dhaka',
                'customer_city'     => 'Dhaka',
                'customer_state'    => 'Dhaka',
                'customer_postcode' => '1200',
                'customer_country'  => 'Bangladesh',
                'value1'            => $txn->txn_ref,
                'value2'            => '',
                'value3'            => '',
                'value4'            => '',
                'return_url'        => route('client.payment.callback', ['gateway' => 'shurjopay']),
                'cancel_url'        => route('client.payment.cancel',   ['gateway' => 'shurjopay', 'ref' => $txn->txn_ref]),
            ]);

        if (!$res->successful()) {
            Cache::forget($this->tokenCacheKey);
            throw new \Exception('ShurjoPay make payment failed: ' . $res->body());
        }

        $data = $res->json();

        if (empty($data['checkout_url'])) {
            throw new \Exception('ShurjoPay checkout_url missing: ' . json_encode($data));
        }

        $txn->update(['gateway_txn_id' => $data['sp_order_id'] ?? $txn->txn_ref]);

        return ['redirect_url' => $data['checkout_url']];
    }

    // ── Step 3: Verify ────────────────────────────────────────────
    public function verify(Request $request, PaymentGatewayTransaction $txn): array
    {
        $orderId = $request->input('order_id') ?? $txn->gateway_txn_id;

        if (!$orderId) {
            return ['success' => false, 'gateway_txn_id' => '', 'message' => 'ShurjoPay order ID missing.'];
        }

        try {
            $tokenData = $this->getTokenData();

            $res = Http::withHeaders([
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $tokenData['token'],
                ])
                ->timeout(20)
                ->post("{$this->baseUrl}/api/verification", [
                    'order_id' => $orderId,
                ]);

            if (!$res->successful()) {
                return ['success' => false, 'gateway_txn_id' => $orderId, 'message' => 'ShurjoPay verification failed.'];
            }

            $data    = $res->json();
            $payment = is_array($data) ? ($data[0] ?? $data) : $data;
            $status  = strtolower($payment['transaction_status'] ?? $payment['sp_message'] ?? '');

            if (!in_array($status, ['success', 'completed', 'paid'])) {
                return ['success' => false, 'gateway_txn_id' => $orderId, 'message' => 'ShurjoPay status: ' . $status];
            }

            return [
                'success'        => true,
                'gateway_txn_id' => $payment['bank_trx_id'] ?? $orderId,
                'message'        => 'ShurjoPay payment verified.',
                'raw'            => $payment,
            ];

        } catch (\Exception $e) {
            Log::error('ShurjoPay verify error: ' . $e->getMessage());
            return ['success' => false, 'gateway_txn_id' => $orderId, 'message' => $e->getMessage()];
        }
    }
}
