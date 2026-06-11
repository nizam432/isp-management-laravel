<?php

namespace App\Http\Controllers\BandwidthBuy;

use App\Http\Controllers\Controller;
use App\Models\BandwidthBuy\BandwidthProvider;
use App\Models\BandwidthBuy\BandwidthService;
use App\Models\BandwidthBuy\BandwidthPurchase;
use App\Models\BandwidthBuy\BandwidthPurchaseLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BandwidthPurchaseController extends Controller
{
    // ── Purchase List ─────────────────────────────────────────────────────────

    public function index()
    {
        $purchases = BandwidthPurchase::with('provider', 'lines.service')
            ->latest('billing_date')
            ->paginate(20);

        return view('bandwidth-buy.purchase.index', compact('purchases'));
    }

    // ── Create form ───────────────────────────────────────────────────────────

    public function create()
    {
        $providers = BandwidthProvider::active()->orderBy('company_name')->get();
        $services  = BandwidthService::active()->orderBy('name')->get();

        return view('bandwidth-buy.purchase.create', compact('providers', 'services'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'provider_id'           => 'required|exists:bandwidth_providers,id',
            'invoice_no'            => 'required|string|max:100',
            'billing_date'          => 'required|date',
            'document'              => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'paid'                  => 'required|numeric|min:0',
            'bank_account'          => 'nullable|string|max:150',

            'lines'                 => 'required|array|min:1',
            'lines.*.service_id'    => 'required|exists:bandwidth_services,id',
            'lines.*.from_date'     => 'required|date',
            'lines.*.to_date'       => 'required|date',
            'lines.*.quantity_mb'   => 'required|numeric|min:0',
            'lines.*.rate'          => 'required|numeric|min:0',
            'lines.*.vat_percent'   => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')
                    ->store('bandwidth/purchases', 'public');
            }

            // Compute totals from lines
            $subTotal = 0;
            $lineData = [];
            foreach ($request->lines as $line) {
                $total    = BandwidthPurchaseLine::computeTotal(
                    (float) $line['quantity_mb'],
                    (float) $line['rate'],
                    (float) $line['vat_percent']
                );
                $subTotal += $total;
                $lineData[] = array_merge($line, ['line_total' => $total]);
            }

            $paid = min((float) $request->paid, $subTotal);
            $due  = max(0, $subTotal - $paid);

            $purchase = BandwidthPurchase::create([
                'invoice_no'   => $request->invoice_no,
                'provider_id'  => $request->provider_id,
                'billing_date' => $request->billing_date,
                'document'     => $documentPath,
                'sub_total'    => $subTotal,
                'paid'         => $paid,
                'due'          => $due,
                'bank_account' => $request->bank_account ?: null,
                'created_by'   => auth()->id(),
            ]);

            foreach ($lineData as $line) {
                $purchase->lines()->create([
                    'service_id'  => $line['service_id'],
                    'from_date'   => $line['from_date'],
                    'to_date'     => $line['to_date'],
                    'quantity_mb' => $line['quantity_mb'],
                    'rate'        => $line['rate'],
                    'vat_percent' => $line['vat_percent'],
                    'line_total'  => $line['line_total'],
                ]);
            }

            DB::commit();

            return redirect()->route('bandwidth-buy.purchase.index')
                ->with('success', 'Purchase bill saved successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    // ── Edit form ─────────────────────────────────────────────────────────────

    public function edit(BandwidthPurchase $purchase)
    {
        $purchase->load('lines.service');
        $providers = BandwidthProvider::active()->orderBy('company_name')->get();
        $services  = BandwidthService::active()->orderBy('name')->get();

        return view('bandwidth-buy.purchase.edit',
            compact('purchase', 'providers', 'services'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, BandwidthPurchase $purchase)
    {
        $request->validate([
            'provider_id'           => 'required|exists:bandwidth_providers,id',
            'invoice_no'            => 'required|string|max:100',
            'billing_date'          => 'required|date',
            'document'              => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'paid'                  => 'required|numeric|min:0',
            'bank_account'          => 'nullable|string|max:150',

            'lines'                 => 'required|array|min:1',
            'lines.*.service_id'    => 'required|exists:bandwidth_services,id',
            'lines.*.from_date'     => 'required|date',
            'lines.*.to_date'       => 'required|date',
            'lines.*.quantity_mb'   => 'required|numeric|min:0',
            'lines.*.rate'          => 'required|numeric|min:0',
            'lines.*.vat_percent'   => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('document')) {
                if ($purchase->document) {
                    Storage::disk('public')->delete($purchase->document);
                }
                $purchase->document = $request->file('document')
                    ->store('bandwidth/purchases', 'public');
                $purchase->save();
            }

            $subTotal = 0;
            $lineData = [];
            foreach ($request->lines as $line) {
                $total    = BandwidthPurchaseLine::computeTotal(
                    (float) $line['quantity_mb'],
                    (float) $line['rate'],
                    (float) $line['vat_percent']
                );
                $subTotal += $total;
                $lineData[] = array_merge($line, ['line_total' => $total]);
            }

            $paid = min((float) $request->paid, $subTotal);
            $due  = max(0, $subTotal - $paid);

            $purchase->update([
                'invoice_no'   => $request->invoice_no,
                'provider_id'  => $request->provider_id,
                'billing_date' => $request->billing_date,
                'sub_total'    => $subTotal,
                'paid'         => $paid,
                'due'          => $due,
                'bank_account' => $request->bank_account ?: null,
            ]);

            // Replace all lines
            $purchase->lines()->delete();
            foreach ($lineData as $line) {
                $purchase->lines()->create([
                    'service_id'  => $line['service_id'],
                    'from_date'   => $line['from_date'],
                    'to_date'     => $line['to_date'],
                    'quantity_mb' => $line['quantity_mb'],
                    'rate'        => $line['rate'],
                    'vat_percent' => $line['vat_percent'],
                    'line_total'  => $line['line_total'],
                ]);
            }

            DB::commit();

            return redirect()->route('bandwidth-buy.purchase.index')
                ->with('success', 'Purchase updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }
}
