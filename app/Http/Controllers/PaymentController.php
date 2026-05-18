<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\AgentCommission;
use App\Models\ActivityLog;
use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['customer', 'invoice', 'receivedBy'])
            ->when($request->method, fn($q) => $q->where('method', $request->method))
            ->when($request->date,   fn($q) => $q->whereDate('paid_at', $request->date))
            ->when($request->search, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('name',  'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")))
            ->latest('paid_at')
            ->paginate(20);

        $todayTotal = Payment::today()->sum('amount');
        $monthTotal = Payment::thisMonth()->sum('amount');

        return view('payments.index', compact('payments', 'todayTotal', 'monthTotal'));
    }

    /**
     * Payment record করো।
     * পুরো বিল দিলে → customer automatically restore হবে MikroTik এ।
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id'     => 'required|exists:invoices,id',
            'amount'         => 'required|numeric|min:1',
            'method'         => 'required|in:cash,bkash,nagad,rocket,card,bank',
            'transaction_id' => 'nullable|string|max:100',
            'paid_at'        => 'required|date',
            'remarks'        => 'nullable|string|max:255',
        ]);

        $invoice = Invoice::with('customer.agent')->findOrFail($request->invoice_id);

        // Payment save করো
        $payment = Payment::create([
            'invoice_id'     => $invoice->id,
            'customer_id'    => $invoice->customer_id,
            'amount'         => $request->amount,
            'method'         => $request->method,
            'transaction_id' => $request->transaction_id,
            'paid_at'        => $request->paid_at,
            'received_by'    => auth()->id(),
            'remarks'        => $request->remarks,
        ]);

        // Invoice status recalculate
        $totalPaid = $invoice->payments()->sum('amount');

        if ($totalPaid >= $invoice->amount) {
            $invoice->update(['status' => 'paid', 'due_amount' => 0]);

            // ── Auto-Restore MikroTik ──────────────────────────
            // পুরো বিল দিয়েছে → suspended হলে restore করো
            $customer = $invoice->customer;
            if (in_array($customer->mikrotik_status, ['suspended', 'disabled'])) {
                $this->restoreCustomerOnMikrotik($customer);
            }
            // ──────────────────────────────────────────────────

        } else {
            $invoice->update([
                'status'     => 'partial',
                'due_amount' => $invoice->amount - $totalPaid,
            ]);
        }

        // Agent commission calculate
        if ($invoice->customer->agent && $invoice->customer->agent->commission_rate > 0) {
            AgentCommission::create([
                'agent_id'   => $invoice->customer->agent_id,
                'payment_id' => $payment->id,
                'amount'     => round(
                    $payment->amount * $invoice->customer->agent->commission_rate / 100, 2
                ),
                'status'     => 'pending',
            ]);
        }

        ActivityLog::log('Payment received', 'Payment', $payment->id, null, $payment->toArray());

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
        $old     = $payment->toArray();

        $payment->commission()->delete();
        $payment->delete();

        $totalPaid = $invoice->payments()->sum('amount');

        if ($totalPaid <= 0) {
            $invoice->update(['status' => 'unpaid', 'due_amount' => $invoice->amount]);
        } else {
            $invoice->update([
                'status'     => 'partial',
                'due_amount' => $invoice->amount - $totalPaid,
            ]);
        }

        ActivityLog::log('Payment deleted', 'Payment', $old['id'], $old, null);

        return back()->with('success', 'Payment deleted successfully.');
    }

    // ══════════════════════════════════════════════
    // Private Helper
    // ══════════════════════════════════════════════

    private function restoreCustomerOnMikrotik($customer): void
    {
        try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if (!$router) return;

            $mikrotik = new MikrotikService();
            $mikrotik->withRouter($router, fn($m) => $m->restoreCustomer($customer));

            $customer->update([
                'status'          => 'active',
                'mikrotik_status' => 'active',
            ]);

            Log::info("Auto-restored customer {$customer->customer_code} after payment.");
        } catch (\Exception $e) {
            Log::warning("MikroTik auto-restore failed for {$customer->customer_code}: " . $e->getMessage());
        }
    }
}
