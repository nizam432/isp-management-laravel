<?php

namespace App\Http\Controllers\MacReseller;

use App\Http\Controllers\Controller;
use App\Models\MacReseller;
use App\Models\MacResellerPgwPayment;
use App\Models\MacResellerPgwSettlement;
use Illuminate\Http\Request;

class MacResellerSettlementController extends Controller
{
    public function index(Request $request)
    {
        $query = MacReseller::with('pgwSettlements')
            ->withSum('pgwPayments as total_received', 'received');

        if ($request->pop_status) $query->where('is_active', $request->pop_status === 'active');
        if ($request->pop_type)   $query->where('pop_type', $request->pop_type);

        $resellers = $query->paginate(25);

        return view('mac-reseller.settlement.index', compact('resellers'));
    }

    public function settle(Request $request, MacReseller $macReseller)
    {
        $request->validate([
            'amount'  => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $totalReceived  = $macReseller->pgwPayments()->where('transaction_status', 'success')->sum('received');
        $alreadySettled = $macReseller->pgwSettlements()->sum('settled_amount');
        $remaining      = $totalReceived - $alreadySettled - $request->amount;

        MacResellerPgwSettlement::create([
            'reseller_id'      => $macReseller->id,
            'total_received'   => $totalReceived,
            'settled_amount'   => $request->amount,
            'remaining_amount' => $remaining,
            'payment_status'   => 'settled',
            'settlement_date'  => now()->toDateString(),
            'settled_by'       => auth()->id(),
            'remarks'          => $request->remarks,
        ]);

        return response()->json(['success' => true, 'message' => 'Settlement recorded.']);
    }

    public function pgwTransactions(Request $request)
    {
        $payments = MacResellerPgwPayment::with('reseller')->latest()->paginate(25);
        return view('mac-reseller.settlement.pgw-transactions', compact('payments'));
    }

    public function settlementHistory(Request $request)
    {
        $settlements = MacResellerPgwSettlement::with(['reseller', 'settledBy'])->latest()->paginate(25);
        return view('mac-reseller.settlement.history', compact('settlements'));
    }
}
