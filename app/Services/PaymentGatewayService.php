<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewaySetting;
use App\Models\PaymentGatewayTransaction;
use App\Services\Gateways\BkashGateway;
use App\Services\Gateways\NagadGateway;
use App\Services\Gateways\SslCommerzGateway;
use App\Services\Gateways\AmarPayGateway;
use App\Services\Gateways\StripeGateway;
use App\Services\Gateways\PaypalGateway;
use App\Services\Gateways\RazorpayGateway;
use App\Services\Gateways\ShurjoPayGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentGatewayService
{
    // ── Single-tenant system — always 'default' ──────────────────
    public static function tenantId(): string
    {
        return 'default';
    }

    public static function activeGateways(string $tenantId): \Illuminate\Support\Collection
    {
        $enabledSlugs = PaymentGateway::enabled()->pluck('slug');
        return PaymentGatewaySetting::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereIn('gateway_slug', $enabledSlugs)
            ->pluck('gateway_slug');
    }

    public static function getSetting(string $tenantId, string $slug): ?PaymentGatewaySetting
    {
        return PaymentGatewaySetting::where('tenant_id', $tenantId)
            ->where('gateway_slug', $slug)->first();
    }

    public static function driver(string $slug, PaymentGatewaySetting $setting): mixed
    {
        return match ($slug) {
            'bkash'      => new BkashGateway($setting),
            'nagad'      => new NagadGateway($setting),
            'sslcommerz' => new SslCommerzGateway($setting),
            'amarpayz'   => new AmarPayGateway($setting),
            'stripe'     => new StripeGateway($setting),
            'paypal'     => new PaypalGateway($setting),
            'razorpay'   => new RazorpayGateway($setting),
            'shurjopay'  => new ShurjoPayGateway($setting),
            default      => throw new \Exception("Unknown gateway: {$slug}"),
        };
    }

    public static function initiate(string $tenantId, string $slug, int $customerId, ?int $invoiceId, float $amount, array $customerData): array
    {
        if (!PaymentGateway::where('slug', $slug)->where('is_enabled', true)->exists()) {
            throw new \Exception("Gateway '{$slug}' is not enabled by admin.");
        }

        $setting = static::getSetting($tenantId, $slug);
        if (!$setting || !$setting->is_active) {
            throw new \Exception("Gateway '{$slug}' is not configured for your account.");
        }

        $txn = PaymentGatewayTransaction::create([
            'txn_ref'     => PaymentGatewayTransaction::generateRef(),
            'tenant_id'   => $tenantId,
            'customer_id' => $customerId,
            'invoice_id'  => $invoiceId,
            'gateway'     => $slug,
            'amount'      => $amount,
            'currency'    => 'BDT',
            'status'      => 'pending',
            'payer_ip'    => request()->ip(),
        ]);

        return static::driver($slug, $setting)->initiate($txn, $customerData);
    }

    public static function verify(Request $request, string $slug, string $txnRef): array
    {
        $txn = PaymentGatewayTransaction::where('txn_ref', $txnRef)
            ->where('gateway', $slug)->firstOrFail();

        if ($txn->isSuccess()) {
            return ['success' => true, 'already_processed' => true, 'txn' => $txn];
        }

        $setting = static::getSetting($txn->tenant_id, $slug);
        if (!$setting) throw new \Exception("Gateway setting not found.");

        $result = static::driver($slug, $setting)->verify($request, $txn);

        DB::transaction(function () use ($txn, $result, $request) {
            if ($result['success']) {
                $txn->update([
                    'status'           => 'success',
                    'gateway_txn_id'   => $result['gateway_txn_id'],
                    'gateway_response' => $result['raw'] ?? [],
                    'paid_at'          => now(),
                ]);
                app(BillingService::class)->collectPayment($txn->customer, [
                    'amount'         => $txn->amount,
                    'method'         => match($txn->gateway) {
                        'sslcommerz' => 'ssl',
                        'amarpayz'   => 'amarpay',
                        'shurjopay'  => 'shurjopay',
                        default      => $txn->gateway,
                    },
                    'payment_date'   => now()->toDateString(),
                    'transaction_id' => $result['gateway_txn_id'],
                    'remarks'        => 'Online payment via ' . strtoupper($txn->gateway),
                    'send_sms'       => true,
                ]);
            } else {
                $txn->update([
                    'status'           => 'failed',
                    'gateway_response' => ['message' => $result['message'], 'request' => $request->all()],
                ]);
            }
        });

        return array_merge($result, ['txn' => $txn]);
    }

    public static function stripeWebhook(Request $request, string $tenantId): array
    {
        $setting = static::getSetting($tenantId, 'stripe');
        if (!$setting) return ['success' => false, 'message' => 'Stripe not configured.'];

        $result = (new StripeGateway($setting))->handleWebhook($request);

        if ($result['success'] && !empty($result['txn_ref'])) {
            $txn = PaymentGatewayTransaction::where('txn_ref', $result['txn_ref'])
                ->where('gateway', 'stripe')->where('status', 'pending')->first();

            if ($txn) {
                DB::transaction(function () use ($txn, $result) {
                    $txn->update(['status' => 'success', 'gateway_txn_id' => $result['gateway_txn_id'], 'gateway_response' => $result['raw'] ?? [], 'paid_at' => now()]);
                    app(BillingService::class)->collectPayment($txn->customer, [
                        'amount' => $txn->amount, 'method' => 'stripe',
                        'payment_date' => now()->toDateString(),
                        'transaction_id' => $result['gateway_txn_id'],
                        'remarks' => 'Online payment via Stripe (webhook)', 'send_sms' => true,
                    ]);
                });
            }
        }

        return $result;
    }
}
