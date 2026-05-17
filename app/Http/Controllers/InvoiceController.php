<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a paginated list of invoices.
     * Supports filtering by status, month, and search keyword.
     */
    public function index(Request $request)
    {
        $invoices = Invoice::with(['customer', 'package'])
            // Filter by status: unpaid / paid / partial / overdue
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            // Filter by billing month e.g. 2025-01
            ->when($request->month, fn($q) => $q->where('month', $request->month))
            // Search by customer name or phone
            ->when($request->search, fn($q) => $q->whereHas('customer', fn($c) =>
                $c->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")))
            ->latest()
            ->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     * If customer_id is passed in the URL, that customer will be pre-selected.
     */
    public function create(Request $request)
    {
        $customers = Customer::active()->with('package')->get();

        // Pre-select customer if customer_id exists in query string
        $customer = $request->customer_id
            ? Customer::find($request->customer_id)
            : null;

        return view('invoices.create', compact('customers', 'customer'));
    }

    /**
     * Store a newly created invoice in the database.
     * Prevents duplicate invoices for the same customer and month.
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

        // Prevent duplicate invoice for same customer + month
        $exists = Invoice::where('customer_id', $request->customer_id)
                         ->where('month', $request->month)
                         ->exists();

        if ($exists) {
            return back()->with('error', 'An invoice for this customer already exists for the selected month.');
        }

        $customer = Customer::find($request->customer_id);
        $data     = $request->all();

        // Auto-generate invoice number e.g. INV-2025-0001
        $data['invoice_no'] = Invoice::generateNumber();

        // Set the customer's current package
        $data['package_id'] = $customer->package_id;

        // Calculate due amount after discount
        $data['due_amount'] = $request->amount - ($request->discount ?? 0);

        $invoice = Invoice::create($data);

        ActivityLog::log('Invoice created', 'Invoice', $invoice->id, null, $invoice->toArray());

        return redirect()->route('invoices.show', $invoice)
                         ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified invoice with payment history.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'package', 'payments.receivedBy']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Delete the specified invoice.
     * Will not delete if payments have been made against it.
     */
    public function destroy(Invoice $invoice)
    {
        // Prevent deletion if payments exist
        if ($invoice->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete — payments exist for this invoice.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
                         ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Download the invoice as a PDF file.
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'package', 'payments']);

        // Generate PDF from blade view
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));

        return $pdf->download('invoice-' . $invoice->invoice_no . '.pdf');
    }

    /**
     * Bulk generate invoices for all active customers for a given month.
     * Skips customers who already have an invoice for that month.
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $customers = Customer::active()->with('package')->get();
        $created   = 0;
        $skipped   = 0;

        foreach ($customers as $customer) {
            // Skip if invoice already exists for this customer and month
            $exists = Invoice::where('customer_id', $customer->id)
                             ->where('month', $request->month)
                             ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Invoice::create([
                'invoice_no'  => Invoice::generateNumber(),
                'customer_id' => $customer->id,
                'package_id'  => $customer->package_id,
                'month'       => $request->month,
                'amount'      => $customer->package->price ?? 0,
                'due_amount'  => $customer->package->price ?? 0,
                'due_date'    => now()->endOfMonth(),
                'status'      => 'unpaid',
            ]);

            $created++;
        }

        return back()->with('success',
            "{$created} invoice(s) created, {$skipped} skipped (already existed)."
        );
    }
}
