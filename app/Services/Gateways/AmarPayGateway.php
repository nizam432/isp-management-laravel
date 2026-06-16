<?php

namespace App\Services\Gateways;

use App\Models\PaymentGatewayTransaction;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AmarPay (aamarpay) Payment Gateway
 * Flow: POST to index.php (get payment_url) → Redirect → Verify via trxcheck API
 */
class AmarPayGateway
{
    private string $appId;
    private string $appKey;
    private bool   $sandbox;
    private string $baseUrl;

    public function __construct(PaymentGatewaySetting $setting)
    {
        $this->appId   = $setting->cfg('app_id',  '');
        $this->appKey  = $setting->cfg('app_key', '');
        $this->sandbox = $setting->sandbox;
        $this->baseUrl = $this->sandbox
            ? 'https://sandbox.aamarpay.com'
            : 'https://secure.aamarpay.com';
    }

    public function initiate(PaymentGatewayTransaction $txn, array $customer): array
    {
        $response = Http::asForm()->timeout(30)
            ->post("{$this->baseUrl}/index.php", [
                'store_id'      => $this->appId,
                'tran_id'       => $txn->txn_ref,
                'success_url'   => route('client.payment.success', ['gateway' => 'amarpayz', 'ref' => $txn->txn_ref]),
                'fail_url'      => route('client.payment.fail',    ['gateway' => 'amarpayz', 'ref' => $txn->txn_ref]),
                'cancel_url'    => route('client.payment.cancel',  ['gateway' => 'amarpayz', 'ref' => $txn->txn_ref]),
                'amount'        => number_format($txn->amount, 2, '.', ''),
                'currency'      => 'BDT',
                'signature_key' => $this->appKey,
                'desc'          => 'Internet Bill Payment',
                'cus_name'      => $customer['name']    ?? 'Customer',
                'cus_email'     => $customer['email']   ?? 'customer@isp.com',
                'cus_phone'     => $customer['phone']   ?? '01700000000',
                'cus_add1'      => $customer['address'] ?? 'Bangladesh',
                'cus_city'      => 'Dhaka',
                'cus_country'   => 'BD',
                'type'          => 'json',
            ]);

        if (!$response->successful()) {
            throw new \Exception('AmarPay API connection failed.');
        }

        $data = $response->json();

        if (empty($data['payment_url'])) {
            throw new \Exception($data['error'] ?? 'AmarPay initiation failed.');
        }

        return ['redirect_url' => $this->baseUrl . $data['payment_url']];
    }

    public function verify(Request $request, PaymentGatewayTransaction $txn): array
    {
        $status     = $request->input('pay_status');
        $merTxnId   = $request->input('mer_txnid') ?? $txn->txn_ref;

        if ($status !== 'Successful') {
            return ['success' => false, 'gateway_txn_id' => '', 'message' => 'AmarPay payment not successful. Status: ' . $status];
        }

        $validateUrl = $this->sandbox
            ? 'https://sandbox.aamarpay.com/api/v1/trxcheck/request.php'
            : 'https://secure.aamarpay.com/api/v1/trxcheck/request.php';

        $res  = Http::get($validateUrl, ['request_id' => $merTxnId, 'store_id' => $this->appId, 'signature_key' => $this->appKey, 'type' => 'json']);
        $data = $res->json();

        if (($data['pay_status'] ?? '') !== 'Successful') {
            return ['success' => false, 'gateway_txn_id' => $merTxnId, 'message' => 'AmarPay validation failed.'];
        }

        if (floatval($data['amount'] ?? 0) < $txn->amount) {
            Log::warning("AmarPay amount mismatch txn:{$txn->txn_ref}");
            return ['success' => false, 'gateway_txn_id' => $merTxnId, 'message' => 'Amount mismatch.'];
        }

        return ['success' => true, 'gateway_txn_id' => $data['pg_txnid'] ?? $merTxnId, 'message' => 'AmarPay payment verified.', 'raw' => $data];
    }
}
