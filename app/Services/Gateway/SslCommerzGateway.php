<?php

namespace App\Services\Gateways;

use App\Models\PaymentGatewayTransaction;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SSLCommerz Payment Gateway
 * Flow: Create session (get GatewayPageURL) → Redirect → Validate via API
 */
class SslCommerzGateway
{
    private string $storeId;
    private string $storePasswd;
    private bool   $sandbox;
    private string $baseUrl;

    public function __construct(PaymentGatewaySetting $setting)
    {
        $this->storeId     = $setting->cfg('store_id',     '');
        $this->storePasswd = $setting->cfg('store_passwd', '');
        $this->sandbox     = $setting->sandbox;
        $this->baseUrl     = $this->sandbox
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    public function initiate(PaymentGatewayTransaction $txn, array $customer): array
    {
        $response = Http::asForm()->timeout(30)
            ->post("{$this->baseUrl}/gwprocess/v4/api.php", [
                'store_id'         => $this->storeId,
                'store_passwd'     => $this->storePasswd,
                'total_amount'     => $txn->amount,
                'currency'         => 'BDT',
                'tran_id'          => $txn->txn_ref,
                'success_url'      => route('client.payment.success', ['gateway' => 'sslcommerz', 'ref' => $txn->txn_ref]),
                'fail_url'         => route('client.payment.fail',    ['gateway' => 'sslcommerz', 'ref' => $txn->txn_ref]),
                'cancel_url'       => route('client.payment.cancel',  ['gateway' => 'sslcommerz', 'ref' => $txn->txn_ref]),
                'ipn_url'          => route('client.payment.ipn',     ['gateway' => 'sslcommerz']),
                'cus_name'         => $customer['name']    ?? 'Customer',
                'cus_email'        => $customer['email']   ?? 'customer@isp.com',
                'cus_add1'         => $customer['address'] ?? 'Bangladesh',
                'cus_city'         => 'Dhaka',
                'cus_country'      => 'Bangladesh',
                'cus_phone'        => $customer['phone']   ?? '01700000000',
                'product_name'     => 'Internet Bill',
                'product_category' => 'ISP Service',
                'product_profile'  => 'non-physical-goods',
                'shipping_method'  => 'NO',
            ]);

        if (!$response->successful()) {
            throw new \Exception('SSLCommerz API connection failed.');
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'SUCCESS') {
            throw new \Exception($data['failedreason'] ?? 'SSLCommerz initiation failed.');
        }

        return ['redirect_url' => $data['GatewayPageURL']];
    }

    public function verify(Request $request, PaymentGatewayTransaction $txn): array
    {
        $valId  = $request->input('val_id');
        $status = $request->input('status');

        if ($status !== 'VALID' && $status !== 'VALIDATED') {
            return ['success' => false, 'gateway_txn_id' => '', 'message' => 'Payment not valid. Status: ' . $status];
        }

        $validateUrl = $this->sandbox
            ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
            : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php';

        $res  = Http::get($validateUrl, ['val_id' => $valId, 'store_id' => $this->storeId, 'store_passwd' => $this->storePasswd, 'v' => 1, 'format' => 'json']);
        $data = $res->json();

        if (($data['status'] ?? '') !== 'VALID') {
            return ['success' => false, 'gateway_txn_id' => $valId, 'message' => 'SSLCommerz validation failed.'];
        }

        if (floatval($data['currency_amount'] ?? 0) < $txn->amount) {
            Log::warning("SSLCommerz amount mismatch txn:{$txn->txn_ref}");
            return ['success' => false, 'gateway_txn_id' => $valId, 'message' => 'Amount mismatch.'];
        }

        return ['success' => true, 'gateway_txn_id' => $data['bank_tran_id'] ?? $valId, 'message' => 'SSLCommerz payment verified.', 'raw' => $data];
    }
}
