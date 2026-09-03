<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ClientSupportTicket;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class ResellerDashboardController extends Controller
{
    public function index()
    {
        $reseller   = Auth::guard('mac_reseller')->user();
        $resellerId = $reseller->id;

        $stats = [
            'total_clients'    => Customer::forReseller($resellerId)->count(),
            'active_clients'   => Customer::forReseller($resellerId)->where('status', 'active')->count(),
            'suspended_clients'=> Customer::forReseller($resellerId)->where('status', 'suspended')->count(),
            'expired_clients'  => Customer::forReseller($resellerId)->where('status', 'expired')->count(),
        ];

        $customerIds = Customer::forReseller($resellerId)->pluck('id');

        $thisMonth = now()->format('Y-m');

        $billing = [
            'collected_this_month' => Payment::whereIn('customer_id', $customerIds)
                ->where('status', 'active')
                ->whereYear('payment_date', now()->year)
                ->whereMonth('payment_date', now()->month)
                ->sum('amount'),
            'total_due' => Invoice::whereIn('customer_id', $customerIds)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->sum('due_amount'),
            'invoices_this_month' => Invoice::whereIn('customer_id', $customerIds)
                ->where('month', $thisMonth)
                ->count(),
            'unpaid_invoices' => Invoice::whereIn('customer_id', $customerIds)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->count(),
        ];

        $support = [
            'pending'    => ClientSupportTicket::whereHas('customer', fn($q) => $q->where('mac_reseller_id', $resellerId))->where('status', 'pending')->count(),
            'processing' => ClientSupportTicket::whereHas('customer', fn($q) => $q->where('mac_reseller_id', $resellerId))->where('status', 'processing')->count(),
        ];

        $recentPayments = Payment::with('customer')
            ->whereIn('customer_id', $customerIds)
            ->where('status', 'active')
            ->latest('payment_date')
            ->limit(6)
            ->get();

        $recentTickets = ClientSupportTicket::with('customer')
            ->whereHas('customer', fn($q) => $q->where('mac_reseller_id', $resellerId))
            ->latest()
            ->limit(6)
            ->get();

        $recentClients = Customer::with('resellerTariffPackage.package')
            ->forReseller($resellerId)
            ->latest()
            ->limit(6)
            ->get();

        return view('reseller.dashboard', compact(
            'reseller', 'stats', 'billing', 'support',
            'recentPayments', 'recentTickets', 'recentClients'
        ));
    }
}