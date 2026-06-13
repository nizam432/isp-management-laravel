<?php

namespace App\Http\Controllers\BandwidthBuy;

use App\Http\Controllers\Controller;
use App\Models\BandwidthBuy\BandwidthProvider;
use App\Models\BandwidthBuy\BandwidthService;
use App\Models\BandwidthBuy\BandwidthPurchaseLine;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BandwidthReportController extends Controller
{
    public function index(Request $request)
    {
        $providers = BandwidthProvider::active()->orderBy('company_name')->get();
        $services  = BandwidthService::active()->orderBy('name')->get();

        $results    = collect();
        $searched   = false;
        $summary    = [];

        if ($request->filled('search')) {
            $searched = true;

            // ── Date parse: input আসে m/d/Y বা Y-m-d — দুটোই handle করো
            $fromDate = null;
            $toDate   = null;

            if ($request->from_date) {
                try {
                    $fromDate = Carbon::createFromFormat('m/d/Y', $request->from_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    try { $fromDate = Carbon::parse($request->from_date)->format('Y-m-d'); } catch (\Exception $e2) {}
                }
            }

            if ($request->to_date) {
                try {
                    $toDate = Carbon::createFromFormat('m/d/Y', $request->to_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    try { $toDate = Carbon::parse($request->to_date)->format('Y-m-d'); } catch (\Exception $e2) {}
                }
            }

            $results = BandwidthPurchaseLine::with(['purchase.provider', 'service'])
                ->when($request->service_id, fn($q) =>
                    $q->where('service_id', $request->service_id))
                ->when($request->provider_id, fn($q) =>
                    $q->whereHas('purchase', fn($q2) =>
                        $q2->where('provider_id', $request->provider_id)))
                ->when($fromDate, fn($q) =>
                    $q->whereDate('from_date', '>=', $fromDate))
                ->when($toDate, fn($q) =>
                    $q->whereDate('to_date', '<=', $toDate))
                ->orderByDesc('created_at')
                ->get();

            // ── Summary stats
            if ($results->count()) {
                $summary = [
                    'total_lines'    => $results->count(),
                    'grand_total'    => $results->sum('line_total'),
                    'total_qty_mb'   => $results->sum('quantity_mb'),
                    'unique_providers' => $results->pluck('purchase.provider.company_name')->unique()->filter()->count(),
                    'unique_services'  => $results->pluck('service.name')->unique()->filter()->count(),
                ];
            }
        }

        return view('bandwidth-buy.report.index', compact(
            'providers', 'services', 'results', 'searched', 'summary'
        ));
    }
}
