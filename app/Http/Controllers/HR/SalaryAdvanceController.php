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
            'employee_id'  => 'required|exists:employees,id',
            'amount'       => 'required|numeric|min:1',
            'advance_date' => 'required|date',
            'deduct_month' => 'nullable|string',
        ]);

        SalaryAdvance::create([
            'employee_id'  => $request->employee_id,
            'amount'       => $request->amount,
            'advance_date' => $request->advance_date,
            'deduct_month' => $request->deduct_month,
            'status'       => 'pending',
            'note'         => $request->note,
            'created_by'   => auth()->id(),
        ]);

        return back()->with('success', 'Salary advance recorded.');
    }

    public function deduct(SalaryAdvance $advance)
    {
        $advance->update(['status' => 'deducted']);
        return back()->with('success', 'Advance marked as deducted.');
    }
}
