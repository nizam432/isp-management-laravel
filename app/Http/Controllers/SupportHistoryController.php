<?php

namespace App\Http\Controllers;

use App\Models\ClientSupportTicket;
use App\Models\SupportCategory;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SupportHistoryController extends Controller
{
    private function getFiltered(Request $request)
    {
        return ClientSupportTicket::with(['customer.zone', 'category', 'solvedBy', 'assignees'])
            ->where('status', 'solved')
            ->when($request->from_date,   fn($q) => $q->whereDate('solved_at', '>=', $request->from_date))
            ->when($request->to_date,     fn($q) => $q->whereDate('solved_at', '<=', $request->to_date))
            ->when($request->solved_by,   fn($q) => $q->where('solved_by', $request->solved_by))
            ->when($request->category_id, fn($q) => $q->where('support_category_id', $request->category_id))
            ->when($request->zone_id,     fn($q) => $q->whereHas('customer', fn($c) => $c->where('zone_id', $request->zone_id)))
            ->latest('solved_at')
            ->get();
    }

    public function index(Request $request)
    {
        // Default: current month
        if (!$request->from_date) {
            $request->merge(['from_date' => now()->startOfMonth()->format('Y-m-d')]);
        }
        if (!$request->to_date) {
            $request->merge(['to_date' => now()->format('Y-m-d')]);
        }

        $tickets = $this->getFiltered($request);

        // Summary counts
        $totalTickets  = $tickets->count();
        $fromClient    = $tickets->where('created_from', 'client')->count();
        $fromAdmin     = $tickets->where('created_from', 'admin')->count();
        $highCount     = $tickets->where('priority', 'high')->count();
        $mediumCount   = $tickets->where('priority', 'medium')->count();
        $lowCount      = $tickets->where('priority', 'low')->count();

        $categories = SupportCategory::active()->orderBy('name')->get();
        $zones      = Zone::orderBy('name')->get();
        $users      = User::orderBy('name')->get();

        return view('support_history.index', compact(
            'tickets', 'totalTickets', 'fromClient', 'fromAdmin',
            'highCount', 'mediumCount', 'lowCount',
            'categories', 'zones', 'users'
        ));
    }

    public function exportPdf(Request $request)
    {
        $tickets = $this->getFiltered($request);
        $pdf = Pdf::loadView('support_history.pdf', compact('tickets'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('support-history-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        $tickets = $this->getFiltered($request);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="support-history-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Sr.No', 'Date', 'Ticket No', 'Client Code', 'Username', 'Mobile No', 'Zone', 'Category', 'Priority', 'Solve Time', 'Solved By', 'Duration']);

            foreach ($tickets as $i => $t) {
                fputcsv($handle, [
                    $i + 1,
                    $t->created_at->format('d-m-Y'),
                    $t->ticket_no,
                    $t->customer->customer_code ?? '—',
                    $t->customer->pppoe_username ?? '—',
                    $t->customer->phone ?? '—',
                    $t->customer->zone->name ?? '—',
                    $t->category->name ?? '—',
                    ucfirst($t->priority),
                    $t->solved_at?->format('d-m-Y H:i'),
                    $t->solvedBy->name ?? '—',
                    $t->duration,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
