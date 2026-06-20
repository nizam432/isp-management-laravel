<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerBillingController extends Controller
{
    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        // শুধু এই reseller-এর অধীনে থাকা customer-দের customer_id লিস্ট
        $customerIds = Customer::forReseller($resellerId)->pluck('id');

        $query = Invoice::with('customer')
            ->whereIn('customer_id', $customerIds);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%")
                         ->orWhere('customer_code', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $invoices = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total_invoices' => Invoice::whereIn('customer_id', $customerIds)->count(),
            'paid'           => Invoice::whereIn('customer_id', $customerIds)->where('status', 'paid')->count(),
            'unpaid'         => Invoice::whereIn('customer_id', $customerIds)->where('status', 'unpaid')->count(),
            'total_due'      => Invoice::whereIn('customer_id', $customerIds)->where('status', '!=', 'paid')->sum('total'),
        ];

        return view('reseller.billing.index', compact('invoices', 'stats'));
    }

    public function show(Invoice $invoice)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        // Ownership check — এই invoice এর customer কি এই reseller এর অধীনে?
        abort_unless(
            $invoice->customer && $invoice->customer->mac_reseller_id === $resellerId,
            403,
            'You do not have access to this invoice.'
        );

        $invoice->load(['customer', 'payments']);

        return view('reseller.billing.show', compact('invoice'));
    }
}
