<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ResellerEmployee;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerPaymentController extends Controller
{
    public function __construct(protected BillingService $billing) {}

    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $payments = Payment::with(['invoice', 'customer', 'receivedByReseller', 'voidLog'])
            ->whereHas('customer', fn($q) => $q->where('mac_reseller_id', $resellerId))
            ->when($request->search, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")))
            ->when($request->method, fn($q) => $q->where('method', $request->method))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->reseller_employee_id, fn($q) => $q->where('reseller_employee_id', $request->reseller_employee_id))
            ->when($request->date_from, fn($q) => $q->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('payment_date', '<=', $request->date_to))
            ->latest()
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        $totalThisMonth = Payment::active()->thisMonth()
            ->whereHas('customer', fn($q) => $q->where('mac_reseller_id', $resellerId))
            ->sum('amount');

        $totalAllTime = Payment::active()
            ->whereHas('customer', fn($q) => $q->where('mac_reseller_id', $resellerId))
            ->sum('amount');

        $employees = ResellerEmployee::where('mac_reseller_id', $resellerId)->where('is_active', true)->get(['id', 'name']);

        return view('reseller.payment.index', compact('payments', 'totalThisMonth', 'totalAllTime', 'employees'));
    }

    public function collectPage()
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $customers = Customer::forReseller($resellerId)->orderBy('name')->get(['id', 'name', 'customer_code', 'phone']);
        $employees = ResellerEmployee::where('mac_reseller_id', $resellerId)->where('is_active', true)->get(['id', 'name']);

        return view('reseller.payment.collect', compact('customers', 'employees'));
    }

    public function collectStore(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate([
            'customer_id'           => 'required|exists:customers,id',
            'amount'                => 'required|numeric|min:1',
            'method'                => 'required|in:cash,bkash,nagad,rocket,card,bank',
            'payment_date'          => 'required|date',
            'reseller_employee_id'  => 'nullable|exists:reseller_employees,id',
            'transaction_id'        => 'nullable|string|max:100',
            'remarks'               => 'nullable|string|max:255',
            'send_sms'              => 'nullable|boolean',
            'set_next_billing_date' => 'nullable|boolean',
        ]);

        // ownership checks
        $customer = Customer::forReseller($resellerId)->findOrFail($data['customer_id']);

        if (!empty($data['reseller_employee_id'])) {
            ResellerEmployee::where('mac_reseller_id', $resellerId)->findOrFail($data['reseller_employee_id']);
        }

        // track the high-water mark so we can tag exactly the payment row(s)
        // this call creates — collectPayment() may split one amount across
        // several invoices (FIFO), creating more than one Payment row.
        $beforeMaxId = Payment::max('id') ?? 0;

        $result = $this->billing->collectPayment($customer, $data);

        Payment::where('id', '>', $beforeMaxId)
            ->where('customer_id', $customer->id)
            ->update(['reseller_employee_id' => $data['reseller_employee_id'] ?? null]);

        $msg = 'Payment collected successfully.';
        if ($result['advance_added'] > 0) {
            $msg .= ' ৳' . number_format($result['advance_added'], 2) . ' added to advance balance.';
        }

        return redirect()->route('reseller.payment.collect')->with('success', $msg);
    }

    /** AJAX — a customer's outstanding invoices + advance balance, for the collect-payment form. */
    public function customerDue(Customer $customer)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        abort_unless($customer->mac_reseller_id === $resellerId, 403);

        $customer->load('resellerTariffPackage.package');

        $due = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('due_amount');

        $invoices = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->orderBy('month', 'asc')
            ->get(['id', 'invoice_no', 'month', 'amount', 'due_amount', 'status']);

        return response()->json([
            'total_due'       => floatval($due),
            'advance_balance' => floatval($customer->advance_balance),
            'invoices'        => $invoices,
            'customer'        => [
                'name'     => $customer->name,
                'phone'    => $customer->phone,
                'username' => $customer->pppoe_username ?? '-',
                'package'  => ['name' => $customer->resellerTariffPackage->package->name ?? '-'],
            ],
        ]);
    }

    public function void(Request $request, Payment $payment)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        abort_unless($payment->customer && $payment->customer->mac_reseller_id === $resellerId, 403);

        if ($payment->isVoid()) {
            return back()->with('error', 'This payment has already been voided.');
        }

        $request->validate(['reason' => 'required|string|max:255']);

        $this->billing->voidPayment($payment, $request->reason);

        return back()->with('success', 'Payment voided successfully. Amount added to advance balance.');
    }
}