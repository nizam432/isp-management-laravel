<?php

namespace App\Services\Gateways;

use App\Models\PaymentGatewayTransaction;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * PayPal Orders API v2
 * Flow: OAuth Token → Create Order (get approve URL) → Customer approves → Capture Order
 */
class PaypalGateway
{
    private string $clientId;
    private string $clientSecret;
    private bool   $sandbox;
    private string $baseUrl;
    private string $tokenCacheKey;

    public function __construct(PaymentGatewaySetting $setting)
    {
        $this->clientId      = $setting->cfg('client_id',     '');
        $this->clientSecret  = $setting->cfg('client_secret', '');
        $this->sandbox       = $setting->sandbox;
        $this->baseUrl       = $this->sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $this->tokenCacheKey = 'paypal_token_' . md5($this->clientId);
    }

    private function getAccessToken(): string
    {
        return Cache::remember($this->tokenCacheKey, 28800, function () {
            $res = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()->timeout(20)
                ->post("{$this->baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

            if (!$res->successful() || empty($res->json('access_token'))) {
                throw new \Exception('PayPal token failed: ' . $res->body());
            }
            return $res->json('access_token');
        });
    }

    public function initiate(PaymentGatewayTransaction $txn, array $customer): array
    {
        $token = $this->getAccessToken();

        $res = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json', 'Prefer' => 'return=representation'])
            ->timeout(30)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent'              => 'CAPTURE',
                'purchase_units'      => [[
                    'reference_id' => $txn->txn_ref,
                    'description'  => 'ISP Internet Bill',
                    'custom_id'    => $txn->txn_ref,
                    'amount'       => [
                        'currency_code' => 'USD',
                        'value'         => number_format($txn->amount / 110, 2, '.', ''), // BDT→USD approx
                    ],
                ]],
                'application_context' => [
                    'brand_name'  => config('app.name', 'ISP Billing'),
                    'landing_page'=> 'LOGIN',
                    'user_action' => 'PAY_NOW',
                    'return_url'  => route('client.payment.callback', ['gateway' => 'paypal']),
                    'cancel_url'  => route('client.payment.cancel',   ['gateway' => 'paypal', 'ref' => $txn->txn_ref]),
                ],
            ]);

        if (!$res->successful()) {
            Cache::forget($this->tokenCacheKey);
            throw new \Exception('PayPal create order failed: ' . $res->body());
        }

        $order = $res->json();
        $txn->update(['gateway_txn_id' => $order['id']]);

        $approveUrl = collect($order['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;
        if (!$approveUrl) throw new \Exception('PayPal approve URL not found.');

        return ['redirect_url' => $approveUrl];
    }

    public function verify(Request $request, PaymentGatewayTransaction $txn): array
    {
        $orderId = $request->input('token') ?? $txn->gateway_txn_id;

        if (!$orderId) {
            return ['success' => false, 'gateway_txn_id' => '', 'message' => 'PayPal order ID missing.'];
        }

        try {
            $token = $this->getAccessToken();

            $res = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

            if (!$res->successful()) {
                return ['success' => false, 'gateway_txn_id' => $orderId, 'message' => 'PayPal capture failed: ' . $res->body()];
            }

            $capture = $res->json();

            if ($capture['status'] !== 'COMPLETED') {
                return ['success' => false, 'gateway_txn_id' => $orderId, 'message' => 'PayPal status: ' . $capture['status']];
            }

            $captureId = $capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? $orderId;

            return ['success' => true, 'gateway_txn_id' => $captureId, 'message' => 'PayPal payment captured.', 'raw' => $capture];

        } catch (\Exception $e) {
            Log::error('PayPal capture error: ' . $e->getMessage());
            return ['success' => false, 'gateway_txn_id' => $orderId, 'message' => $e->getMessage()];
        }
    }
}
