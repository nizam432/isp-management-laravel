<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MacResellerFunding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerReportController extends Controller
{
    public function index(Request $request)
    {
        $resellerId  = Auth::guard('mac_reseller')->id();
        $customerIds = Customer::forReseller($resellerId)->pluck('id');

        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate   = $request->input('to_date', now()->format('Y-m-d'));

        // ── Client Summary ─────────────────────────────
        $clientStats = [
            'total'     => Customer::forReseller($resellerId)->count(),
            'active'    => Customer::forReseller($resellerId)->where('status', 'active')->count(),
            'expired'   => Customer::forReseller($resellerId)->where('status', 'expired')->count(),
            'new_in_range' => Customer::forReseller($resellerId)
                ->whereBetween('connection_date', [$fromDate, $toDate])->count(),
        ];

        // ── Billing Summary ─────────────────────────────
        $invoicesInRange = Invoice::whereIn('customer_id', $customerIds)
            ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59']);

        $billingStats = [
            'total_invoiced' => (clone $invoicesInRange)->sum('total'),
            'total_paid'     => (clone $invoicesInRange)->where('status', 'paid')->sum('total'),
            'total_unpaid'   => (clone $invoicesInRange)->where('status', '!=', 'paid')->sum('total'),
            'invoice_count'  => (clone $invoicesInRange)->count(),
        ];

        // ── Fund Summary ─────────────────────────────
        $fundStats = [
            'total_funded' => MacResellerFunding::where('reseller_id', $resellerId)
                ->whereBetween('funding_date', [$fromDate, $toDate])
                ->sum('payment'),
            'total_due'    => MacResellerFunding::where('reseller_id', $resellerId)
                ->whereBetween('funding_date', [$fromDate, $toDate])
                ->sum('due_amount'),
        ];

        // ── Package-wise client distribution ─────────────────
        $packageDistribution = Customer::forReseller($resellerId)
            ->with('package')
            ->get()
            ->groupBy(fn($c) => $c->package?->name ?? 'No Package')
            ->map(fn($g) => $g->count())
            ->sortDesc();

        return view('reseller.report.index', compact(
            'clientStats', 'billingStats', 'fundStats', 'packageDistribution', 'fromDate', 'toDate'
        ));
    }
}
