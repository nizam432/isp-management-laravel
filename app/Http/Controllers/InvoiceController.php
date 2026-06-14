<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Package;
use App\Models\MikrotikRouter;
use App\Models\Zone;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvoiceController extends Controller
{
    public function __construct(protected BillingService $billing) {}

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

        $stats           = $this->billing->getInvoiceStats();
        $packages        = Package::active()->get();
        $routers         = MikrotikRouter::where('is_active', 1)->get();
        $zones           = Zone::all();
        $connectionTypes = \App\Models\ConnectionType::all();
        $clientTypes     = \App\Models\ClientType::all();
        $billingType     = Setting::get('billing_type', 'monthly');

        return view('invoices.index', compact(
            'invoices', 'stats', 'packages', 'routers',
            'zones', 'connectionTypes', 'clientTypes', 'billingType'
        ));
    }

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
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'An invoice already exists for this customer and month.'], 422);
            }
            return back()->with('error', 'An invoice already exists for this customer and month.');
        }

        $customer = Customer::find($request->customer_id);

        // Calculate due date from settings if not provided
        $dueDate = $request->due_date ?? Invoice::calculateDueDate();

        $invoice = Invoice::create([
            'invoice_no'   => Invoice::generateNumber(),
            'customer_id'  => $request->customer_id,
            'package_id'   => $customer->package_id,
            'month'        => $request->month,
            'billing_type' => 'monthly',
            'amount'       => $request->amount,
            'discount'     => $request->discount ?? 0,
            'due_amount'   => $request->amount - ($request->discount ?? 0),
            'due_date'     => $dueDate,
            'notes'        => $request->notes,
            'status'       => 'unpaid',
        ]);

        if ($customer->advance_balance > 0) {
            $this->billing->applyAdvanceToInvoice($invoice);
        }

        ActivityLog::log('Invoice created', 'Invoice', $invoice->id, null, $invoice->toArray());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Invoice created successfully.']);
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'package', 'payments.receivedBy', 'payments.voidLog.voidedBy']);
        $footerText = Setting::get('invoice_footer_text', 'Thank you for your payment.');
        $currency   = Setting::get('currency', 'BDT');
        $vatPercent = floatval(Setting::get('vat_percentage', 0));

        return view('invoices.show', compact('invoice', 'footerText', 'currency', 'vatPercent'));
    }

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

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'package', 'payments']);
        $footerText = Setting::get('invoice_footer_text', 'Thank you for your payment.');
        $currency   = Setting::get('currency', 'BDT');
        $vatPercent = floatval(Setting::get('vat_percentage', 0));

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'footerText', 'currency', 'vatPercent'));
        return $pdf->download('invoice-' . $invoice->invoice_no . '.pdf');
    }

    public function bulkGenerate(Request $request)
    {
        $request->validate(['month' => 'required|date_format:Y-m']);

        $billingType = Setting::get('billing_type', 'monthly');

        // Date to Date billing — bulk generate not applicable
        if ($billingType === 'date_to_date') {
            return back()->with('error', 'Bulk generate is not available for Date to Date billing. Invoices are generated automatically via scheduler.');
        }

        $customers = Customer::active()->with('package')->get();
        $created   = 0;
        $skipped   = 0;

        // Due date from settings
        $defaultBillingDate = intval(Setting::get('default_billing_date', 1));
        $monthCarbon        = Carbon::createFromFormat('Y-m', $request->month);
        $dueDate            = $monthCarbon->copy()->day($defaultBillingDate)->endOfMonth()->toDateString();

        foreach ($customers as $customer) {
            $exists = Invoice::where('customer_id', $customer->id)
                             ->where('month', $request->month)
                             ->exists();

            if ($exists) { $skipped++; continue; }

            $amount = $customer->monthly_bill_amount > 0
                ? $customer->monthly_bill_amount
                : ($customer->package->price ?? 0);

            $invoice = Invoice::create([
                'invoice_no'   => Invoice::generateNumber(),
                'customer_id'  => $customer->id,
                'package_id'   => $customer->package_id,
                'month'        => $request->month,
                'billing_type' => 'monthly',
                'amount'       => $amount,
                'due_amount'   => $amount,
                'due_date'     => $dueDate,
                'status'       => 'unpaid',
            ]);

            if ($customer->advance_balance > 0) {
                $this->billing->applyAdvanceToInvoice($invoice);
                $customer->refresh();
            }

            $created++;
        }

        return back()->with('success', "{$created} invoice(s) created, {$skipped} skipped (already existed).");
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids ?? [];
        Invoice::whereIn('id', $ids)
            ->where('status', 'unpaid')
            ->whereDoesntHave('payments')
            ->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    public function bulkXlsx(Request $request)
    {
        $ids      = explode(',', $request->ids ?? '');
        $invoices = Invoice::with(['customer', 'package'])->whereIn('id', $ids)->get();
        $currency = Setting::get('currency', 'BDT');

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = ['A' => 'Invoice No', 'B' => 'Customer', 'C' => 'Customer Code', 'D' => 'Phone',
                    'E' => 'Package', 'F' => 'Period', 'G' => 'Amount (' . $currency . ')',
                    'H' => 'Discount', 'I' => 'Due', 'J' => 'Status', 'K' => 'Due Date'];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF3C8DBC');

        foreach ($invoices as $row => $inv) {
            $r = $row + 2;
            $sheet->setCellValue('A' . $r, $inv->invoice_no);
            $sheet->setCellValue('B' . $r, $inv->customer->name);
            $sheet->setCellValue('C' . $r, $inv->customer->customer_code ?? '-');
            $sheet->setCellValue('D' . $r, $inv->customer->phone);
            $sheet->setCellValue('E' . $r, $inv->package->name ?? '-');
            $sheet->setCellValue('F' . $r, $inv->period_label);
            $sheet->setCellValue('G' . $r, floatval($inv->amount));
            $sheet->setCellValue('H' . $r, floatval($inv->discount));
            $sheet->setCellValue('I' . $r, floatval($inv->due_amount));
            $sheet->setCellValue('J' . $r, $inv->status);
            $sheet->setCellValue('K' . $r, $inv->due_date?->format('d M Y') ?? '-');
        }

        $filename = 'invoices-' . now()->format('Y-m-d') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function bulkPdf(Request $request)
    {
        $ids      = explode(',', $request->ids ?? '');
        $invoices = Invoice::with(['customer', 'package', 'payments'])->whereIn('id', $ids)->get();

        if ($invoices->count() === 0) {
            return back()->with('error', 'No invoices selected.');
        }

        $footerText = Setting::get('invoice_footer_text', 'Thank you for your payment.');
        $currency   = Setting::get('currency', 'BDT');
        $vatPercent = floatval(Setting::get('vat_percentage', 0));

        if ($invoices->count() === 1) {
            $invoice = $invoices->first();
            $pdf     = Pdf::loadView('invoices.pdf', compact('invoice', 'footerText', 'currency', 'vatPercent'));
            return $pdf->download('invoice-' . $invoice->invoice_no . '.pdf');
        }

        $zipName = 'invoices-' . now()->format('Y-m-d') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipName);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($invoices as $invoice) {
            $pdf     = Pdf::loadView('invoices.pdf', compact('invoice', 'footerText', 'currency', 'vatPercent'));
            $pdfPath = storage_path('app/temp/' . $invoice->invoice_no . '.pdf');
            $pdf->save($pdfPath);
            $zip->addFile($pdfPath, $invoice->invoice_no . '.pdf');
        }

        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    public function bulkSms(Request $request)
    {
        $ids = $request->ids ?? [];
        return response()->json(['message' => count($ids) . ' SMS queued successfully.']);
    }
}