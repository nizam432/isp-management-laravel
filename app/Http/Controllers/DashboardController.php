<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\InventoryItem;

class DashboardController extends Controller
{
    /**
     * Show the main dashboard with summary statistics.
     */
    public function index()
    {
        // Collect key statistics from all modules
        $stats = [
            'total_customers'   => Customer::count(),
            'active_customers'  => Customer::active()->count(),
            'expired_customers' => Customer::expired()->count(),
            'today_payments'    => Payment::today()->sum('amount'),
            'month_payments'    => Payment::thisMonth()->sum('amount'),
            'unpaid_invoices'   => Invoice::unpaid()->count(),
            'overdue_invoices'  => Invoice::overdue()->count(),
            'open_tickets'      => Ticket::open()->count(),
            'urgent_tickets'    => Ticket::urgent()->count(),
            'low_stock_items'   => InventoryItem::lowStock()->count(),
        ];

        // Build last 6 months payment chart data
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
