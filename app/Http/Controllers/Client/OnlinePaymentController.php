<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayTransaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OnlinePaymentController extends Controller
{
    // ── Show payment method selection page ───────────────────────
    public function selectGateway(Invoice $invoice)
    {
        $customer = Auth::guard('customer')->user();

        if ($invoice->customer_id !== $customer->id) {
            abort(403, 'Unauthorized.');
        }

        if ($invoice->status === 'paid') {
            return redirect()->route('client.invoices')
                ->with('info', 'This invoice is already paid.');
        }

        $tenantId     = PaymentGatewayService::tenantId();
        $enabledSlugs = PaymentGatewayService::activeGateways($tenantId);
        $gateways     = PaymentGateway::enabled()->whereIn('slug', $enabledSlugs)->get();

        if ($gateways->isEmpty()) {
            return redirect()->route('client.invoices')
                ->with('error', 'No online payment gateway is active. Please contact admin.');
        }

        // pay_all=1 হলে customer এর সব unpaid invoices এর total due
        $payAll = request('pay_all') == '1';
        if ($payAll) {
            $allUnpaid   = Invoice::where('customer_id', $customer->id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->get();
            $totalDue    = $allUnpaid->sum('due_amount');
            $unpaidCount = $allUnpaid->count();
        } else {
            $totalDue    = $invoice->due_amount;
            $unpaidCount = 1;
        }

        return view('client.payment.select-gateway', compact(
            'invoice', 'gateways', 'customer', 'payAll', 'totalDue', 'unpaidCount'
        ));
    }

    // ── Initiate payment ─────────────────────────────────────────
    public function initiate(Request $request, Invoice $invoice)
    {
        $customer = Auth::guard('customer')->user();

        if ($invoice->customer_id !== $customer->id) abort(403);
        if ($invoice->status === 'paid') {
            return redirect()->route('client.invoices')->with('info', 'Invoice already paid.');
        }

        $request->validate([
            'gateway' => 'required|string|in:sslcommerz,amarpayz,bkash,nagad,stripe,paypal,razorpay',
            'amount'  => 'nullable|numeric|min:1',
        ]);

        $slug     = $request->gateway;
        $payAll   = $request->pay_all == '1';
        $tenantId = PaymentGatewayService::tenantId();

        // Amount determine
        $allowPartial = \App\Models\Setting::get('allow_partial_payment', '0') == '1';

        if ($payAll) {
            // সব unpaid invoice এর total
            $totalDue = Invoice::where('customer_id', $customer->id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->sum('due_amount');
            $amount = $allowPartial && $request->amount
                ? min(floatval($request->amount), $totalDue)
                : $totalDue;
        } else {
            $amount = $allowPartial && $request->amount
                ? min(floatval($request->amount), floatval($invoice->due_amount))
                : floatval($invoice->due_amount);
        }

        try {
            $result = PaymentGatewayService::initiate(
                tenantId:     $tenantId,
                slug:         $slug,
                customerId:   $customer->id,
                invoiceId:    $invoice->id,
                amount:       $amount,
                customerData: [
                    'name'    => $customer->name,
                    'email'   => $customer->email   ?? 'customer@isp.com',
                    'phone'   => $customer->phone   ?? $customer->mobile ?? '01700000000',
                    'address' => $customer->address ?? 'Bangladesh',
                ]
            );

            return redirect($result['redirect_url']);

        } catch (\Exception $e) {
            Log::error("Payment initiate error [{$slug}]: " . $e->getMessage());
            return redirect()->route('client.payment.select', $invoice->id)
                ->with('error', 'Payment could not be initiated: ' . $e->getMessage());
        }
    }

    // ── SSLCommerz / AmarPay success callback ────────────────────
    public function success(Request $request, string $gateway)
    {
        $ref = $request->input('ref')
            ?? $request->input('tran_id')
            ?? $request->input('mer_txnid')
            ?? $request->input('session_id'); // stripe fallback key check

        // Stripe uses session_id — ref is passed in URL query
        if (!$ref && $gateway === 'stripe') {
            $ref = $request->input('ref');
        }

        if (!$ref) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Invalid payment response.');
        }

        return $this->processCallback($request, $gateway, $ref);
    }

    // ── bKash / Nagad / Razorpay / Stripe callback ───────────────
    public function callback(Request $request, string $gateway)
    {
        $ref = $request->input('merchantInvoiceNumber') // bKash
            ?? $request->input('order_id')              // Nagad
            ?? $request->input('razorpay_payment_link_reference_id') // Razorpay
            ?? $request->input('ref');

        if (!$ref) {
            $paymentId = $request->input('paymentID'); // bKash fallback
            if ($paymentId) {
                $txn = PaymentGatewayTransaction::where('gateway_txn_id', $paymentId)
                    ->where('gateway', $gateway)->first();
                if ($txn) $ref = $txn->txn_ref;
            }
        }

        // Razorpay: reference_id is the txn_ref we set
        if (!$ref && $gateway === 'razorpay') {
            $ref = $request->input('razorpay_payment_link_reference_id');
        }

        if (!$ref) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Payment reference not found.');
        }

        return $this->processCallback($request, $gateway, $ref);
    }

    // ── PayPal return callback ────────────────────────────────────
    // PayPal redirects to callback with ?token=ORDER_ID
    // We find txn by gateway_txn_id (order ID stored at initiate)

    // ── Shared: verify & redirect ────────────────────────────────
    private function processCallback(Request $request, string $gateway, string $ref)
    {
        try {
            $result = PaymentGatewayService::verify($request, $gateway, $ref);

            if ($result['success']) {
                $txn = $result['txn'];
                return redirect()->route('client.payment.success-page', $txn->txn_ref)
                    ->with('success', 'Payment successful! Thank you.');
            } else {
                return redirect()->route('client.invoices')
                    ->with('error', 'Payment failed: ' . ($result['message'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error("Payment callback error [{$gateway}]: " . $e->getMessage());
            return redirect()->route('client.invoices')
                ->with('error', 'Could not verify payment. Please contact support.');
        }
    }

    // ── Fail ─────────────────────────────────────────────────────
    public function fail(Request $request, string $gateway)
    {
        $ref = $request->input('ref')
            ?? $request->input('tran_id')
            ?? $request->input('mer_txnid');

        if ($ref) {
            PaymentGatewayTransaction::where('txn_ref', $ref)
                ->where('status', 'pending')
                ->update(['status' => 'failed', 'gateway_response' => $request->all()]);
        }

        return redirect()->route('client.invoices')
            ->with('error', 'Payment failed. Please try again.');
    }

    // ── Cancel ───────────────────────────────────────────────────
    public function cancel(Request $request, string $gateway)
    {
        $ref = $request->input('ref')
            ?? $request->input('tran_id')
            ?? $request->input('token'); // PayPal

        if ($ref) {
            PaymentGatewayTransaction::where('txn_ref', $ref)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled', 'gateway_response' => $request->all()]);
        }

        return redirect()->route('client.invoices')
            ->with('info', 'Payment was cancelled.');
    }

    // ── Success page ─────────────────────────────────────────────
    public function successPage(string $ref)
    {
        $txn = PaymentGatewayTransaction::where('txn_ref', $ref)
            ->where('status', 'success')
            ->with(['customer', 'invoice'])
            ->firstOrFail();

        $customer = Auth::guard('customer')->user();
        if ($txn->customer_id !== $customer->id) abort(403);

        return view('client.payment.success', compact('txn'));
    }

    // ── IPN — server-to-server ───────────────────────────────────
    public function ipn(Request $request, string $gateway)
    {
        $ref = $request->input('tran_id')    // sslcommerz
            ?? $request->input('mer_txnid'); // amarpayz

        if (!$ref) return response('REF_MISSING', 400);

        try {
            $result = PaymentGatewayService::verify($request, $gateway, $ref);
            return response($result['success'] ? 'OK' : 'FAILED', 200);
        } catch (\Exception $e) {
            Log::error("IPN error [{$gateway}]: " . $e->getMessage());
            return response('ERROR', 500);
        }
    }

    // ── Stripe Webhook ───────────────────────────────────────────
    public function stripeWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        $txnRef  = $payload['data']['object']['client_reference_id'] ?? null;

        if (!$txnRef) return response('REF_MISSING', 400);

        $txn = PaymentGatewayTransaction::where('txn_ref', $txnRef)
            ->where('gateway', 'stripe')->first();

        if (!$txn) return response('TXN_NOT_FOUND', 404);

        try {
            $result = PaymentGatewayService::stripeWebhook($request, $txn->tenant_id);
            return response($result['success'] ? 'OK' : 'FAILED', 200);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response('ERROR', 500);
        }
    }
}
