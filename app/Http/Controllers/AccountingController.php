<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Payment;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    public function dashboard()
    {
        $thisMonth = now()->format('Y-m');
        [$y, $mo]  = explode('-', $thisMonth);

        // This month income
        $thisMonthBill   = (float) Payment::active()
            ->whereYear('payment_date', $y)->whereMonth('payment_date', $mo)->sum('amount');
        $thisMonthManual = (float) Income::active()
            ->whereYear('income_date', $y)->whereMonth('income_date', $mo)->sum('amount');
        $thisMonthIncome  = $thisMonthBill + $thisMonthManual;

        // This month expense
        $thisMonthExpense = (float) Expense::active()
            ->whereYear('expense_date', $y)->whereMonth('expense_date', $mo)->sum('amount');

        $thisMonthProfit = $thisMonthIncome - $thisMonthExpense;
        $profitMargin    = $thisMonthIncome > 0
            ? round(($thisMonthProfit / $thisMonthIncome) * 100, 1) : 0;

        // 6-month trend
        $trend = collect(range(5, 0))->map(function ($i) {
            $m = now()->subMonths($i)->format('Y-m');
            [$y, $mo] = explode('-', $m);

            $bill    = (float) Payment::active()->whereYear('payment_date', $y)->whereMonth('payment_date', $mo)->sum('amount');
            $manual  = (float) Income::active()->whereYear('income_date', $y)->whereMonth('income_date', $mo)->sum('amount');
            $income  = $bill + $manual;
            $expense = (float) Expense::active()->whereYear('expense_date', $y)->whereMonth('expense_date', $mo)->sum('amount');

            return [
                'month'   => now()->subMonths($i)->format('M Y'),
                'income'  => $income,
                'expense' => $expense,
                'profit'  => $income - $expense,
            ];
        });

        // Expense breakdown this month
        $expenseBreakdown = Expense::breakdownForMonth($thisMonth);

        // Recent records
        $recentIncomes  = Income::with('category')->active()->latest('income_date')->take(5)->get();
        $recentExpenses = Expense::with('category')->active()->latest('expense_date')->take(5)->get();

        return view('accounting.dashboard', compact(
            'thisMonthBill', 'thisMonthManual', 'thisMonthIncome',
            'thisMonthExpense', 'thisMonthProfit', 'profitMargin',
            'trend', 'expenseBreakdown', 'recentIncomes', 'recentExpenses'
        ));
    }
}
