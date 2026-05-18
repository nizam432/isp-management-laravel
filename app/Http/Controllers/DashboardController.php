<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\InventoryItem;
use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_customers'    => Customer::count(),
            'active_customers'   => Customer::active()->count(),
            'expired_customers'  => Customer::expired()->count(),
            'suspended_customers'=> Customer::where('status', 'suspended')->count(),
            'today_payments'     => Payment::today()->sum('amount'),
            'month_payments'     => Payment::thisMonth()->sum('amount'),
            'unpaid_invoices'    => Invoice::unpaid()->count(),
            'overdue_invoices'   => Invoice::overdue()->count(),
            'open_tickets'       => Ticket::open()->count(),
            'urgent_tickets'     => Ticket::urgent()->count(),
            'low_stock_items'    => InventoryItem::lowStock()->count(),
            'online_users'       => 0, // MikroTik থেকে live data
        ];

        // ── MikroTik Live Online Count ──────────────────
        try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if ($router) {
                $mikrotik = new MikrotikService();
                $stats['online_users'] = $mikrotik->withRouter(
                    $router,
                    fn($m) => $m->getOnlineUserCount()
                );
            }
        } catch (\Exception $e) {
            Log::warning('Dashboard MikroTik online count failed: ' . $e->getMessage());
        }
        // ────────────────────────────────────────────────

        // Last 6 months payment chart
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month       = now()->subMonths($i);
            $chartData[] = [
                'month'  => $month->format('M Y'),
                'amount' => Payment::whereMonth('paid_at', $month->month)
                                   ->whereYear('paid_at', $month->year)
                                   ->sum('amount'),
            ];
        }

        // Latest 10 payments
        $recentPayments = Payment::with(['customer', 'invoice'])
                                 ->latest('paid_at')
                                 ->take(10)
                                 ->get();

        // Latest 5 tickets
        $recentTickets = Ticket::with('customer')
                               ->latest()
                               ->take(5)
                               ->get();

        return view('dashboard', compact('stats', 'chartData', 'recentPayments', 'recentTickets'));
    }
}
