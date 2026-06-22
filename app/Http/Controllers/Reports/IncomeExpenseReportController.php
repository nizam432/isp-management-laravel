<?php
// ════════════════════════════════════════════
// app/Http/Controllers/Reports/IncomeExpenseReportController.php
//
//   1. Income Report  — payments (Monthly Bill) + manual incomes unified
//   2. Expense Report
// ════════════════════════════════════════════

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class IncomeExpenseReportController extends Controller
{
    // ══════════════════════════════════════════════════════
    // INCOME REPORT
    // ══════════════════════════════════════════════════════

    public function incomeReport(Request $request)
    {
        ['rows' => $rows, 'grandTotal' => $grandTotal] = $this->buildUnifiedIncome($request);

        $perPage    = (int) $request->get('show', 25);
        $page       = (int) $request->get('page', 1);
        $paginated  = $this->manualPaginate($rows, $perPage, $page);
        $categories = IncomeCategory::orderBy('name')->get();

        return view('reports.bill.income-report', compact(
            'paginated', 'grandTotal', 'categories', 'perPage'
        ));
    }

    public function exportIncomePdf(Request $request)
    {
        ['rows' => $rows, 'grandTotal' => $grandTotal] = $this->buildUnifiedIncome($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reports.bill.income-report-pdf',
            compact('rows', 'grandTotal')
        )->setPaper('a4', 'landscape');

        return $pdf->download('income-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportIncomeXlsx(Request $request)
    {
        ['rows' => $rows, 'grandTotal' => $grandTotal] = $this->buildUnifiedIncome($request);
        $filename = 'income-report-' . now()->format('Y-m-d') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Income Report');

        $headers = ['#', 'Income Id', 'Name', 'Income Head', 'Date', 'Invoice No', 'Description', 'Amount'];
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A5276']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($rows as $i => $item) {
            $sheet->fromArray([
                $i + 1,
                $item['id'],
                $item['name'],
                $item['head'],
                $item['date'],
                $item['invoice_no'],
                $item['description'],
                $item['amount'],
            ], null, 'A' . $row);
            $row++;
        }

        $sheet->fromArray(['', '', '', '', '', '', 'TOTAL', $grandTotal['amount']], null, 'A' . $row);
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD5F5E3']],
        ]);

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Unified income: payments (Monthly Bill) + manual incomes merged & sorted by date desc.
     */
    private function buildUnifiedIncome(Request $request): array
    {
        $from       = $request->get('from_date');
        $to         = $request->get('to_date');
        $categoryId = $request->get('category_id');

        $rows = collect();

        // ── 1. Payments (Monthly Bill) ──
        // Only include when no category filter OR filter is "Monthly Bill" category
        $monthlyBillCat = IncomeCategory::where('slug', 'monthly-bill')
            ->orWhere('name', 'Monthly Bill')
            ->first();

        $includePayments = !$categoryId || ($monthlyBillCat && $categoryId == $monthlyBillCat->id);

        if ($includePayments) {
            $payQuery = Payment::query()
                ->with(['customer', 'invoice'])
                ->where('status', 'active');

            if ($from) $payQuery->whereDate('paid_at', '>=', Carbon::parse($from));
            if ($to)   $payQuery->whereDate('paid_at', '<=', Carbon::parse($to));

            $payments = $payQuery->get();

            foreach ($payments as $pay) {
                $rows->push([
                    'id'          => $pay->id,
                    'name'        => 'Monthly Bill',
                    'head'        => 'Monthly Bill',
                    'date'        => $pay->paid_at ? Carbon::parse($pay->paid_at)->format('Y-m-d') : '-',
                    'date_sort'   => $pay->paid_at ? Carbon::parse($pay->paid_at)->timestamp : 0,
                    'invoice_no'  => $pay->invoice->invoice_no ?? '-',
                    'description' => 'Bill Payment for customer : ' . ($pay->customer->pppoe_username ?? $pay->customer->customer_code ?? '-'),
                    'amount'      => (float) $pay->amount,
                    'source'      => 'payment',
                ]);
            }
        }

        // ── 2. Manual Incomes ──
        $incomeQuery = Income::query()
            ->with('category')
            ->where('status', 'active');

        if ($from)       $incomeQuery->whereDate('income_date', '>=', Carbon::parse($from));
        if ($to)         $incomeQuery->whereDate('income_date', '<=', Carbon::parse($to));
        if ($categoryId) $incomeQuery->where('category_id', $categoryId);

        $incomes = $incomeQuery->get();

        foreach ($incomes as $income) {
            $rows->push([
                'id'          => $income->id,
                'name'        => $income->category->name ?? '-',
                'head'        => $income->category->name ?? '-',
                'date'        => $income->income_date ? Carbon::parse($income->income_date)->format('Y-m-d') : '-',
                'date_sort'   => $income->income_date ? Carbon::parse($income->income_date)->timestamp : 0,
                'invoice_no'  => $income->income_no ?? '-',
                'description' => $income->description ?? '-',
                'amount'      => (float) $income->amount,
                'source'      => 'income',
            ]);
        }

        // Sort by date desc
        $rows = $rows->sortByDesc('date_sort')->values();

        $grandTotal = [
            'amount' => $rows->sum('amount'),
            'count'  => $rows->count(),
        ];

        return compact('rows', 'grandTotal');
    }

    /**
     * Manual pagination for merged collection.
     */
    private function manualPaginate(Collection $items, int $perPage, int $page): array
    {
        $total  = $items->count();
        $offset = ($page - 1) * $perPage;
        $slice  = $items->slice($offset, $perPage)->values();

        return [
            'data'          => $slice,
            'total'         => $total,
            'per_page'      => $perPage,
            'current_page'  => $page,
            'last_page'     => (int) ceil($total / $perPage),
            'from'          => $total > 0 ? $offset + 1 : 0,
            'to'            => min($offset + $perPage, $total),
        ];
    }

    // ══════════════════════════════════════════════════════
    // EXPENSE REPORT
    // ══════════════════════════════════════════════════════

    public function expenseReport(Request $request)
    {
        $query = $this->buildExpenseQuery($request);

        $perPage  = (int) $request->get('show', 25);
        $expenses = $query->paginate($perPage)->withQueryString();

        $allExpenses = $this->buildExpenseQuery($request)->get();
        $grandTotal  = [
            'amount' => $allExpenses->sum('amount'),
            'count'  => $allExpenses->count(),
        ];

        $categories = ExpenseCategory::orderBy('name')->get();

        return view('reports.bill.expense-report', compact(
            'expenses', 'grandTotal', 'categories', 'perPage'
        ));
    }

    public function exportExpensePdf(Request $request)
    {
        $expenses   = $this->buildExpenseQuery($request)->get();
        $grandTotal = ['amount' => $expenses->sum('amount'), 'count' => $expenses->count()];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reports.bill.expense-report-pdf',
            compact('expenses', 'grandTotal')
        )->setPaper('a4', 'landscape');

        return $pdf->download('expense-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExpenseXlsx(Request $request)
    {
        $expenses = $this->buildExpenseQuery($request)->get();
        $filename = 'expense-report-' . now()->format('Y-m-d') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Expense Report');

        $headers = ['#', 'Expense ID', 'Name', 'Expense Head', 'Date', 'Invoice No', 'Employee', 'Description', 'Amount'];
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7B0000']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($expenses as $i => $expense) {
            $sheet->fromArray([
                $i + 1,
                $expense->id,
                $expense->category->name ?? '-',
                $expense->category->name ?? '-',
                $expense->expense_date ? Carbon::parse($expense->expense_date)->format('Y-m-d') : '-',
                $expense->expense_no ?? '-',
                $expense->payee ?? '-',
                $expense->description ?? '-',
                $expense->amount,
            ], null, 'A' . $row);
            $row++;
        }

        $sheet->fromArray(['', '', '', '', '', '', '', 'TOTAL', $expenses->sum('amount')], null, 'A' . $row);
        $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFCE4E4']],
        ]);

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function buildExpenseQuery(Request $request)
    {
        $query = Expense::query()
            ->with(['category', 'createdBy'])
            ->whereIn('status', ['approved', 'pending']);

        if ($from = $request->get('from_date'))       $query->whereDate('expense_date', '>=', Carbon::parse($from));
        if ($to = $request->get('to_date'))           $query->whereDate('expense_date', '<=', Carbon::parse($to));
        if ($categoryId = $request->get('category_id')) $query->where('category_id', $categoryId);
        if ($status = $request->get('status'))        $query->where('status', $status);

        return $query->orderByDesc('expense_date');
    }
}
