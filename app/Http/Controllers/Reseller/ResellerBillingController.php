<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MacResellerZone;
use App\Models\MacResellerTariffPackage;
use App\Services\BillingService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerBillingController extends Controller
{
    public function __construct(protected BillingService $billing) {}

    public function index(Request $request)
    {
        $resellerId  = Auth::guard('mac_reseller')->id();
        $customerIds = Customer::forReseller($resellerId)->pluck('id');

        $query = Invoice::with('customer')->whereIn('customer_id', $customerIds);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_no', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%")
                         ->orWhere('customer_code', 'like', "%{$s}%");
                  });
            });
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('package_id')) {
            $query->whereHas('customer', fn($q) => $q->where('mac_reseller_tariff_package_id', $request->package_id));
        }
        if ($request->filled('zone_id')) {
            $query->whereHas('customer', fn($q) => $q->where('mac_reseller_zone_id', $request->zone_id));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage  = (int) $request->input('per_page', 20);
        $invoices = $query->latest()->paginate($perPage)->withQueryString();

        // Total due per customer (for the "Total Due" column, matches Admin's list)
        $totalDueMap = Invoice::whereIn('customer_id', $customerIds)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->selectRaw('customer_id, SUM(due_amount) as total')
            ->groupBy('customer_id')
            ->pluck('total', 'customer_id');

        $thisMonth = now()->format('Y-m');
        $lastMonth = now()->subMonthNoOverflow()->format('Y-m');

        $stats = [
            'paid_clients' => [
                'current' => Customer::forReseller($resellerId)->whereHas('invoices', fn($q) => $q->where('month', $thisMonth)->where('status', 'paid'))->count(),
                'last'    => Customer::forReseller($resellerId)->whereHas('invoices', fn($q) => $q->where('month', $lastMonth)->where('status', 'paid'))->count(),
            ],
            'unpaid_clients' => [
                'current' => Customer::forReseller($resellerId)->whereHas('invoices', fn($q) => $q->where('month', $thisMonth)->whereIn('status', ['unpaid', 'partial', 'overdue']))->count(),
                'last'    => Customer::forReseller($resellerId)->whereHas('invoices', fn($q) => $q->where('month', $lastMonth)->whereIn('status', ['unpaid', 'partial', 'overdue']))->count(),
            ],
            'received_bill' => [
                'current' => Invoice::whereIn('customer_id', $customerIds)->where('month', $thisMonth)->sum('amount') - Invoice::whereIn('customer_id', $customerIds)->where('month', $thisMonth)->sum('due_amount'),
                'last'    => Invoice::whereIn('customer_id', $customerIds)->where('month', $lastMonth)->sum('amount') - Invoice::whereIn('customer_id', $customerIds)->where('month', $lastMonth)->sum('due_amount'),
            ],
            'generated_bill' => [
                'current' => Invoice::whereIn('customer_id', $customerIds)->where('month', $thisMonth)->count(),
                'last'    => Invoice::whereIn('customer_id', $customerIds)->where('month', $lastMonth)->count(),
            ],
            'monthly_bill' => [
                'current' => Invoice::whereIn('customer_id', $customerIds)->where('month', $thisMonth)->sum('amount'),
                'last'    => Invoice::whereIn('customer_id', $customerIds)->where('month', $lastMonth)->sum('amount'),
            ],
            'total_due'      => Invoice::whereIn('customer_id', $customerIds)->whereIn('status', ['unpaid', 'partial', 'overdue'])->sum('due_amount'),
            'advance_amount' => Customer::forReseller($resellerId)->sum('advance_balance'),
        ];

        $paidNow   = $stats['paid_clients']['current'];
        $unpaidNow = $stats['unpaid_clients']['current'];
        $totalNow  = $paidNow + $unpaidNow;
        $stats['collection_rate'] = [
            'current' => $totalNow > 0 ? round(($paidNow / $totalNow) * 100) : 0,
            'last'    => 0,
        ];

        $zones    = MacResellerZone::forReseller($resellerId)->orderBy('name')->get();
        $packages = MacResellerTariffPackage::where('tariff_id', Auth::guard('mac_reseller')->user()->tariff_id)
            ->with('package')->get()->filter(fn($tp) => $tp->package);

        return view('reseller.billing.index', compact('invoices', 'stats', 'totalDueMap', 'zones', 'packages'));
    }

    public function show(Invoice $invoice)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        abort_unless(
            $invoice->customer && $invoice->customer->mac_reseller_id === $resellerId,
            403,
            'You do not have access to this invoice.'
        );

        $invoice->load(['customer.resellerTariffPackage.package', 'payments.receivedByReseller', 'payments.receivedBy']);

        [$currency, $vatPercent, $footerText] = $this->invoicePrintSettings($resellerId);

        return view('reseller.billing.show', compact('invoice', 'currency', 'vatPercent', 'footerText'));
    }

    public function pdf(Invoice $invoice)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        abort_unless(
            $invoice->customer && $invoice->customer->mac_reseller_id === $resellerId,
            403,
            'You do not have access to this invoice.'
        );

        $invoice->load(['customer.resellerTariffPackage.package', 'payments.receivedByReseller', 'payments.receivedBy']);

        [$currency, $vatPercent, $footerText] = $this->invoicePrintSettings($resellerId);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reseller.billing.pdf', compact('invoice', 'currency', 'vatPercent', 'footerText'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('invoice-' . $invoice->invoice_no . '.pdf');
    }

    /** Company/billing print settings from this reseller's own Settings page. */
    private function invoicePrintSettings(int $resellerId): array
    {
        $currency   = \App\Models\ResellerSetting::get($resellerId, 'currency', 'BDT');
        $vatPercent = (float) \App\Models\ResellerSetting::get($resellerId, 'vat_percentage', 0);
        $footerText = \App\Models\ResellerSetting::get($resellerId, 'invoice_footer_text', 'Thank you for your payment.');

        return [$currency, $vatPercent, $footerText];
    }

    public function create()
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $customers = Customer::forReseller($resellerId)
            ->orderBy('name')
            ->get(['id', 'name', 'customer_code', 'phone', 'monthly_bill_amount']);

        return view('reseller.billing.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'month'       => 'required|date_format:Y-m',
            'amount'      => 'required|numeric|min:0',
            'due_date'    => 'nullable|date',
            'discount'    => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string',
        ]);

        $customer = Customer::forReseller($resellerId)->findOrFail($data['customer_id']);

        $exists = Invoice::where('customer_id', $customer->id)->where('month', $data['month'])->exists();
        if ($exists) {
            $message = 'An invoice already exists for this client and month.';
            return $request->ajax() || $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withInput()->with('error', $message);
        }

        $dueDate = $data['due_date'] ?? Invoice::calculateDueDate();

        $invoice = Invoice::create([
            'invoice_no'   => Invoice::generateNumber(),
            'customer_id'  => $customer->id,
            'package_id'   => $customer->package_id,
            'month'        => $data['month'],
            'billing_type' => 'monthly',
            'amount'       => $data['amount'],
            'discount'     => $data['discount'] ?? 0,
            'due_amount'   => $data['amount'] - ($data['discount'] ?? 0),
            'due_date'     => $dueDate,
            'notes'        => $data['notes'] ?? null,
            'status'       => 'unpaid',
        ]);

        if ($customer->advance_balance > 0) {
            $this->billing->applyAdvanceToInvoice($invoice);
        }

        if (\App\Models\Setting::get('invoice_generated_sms', '1') == '1' && $customer->phone) {
            try {
                (new SmsService(macResellerId: $resellerId))->sendInvoiceGenerated(
                    $customer->phone,
                    $customer->name,
                    floatval($invoice->due_amount),
                    $invoice->month
                );
            } catch (\Exception $e) {
                \Log::error('Invoice generated SMS failed: ' . $e->getMessage());
            }
        }

        $message = "Invoice {$invoice->invoice_no} created successfully.";

        return $request->ajax() || $request->wantsJson()
            ? response()->json(['message' => $message, 'invoice_id' => $invoice->id])
            : redirect()->route('reseller.billing.index')->with('success', $message);
    }

    /** AJAX — total outstanding due + advance balance for a customer (used by the New Invoice modal). */
    public function customerDue(Customer $customer)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        abort_unless($customer->mac_reseller_id === $resellerId, 403);

        $totalDue = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('due_amount');

        return response()->json([
            'total_due'       => $totalDue,
            'advance_balance' => $customer->advance_balance,
        ]);
    }

    /** Generate invoices for every active reseller customer for the given month (monthly_bill_amount based). */
    public function bulkGenerate(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate(['month' => 'required|date_format:Y-m']);

        $customers = Customer::forReseller($resellerId)->active()->get();
        $created   = 0;
        $skipped   = 0;

        foreach ($customers as $customer) {
            $exists = Invoice::where('customer_id', $customer->id)->where('month', $data['month'])->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            $amount = (float) ($customer->monthly_bill_amount ?? 0);

            $invoice = Invoice::create([
                'invoice_no'   => Invoice::generateNumber(),
                'customer_id'  => $customer->id,
                'package_id'   => $customer->package_id,
                'month'        => $data['month'],
                'billing_type' => 'monthly',
                'amount'       => $amount,
                'discount'     => 0,
                'due_amount'   => $amount,
                'due_date'     => Invoice::calculateDueDate(),
                'status'       => 'unpaid',
            ]);

            if ($customer->advance_balance > 0) {
                $this->billing->applyAdvanceToInvoice($invoice);
            }

            $created++;
        }

        return redirect()->route('reseller.billing.index')
            ->with('success', "{$created} invoice(s) generated. {$skipped} skipped (already existed).");
    }

    /** Delete selected UNPAID invoices, restricted to this reseller's own customers. */
    public function bulkDelete(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate(['ids' => 'required|array']);

        $customerIds = Customer::forReseller($resellerId)->pluck('id');

        $deleted = Invoice::whereIn('id', $request->ids)
            ->whereIn('customer_id', $customerIds)
            ->where('status', 'unpaid')
            ->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }
}