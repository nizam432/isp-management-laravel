<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\SalaryAdvance;
use App\Models\HR\Employee;
use Illuminate\Http\Request;

class SalaryAdvanceController extends Controller
{
    public function index()
    {
        $advances  = SalaryAdvance::with('employee')->latest()->paginate(20);
        $employees = Employee::active()->get();
        return view('hr.salary-advance.index', compact('advances', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'amount'             => 'required|numeric|min:1',
            'payment_type'       => 'required|in:one_time,installment',
            'installment_amount' => 'required_if:payment_type,installment|nullable|numeric|min:1',
            'total_installments' => 'required_if:payment_type,installment|nullable|integer|min:1',
            'advance_date'       => 'required|date',
            'deduct_month'       => 'nullable|string',
        ]);

        // Calculate installment details
        $paymentType      = $request->payment_type;
        $amount           = (float) $request->amount;
        $installmentAmt   = 0;
        $totalInstallments = 1;

        if ($paymentType === 'installment') {
            $installmentAmt    = (float) $request->installment_amount;
            $totalInstallments = (int) $request->total_installments;
        } else {
            // One time — full amount in one deduction
            $installmentAmt    = $amount;
            $totalInstallments = 1;
        }

        SalaryAdvance::create([
            'employee_id'        => $request->employee_id,
            'amount'             => $amount,
            'payment_type'       => $paymentType,
            'installment_amount' => $installmentAmt,
            'total_installments' => $totalInstallments,
            'paid_installments'  => 0,
            'remaining_amount'   => $amount, // full amount remaining
            'advance_date'       => $request->advance_date,
            'deduct_month'       => $request->deduct_month,
            'status'             => 'pending',
            'note'               => $request->note,
            'created_by'         => auth()->id(),
        ]);

        return back()->with('success', 'Salary advance recorded successfully.');
    }

    public function deduct(SalaryAdvance $advance)
    {
        if ($advance->status === 'deducted') {
            return back()->with('error', 'This advance is already fully deducted.');
        }

        // Get deduction amount for this month
        $deductAmount = $advance->getNextDeductionAmount();

        if ($deductAmount <= 0) {
            return back()->with('error', 'No remaining amount to deduct.');
        }

        // Deduct using model method
        $advance->deduct($deductAmount);

        $message = $advance->isInstallment()
            ? "Installment of ৳ " . number_format($deductAmount) . " deducted. Remaining: ৳ " . number_format($advance->remaining_amount)
            : "Full advance of ৳ " . number_format($deductAmount) . " deducted.";

        return back()->with('success', $message);
    }
}