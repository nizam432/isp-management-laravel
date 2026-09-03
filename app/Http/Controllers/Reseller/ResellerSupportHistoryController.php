<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ClientSupportTicket;
use App\Models\SupportCategory;
use App\Models\MacResellerZone;
use App\Models\HR\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ResellerSupportHistoryController extends Controller
{
    private function getFiltered(Request $request, int $resellerId)
    {
        return ClientSupportTicket::with(['customer.resellerZone', 'category', 'assignees'])
            ->whereHas('customer', fn($c) => $c->where('mac_reseller_id', $resellerId))
            ->where('status', 'solved')
            ->when($request->from_date,   fn($q) => $q->whereDate('solved_at', '>=', $request->from_date))
            ->when($request->to_date,     fn($q) => $q->whereDate('solved_at', '<=', $request->to_date))
            ->when($request->category_id, fn($q) => $q->where('support_category_id', $request->category_id))
            ->when($request->zone_id,     fn($q) => $q->whereHas('customer', fn($c) => $c->where('mac_reseller_zone_id', $request->zone_id)))
            ->latest('solved_at')
            ->get();
    }

    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        if (!$request->from_date) {
            $request->merge(['from_date' => now()->startOfMonth()->format('Y-m-d')]);
        }
        if (!$request->to_date) {
            $request->merge(['to_date' => now()->format('Y-m-d')]);
        }

        $tickets = $this->getFiltered($request, $resellerId);

        $totalTickets = $tickets->count();
        $highCount    = $tickets->where('priority', 'high')->count();
        $mediumCount  = $tickets->where('priority', 'medium')->count();
        $lowCount     = $tickets->where('priority', 'low')->count();

        $categories = SupportCategory::forReseller($resellerId)->active()->orderBy('name')->get();
        $zones      = MacResellerZone::forReseller($resellerId)->orderBy('name')->get();

        return view('reseller.client-support.history', compact(
            'tickets', 'totalTickets', 'highCount', 'mediumCount', 'lowCount',
            'categories', 'zones'
        ));
    }

    public function exportPdf(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        $tickets    = $this->getFiltered($request, $resellerId);

        $pdf = Pdf::loadView('reseller.client-support.history-pdf', compact('tickets'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('support-history-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        $tickets    = $this->getFiltered($request, $resellerId);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="support-history-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Sr.No', 'Date', 'Ticket No', 'Client Code', 'Username', 'Mobile No', 'Zone', 'Category', 'Priority', 'Solve Time', 'Duration']);

            foreach ($tickets as $i => $t) {
                fputcsv($handle, [
                    $i + 1,
                    $t->created_at->format('d-m-Y'),
                    $t->ticket_no,
                    $t->customer->customer_code ?? '—',
                    $t->customer->pppoe_username ?? '—',
                    $t->customer->phone ?? '—',
                    $t->customer->resellerZone->name ?? '—',
                    $t->category->name ?? '—',
                    ucfirst($t->priority),
                    $t->solved_at?->format('d-m-Y H:i'),
                    $t->duration,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}