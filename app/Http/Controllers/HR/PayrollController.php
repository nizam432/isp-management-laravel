<?php

namespace App\Http\Controllers\HR;

use App\Models\HR\Employee;
use App\Models\HR\Payroll;
use App\Models\HR\PayrollDetail;
use App\Models\HR\SalaryHead;
use App\Models\HR\SalaryAdvance;
use Illuminate\HR\Http\Request;
use Illuminate\HR\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $month     = $request->month ?? now()->format('Y-m');
        $payrolls  = Payroll::with('employee')
                            ->where('month', $month)
                            ->latest()
                            ->paginate(20);
        return view('payroll.index', compact('payrolls', 'month'));
    }

    public function generate(Request $request)
    {
        $month       = $request->month ?? now()->format('Y-m');
        $employees   = Employee::active()->with('advances')->get();
        $salaryHeads = SalaryHead::active()->get();

        return view('payroll.generate', compact('employees', 'salaryHeads', 'month'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'month'       => 'required|string',
            'employees'   => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->employees as $employeeId => $data) {
                if (empty($data['include'])) continue;

                $employee = Employee::findOrFail($employeeId);

                // Calculate totals
                $grossSalary    = 0;
                $totalDeduction = 0;

                $heads = [];
                if (!empty($data['heads'])) {
                    foreach ($data['heads'] as $headId => $amount) {
                        $head   = SalaryHead::find($headId);
                        $amount = (float) $amount;
                        if ($head->type === 'addition') {
                            $grossSalary += $amount;
                        } else {
                            $totalDeduction += $amount;
                        }
                        $heads[$headId] = $amount;
                    }
                }

                // Advance deduction
                $advance = SalaryAdvance::where('employee_id', $employeeId)
                                        ->where('deduct_month', $request->month)
                                        ->where('status', 'pending')
                                        ->sum('amount');
                $totalDeduction += $advance;

                $netSalary = $grossSalary - $totalDeduction;

                // Create payroll
                $payroll = Payroll::create([
                    'employee_id'     => $employeeId,
                    'month'           => $request->month,
                    'basic_salary'    => $employee->basic_salary,
                    'gross_salary'    => $grossSalary,
                    'total_deduction' => $totalDeduction,
                    'net_salary'      => $netSalary,
                    'status'          => 'pending',
                    'created_by'      => auth()->id(),
                ]);

                // Details
                foreach ($heads as $headId => $amount) {
                    PayrollDetail::create([
                        'payroll_id'     => $payroll->id,
                        'salary_head_id' => $headId,
                        'amount'         => $amount,
                    ]);
                }

                // Mark advance as deducted
                SalaryAdvance::where('employee_id', $employeeId)
                              ->where('deduct_month', $request->month)
                              ->where('status', 'pending')
                              ->update(['status' => 'deducted']);
            }

            DB::commit();
            return redirect()->route('payroll.index', ['month' => $request->month])
                             ->with('success', 'Payroll generated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['employee.department', 'employee.position', 'details.salaryHead']);
        return view('payroll.show', compact('payroll'));
    }

    public function pay(Request $request, Payroll $payroll)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,bank,bkash,nagad',
            'payment_date'   => 'required|date',
        ]);

        $payroll->update([
            'status'         => 'paid',
            'payment_method' => $request->payment_method,
            'payment_date'   => $request->payment_date,
            'note'           => $request->note,
        ]);

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function payslip(Payroll $payroll)
    {
        $payroll->load(['employee.department', 'employee.position', 'details.salaryHead']);
        return view('payroll.payslip', compact('payroll'));
    }
}
