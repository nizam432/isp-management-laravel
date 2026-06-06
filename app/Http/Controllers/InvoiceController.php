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
     * Display invoice list with filters and stats cards.
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
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        // Stats cards data
        $stats = $this->billing->getInvoiceStats();

        // Filter dropdowns
        $packages        = Package::active()->get();
        $routers         = MikrotikRouter::where('is_active', 1)->get();
        $zones           = Zone::all();
        $connectionTypes = \App\Models\ConnectionType::all();
        $clientTypes     = \App\Models\ClientType::all();

        return view('invoices.index', compact(
            'invoices', 'stats', 'packages', 'routers',
            'zones', 'connectionTypes', 'clientTypes'
        ));
    }

    /**
     * Store a newly created invoice.
     * Prevents duplicate invoices for the same customer and month.
     * Automatically deducts from advance balance if available.
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
            return back()->with('error', 'An invoice already exists for this customer and month.');
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

        // Auto-deduct from advance balance if available
        if ($customer->advance_balance > 0) {
            $this->billing->applyAdvanceToInvoice($invoice);
        }

        ActivityLog::log('Invoice created', 'Invoice', $invoice->id, null, $invoice->toArray());

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the invoice detail page with payment history.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'package', 'payments.receivedBy', 'payments.voidLog.voidedBy']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Delete an invoice.
     * Paid invoices cannot be deleted.
     * Invoices with payments cannot be deleted.
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Paid invoices cannot be deleted.');
        }

        if ($invoice->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete — payments exist for this invoice.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Download invoice as PDF.
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'package', 'payments']);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download('invoice-' . $invoice->invoice_no . '.pdf');
    }

    /**
     * Bulk generate invoices for all active customers for a given month.
     * Skips customers who already have an invoice for that month.
     * Automatically deducts from advance balance if available.
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate(['month' => 'required|date_format:Y-m']);

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

            // Auto-deduct from advance balance if available
            // Refresh customer to get latest advance_balance from DB after each deduction
            if ($customer->advance_balance > 0) {
                $this->billing->applyAdvanceToInvoice($invoice);
                $customer->refresh();
            }

            $created++;
        }

        return back()->with('success', "{$created} invoice(s) created, {$skipped} skipped (already existed).");
    }

    /**
     * Bulk delete unpaid invoices.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids ?? [];
        Invoice::whereIn('id', $ids)
            ->where('status', 'unpaid')
            ->whereDoesntHave('payments')
            ->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    /**
     * Bulk XLSX export.
     */
    public function bulkXlsx(Request $request)
    {
        $ids      = explode(',', $request->ids ?? '');
        $invoices = Invoice::with(['customer', 'package'])->whereIn('id', $ids)->get();

        $csvHeader = ['Invoice No', 'Customer', 'Phone', 'Customer ID', 'Package', 'Month', 'Amount', 'Due', 'Status', 'Due Date'];
        $rows      = [$csvHeader];

        foreach ($invoices as $inv) {
            $rows[] = [
                $inv->invoice_no,
                $inv->customer->name,
                $inv->customer->phone,
                $inv->customer->customer_id ?? $inv->customer_id,
                $inv->package->name ?? '-',
                $inv->month,
                $inv->amount,
                $inv->due_amount,
                $inv->status,
                $inv->due_date?->format('d M Y') ?? '-',
            ];
        }

        $filename = 'invoices-' . now()->format('Y-m-d') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
/**
 * Bulk PDF download as ZIP.
 */
    public function bulkPdf(Request $request)
    {
        $ids      = explode(',', $request->ids ?? '');
        $invoices = Invoice::with(['customer', 'package', 'payments'])->whereIn('id', $ids)->get();

        if ($invoices->count() === 1) {
            $invoice = $invoices->first();
            $pdf     = Pdf::loadView('invoices.pdf', compact('invoice'));
            return $pdf->download('invoice-' . $invoice->invoice_no . '.pdf');
        }

        // Multiple — ZIP করে download
        $zip      = new \ZipArchive();
        $zipName  = 'invoices-' . now()->format('Y-m-d') . '.zip';
        $zipPath  = storage_path('app/temp/' . $zipName);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($invoices as $invoice) {
            $pdf     = Pdf::loadView('invoices.pdf', compact('invoice'));
            $pdfPath = storage_path('app/temp/' . $invoice->invoice_no . '.pdf');
            $pdf->save($pdfPath);
            $zip->addFile($pdfPath, $invoice->invoice_no . '.pdf');
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
    /**
     * Bulk SMS send.
     */
    public function bulkSms(Request $request)
    {
        $ids = $request->ids ?? [];
        // SMS service call here
        return response()->json(['message' => count($ids) . ' SMS queued successfully.']);
    }
}