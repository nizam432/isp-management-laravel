<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerReportController extends Controller
{
    /** GET /reseller/report — landing page with links to each report. */
    public function index()
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $stats = [
            'total_clients'   => Customer::forReseller($resellerId)->count(),
            'active_clients'  => Customer::forReseller($resellerId)->where('status', 'active')->count(),
            'this_month_paid' => Payment::active()->thisMonth()
                ->whereHas('customer', fn($q) => $q->where('mac_reseller_id', $resellerId))
                ->sum('amount'),
        ];

        return view('reseller.report.index', compact('stats'));
    }

    /**
     * GET /reseller/report/btrc — simplified subscriber summary report.
     *
     * NOTE: this is a simplified version — the actual BTRC-format regulatory
     * report (specific columns/layout BTRC requires) wasn't available to
     * build against, so this shows a subscriber-by-status / by-package
     * breakdown instead. Swap in the real format once the Admin-side BTRC
     * report implementation is shared.
     */
    public function btrc(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $byStatus = Customer::forReseller($resellerId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byPackage = Customer::forReseller($resellerId)
            ->with('resellerTariffPackage.package')
            ->get()
            ->groupBy(fn($c) => $c->resellerTariffPackage?->package?->name ?? 'N/A')
            ->map->count();

        $totalClients = Customer::forReseller($resellerId)->count();

        return view('reseller.report.btrc', compact('byStatus', 'byPackage', 'totalClients'));
    }

    /**
     * GET /reseller/report/status-history — client status overview.
     *
     * NOTE: there is no dedicated status-change audit log for reseller
     * clients yet (Admin's ActivityLog isn't reseller-scoped), so this shows
     * each client's CURRENT status and when the record was last updated,
     * sorted by most recently changed. It's a best-effort substitute, not a
     * full change history — build a proper audit trail if per-change detail
     * (old → new status, who changed it) is needed later.
     */
    public function statusHistory(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $clients = Customer::forReseller($resellerId)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();

        return view('reseller.report.status-history', compact('clients'));
    }

    /** GET /reseller/report/bill-collection — payments collected, grouped by day. */
    public function billCollection(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $from = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('date_to', now()->format('Y-m-d'));

        $payments = Payment::active()
            ->whereHas('customer', fn($q) => $q->where('mac_reseller_id', $resellerId))
            ->whereBetween('payment_date', [$from, $to])
            ->orderByDesc('payment_date')
            ->get();

        $byDay = $payments->groupBy(fn($p) => optional($p->payment_date)->format('Y-m-d'))
            ->map(fn($group) => $group->sum('amount'));

        $total = $payments->sum('amount');

        return view('reseller.report.bill-collection', compact('payments', 'byDay', 'total', 'from', 'to'));
    }

    /** GET /reseller/report/messages — redirects to the already-built SMS Reports page (same underlying data). */
    public function messages()
    {
        return redirect()->route('reseller.sms-service.reports');
    }
}
