<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ExpenseController extends Controller
{
    // =========================================================================
    // INDEX — Expense list with filters + summary cards
    // =========================================================================

    public function index(Request $request)
    {
        $expenses = Expense::with(['category', 'createdBy'])
            ->when($request->search, fn($q) =>
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhere('payee', 'like', "%{$request->search}%")
                  ->orWhere('expense_no', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn($q) =>
                $q->where('category_id', $request->category_id))
            ->when($request->status, fn($q) =>
                $q->where('status', $request->status))
            ->when($request->payment_method, fn($q) =>
                $q->where('payment_method', $request->payment_method))
            ->when($request->date_from, fn($q) =>
                $q->whereDate('expense_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) =>
                $q->whereDate('expense_date', '<=', $request->date_to))
            ->when($request->month, fn($q) =>
                $q->byMonth($request->month))
            ->latest('expense_date')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        $categories = ExpenseCategory::active()->ordered()->get();

        // Summary cards — this month
        $thisMonth   = now()->format('Y-m');
        $totalThis   = Expense::active()->byMonth($thisMonth)->sum('amount');
        $totalLast   = Expense::active()->byMonth(now()->subMonth()->format('Y-m'))->sum('amount');
        $pendingCount = Expense::pending()->count();
        $todayTotal  = Expense::active()->today()->sum('amount');

        return view('expenses.index', compact(
            'expenses', 'categories',
            'totalThis', 'totalLast', 'pendingCount', 'todayTotal'
        ));
    }

    // =========================================================================
    // CREATE — Show form
    // =========================================================================

    public function create()
    {
        $categories = ExpenseCategory::active()->ordered()->get();
        return view('expenses.create', compact('categories'));
    }

    // =========================================================================
    // STORE — Save new expense
    // =========================================================================

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:expense_categories,id',
            'amount'         => 'required|numeric|min:0.01|max:99999999',
            'expense_date'   => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,bkash,nagad,rocket,bank,cheque,card',
            'transaction_id' => 'nullable|string|max:100',
            'payee'          => 'nullable|string|max:150',
            'reference_no'   => 'nullable|string|max:100',
            'description'    => 'nullable|string|max:500',
            'receipt'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')
                ->store('receipts/' . date('Y/m'), 'public');
        }

        $expense = Expense::create([
            'category_id'    => $validated['category_id'],
            'amount'         => $validated['amount'],
            'expense_date'   => $validated['expense_date'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'payee'          => $validated['payee'] ?? null,
            'reference_no'   => $validated['reference_no'] ?? null,
            'description'    => $validated['description'] ?? null,
            'receipt_path'   => $receiptPath,
            'status'         => 'approved',        // auto-approve; add approval flow later
            'created_by'     => auth()->id(),
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
        ]);

        ActivityLog::log('Expense created', 'Expense', $expense->id, null, $expense->toArray());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense saved successfully.',
                'expense' => $expense->load('category'),
            ]);
        }

        return redirect()->route('expenses.index')
            ->with('success', "Expense {$expense->expense_no} saved successfully.");
    }

    // =========================================================================
    // SHOW — Expense detail
    // =========================================================================

    public function show(Expense $expense)
    {
        $expense->load(['category', 'createdBy', 'approvedBy']);
        return view('expenses.show', compact('expense'));
    }

    // =========================================================================
    // EDIT — Show edit form
    // =========================================================================

    public function edit(Expense $expense)
    {
        if ($expense->isVoid()) {
            return back()->with('error', 'Void expenses cannot be edited.');
        }

        $categories = ExpenseCategory::active()->ordered()->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    // =========================================================================
    // UPDATE — Save changes
    // =========================================================================

    public function update(Request $request, Expense $expense)
    {
        if ($expense->isVoid()) {
            return back()->with('error', 'Void expenses cannot be edited.');
        }

        $validated = $request->validate([
            'category_id'    => 'required|exists:expense_categories,id',
            'amount'         => 'required|numeric|min:0.01|max:99999999',
            'expense_date'   => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,bkash,nagad,rocket,bank,cheque,card',
            'transaction_id' => 'nullable|string|max:100',
            'payee'          => 'nullable|string|max:150',
            'reference_no'   => 'nullable|string|max:100',
            'description'    => 'nullable|string|max:500',
            'receipt'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $old = $expense->toArray();

        // Replace receipt if new file uploaded
        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $validated['receipt_path'] = $request->file('receipt')
                ->store('receipts/' . date('Y/m'), 'public');
        }

        $expense->update([
            'category_id'    => $validated['category_id'],
            'amount'         => $validated['amount'],
            'expense_date'   => $validated['expense_date'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'payee'          => $validated['payee'] ?? null,
            'reference_no'   => $validated['reference_no'] ?? null,
            'description'    => $validated['description'] ?? null,
            'receipt_path'   => $validated['receipt_path'] ?? $expense->receipt_path,
        ]);

        ActivityLog::log('Expense updated', 'Expense', $expense->id, $old, $expense->toArray());

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    // =========================================================================
    // VOID — Soft-cancel an expense (never hard-delete)
    // =========================================================================

    public function void(Request $request, Expense $expense)
    {
        if ($expense->isVoid()) {
            return back()->with('error', 'This expense has already been voided.');
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $expense->update([
            'status'        => 'void',
            'reject_reason' => $request->reason,
        ]);

        ActivityLog::log('Expense voided', 'Expense', $expense->id);

        return back()->with('success', 'Expense voided. It will no longer appear in reports.');
    }

    // =========================================================================
    // DESTROY — Hard delete (only void expenses, only admin)
    // =========================================================================

    public function destroy(Expense $expense)
    {
        // Only allow deleting already-voided records
        if (! $expense->isVoid()) {
            return back()->with('error', 'Please void the expense first before deleting.');
        }

        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        ActivityLog::log('Expense deleted', 'Expense', $expense->id, $expense->toArray(), null);

        $expense->forceDelete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense permanently deleted.');
    }

    // =========================================================================
    // P&L REPORT — Income vs Expense, month-wise
    // =========================================================================

    public function profitLoss(Request $request)
    {
        $fromMonth = $request->from_month ?? now()->format('Y-m');
        $toMonth   = $request->to_month   ?? now()->format('Y-m');

        if ($fromMonth > $toMonth) {
            [$fromMonth, $toMonth] = [$toMonth, $fromMonth];
        }

        // Generate list of months in range
        $months = [];
        $cursor = \Carbon\Carbon::parse($fromMonth . '-01');
        $end    = \Carbon\Carbon::parse($toMonth   . '-01');
        while ($cursor->lte($end)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $rows         = [];
        $grandIncome  = 0;
        $grandExpense = 0;

        foreach ($months as $m) {
            [$y, $mo] = explode('-', $m);

            $monthlyBill = (float) Payment::active()
                ->whereYear('payment_date', $y)
                ->whereMonth('payment_date', $mo)
                ->sum('amount');

            $manualIncome = class_exists(\App\Models\Income::class)
                ? (float) \App\Models\Income::active()
                    ->whereYear('income_date', $y)
                    ->whereMonth('income_date', $mo)
                    ->sum('amount')
                : 0;

            $totalIncome  = $monthlyBill + $manualIncome;
            $totalExpense = (float) Expense::active()
                ->whereYear('expense_date', $y)
                ->whereMonth('expense_date', $mo)
                ->sum('amount');

            $netProfit = $totalIncome - $totalExpense;
            $margin    = $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 1) : 0;

            $rows[] = [
                'month'         => $m,
                'month_label'   => \Carbon\Carbon::parse($m . '-01')->format('M Y'),
                'monthly_bill'  => $monthlyBill,
                'manual_income' => $manualIncome,
                'total_income'  => $totalIncome,
                'total_expense' => $totalExpense,
                'net_profit'    => $netProfit,
                'margin'        => $margin,
            ];

            $grandIncome  += $totalIncome;
            $grandExpense += $totalExpense;
        }

        $grandProfit = $grandIncome - $grandExpense;
        $grandMargin = $grandIncome > 0 ? round(($grandProfit / $grandIncome) * 100, 1) : 0;

        $chartData = collect($rows)->map(fn($r) => [
            'month'   => $r['month_label'],
            'income'  => $r['total_income'],
            'expense' => $r['total_expense'],
            'profit'  => $r['net_profit'],
        ])->values();

        return view('accounting.profit-loss', compact(
            'fromMonth', 'toMonth', 'rows',
            'grandIncome', 'grandExpense', 'grandProfit', 'grandMargin',
            'chartData'
        ));
    }

    // =========================================================================
    // P&L PDF EXPORT
    // =========================================================================

    public function profitLossPdf(Request $request)
    {
        $fromMonth = $request->from_month ?? now()->format('Y-m');
        $toMonth   = $request->to_month   ?? now()->format('Y-m');

        if ($fromMonth > $toMonth) {
            [$fromMonth, $toMonth] = [$toMonth, $fromMonth];
        }

        $months = [];
        $cursor = \Carbon\Carbon::parse($fromMonth . '-01');
        $end    = \Carbon\Carbon::parse($toMonth   . '-01');
        while ($cursor->lte($end)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $rows = []; $grandIncome = 0; $grandExpense = 0;

        foreach ($months as $m) {
            [$y, $mo] = explode('-', $m);
            $monthlyBill  = (float) Payment::active()->whereYear('payment_date', $y)->whereMonth('payment_date', $mo)->sum('amount');
            $manualIncome = class_exists(\App\Models\Income::class) ? (float) \App\Models\Income::active()->whereYear('income_date', $y)->whereMonth('income_date', $mo)->sum('amount') : 0;
            $totalIncome  = $monthlyBill + $manualIncome;
            $totalExpense = (float) Expense::active()->whereYear('expense_date', $y)->whereMonth('expense_date', $mo)->sum('amount');
            $netProfit    = $totalIncome - $totalExpense;
            $margin       = $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 1) : 0;
            $rows[]       = ['month' => $m, 'month_label' => \Carbon\Carbon::parse($m . '-01')->format('M Y'), 'monthly_bill' => $monthlyBill, 'manual_income' => $manualIncome, 'total_income' => $totalIncome, 'total_expense' => $totalExpense, 'net_profit' => $netProfit, 'margin' => $margin];
            $grandIncome  += $totalIncome;
            $grandExpense += $totalExpense;
        }

        $grandProfit = $grandIncome - $grandExpense;
        $grandMargin = $grandIncome > 0 ? round(($grandProfit / $grandIncome) * 100, 1) : 0;

        $pdf = Pdf::loadView('accounting.profit-loss-pdf', compact(
            'fromMonth', 'toMonth', 'rows',
            'grandIncome', 'grandExpense', 'grandProfit', 'grandMargin'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('profit-loss-' . $fromMonth . '-to-' . $toMonth . '.pdf');
    }

    // =========================================================================
    // AJAX — Monthly expense totals for dashboard chart
    // =========================================================================

    public function chartData(Request $request)
    {
        $months = collect(range(5, 0))->map(function ($i) {
            $m    = now()->subMonths($i)->format('Y-m');
            [$y, $mo] = explode('-', $m);

            return [
                'label'   => now()->subMonths($i)->format('M'),
                'expense' => (float) Expense::active()
                    ->whereYear('expense_date', $y)
                    ->whereMonth('expense_date', $mo)
                    ->sum('amount'),
            ];
        });

        return response()->json($months);
    }

    // =========================================================================
    // CATEGORIES — index / store / update / destroy
    // =========================================================================

    public function categoriesIndex()
    {
        $categories = ExpenseCategory::withCount('expenses')->ordered()->get();
        return view('expenses.categories', compact('categories'));
    }

    public function categoryStore(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100|unique:expense_categories,name',
            'color' => 'nullable|string|max:7',
            'icon'  => 'nullable|string|max:50',
        ]);

        $category = ExpenseCategory::create($request->only('name', 'color', 'icon', 'description'));

        return back()->with('success', "Category '{$category->name}' created.");
    }

    public function categoryUpdate(Request $request, ExpenseCategory $expenseCategory)
    {
        $request->validate([
            'name'  => 'required|string|max:100|unique:expense_categories,name,' . $expenseCategory->id,
            'color' => 'nullable|string|max:7',
            'icon'  => 'nullable|string|max:50',
        ]);

        $expenseCategory->update($request->only('name', 'color', 'icon', 'description', 'is_active'));

        return back()->with('success', 'Category updated.');
    }

    public function categoryDestroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->count() > 0) {
            return back()->with('error', 'Cannot delete — expenses are linked to this category.');
        }

        $expenseCategory->delete();

        return back()->with('success', 'Category deleted.');
    }

    // =========================================================================
    // QUICK ADD EXPENSE CATEGORY — AJAX
    // =========================================================================
    public function quickAddCategory(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100|unique:expense_categories,name',
            'color' => 'nullable|string|max:7',
            'icon'  => 'nullable|string|max:50',
        ], [
            'name.unique' => 'This category already exists.',
        ]);

        $category = ExpenseCategory::create([
            'name'        => trim($request->name),
            'color'       => $request->color ?? '#6c757d',
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