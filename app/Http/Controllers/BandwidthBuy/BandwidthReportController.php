<?php

namespace App\Http\Controllers\BandwidthBuy;

use App\Http\Controllers\Controller;
use App\Models\BandwidthBuy\BandwidthProvider;
use App\Models\BandwidthBuy\BandwidthService;
use App\Models\BandwidthBuy\BandwidthPurchaseLine;
use Illuminate\Http\Request;

class BandwidthReportController extends Controller
{
    public function index(Request $request)
    {
        $providers = BandwidthProvider::active()->orderBy('company_name')->get();
        $services  = BandwidthService::active()->orderBy('name')->get();

        $results = collect();

        if ($request->filled('search')) {
            $results = BandwidthPurchaseLine::with(['purchase.provider', 'service'])
                ->when($request->service_id, fn($q) =>
                    $q->where('service_id', $request->service_id))
                ->when($request->provider_id, fn($q) =>
                    $q->whereHas('purchase', fn($q2) =>
                        $q2->where('provider_id', $request->provider_id)))
                ->when($request->from_date, fn($q) =>
                    $q->where('from_date', '>=', $request->from_date))
                ->when($request->to_date, fn($q) =>
                    $q->where('to_date', '<=', $request->to_date))
                ->get();
        }

        return view('bandwidth-buy.report.index', compact('providers', 'services', 'results'));
    }
}
