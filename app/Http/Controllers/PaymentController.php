<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected BillingService $billing) {}

    /** Display paginated payment history with filters. */
    public function index(Request $request)
    {
        $payments = Payment::with(['invoice', 'customer', 'receivedBy', 'voidLog'])
            ->when($request->search, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")))
            ->when($request->method, fn($q) => $q->where('method', $request->method))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->received_by, fn($q) => $q->where('received_by', $request->received_by))
            ->when($request->date_from, fn($q) => $q->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('payment_date', '<=', $request->date_to))
            ->when($request->zone_id, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('zone_id', $request->zone_id)))
            ->latest()
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        $totalThisMonth  = Payment::active()->thisMonth()->sum('amount');
        $totalAllTime    = Payment::active()->sum('amount');
        $cashThisMonth   = Payment::active()->thisMonth()->where('method', 'cash')->sum('amount');
        $mobileThisMonth = Payment::active()->thisMonth()
            ->whereIn('method', ['bkash', 'nagad', 'rocket'])->sum('amount');

        $employees = User::role('employee')->get();
        $zones     = \App\Models\Zone::all();

        return view('payments.index', compact(
            'payments', 'totalThisMonth', 'totalAllTime',
            'cashThisMonth', 'mobileThisMonth', 'employees', 'zones'
        ));
    }

    /** Process payment for a specific invoice via the invoice list modal. */
    public function payInvoice(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount'                => 'required|numeric|min:1',
            'method'                => 'required|in:cash,bkash,nagad,rocket,card,bank,advance',
            'payment_date'          => 'required|date',
            'received_by'           => 'nullable|exists:users,id',
            'transaction_id'        => 'nullable|string|max:100',
            'discount'              => 'nullable|numeric|min:0',
            'remarks'               => 'nullable|string|max:255',
            'send_sms'              => 'nullable|boolean',
            'set_next_billing_date' => 'nullable|boolean',
        ]);

        $customer = $invoice->customer;

        $result = $this->billing->collectPayment($customer, $request->all());

        return redirect()->route('invoices.index')
            ->with('success', 'Payment সফল হয়েছে।');
    }

    /** Show the collect payment page for manual customer payment. */
    public function collectPage()
    {
        $employees = \App\Models\HR\Employee::select('id', 'name')->where('status', 'active')->get();
        return view('payments.collect', compact('employees'));
    }

    public function collectStore(Request $request)
    {
        $request->validate([
            'customer_id'           => 'required|exists:customers,id',
            'amount'                => 'required|numeric|min:1',
            'method'                => 'required|in:cash,bkash,nagad,rocket,card,bank',
            'payment_date'          => 'required|date',
            'received_by'           => 'nullable|exists:users,id',
            'transaction_id'        => 'nullable|string|max:100',
            'remarks'               => 'nullable|string|max:255',
            'send_sms'              => 'nullable|boolean',
            'set_next_billing_date' => 'nullable|boolean',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $result   = $this->billing->collectPayment($customer, $request->all());

        $msg = 'Payment সফল।';
        if ($result['advance_added'] > 0) {
            $msg .= ' ৳' . number_format($result['advance_added'], 2) . ' advance balance এ জমা হয়েছে।';
        }

        return redirect()->route('payments.collect')->with('success', $msg);
    }

    /** Return a customer's outstanding invoices and advance balance via AJAX. */
    public function customerDue(Customer $customer)
    {
        $due = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('due_amount');

        $invoices = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->orderBy('month', 'asc')
            ->get(['id', 'invoice_no', 'month', 'amount', 'due_amount', 'status']);

        return response()->json([
            'customer'        => $customer->load('package'),
            'total_due'       => floatval($due),
            'advance_balance' => floatval($customer->advance_balance),
            'invoices'        => $invoices,
        ]);
    }

    /** Void a payment and restore the amount to the customer's advance balance. */
    public function void(Request $request, Payment $payment)
    {
        if ($payment->isVoid()) {
            return back()->with('error', 'This payment has already been voided.');
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $this->billing->voidPayment($payment, $request->reason);

        return back()->with('success', 'Payment voided successfully. Amount has been added to advance balance.');
    }
}