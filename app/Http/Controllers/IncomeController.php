<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IncomeController extends Controller
{
    // =========================================================================
    // INDEX
    // =========================================================================
    public function index(Request $request)
    {
        $incomes = Income::with(['category', 'customer', 'createdBy'])
            ->when($request->search, fn($q) =>
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhere('payer', 'like', "%{$request->search}%")
                  ->orWhere('income_no', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn($q) =>
                $q->where('category_id', $request->category_id))
            ->when($request->status, fn($q) =>
                $q->where('status', $request->status))
            ->when($request->payment_method, fn($q) =>
                $q->where('payment_method', $request->payment_method))
            ->when($request->date_from, fn($q) =>
                $q->whereDate('income_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) =>
                $q->whereDate('income_date', '<=', $request->date_to))
            ->when($request->month, fn($q) =>
                $q->byMonth($request->month))
            ->orderByDesc('income_date')
            ->orderByDesc('id')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        // Only manual categories for the form (exclude Monthly Bill)
        $categories = IncomeCategory::active()->manual()->ordered()->get();
        // All categories for filter dropdown
        $allCategories = IncomeCategory::active()->ordered()->get();

        $thisMonth   = now()->format('Y-m');
        $totalThis   = Income::active()->byMonth($thisMonth)->sum('amount');
        $totalLast   = Income::active()->byMonth(now()->subMonth()->format('Y-m'))->sum('amount');
        $todayTotal  = Income::active()->today()->sum('amount');

        // Monthly bill income from payments this month
        $monthlyBillThis = Payment::active()->thisMonth()->sum('amount');

        return view('incomes.index', compact(
            'incomes', 'categories', 'allCategories',
            'totalThis', 'totalLast', 'todayTotal', 'monthlyBillThis'
        ));
    }

    /**
     * Shared filter logic for index(), exportXlsx(), and exportPdf() — so exports
     * always match whatever the admin currently has filtered on the Income page.
     */
    private function filteredIncomesQuery(Request $request)
    {
        return Income::with(['category', 'customer', 'createdBy'])
            ->when($request->search, fn($q) =>
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhere('payer', 'like', "%{$request->search}%")
                  ->orWhere('income_no', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn($q) =>
                $q->where('category_id', $request->category_id))
            ->when($request->status, fn($q) =>
                $q->where('status', $request->status))
            ->when($request->payment_method, fn($q) =>
                $q->where('payment_method', $request->payment_method))
            ->when($request->date_from, fn($q) =>
                $q->whereDate('income_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) =>
                $q->whereDate('income_date', '<=', $request->date_to))
            ->when($request->month, fn($q) =>
                $q->byMonth($request->month))
            ->orderByDesc('income_date')
            ->orderByDesc('id');
    }

    /** GET /incomes/export/xlsx — export the currently filtered income list as XLSX. */
    public function exportXlsx(Request $request)
    {
        $incomes = $this->filteredIncomesQuery($request)->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Income');

        $headers = ['A' => 'Income No', 'B' => 'Date', 'C' => 'Category', 'D' => 'Description',
                    'E' => 'Payer', 'F' => 'Method', 'G' => 'Amount', 'H' => 'Status'];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF3C8DBC');

        foreach ($incomes as $row => $inc) {
            $r = $row + 2;
            $sheet->setCellValue('A' . $r, $inc->income_no);
            $sheet->setCellValue('B' . $r, $inc->income_date->format('d M Y'));
            $sheet->setCellValue('C' . $r, $inc->category->name ?? '-');
            $sheet->setCellValue('D' . $r, $inc->description ?? '-');
            $sheet->setCellValue('E' . $r, $inc->payer ?? ($inc->customer?->name ?? '-'));
            $sheet->setCellValue('F' . $r, strtoupper($inc->payment_method));
            $sheet->setCellValue('G' . $r, floatval($inc->amount));
            $sheet->setCellValue('H' . $r, ucfirst($inc->status));
        }

        $filename = 'income-' . now()->format('Y-m-d') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /** GET /incomes/export/pdf — export the currently filtered income list as PDF. */
    public function exportPdf(Request $request)
    {
        $incomes = $this->filteredIncomesQuery($request)->get();
        $total   = $incomes->where('status', 'active')->sum('amount');

        $pdf = Pdf::loadView('incomes.pdf', compact('incomes', 'total'));
        return $pdf->download('income-' . now()->format('Y-m-d') . '.pdf');
    }

    // =========================================================================
    // STORE — AJAX
    // =========================================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:income_categories,id',
            'amount'         => 'required|numeric|min:0.01|max:99999999',
            'income_date'    => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,bkash,nagad,rocket,bank,cheque,card',
            'transaction_id' => 'nullable|string|max:100',
            'customer_id'    => 'nullable|exists:customers,id',
            'payer'          => 'nullable|string|max:150',
            'reference_no'   => 'nullable|string|max:100',
            'description'    => 'nullable|string|max:500',
            'receipt'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Block direct entry for Monthly Bill (is_system)
        $cat = IncomeCategory::find($validated['category_id']);
        if ($cat && $cat->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Monthly Bill income is auto-pulled from payments. Manual entry not allowed.',
            ], 422);
        }

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')
                ->store('receipts/income/' . date('Y/m'), 'public');
        }

        $income = Income::create([
            'category_id'    => $validated['category_id'],
            'amount'         => $validated['amount'],
            'income_date'    => $validated['income_date'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'customer_id'    => $validated['customer_id'] ?? null,
            'payer'          => $validated['payer'] ?? null,
            'reference_no'   => $validated['reference_no'] ?? null,
            'description'    => $validated['description'] ?? null,
            'receipt_path'   => $receiptPath,
            'status'         => 'active',
            'created_by'     => auth()->id(),
        ]);

        ActivityLog::log('Income created', 'Income', $income->id, null, $income->toArray());

        return response()->json([
            'success' => true,
            'message' => "Income {$income->income_no} saved successfully.",
            'income'  => $this->formatRow($income->load('category', 'customer')),
        ]);
    }

    // =========================================================================
    // EDIT DATA — AJAX GET
    // =========================================================================
    public function editData(Income $income)
    {
        if ($income->isVoid()) {
            return response()->json(['success' => false, 'message' => 'Void income cannot be edited.'], 422);
        }

        if (! $income->isDirectSource()) {
            return response()->json([
                'success' => false,
                'message' => "This income came from {$income->sourceLabel}. Please edit it from the source module.",
            ], 422);
        }

        return response()->json([
            'success' => true,
            'income'  => [
                'id'             => $income->id,
                'income_no'      => $income->income_no,
                'category_id'    => $income->category_id,
                'amount'         => $income->amount,
                'income_date'    => $income->income_date->format('Y-m-d'),
                'payment_method' => $income->payment_method,
                'transaction_id' => $income->transaction_id,
                'customer_id'    => $income->customer_id,
                'payer'          => $income->payer,
                'reference_no'   => $income->reference_no,
                'description'    => $income->description,
                'receipt_url'    => $income->receiptUrl,
            ],
        ]);
    }

    // =========================================================================
    // UPDATE — AJAX
    // =========================================================================
    public function update(Request $request, Income $income)
    {
        if ($income->isVoid()) {
            return response()->json(['success' => false, 'message' => 'Void income cannot be edited.'], 422);
        }

        if (! $income->isDirectSource()) {
            return response()->json([
                'success' => false,
                'message' => "This income came from {$income->sourceLabel}. Please edit it from the source module.",
            ], 422);
        }

        $validated = $request->validate([
            'category_id'    => 'required|exists:income_categories,id',
            'amount'         => 'required|numeric|min:0.01|max:99999999',
            'income_date'    => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,bkash,nagad,rocket,bank,cheque,card',
            'transaction_id' => 'nullable|string|max:100',
            'customer_id'    => 'nullable|exists:customers,id',
            'payer'          => 'nullable|string|max:150',
            'reference_no'   => 'nullable|string|max:100',
            'description'    => 'nullable|string|max:500',
            'receipt'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $cat = IncomeCategory::find($validated['category_id']);
        if ($cat && $cat->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Monthly Bill category cannot be used for manual entries.',
            ], 422);
        }

        $old = $income->toArray();

        if ($request->hasFile('receipt')) {
            if ($income->receipt_path) {
                Storage::disk('public')->delete($income->receipt_path);
            }
            $validated['receipt_path'] = $request->file('receipt')
                ->store('receipts/income/' . date('Y/m'), 'public');
        }

        $income->update([
            'category_id'    => $validated['category_id'],
            'amount'         => $validated['amount'],
            'income_date'    => $validated['income_date'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'customer_id'    => $validated['customer_id'] ?? null,
            'payer'          => $validated['payer'] ?? null,
            'reference_no'   => $validated['reference_no'] ?? null,
            'description'    => $validated['description'] ?? null,
            'receipt_path'   => $validated['receipt_path'] ?? $income->receipt_path,
        ]);

        ActivityLog::log('Income updated', 'Income', $income->id, $old, $income->toArray());

        return response()->json([
            'success' => true,
            'message' => "Income {$income->income_no} updated successfully.",
            'income'  => $this->formatRow($income->load('category', 'customer')),
        ]);
    }

    // =========================================================================
    // VOID — AJAX
    // =========================================================================
    public function void(Request $request, Income $income)
    {
        if ($income->isVoid()) {
            return response()->json(['success' => false, 'message' => 'Already voided.'], 422);
        }

        if (! $income->isDirectSource()) {
            return response()->json([
                'success' => false,
                'message' => "This income came from {$income->sourceLabel}. Please void it from the source module.",
            ], 422);
        }

        $request->validate(['reason' => 'required|string|max:255']);

        $income->update([
            'status'      => 'void',
            'void_reason' => $request->reason,
            'void_date'   => now(),
            'void_by'     => auth()->id(),
        ]);

        ActivityLog::log('Income voided', 'Income', $income->id);

        return response()->json([
            'success' => true,
            'message' => "Income {$income->income_no} has been voided.",
        ]);
    }

    // =========================================================================
    // DESTROY — only void records
    // =========================================================================
    public function destroy(Income $income)
    {
        if (! $income->isVoid()) {
            return response()->json(['success' => false, 'message' => 'Please void the income first.'], 422);
        }

        if ($income->receipt_path) {
            Storage::disk('public')->delete($income->receipt_path);
        }

        ActivityLog::log('Income deleted', 'Income', $income->id, $income->toArray(), null);
        $income->forceDelete();

        return response()->json(['success' => true, 'message' => 'Income permanently deleted.']);
    }

    // =========================================================================
    // SHOW
    // =========================================================================
    public function show(Income $income)
    {
        $income->load(['category', 'customer', 'createdBy']);
        return view('incomes.show', compact('income'));
    }

    // =========================================================================
    // PRIVATE — format row for AJAX response
    // =========================================================================
    private function formatRow(Income $income): array
    {
        return [
            'id'             => $income->id,
            'income_no'      => $income->income_no,
            'income_date'    => $income->income_date->format('d M Y'),
            'category_name'  => $income->category->name ?? '—',
            'category_style' => $income->category->badgeStyle ?? '',
            'description'    => $income->description ?? '—',
            'payer'          => $income->payer ?? ($income->customer?->name ?? '—'),
            'payment_method' => strtoupper($income->payment_method),
            'amount'         => number_format($income->amount, 2),
            'status_badge'   => $income->statusBadge,
            'is_direct'      => $income->isDirectSource(),
            'source_type'    => $income->source_type,
            'source_label'   => $income->sourceLabel,
            'show_url'       => route('incomes.show', $income),
            'edit_data_url'  => route('incomes.edit-data', $income),
            'update_url'     => route('incomes.update', $income),
            'void_url'       => route('incomes.void', $income),
        ];
    }

    // =========================================================================
    // INCOME CATEGORIES — index / store / update / destroy
    // =========================================================================
    public function categoriesIndex()
    {
        $categories = IncomeCategory::withCount('incomes')->ordered()->get();
        return view('incomes.categories', compact('categories'));
    }

    public function categoryStore(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100|unique:income_categories,name',
            'color' => 'nullable|string|max:7',
            'icon'  => 'nullable|string|max:50',
        ]);
        $cat = IncomeCategory::create($request->only('name', 'color', 'icon', 'description'));
        return back()->with('success', "Category '{$cat->name}' created.");
    }

    public function categoryUpdate(Request $request, IncomeCategory $incomeCategory)
    {
        $request->validate([
            'name'  => 'required|string|max:100|unique:income_categories,name,' . $incomeCategory->id,
            'color' => 'nullable|string|max:7',
            'icon'  => 'nullable|string|max:50',
        ]);
        $incomeCategory->update($request->only('name', 'color', 'icon', 'description', 'is_active'));
        return back()->with('success', 'Category updated.');
    }

    public function categoryDestroy(IncomeCategory $incomeCategory)
    {
        if ($incomeCategory->incomes()->count() > 0) {
            return back()->with('error', 'Cannot delete — income entries are linked to this category.');
        }
        $incomeCategory->delete();
        return back()->with('success', 'Category deleted.');
    }

    // =========================================================================
    // QUICK ADD INCOME CATEGORY — AJAX
    // =========================================================================
    public function quickAddCategory(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100|unique:income_categories,name',
            'color' => 'nullable|string|max:7',
            'icon'  => 'nullable|string|max:50',
        ], [
            'name.unique' => 'This category already exists.',
        ]);

        $category = IncomeCategory::create([
            'name'        => trim($request->name),
            'color'       => $request->color ?? '#185FA5',
            'icon'        => $request->icon ?? null,
            'description' => $request->description ?? null,
            'is_active'   => true,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => "Category '{$category->name}' added successfully.",
            'category' => [
                'id'   => $category->id,
                'name' => $category->name,
            ],
        ]);
    }
}
