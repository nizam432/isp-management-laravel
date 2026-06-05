<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Package;
use App\Models\MikrotikRouter;
use App\Models\Zone;
use App\Models\ActivityLog;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function __construct(protected BillingService $billing) {}

    /**
     * Invoice list — filters + stats cards
     */
    public function index(Request $request)
    {
        $invoices = Invoice::with(['customer', 'package'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->month, fn($q) => $q->where('month', $request->month))
            ->when($request->package_id, fn($q) => $q->where('package_id', $request->package_id))
            ->when($request->router_id, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('router_id', $request->router_id)))
            ->when($request->zone_id, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('zone_id', $request->zone_id)))
            ->when($request->sub_zone_id, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('sub_zone_id', $request->sub_zone_id)))
            ->when($request->connection_type_id, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('connection_type_id', $request->connection_type_id)))
            ->when($request->client_type_id, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('client_type_id', $request->client_type_id)))
            ->when($request->search, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Stats cards data
        $stats   = $this->billing->getInvoiceStats();

        // Filter dropdowns
        $packages         = Package::active()->get();
        $routers          = MikrotikRouter::where('is_active', 1)->get();
        $zones            = Zone::all();
        $connectionTypes  = \App\Models\ConnectionType::all();
        $clientTypes      = \App\Models\ClientType::all();

        return view('invoices.index', compact(
            'invoices', 'stats', 'packages', 'routers',
            'zones', 'connectionTypes', 'clientTypes'
        ));
    }

    /**
     * New invoice — modal এ দেখাবে (AJAX)
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'month'       => 'required|date_format:Y-m',
            'amount'      => 'required|numeric|min:0',
            'due_date'    => 'nullable|date',
            'discount'    => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string',
        ]);

        $exists = Invoice::where('customer_id', $request->customer_id)
                         ->where('month', $request->month)
                         ->exists();

        if ($exists) {
            return back()->with('error', 'এই customer এর এই মাসে ইতিমধ্যে invoice আছে।');
        }

        $customer = Customer::find($request->customer_id);

        $invoice = Invoice::create([
            'invoice_no'  => Invoice::generateNumber(),
            'customer_id' => $request->customer_id,
            'package_id'  => $customer->package_id,
            'month'       => $request->month,
            'amount'      => $request->amount,
            'discount'    => $request->discount ?? 0,
            'due_amount'  => $request->amount - ($request->discount ?? 0),
            'due_date'    => $request->due_date,
            'notes'       => $request->notes,
            'status'      => 'unpaid',
        ]);

        // Advance balance থাকলে auto-deduct
        if ($customer->advance_balance > 0) {
            $this->billing->applyAdvanceToInvoice($invoice);
        }

        ActivityLog::log('Invoice created', 'Invoice', $invoice->id, null, $invoice->toArray());

        return redirect()->route('invoices.index')->with('success', 'Invoice তৈরি হয়েছে।');
    }

    /**
     * Invoice detail page
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'package', 'payments.receivedBy', 'payments.voidLog.voidedBy']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Delete — শুধু unpaid invoice
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Paid invoice delete করা যাবে না।');
        }

        if ($invoice->payments()->count() > 0) {
            return back()->with('error', 'Payment আছে — delete করা যাবে না।');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice delete হয়েছে।');
    }

    /**
     * PDF download
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'package', 'payments']);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download('invoice-' . $invoice->invoice_no . '.pdf');
    }

    /**
     * Bulk generate
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate(['month' => 'required|date_format:Y-m']);

        $customers = Customer::active()->with('package')->get();
        $created   = 0;
        $skipped   = 0;

        foreach ($customers as $customer) {
            $exists = Invoice::where('customer_id', $customer->id)
                             ->where('month', $request->month)
                             ->exists();

            if ($exists) { $skipped++; continue; }

            $invoice = Invoice::create([
                'invoice_no'  => Invoice::generateNumber(),
                'customer_id' => $customer->id,
                'package_id'  => $customer->package_id,
                'month'       => $request->month,
                'amount'      => $customer->package->price ?? 0,
                'due_amount'  => $customer->package->price ?? 0,
                'due_date'    => now()->endOfMonth(),
                'status'      => 'unpaid',
            ]);

            // Advance balance থাকলে auto-deduct
            if ($customer->advance_balance > 0) {
                $this->billing->applyAdvanceToInvoice($invoice);
            }

            $created++;
        }

        return back()->with('success', "{$created}টি invoice তৈরি হয়েছে, {$skipped}টি skip হয়েছে।");
    }
}