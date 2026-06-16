<?php

namespace App\Services\Gateways;

use App\Models\PaymentGatewayTransaction;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Stripe Checkout Sessions API
 * Flow: Create Session (get URL) → Redirect → Verify via Retrieve Session
 */
class StripeGateway
{
    private string $secretKey;
    private string $publishableKey;
    private string $webhookSecret;
    private string $baseUrl = 'https://api.stripe.com/v1';

    public function __construct(PaymentGatewaySetting $setting)
    {
        $this->secretKey      = $setting->cfg('secret_key',      '');
        $this->publishableKey = $setting->cfg('publishable_key', '');
        $this->webhookSecret  = $setting->cfg('webhook_secret',  '');
    }

    public function initiate(PaymentGatewayTransaction $txn, array $customer): array
    {
        $amountInt = (int) round($txn->amount); // BDT = zero-decimal currency in Stripe

        $response = Http::withBasicAuth($this->secretKey, '')
            ->asForm()->timeout(30)
            ->post("{$this->baseUrl}/checkout/sessions", [
                'payment_method_types[]'                         => 'card',
                'line_items[0][price_data][currency]'            => 'bdt',
                'line_items[0][price_data][product_data][name]'  => 'Internet Bill Payment',
                'line_items[0][price_data][unit_amount]'         => $amountInt,
                'line_items[0][quantity]'                        => 1,
                'mode'                                           => 'payment',
                'client_reference_id'                            => $txn->txn_ref,
                'customer_email'                                 => $customer['email'] ?? '',
                'success_url'                                    => route('client.payment.success', ['gateway' => 'stripe', 'ref' => $txn->txn_ref]) . '&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'                                     => route('client.payment.cancel',  ['gateway' => 'stripe', 'ref' => $txn->txn_ref]),
                'metadata[txn_ref]'                              => $txn->txn_ref,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Stripe: ' . $response->json('error.message', 'API error.'));
        }

        $data = $response->json();
        $txn->update(['gateway_txn_id' => $data['id']]);

        return ['redirect_url' => $data['url']];
    }

    public function verify(Request $request, PaymentGatewayTransaction $txn): array
    {
        $sessionId = $request->input('session_id') ?? $txn->gateway_txn_id;

        if (!$sessionId) {
            return ['success' => false, 'gateway_txn_id' => '', 'message' => 'Stripe session ID not found.'];
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->timeout(30)
            ->get("{$this->baseUrl}/checkout/sessions/{$sessionId}");

        if (!$response->successful()) {
            return ['success' => false, 'gateway_txn_id' => $sessionId, 'message' => 'Stripe session retrieval failed.'];
        }

        $session = $response->json();

        if ($session['payment_status'] !== 'paid') {
            return ['success' => false, 'gateway_txn_id' => $sessionId, 'message' => 'Stripe status: ' . $session['payment_status']];
        }

        if (($session['amount_total'] ?? 0) < (int) round($txn->amount)) {
            Log::warning("Stripe amount mismatch txn:{$txn->txn_ref}");
            return ['success' => false, 'gateway_txn_id' => $sessionId, 'message' => 'Amount mismatch.'];
        }

        return ['success' => true, 'gateway_txn_id' => $session['payment_intent'] ?? $sessionId, 'message' => 'Stripe payment verified.', 'raw' => $session];
    }

    public function handleWebhook(Request $request): array
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        if (!$this->webhookSecret) {
            return ['success' => false, 'message' => 'Webhook secret not configured.'];
        }

        $parts = explode(',', $sigHeader);
        $timestamp = '';
        $signatures = [];
        foreach ($parts as $part) {
            if (str_starts_with($part, 't='))  $timestamp    = substr($part, 2);
            if (str_starts_with($part, 'v1=')) $signatures[] = substr($part, 3);
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$payload}", $this->webhookSecret);
        $valid    = false;
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) { $valid = true; break; }
        }

        if (!$valid) return ['success' => false, 'message' => 'Invalid webhook signature.'];

        $event = json_decode($payload, true);

        if ($event['type'] === 'checkout.session.completed') {
            $session = $event['data']['object'];
            return ['success' => true, 'txn_ref' => $session['client_reference_id'], 'gateway_txn_id' => $session['payment_intent'], 'raw' => $session];
        }

        return ['success' => false, 'message' => 'Unhandled event: ' . $event['type']];
    }
}
