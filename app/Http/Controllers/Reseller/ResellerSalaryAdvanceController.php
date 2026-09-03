<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\SalaryAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerSalaryAdvanceController extends Controller
{
    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $advances = SalaryAdvance::whereHas('employee', fn($q) => $q->where('mac_reseller_id', $resellerId))
            ->with('employee')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('reseller.hr.salary-advance.index', compact('advances'));
    }

    public function create()
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        $employees  = Employee::forReseller($resellerId)->active()->orderBy('name')->get(['id', 'name']);

        return view('reseller.hr.salary-advance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate([
            'employee_id'         => 'required|exists:employees,id',
            'amount'              => 'required|numeric|min:1',
            'payment_type'        => 'required|in:one_time,installment',
            'total_installments'  => 'required_if:payment_type,installment|nullable|integer|min:1',
            'advance_date'        => 'required|date',
            'deduct_month'        => 'required|date_format:Y-m',
            'note'                => 'nullable|string',
        ]);

        Employee::forReseller($resellerId)->findOrFail($data['employee_id']);

        $installmentAmount = $data['payment_type'] === 'installment'
            ? round($data['amount'] / $data['total_installments'], 2)
            : $data['amount'];

        SalaryAdvance::create([
            'employee_id'         => $data['employee_id'],
            'amount'              => $data['amount'],
            'payment_type'        => $data['payment_type'],
            'installment_amount'  => $installmentAmount,
            'total_installments'  => $data['payment_type'] === 'installment' ? $data['total_installments'] : 1,
            'paid_installments'   => 0,
            'remaining_amount'    => $data['amount'],
            'advance_date'        => $data['advance_date'],
            'deduct_month'        => $data['deduct_month'],
            'status'              => 'pending',
            'note'                => $data['note'] ?? null,
            'created_by'          => null,
        ]);

        return redirect()->route('reseller.hr.salary-advance.index')->with('success', 'Salary advance added successfully.');
    }
}