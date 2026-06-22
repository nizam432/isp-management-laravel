<?php
// ════════════════════════════════════════════
// app/Http/Controllers/Reports/BillCollectionReportController.php
//
// Tier 1 — Billing / Collection Reports
//   1. Renewal / Expiry Report
//   2. Aging Due Report
//   3. Daily Collection Report
//   4. Package-wise Revenue Report
//   5. Bill Receive History (multi-filter payment ledger)
// ════════════════════════════════════════════

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use App\Models\Zone;
use App\Models\SubZone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillCollectionReportController extends Controller
{
    /**
     * 1. Renewal / Expiry Report
     * Customers whose package expire_date falls within a chosen window
     * (today / next 3 days / next 7 days / custom range / already expired).
     */
    public function renewal(Request $request)
    {
        $range = $request->get('range', '7'); // today|3|7|expired|custom
        $today = Carbon::today();

        $query = Customer::query()
            ->with(['package', 'zone', 'subZone', 'agent', 'macReseller'])
            ->whereNotNull('expire_date')
            ->whereIn('status', ['active', 'expired']);

        switch ($range) {
            case 'today':
                $query->whereDate('expire_date', $today);
                break;
            case '3':
                $query->whereBetween('expire_date', [$today, $today->copy()->addDays(3)]);
                break;
            case 'expired':
                $query->whereDate('expire_date', '<', $today);
                break;
            case 'custom':
                $from = $request->get('from') ? Carbon::parse($request->get('from')) : $today;
                $to   = $request->get('to') ? Carbon::parse($request->get('to')) : $today->copy()->addDays(7);
                $query->whereBetween('expire_date', [$from, $to]);
                break;
            case '7':
            default:
                $range = '7';
                $query->whereBetween('expire_date', [$today, $today->copy()->addDays(7)]);
                break;
        }

        if ($zoneId = $request->get('zone_id')) {
            $query->where('zone_id', $zoneId);
        }
        if ($packageId = $request->get('package_id')) {
            $query->where('package_id', $packageId);
        }

        $customers = $query->orderBy('expire_date')->paginate(50)->withQueryString();

        // Summary counts (independent of pagination)
        $summaryBase = Customer::query()->whereNotNull('expire_date')->whereIn('status', ['active', 'expired']);
        $summary = [
            'today'   => (clone $summaryBase)->whereDate('expire_date', $today)->count(),
            'next3'   => (clone $summaryBase)->whereBetween('expire_date', [$today, $today->copy()->addDays(3)])->count(),
            'next7'   => (clone $summaryBase)->whereBetween('expire_date', [$today, $today->copy()->addDays(7)])->count(),
            'expired' => (clone $summaryBase)->whereDate('expire_date', '<', $today)->count(),
        ];

        $zones    = \App\Models\Zone::orderBy('name')->get();
        $packages = Package::orderBy('name')->get();

        return view('reports.bill.renewal', compact('customers', 'summary', 'range', 'zones', 'packages'));
    }

    /**
     * 2. Aging Due Report
     * Unpaid/overdue invoices bucketed by how many days past due_date.
     */
    public function agingDue(Request $request)
    {
        $today = Carbon::today();

        $query = Invoice::query()
            ->with(['customer.zone', 'customer.agent', 'package'])
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->where('due_amount', '>', 0);

        if ($zoneId = $request->get('zone_id')) {
            $query->whereHas('customer', fn ($q) => $q->where('zone_id', $zoneId));
        }
        if ($agentId = $request->get('agent_id')) {
            $query->whereHas('customer', fn ($q) => $q->where('agent_id', $agentId));
        }

        $invoices = $query->get();

        // Bucket invoices by age (days overdue from due_date)
        $buckets = [
            '0_30'   => collect(),
            '31_60'  => collect(),
            '61_90'  => collect(),
            '90_plus'=> collect(),
            'not_due'=> collect(), // due_date in future but still unpaid
        ];

        foreach ($invoices as $invoice) {
            if (!$invoice->due_date) {
                $buckets['not_due']->push($invoice);
                continue;
            }
            $daysOverdue = $today->diffInDays(Carbon::parse($invoice->due_date), false) * -1;

            if ($daysOverdue < 0) {
                $buckets['not_due']->push($invoice);
            } elseif ($daysOverdue <= 30) {
                $buckets['0_30']->push($invoice);
            } elseif ($daysOverdue <= 60) {
                $buckets['31_60']->push($invoice);
            } elseif ($daysOverdue <= 90) {
                $buckets['61_90']->push($invoice);
            } else {
                $buckets['90_plus']->push($invoice);
            }
        }

        $summary = [];
        foreach ($buckets as $key => $list) {
            $summary[$key] = [
                'count'  => $list->count(),
                'amount' => $list->sum('due_amount'),
            ];
        }
        $totalDue = $invoices->sum('due_amount');

        $zones  = \App\Models\Zone::orderBy('name')->get();
        $agents = \App\Models\Agent::orderBy('name')->get();

        // Default active tab/bucket for the table below the summary cards
        $activeBucket = $request->get('bucket', '0_30');
        $activeList   = $buckets[$activeBucket] ?? $buckets['0_30'];

        return view('reports.bill.aging-due', compact(
            'summary', 'totalDue', 'zones', 'agents', 'activeBucket', 'activeList'
        ));
    }

    /**
     * 3. Daily Collection Report
     * Payments received on a given date, grouped by method and by collector (received_by).
     */
    public function dailyCollection(Request $request)
    {
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();

        $query = Payment::query()
            ->with(['customer', 'receivedBy', 'invoice'])
            ->where('status', 'active')
            ->whereDate('paid_at', $date);

        if ($method = $request->get('method')) {
            $query->where('method', $method);
        }
        if ($userId = $request->get('received_by')) {
            $query->where('received_by', $userId);
        }

        $payments = $query->orderBy('paid_at', 'desc')->get();

        $total = $payments->sum('amount');

        $byMethod = $payments->groupBy('method')->map(function ($group) {
            return [
                'count'  => $group->count(),
                'amount' => $group->sum('amount'),
            ];
        });

        $byCollector = $payments->groupBy(function ($p) {
            return $p->receivedBy->name ?? 'Unassigned';
        })->map(function ($group) {
            return [
                'count'  => $group->count(),
                'amount' => $group->sum('amount'),
            ];
        });

        $collectors = \App\Models\User::orderBy('name')->get();

        return view('reports.bill.daily-collection', compact(
            'payments', 'total', 'byMethod', 'byCollector', 'date', 'collectors'
        ));
    }

    /**
     * 4. Package-wise Revenue Report
     * Revenue + subscriber counts grouped by package, for a given month or date range.
     */
    public function packageRevenue(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));

        // Revenue from invoices for the selected month, grouped by package
        $invoiceStats = Invoice::query()
            ->select('package_id', DB::raw('COUNT(*) as invoice_count'), DB::raw('SUM(amount) as billed'), DB::raw('SUM(amount - due_amount) as collected'), DB::raw('SUM(due_amount) as due'))
            ->where('month', $month)
            ->groupBy('package_id')
            ->get()
            ->keyBy('package_id');

        // Active subscriber count per package (current snapshot, not month-bound)
        $subscriberCounts = Customer::query()
            ->select('package_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('package_id')
            ->where('status', 'active')
            ->groupBy('package_id')
            ->pluck('total', 'package_id');

        $packages = Package::orderBy('name')->get();

        $rows = $packages->map(function ($package) use ($invoiceStats, $subscriberCounts) {
            $stat = $invoiceStats->get($package->id);
            return (object) [
                'package'     => $package,
                'subscribers' => $subscriberCounts->get($package->id, 0),
                'invoices'    => $stat->invoice_count ?? 0,
                'billed'      => $stat->billed ?? 0,
                'collected'   => $stat->collected ?? 0,
                'due'         => $stat->due ?? 0,
            ];
        })->sortByDesc('billed')->values();

        $totals = (object) [
            'subscribers' => $rows->sum('subscribers'),
            'billed'      => $rows->sum('billed'),
            'collected'   => $rows->sum('collected'),
            'due'         => $rows->sum('due'),
        ];

        return view('reports.bill.package-revenue', compact('rows', 'totals', 'month'));
    }

    /**
     * 5. Bill Receive History
     * Full filterable payment ledger — mirrors the classic "Bill Receive History"
     * report style: many filter fields, one wide table, grand total row.
     */
    public function receiveHistory(Request $request)
    {
        $query = $this->buildReceiveHistoryQuery($request);

        // Clone BEFORE pagination executes the query, so totals reflect
        // the full filtered result set rather than just the current page.
        $totalsQuery = clone $query;

        $perPage = (int) $request->get('show', 25);
        $payments = $query->paginate($perPage)->withQueryString();

        $grandTotal = [
            'received'     => (clone $totalsQuery)->sum('amount'),
            'monthly_bill' => (clone $totalsQuery)->get()->sum(fn ($p) => $p->customer->monthly_bill_amount ?? 0),
        ];

        $packages    = Package::orderBy('name')->get();
        $zones       = Zone::orderBy('name')->get();
        $subZones    = $request->get('zone_id')
            ? SubZone::where('zone_id', $request->get('zone_id'))->orderBy('name')->get()
            : SubZone::orderBy('name')->get();
        $resellers   = \App\Models\MacReseller::orderBy('business_name')->get();
        $users       = User::orderBy('name')->get();
        $methods     = ['cash', 'bkash', 'nagad', 'rocket', 'bank', 'card', 'advance'];
        $billingStatuses = ['active', 'inactive', 'left', 'free'];

        return view('reports.bill.receive-history', compact(
            'payments', 'grandTotal', 'packages', 'zones', 'subZones',
            'resellers', 'users', 'methods', 'billingStatuses', 'perPage'
        ));
    }

    /**
     * 5a. Bill Receive History — PDF export
     * Applies the same filters as receiveHistory() but renders the full
     * (non-paginated) result set as a downloadable PDF.
     */
    public function exportReceiveHistoryPdf(Request $request)
    {
        $payments = $this->buildReceiveHistoryQuery($request)->get();

        $grandTotal = [
            'received'     => $payments->sum('amount'),
            'monthly_bill' => $payments->sum(fn ($p) => $p->customer->monthly_bill_amount ?? 0),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.bill.receive-history-pdf', compact('payments', 'grandTotal'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('bill-collection-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * 5b. Bill Receive History — XLSX export
     */
    public function exportReceiveHistoryCsv(Request $request)
    {
        $payments  = $this->buildReceiveHistoryQuery($request)->get();
        $filename  = 'bill-collection-' . now()->format('Y-m-d') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bill Collection');

        // ── Header row ──
        $headers = [
            'R.Date', 'C.Code', 'Name', 'Mobile', 'Zone',
            'Package', 'Agent', 'TrxId', 'Monthly Bill', 'Received',
            'Creation Date', 'Received By', 'Gateway', 'Note',
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Bold + background for header
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

        // ── Data rows ──
        $row = 2;
        foreach ($payments as $pay) {
            $sheet->fromArray([
                $pay->paid_at ? \Carbon\Carbon::parse($pay->paid_at)->format('d M Y h:i A') : '-',
                $pay->customer->customer_code ?? '-',
                $pay->customer->name ?? '-',
                $pay->customer->phone ?? '-',
                $pay->customer->zone->name ?? '-',
                $pay->customer->package->name ?? '-',
                $pay->customer->agent->name ?? '-',
                $pay->transaction_id ?? '-',
                $pay->customer->monthly_bill_amount ?? 0,
                $pay->amount,
                $pay->created_at ? \Carbon\Carbon::parse($pay->created_at)->format('d M Y h:i A') : '-',
                $pay->receivedBy->name ?? '-',
                strtoupper($pay->method),
                $pay->remarks ?? '-',
            ], null, 'A' . $row);
            $row++;
        }

        // ── Grand total row ──
        $sheet->fromArray(['', '', '', '', '', '', '', 'TOTAL', $payments->sum(fn($p) => $p->customer->monthly_bill_amount ?? 0), $payments->sum('amount'), '', '', '', ''], null, 'A' . $row);
        $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCE6F1']],
        ]);

        // ── Auto column width ──
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Output ──
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * 6. Monthly Billing Report
     * Month-wise invoice list with customer details, generated/received/due amounts.
     * Mirrors the "Monthly Billing Report" screen (Monthwise advance & due report).
     */
    public function monthlyBilling(Request $request)
    {
        $month    = $request->get('month', Carbon::now()->format('Y-m'));
        $perPage  = (int) $request->get('show', 25);
        $invoices = $this->buildMonthlyBillingQuery($request)->paginate($perPage)->withQueryString();

        $allInvoices = $this->buildMonthlyBillingQuery($request)->get();
        $grandTotal  = [
            'generated' => $allInvoices->sum('amount'),
            'received'  => $allInvoices->sum(fn ($inv) => $inv->amount - $inv->due_amount),
            'due'       => $allInvoices->sum('due_amount'),
            'advance'   => $allInvoices->sum(fn ($inv) => $inv->customer->advance_balance ?? 0),
        ];

        $zones           = Zone::orderBy('name')->get();
        $packages        = Package::orderBy('name')->get();
        $clientTypes     = \App\Models\ClientType::orderBy('name')->get();
        $connectionTypes = \App\Models\ConnectionType::orderBy('name')->get();
        $protocolTypes   = \App\Models\ProtocolType::orderBy('name')->get();
        $routers         = \App\Models\MikrotikRouter::orderBy('name')->get();

        return view('reports.bill.monthly-billing', compact(
            'invoices', 'grandTotal', 'month', 'perPage',
            'zones', 'packages', 'clientTypes', 'connectionTypes', 'protocolTypes', 'routers'
        ));
    }

    /**
     * 6a. Monthly Billing Report — PDF Export
     */
    public function exportMonthlyBillingPdf(Request $request)
    {
        $month    = $request->get('month', Carbon::now()->format('Y-m'));
        $invoices = $this->buildMonthlyBillingQuery($request)->get();

        $grandTotal = [
            'generated' => $invoices->sum('amount'),
            'received'  => $invoices->sum(fn ($inv) => $inv->amount - $inv->due_amount),
            'due'       => $invoices->sum('due_amount'),
            'advance'   => $invoices->sum(fn ($inv) => $inv->customer->advance_balance ?? 0),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reports.bill.monthly-billing-pdf',
            compact('invoices', 'grandTotal', 'month')
        )->setPaper('a4', 'landscape');

        return $pdf->download('monthly-billing-' . $month . '.pdf');
    }

    /**
     * 6b. Monthly Billing Report — XLSX Export
     */
    public function exportMonthlyBillingCsv(Request $request)
    {
        $month    = $request->get('month', Carbon::now()->format('Y-m'));
        $invoices = $this->buildMonthlyBillingQuery($request)->get();
        $filename = 'monthly-billing-' . $month . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monthly Billing');

        // ── Header row ──
        $headers = [
            '#', 'C.Code', 'ID/IP', 'Name', 'Mobile', 'Zone', 'Cus.Type',
            'Conn.Type', 'R.Address', 'Package', 'Speed',
            'Generated', 'Received', 'Balance Due', 'Advance', 'Payment Date', 'Server',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:Q1')->applyFromArray($headerStyle);

        // ── Data rows ──
        $row = 2;
        foreach ($invoices as $i => $inv) {
            $cust = $inv->customer;
            $sheet->fromArray([
                $i + 1,
                $cust->customer_code ?? '-',
                $cust->pppoe_username ?? $cust->ip_address ?? '-',
                $cust->name ?? '-',
                $cust->phone ?? '-',
                $cust->zone->name ?? '-',
                $cust->clientType->name ?? '-',
                $cust->connectionType->name ?? '-',
                $cust->address ?? '-',
                $cust->package->name ?? '-',
                ($cust->package->speed_download ?? '-') . 'Mbps',
                $inv->amount,
                $inv->amount - $inv->due_amount,
                $inv->due_amount,
                $cust->advance_balance ?? 0,
                $cust->last_payment_date ? \Carbon\Carbon::parse($cust->last_payment_date)->format('d M Y') : '-',
                $cust->router->name ?? '-',
            ], null, 'A' . $row);
            $row++;
        }

        // ── Grand total row ──
        $sheet->fromArray([
            '', '', '', '', '', '', '', '', '', '', 'TOTAL',
            $invoices->sum('amount'),
            $invoices->sum(fn ($inv) => $inv->amount - $inv->due_amount),
            $invoices->sum('due_amount'),
            $invoices->sum(fn ($inv) => $inv->customer->advance_balance ?? 0),
            '', '',
        ], null, 'A' . $row);
        $sheet->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCE6F1']],
        ]);

        // ── Auto column width ──
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Output ──
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Shared filter query builder for Monthly Billing Report.
     */
    private function buildMonthlyBillingQuery(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));

        $query = Invoice::query()
            ->with([
                'customer.zone', 'customer.clientType', 'customer.connectionType',
                'customer.protocolType', 'customer.package', 'customer.router',
            ])
            ->where('month', $month);

        if ($zoneId = $request->get('zone_id'))
            $query->whereHas('customer', fn ($q) => $q->where('zone_id', $zoneId));
        if ($packageId = $request->get('package_id'))
            $query->where('package_id', $packageId);
        if ($clientTypeId = $request->get('client_type_id'))
            $query->whereHas('customer', fn ($q) => $q->where('client_type_id', $clientTypeId));
        if ($connectionTypeId = $request->get('connection_type_id'))
            $query->whereHas('customer', fn ($q) => $q->where('connection_type_id', $connectionTypeId));
        if ($protocolTypeId = $request->get('protocol_type_id'))
            $query->whereHas('customer', fn ($q) => $q->where('protocol_type_id', $protocolTypeId));
        if ($billingStatus = $request->get('billing_status'))
            $query->whereHas('customer', fn ($q) => $q->where('billing_status', $billingStatus));
        if ($paymentStatus = $request->get('payment_status'))
            $query->where('status', $paymentStatus);
        if ($mikrotikStatus = $request->get('mikrotik_status'))
            $query->whereHas('customer', fn ($q) => $q->where('mikrotik_status', $mikrotikStatus));
        if ($routerId = $request->get('router_id'))
            $query->whereHas('customer', fn ($q) => $q->where('router_id', $routerId));

        return $query;
    }

    /**
     * Shared filter logic for Bill Receive History (used by the page view
     * and both export methods) so all three always stay in sync.
     */
    private function buildReceiveHistoryQuery(Request $request)
    {
        $query = Payment::query()
            ->with(['customer.zone', 'customer.subZone', 'customer.package', 'customer.agent', 'customer.macReseller', 'receivedBy']);

        // ── Text search (customer code / username / name / mobile) ──
        if ($search = $request->get('search')) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('customer_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('pppoe_username', 'like', "%{$search}%");
            });
        }

        // ── Package filter ──
        if ($packageId = $request->get('package_id')) {
            $query->whereHas('customer', fn ($q) => $q->where('package_id', $packageId));
        }

        // ── Zone / SubZone filter ──
        if ($zoneId = $request->get('zone_id')) {
            $query->whereHas('customer', fn ($q) => $q->where('zone_id', $zoneId));
        }
        if ($subZoneId = $request->get('sub_zone_id')) {
            $query->whereHas('customer', fn ($q) => $q->where('sub_zone_id', $subZoneId));
        }

        // ── Billing status filter (customer.billing_status) ──
        if ($billingStatus = $request->get('billing_status')) {
            $query->whereHas('customer', fn ($q) => $q->where('billing_status', $billingStatus));
        }

        // ── Reseller / POP filter ──
        if ($resellerId = $request->get('mac_reseller_id')) {
            $query->whereHas('customer', fn ($q) => $q->where('mac_reseller_id', $resellerId));
        }

        // ── Payment method (Payment Gateway in the sample) ──
        if ($method = $request->get('method')) {
            $query->where('method', $method);
        }

        // ── Received By / Created By (both map to received_by here) ──
        if ($receivedBy = $request->get('received_by')) {
            $query->where('received_by', $receivedBy);
        }

        // ── Creation date range (payments.created_at) ──
        if ($creationFrom = $request->get('creation_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($creationFrom));
        }
        if ($creationTo = $request->get('creation_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($creationTo));
        }

        // ── Recharge / paid date range (payments.paid_at) ──
        if ($paidFrom = $request->get('paid_from')) {
            $query->whereDate('paid_at', '>=', Carbon::parse($paidFrom));
        }
        if ($paidTo = $request->get('paid_to')) {
            $query->whereDate('paid_at', '<=', Carbon::parse($paidTo));
        }

        // No default date filter — show all payments when no filter is applied.
        // Users can narrow results using the date range filters above.

        return $query->where('status', 'active')->orderByDesc('paid_at');
    }
}
