<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display the revenue report for a given month.
     * Shows all payments with a breakdown by payment method.
     */
    public function revenue(Request $request)
    {
        // Default to the current month if no month is provided
        $month = $request->month ?? now()->format('Y-m');

        // Fetch all payments for the selected month
        $payments = Payment::with(['customer', 'invoice'])
            ->whereMonth('paid_at', date('m', strtotime($month . '-01')))
            ->whereYear('paid_at',  date('Y', strtotime($month . '-01')))
            ->get();

        // Total revenue for the month
        $total = $payments->sum('amount');

        // Breakdown by payment method e.g. cash: 5000, bkash: 3000
        $byMethod = $payments->groupBy('method')->map->sum('amount');

        return view('reports.revenue', compact('payments', 'total', 'byMethod', 'month'));
    }

    /**
     * Display the outstanding dues report.
     * Lists all unpaid, partial, and overdue invoices.
     * Optionally filtered by customer area.
     */
    public function due(Request $request)
    {
        $invoices = Invoice::with(['customer', 'package'])
            // Include unpaid, partially paid, and overdue invoices
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            // Optionally filter by customer area
            ->when($request->area, fn($q) => $q->whereHas('customer',
                fn($c) => $c->where('area', $request->area)))
            ->get();

        // Sum of all outstanding due amounts
        $totalDue = $invoices->sum('due_amount');

        return view('reports.due', compact('invoices', 'totalDue'));
    }

    /**
     * Display the customer report.
     * Shows breakdown by package and area.
     */
    public function customers(Request $request)
    {
        $customers = Customer::with(['package', 'agent'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->area,   fn($q) => $q->where('area', $request->area))
            ->get();

        // Group customers by package name and count
        $byPackage = $customers->groupBy('package.name')->map->count();

        // Group customers by area and count
        $byArea = $customers->groupBy('area')->map->count();

        return view('reports.customers', compact('customers', 'byPackage', 'byArea'));
    }

    /**
     * Export a report as a downloadable PDF file.
     * Supported types: 'due', 'customer'
     */
    public function exportPdf(Request $request, $type)
    {
        // Build data array based on report type
        $data = match($type) {
            'due'      => [
                'invoices'  => Invoice::with('customer')
                                      ->whereIn('status', ['unpaid', 'overdue'])
                                      ->get(),
            ],
            'customer' => [
                'customers' => Customer::with('package')->get(),
            ],
            default => [],
        };

        // Load the corresponding PDF blade view and set A4 landscape layout
        $pdf = Pdf::loadView("reports.pdf.{$type}", $data)
                  ->setPaper('a4', 'landscape');

        // e.g. report-due-2025-01-15.pdf
        return $pdf->download("report-{$type}-" . now()->format('Y-m-d') . '.pdf');
    }
}
