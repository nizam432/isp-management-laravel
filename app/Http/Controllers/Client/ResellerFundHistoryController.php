<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\MacResellerFunding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerFundHistoryController extends Controller
{
    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        $reseller   = Auth::guard('mac_reseller')->user();

        $query = MacResellerFunding::where('reseller_id', $resellerId);

        if ($request->filled('transaction_status')) {
            $query->where('transaction_status', $request->transaction_status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('funding_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('funding_date', '<=', $request->to_date);
        }

        $fundings = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'remaining_fund' => $reseller->remaining_fund,
            'total_paid'     => MacResellerFunding::where('reseller_id', $resellerId)->where('transaction_status', 'paid')->sum('payment'),
            'total_due'      => MacResellerFunding::where('reseller_id', $resellerId)->sum('due_amount'),
            'total_records'  => MacResellerFunding::where('reseller_id', $resellerId)->count(),
        ];

        return view('reseller.fund-history.index', compact('fundings', 'stats'));
    }
}
