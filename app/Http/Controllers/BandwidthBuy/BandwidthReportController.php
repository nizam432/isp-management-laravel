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

        $summary    = [];

        // Build base query and apply filters if present. By default, load all results.
        $query = BandwidthPurchaseLine::with(['purchase.provider', 'service']);

        // ── Date parse: input may be m/d/Y or Y-m-d — handle both
        $fromDate = null;
        $toDate   = null;

        if ($request->from_date) {
            try {
                $fromDate = Carbon::createFromFormat('m/d/Y', $request->from_date)->format('Y-m-d');
            } catch (\Exception $e) {
                try { $fromDate = Carbon::parse($request->from_date)->format('Y-m-d'); } catch (\Exception $e2) { $fromDate = null; }
            }
        }

        if ($request->to_date) {
            try {
                $toDate = Carbon::createFromFormat('m/d/Y', $request->to_date)->format('Y-m-d');
            } catch (\Exception $e) {
                try { $toDate = Carbon::parse($request->to_date)->format('Y-m-d'); } catch (\Exception $e2) { $toDate = null; }
            }
        }

        $query->when($request->service_id, fn($q) =>
                    $q->where('service_id', $request->service_id))
              ->when($request->provider_id || $fromDate || $toDate, fn($q) =>
                    $q->whereHas('purchase', function ($q2) use ($request, $fromDate, $toDate) {
                        $q2->when($request->provider_id, fn($q3) =>
                                $q3->where('provider_id', $request->provider_id))
                           ->when($fromDate, fn($q3) =>
                                $q3->whereDate('billing_date', '>=', $fromDate))
                           ->when($toDate, fn($q3) =>
                                $q3->whereDate('billing_date', '<=', $toDate));
                    }));
        // Compute overall summary from the full filtered set (no pagination)
        $all = (clone $query)->orderByDesc('created_at')->get();

        if ($all->count()) {
            $summary = [
                'total_lines'    => $all->count(),
                'grand_total'    => $all->sum('line_total'),
                'total_qty_mb'   => $all->sum('quantity_mb'),
                'unique_providers' => $all->pluck('purchase.provider.company_name')->unique()->filter()->count(),
                'unique_services'  => $all->pluck('service.name')->unique()->filter()->count(),
            ];
        }

        // Paginate results for the view
        $perPage = 50;
        $results = (clone $query)->orderByDesc('created_at')->paginate($perPage)->appends($request->query());

        return view('bandwidth-buy.report.index', compact(
            'providers', 'services', 'results', 'summary'
        ));
    }

    /**
     * DataTables AJAX endpoint for server-side processing
     */
    public function datatables(Request $request)
    {
        $draw = intval($request->input('draw'));
        $start = intval($request->input('start'));
        $length = intval($request->input('length'));
        $searchValue = $request->input('search.value');

        // Build query with filters
        $query = BandwidthPurchaseLine::with(['purchase.provider', 'service']);

        // Parse dates
        $fromDate = null;
        $toDate = null;

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

        // Apply filters
        $query->when($request->service_id, fn($q) =>
                    $q->where('service_id', $request->service_id))
              ->when($request->provider_id || $fromDate || $toDate, fn($q) =>
                    $q->whereHas('purchase', function ($q2) use ($request, $fromDate, $toDate) {
                        $q2->when($request->provider_id, fn($q3) =>
                                $q3->where('provider_id', $request->provider_id))
                           ->when($fromDate, fn($q3) =>
                                $q3->whereDate('billing_date', '>=', $fromDate))
                           ->when($toDate, fn($q3) =>
                                $q3->whereDate('billing_date', '<=', $toDate));
                    }));

        // Global search
        if ($searchValue) {
            $query->where(function ($q) use ($searchValue) {
                $q->whereHas('purchase', fn($q2) => 
                    $q2->where('invoice_no', 'like', "%$searchValue%")
                      ->orWhereHas('provider', fn($q3) => 
                        $q3->where('company_name', 'like', "%$searchValue%")))
                 ->orWhereHas('service', fn($q2) => 
                    $q2->where('name', 'like', "%$searchValue%"));
            });
        }

        // Total records (before search)
        $totalRecords = BandwidthPurchaseLine::count();

        // Filtered records (after search/filters)
        $filteredRecords = (clone $query)->count();

        // Sorting
        $orderBy = 'created_at';
        $orderDir = 'DESC';

        if ($request->input('order.0.column') === '0') {
            $orderBy = 'created_at';
        }

        if ($request->input('order.0.dir') === 'asc') {
            $orderDir = 'ASC';
        }

        // Fetch paginated data
        $results = $query->orderBy($orderBy, $orderDir)
                        ->skip($start)
                        ->take($length)
                        ->get();

        // Format data for DataTables
        $data = $results->map(function ($line, $index) use ($start) {
            return [
                'idx'          => $start + $index + 1,
                'invoice_no'   => $line->purchase->invoice_no ?? '—',
                'provider'     => $line->purchase->provider->company_name ?? '—',
                'service'      => $line->service->name ?? '—',
                'from_date'    => $line->from_date->format('d M Y'),
                'to_date'      => $line->to_date->format('d M Y'),
                'quantity_mb'  => number_format($line->quantity_mb, 2),
                'rate'         => number_format($line->rate, 2),
                'vat_percent'  => number_format($line->vat_percent, 2),
                'line_total'   => number_format($line->line_total, 2),
                'purchase_id'  => $line->purchase_id,
                'service_name' => $line->service->name ?? '—',
            ];
        })->toArray();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ]);
    }
}
