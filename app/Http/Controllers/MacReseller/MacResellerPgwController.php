<?php

namespace App\Http\Controllers\MacReseller;

use App\Http\Controllers\Controller;
use App\Models\MacReseller;
use App\Models\MacResellerPgwPayment;
use Illuminate\Http\Request;

class MacResellerPgwController extends Controller
{
    public function index(Request $request)
    {
        $query = MacResellerPgwPayment::with('reseller')->latest();

        if ($request->reseller_id)        $query->where('reseller_id', $request->reseller_id);
        if ($request->transaction_status) $query->where('transaction_status', $request->transaction_status);
        if ($request->from_date)          $query->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date)            $query->whereDate('created_at', '<=', $request->to_date);
        if ($request->payment_gateway)    $query->where('payment_gateway', $request->payment_gateway);
        if ($request->gateway_type)       $query->where('gateway_type', $request->gateway_type);

        $payments  = $query->paginate(100);
        $resellers = MacReseller::orderBy('business_name')->get();

        $totalBill     = $payments->sum('monthly_bill');
        $totalReceived = $payments->sum('received');

        return view('mac-reseller.pgw.index', compact('payments', 'resellers', 'totalBill', 'totalReceived'));
    }
}
