<?php

namespace App\Http\Controllers\MacReseller;

use App\Http\Controllers\Controller;
use App\Models\MacReseller;
use App\Models\MacResellerFunding;
use App\Models\User;
use Illuminate\Http\Request;

class MacResellerFundingController extends Controller
{
    public function index(Request $request)
    {
        $query = MacResellerFunding::with(['reseller', 'fundGivenBy', 'receivedBy'])->latest();

        if ($request->reseller_id)        $query->where('reseller_id', $request->reseller_id);
        if ($request->transaction_status) $query->where('transaction_status', $request->transaction_status);
        if ($request->from_date)          $query->whereDate('funding_date', '>=', $request->from_date);
        if ($request->to_date)            $query->whereDate('funding_date', '<=', $request->to_date);
        if ($request->payment_by)         $query->where('fund_given_by', $request->payment_by);
        if ($request->received_by)        $query->where('received_by', $request->received_by);
        if ($request->restrict_status !== null && $request->restrict_status !== '') {
            $query->where('restrict_online', $request->restrict_status);
        }

        $fundings  = $query->paginate(25);
        $resellers = MacReseller::orderBy('business_name')->get();
        $employees = User::orderBy('name')->get();

        return view('mac-reseller.funding.index', compact('fundings', 'resellers', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reseller_id'   => 'required|exists:mac_resellers,id',
            'fund_amount'   => 'required|numeric|min:1',
            'payment'       => 'required|numeric|min:0',
            'apply_vat'     => 'nullable|boolean',
            'vat'           => 'nullable|numeric|min:0',
            'discount'      => 'nullable|numeric|min:0',
            'received_by'   => 'required|exists:users,id',
            'received_date' => 'required|date',
            'remarks'       => 'nullable|string',
        ]);

        $fundAmount = (float) $data['fund_amount'];
        $payment    = (float) $data['payment'];
        $vat        = (float) ($data['vat'] ?? 0);
        $discount   = (float) ($data['discount'] ?? 0);
        $netAmount  = $fundAmount + $vat - $discount;
        $due        = max(0, $netAmount - $payment);
        $status     = $due <= 0 ? 'paid' : ($payment > 0 ? 'partial' : 'due');

        $funding = MacResellerFunding::create(array_merge($data, [
            'invoice_number'     => MacResellerFunding::generateInvoiceNumber(),
            'processing_fee'     => 0,
            'due_amount'         => $due,
            'funding_date'       => now()->toDateString(),
            'fund_given_by'      => auth()->id(),
            'transaction_status' => $status,
        ]));

        MacReseller::find($data['reseller_id'])->increment('remaining_fund', $payment);

        return response()->json([
            'success' => true,
            'message' => 'Fund transaction saved.',
            'invoice' => $funding->invoice_number,
        ]);
    }

    public function markPaid(MacResellerFunding $funding)
    {
        $due = $funding->due_amount;
        $funding->update([
            'payment'            => $funding->fund_amount,
            'due_amount'         => 0,
            'transaction_status' => 'paid',
            'received_date'      => now()->toDateString(),
        ]);
        $funding->reseller->increment('remaining_fund', $due);
        return response()->json(['success' => true]);
    }

    public function refund(MacResellerFunding $funding)
    {
        $funding->reseller->decrement('remaining_fund', $funding->payment);
        $funding->update([
            'transaction_status' => 'due',
            'payment'            => 0,
            'due_amount'         => $funding->fund_amount,
        ]);
        return response()->json(['success' => true]);
    }

    public function toggleRestrict(MacResellerFunding $funding)
    {
        $funding->update(['restrict_online' => !$funding->restrict_online]);
        return response()->json(['success' => true]);
    }

    public function bulkToggleRestrict(Request $request)
    {
        $request->validate(['action' => 'required|in:block,unblock']);
        $restrict = $request->action === 'block';
        MacReseller::query()->update(['restrict_online_payment' => $restrict]);
        return response()->json(['success' => true]);
    }

    public function history(Request $request)
    {
        $fundings = MacResellerFunding::with('reseller')
            ->where('transaction_status', 'paid')
            ->latest()
            ->paginate(25);
        return view('mac-reseller.funding.history', compact('fundings'));
    }

    public function downloadPdf(Request $request)
    {
        $fundings = MacResellerFunding::with('reseller')->latest()->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mac-reseller.funding.pdf', compact('fundings'));
        return $pdf->download('mac-reseller-funding.pdf');
    }

    public function downloadExcel(Request $request)
    {
        return response()->json(['message' => 'Excel export — implement with Maatwebsite Excel']);
    }
}
