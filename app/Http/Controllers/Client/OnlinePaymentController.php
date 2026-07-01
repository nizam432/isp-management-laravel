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

    // ── Initiate payment — redirect to gateway ───────────────────
    public function initiate(Request $request, Invoice $invoice)
    {
        $customer = Auth::guard('customer')->user();

        if ($invoice->customer_id !== $customer->id) abort(403);
        if ($invoice->status === 'paid') {
            return redirect()->route('client.invoices')->with('info', 'Invoice already paid.');
        }

        $request->validate(['gateway' => 'required|string|in:sslcommerz,amarpayz,bkash,nagad,stripe,paypal,razorpay,shurjopay']);

        $slug     = $request->gateway;
        $tenantId = PaymentGatewayService::tenantId();

        try {
            $result = PaymentGatewayService::initiate(
                tenantId:     $tenantId,
                slug:         $slug,
                customerId:   $customer->id,
                invoiceId:    $invoice->id,
                amount:       floatval($invoice->due_amount),
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
                ->with('error', 'Payment শুরু করতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    // ── Gateway success/callback ─────────────────────────────────
    public function success(Request $request, string $gateway)
    {
        $ref = $request->input('ref')         // sslcommerz, amarpayz
            ?? $request->input('tran_id')     // sslcommerz fallback
            ?? $request->input('mer_txnid');  // amarpayz fallback

        if (!$ref) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Invalid payment response.');
        }

        return $this->processCallback($request, $gateway, $ref);
    }

    // ── bKash / Nagad callback URL ───────────────────────────────
    public function callback(Request $request, string $gateway)
    {
        $ref = $request->input('merchantInvoiceNumber') // bKash
            ?? $request->input('ref');

        if (!$ref) {
            // bKash paymentID lookup
            $paymentId = $request->input('paymentID');
            if ($paymentId) {
                $txn = PaymentGatewayTransaction::where('gateway_txn_id', $paymentId)
                    ->where('gateway', $gateway)->first();
                if ($txn) $ref = $txn->txn_ref;
            }
        }

        if (!$ref) {
            // ShurjoPay & Nagad — order_id = sp_order_id stored as gateway_txn_id
            $orderId = $request->input('order_id');
            if ($orderId) {
                $txn = PaymentGatewayTransaction::where('gateway_txn_id', $orderId)
                    ->where('gateway', $gateway)->first();
                if ($txn) {
                    $ref = $txn->txn_ref;
                } else {
                    // order_id might be the txn_ref itself
                    $ref = $orderId;
                }
            }
        }

        if (!$ref) {
            return redirect()->route('client.dashboard')->with('error', 'Payment reference not found.');
        }

        return $this->processCallback($request, $gateway, $ref);
    }

    // ── Shared: verify & redirect ────────────────────────────────
    private function processCallback(Request $request, string $gateway, string $ref)
    {
        try {
            $result = PaymentGatewayService::verify($request, $gateway, $ref);

            if ($result['success']) {
                $txn = $result['txn'];
                // Redirect to no-auth success page
                return redirect()->route('client.payment.success-page', $txn->txn_ref)
                    ->with('success', 'Payment successful! Thank you.');
            } else {
                return redirect()->route('client.login')
                    ->with('error', 'Payment failed: ' . ($result['message'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error("Payment callback error [{$gateway}]: " . $e->getMessage());
            return redirect()->route('client.login')
                ->with('error', 'Could not verify payment. Please contact support.');
        }
    }

    // ── Fail / Cancel ────────────────────────────────────────────
    public function fail(Request $request, string $gateway)
    {
        $ref = $request->input('ref') ?? $request->input('tran_id') ?? $request->input('mer_txnid');
        if ($ref) {
            PaymentGatewayTransaction::where('txn_ref', $ref)
                ->where('status', 'pending')
                ->update(['status' => 'failed', 'gateway_response' => $request->all()]);
        }

        return redirect()->route('client.invoices')
            ->with('error', 'Payment ব্যর্থ হয়েছে। আবার চেষ্টা করুন।');
    }

    public function cancel(Request $request, string $gateway)
    {
        $ref = $request->input('ref') ?? $request->input('tran_id');
        if ($ref) {
            PaymentGatewayTransaction::where('txn_ref', $ref)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled', 'gateway_response' => $request->all()]);
        }

        return redirect()->route('client.invoices')
            ->with('info', 'Payment বাতিল করা হয়েছে।');
    }

    // ── Success confirmation page ─────────────────────────────────
    public function successPage(string $ref)
    {
        $txn = PaymentGatewayTransaction::where('txn_ref', $ref)
            ->where('status', 'success')
            ->with(['customer', 'invoice'])
            ->firstOrFail();

        // No auth check — SSLCommerz callback has no session
        // txn_ref is unguessable so this is safe
        return view('client.payment.success', compact('txn'));
    }

    // ── IPN endpoint (SSLCommerz, AmarPay) — no auth ────────────
    public function ipn(Request $request, string $gateway)
    {
        $ref = $request->input('tran_id')      // sslcommerz
            ?? $request->input('mer_txnid');   // amarpayz

        if (!$ref) return response('REF_MISSING', 400);

        try {
            $result = PaymentGatewayService::verify($request, $gateway, $ref);
            return response($result['success'] ? 'OK' : 'FAILED', 200);
        } catch (\Exception $e) {
            Log::error("IPN error [{$gateway}]: " . $e->getMessage());
            return response('ERROR', 500);
        }
    }
}
