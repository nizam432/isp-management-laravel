<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ClientSupportTicket;
use App\Models\InventoryItem;
use App\Models\MikrotikRouter;
use App\Models\Expense;
use App\Models\Income;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $range = $this->resolveDateRange('all_time');
        $stats = $this->buildStats($range['from'], $range['to']);

        // ── Last 12 Months Income vs Expense Chart ──────────
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $income  = Income::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('amount');

            $expense = Expense::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('amount');

            $chartData[] = [
                'month'   => $month->format('M Y'),
                'income'  => (float) $income,
                'expense' => (float) $expense,
            ];
        }

        // Latest 10 payments
        $recentPayments = Payment::with(['customer', 'invoice'])
            ->latest('paid_at')
            ->take(10)
            ->get();

        // Latest 5 tickets
        $recentTickets = ClientSupportTicket::with('customer')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'chartData', 'recentPayments', 'recentTickets'));
    }

    /**
     * Return dashboard stat cards as JSON when the date filter changes.
     * GET /dashboard/stats?range=last_7_days
     * GET /dashboard/stats?range=custom&from=2026-01-01&to=2026-01-31
     */
    public function stats(Request $request)
    {
        $request->validate([
            'range' => 'required|string',
            'from'  => 'nullable|date',
            'to'    => 'nullable|date',
        ]);

        $range = $this->resolveDateRange($request->range, $request->from, $request->to);
        $stats = $this->buildStats($range['from'], $range['to']);

        return response()->json([
            'success' => true,
            'stats'   => $stats,
            'label'   => $range['label'],
        ]);
    }

    /** Build all stat values for the given date range. */
    private function buildStats(Carbon $from, Carbon $to): array
    {
        $stats = [
            // ── Customer cards ──────────────────────────
            'total_customers'    => Customer::whereBetween('connection_date', [$from, $to])->count(),
            'active_customers'   => Customer::active()->whereBetween('connection_date', [$from, $to])->count(),
            'inactive_customers' => Customer::where('status', 'inactive')->whereBetween('connection_date', [$from, $to])->count(),
            'expired_customers'  => Customer::expired()->whereBetween('connection_date', [$from, $to])->count(),

            // ── Billing cards ───────────────────────────
            'paid_customers'   => Invoice::where('status', 'paid')->whereBetween('created_at', [$from, $to])->distinct('customer_id')->count('customer_id'),
            'unpaid_customers' => Invoice::where('status', '!=', 'paid')->whereBetween('created_at', [$from, $to])->distinct('customer_id')->count('customer_id'),

            // ── Network cards ───────────────────────────
            'online_clients' => 0, // populated below from MikroTik
            'free_clients'   => Customer::whereHas('package', fn ($q) => $q->where('price', 0))
                ->whereBetween('connection_date', [$from, $to])->count(),

            // ── Finance cards ───────────────────────────
            'collection'     => Payment::whereBetween('paid_at', [$from, $to])->sum('amount'),
            'due_invoice'    => Invoice::where('status', '!=', 'paid')->whereBetween('created_at', [$from, $to])->sum('due_amount'),
            'total_expense'  => Expense::whereBetween('created_at', [$from, $to])->sum('amount'),
            'total_income'   => Income::whereBetween('created_at', [$from, $to])->sum('amount'),

            // ── Ticket cards ─────────────────────────────
            'open_tickets'       => ClientSupportTicket::pending()->whereBetween('created_at', [$from, $to])->count(),
            'processing_tickets' => ClientSupportTicket::processing()->whereBetween('created_at', [$from, $to])->count(),
            'solved_tickets'     => ClientSupportTicket::solved()->whereBetween('created_at', [$from, $to])->count(),
            'closed_tickets'     => ClientSupportTicket::where('status', 'closed')->whereBetween('created_at', [$from, $to])->count(),
        ];

        // Live count is always real-time; date filter does not apply here.
        try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if ($router) {
                $mikrotik = new MikrotikService();
                $stats['online_clients'] = $mikrotik->withRouter(
                    $router,
                    fn ($m) => $m->getOnlineUserCount()
                );
            }
        } catch (\Exception $e) {
            Log::warning('Dashboard MikroTik online count failed: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Resolve a named date range preset to from/to Carbon instances.
     * Financial year uses the Bangladesh standard: 1 July – 30 June.
     */
    private function resolveDateRange(string $range, ?string $customFrom = null, ?string $customTo = null): array
    {
        $now = now();

        switch ($range) {
            case 'all_time':
                return [
                    'from'  => Carbon::create(2000, 1, 1)->startOfDay(),
                    'to'    => $now->copy()->endOfDay(),
                    'label' => 'All Time',
                ];

            case 'today':
                return ['from' => $now->copy()->startOfDay(), 'to' => $now->copy()->endOfDay(), 'label' => 'Today'];

            case 'yesterday':
                $y = $now->copy()->subDay();
                return ['from' => $y->copy()->startOfDay(), 'to' => $y->copy()->endOfDay(), 'label' => 'Yesterday'];

            case 'last_7_days':
                return ['from' => $now->copy()->subDays(6)->startOfDay(), 'to' => $now->copy()->endOfDay(), 'label' => 'Last 7 Days'];

            case 'last_30_days':
                return ['from' => $now->copy()->subDays(29)->startOfDay(), 'to' => $now->copy()->endOfDay(), 'label' => 'Last 30 Days'];

            case 'this_month':
                return ['from' => $now->copy()->startOfMonth(), 'to' => $now->copy()->endOfMonth(), 'label' => 'This Month'];

            case 'last_month':
                $m = $now->copy()->subMonthNoOverflow();
                return ['from' => $m->copy()->startOfMonth(), 'to' => $m->copy()->endOfMonth(), 'label' => 'Last Month'];

            case 'this_month_last_year':
                $m = $now->copy()->subYearNoOverflow();
                return ['from' => $m->copy()->startOfMonth(), 'to' => $m->copy()->endOfMonth(), 'label' => 'This Month Last Year'];

            case 'this_year':
                return ['from' => $now->copy()->startOfYear(), 'to' => $now->copy()->endOfYear(), 'label' => 'This Year'];

            case 'last_year':
                $y = $now->copy()->subYearNoOverflow();
                return ['from' => $y->copy()->startOfYear(), 'to' => $y->copy()->endOfYear(), 'label' => 'Last Year'];

            case 'current_financial_year':
                [$from, $to] = $this->financialYearRange($now);
                return ['from' => $from, 'to' => $to, 'label' => 'Current Financial Year'];

            case 'last_financial_year':
                [$from, $to] = $this->financialYearRange($now->copy()->subYearNoOverflow());
                return ['from' => $from, 'to' => $to, 'label' => 'Last Financial Year'];

            case 'custom':
                $from = $customFrom ? Carbon::parse($customFrom)->startOfDay() : $now->copy()->startOfMonth();
                $to   = $customTo ? Carbon::parse($customTo)->endOfDay() : $now->copy()->endOfDay();
                return ['from' => $from, 'to' => $to, 'label' => 'Custom Range'];

            default:
                return [
                    'from'  => Carbon::create(2000, 1, 1)->startOfDay(),
                    'to'    => $now->copy()->endOfDay(),
                    'label' => 'All Time',
                ];
        }
    }

    /** Return start and end of the Bangladesh financial year (Jul 1 – Jun 30) containing $reference. */
    private function financialYearRange(Carbon $reference): array
    {
        if ($reference->month >= 7) {
            $from = Carbon::create($reference->year, 7, 1)->startOfDay();
            $to   = Carbon::create($reference->year + 1, 6, 30)->endOfDay();
        } else {
            $from = Carbon::create($reference->year - 1, 7, 1)->startOfDay();
            $to   = Carbon::create($reference->year, 6, 30)->endOfDay();
        }

        return [$from, $to];
    }
}
