<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\Payroll;
use App\Models\HR\PayrollDetail;
use App\Models\HR\PayrollPayment;
use App\Models\HR\SalaryAdvance;
use App\Models\HR\SalaryHead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResellerPayrollController extends Controller
{
    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $payrolls = Payroll::whereHas('employee', fn($q) => $q->where('mac_reseller_id', $resellerId))
            ->with('employee')
            ->when($request->month, fn($q) => $q->where('month', $request->month))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total'   => Payroll::whereHas('employee', fn($q) => $q->where('mac_reseller_id', $resellerId))->count(),
            'pending' => Payroll::whereHas('employee', fn($q) => $q->where('mac_reseller_id', $resellerId))->where('status', 'pending')->count(),
            'paid'    => Payroll::whereHas('employee', fn($q) => $q->where('mac_reseller_id', $resellerId))->where('status', 'paid')->count(),
            'due'     => Payroll::whereHas('employee', fn($q) => $q->where('mac_reseller_id', $resellerId))->where('status', '!=', 'void')->sum('due_amount'),
        ];

        return view('reseller.hr.payroll.index', compact('payrolls', 'stats'));
    }

    public function create()
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $employees   = Employee::forReseller($resellerId)->active()->orderBy('name')->get(['id', 'name', 'basic_salary']);
        $additions   = SalaryHead::forReseller($resellerId)->active()->addition()->orderBy('name')->get();
        $deductions  = SalaryHead::forReseller($resellerId)->active()->deduction()->orderBy('name')->get();

        return view('reseller.hr.payroll.create', compact('employees', 'additions', 'deductions'));
    }

    public function store(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'month'         => 'required|date_format:Y-m',
            'payment_method'=> 'nullable|string|max:100',
            'note'          => 'nullable|string',
            'additions'     => 'nullable|array',
            'additions.*'   => 'nullable|numeric|min:0',
            'deductions'    => 'nullable|array',
            'deductions.*'  => 'nullable|numeric|min:0',
        ]);

        $employee = Employee::forReseller($resellerId)->findOrFail($data['employee_id']);

        $exists = Payroll::where('employee_id', $employee->id)->where('month', $data['month'])->exists();
        if ($exists) {
            return back()->withInput()->with('error', 'এই মাসের জন্য এই কর্মচারীর payroll ইতিমধ্যে তৈরি করা আছে।');
        }

        $payroll = DB::transaction(function () use ($employee, $data, $resellerId) {
            $basicSalary = (float) $employee->basic_salary;

            $additionTotal  = 0;
            $deductionTotal = 0;
            $detailRows     = [];

            foreach ($data['additions'] ?? [] as $headId => $amount) {
                if ($amount <= 0) continue;
                SalaryHead::forReseller($resellerId)->addition()->findOrFail($headId);
                $additionTotal += $amount;
                $detailRows[] = ['salary_head_id' => $headId, 'amount' => $amount];
            }

            foreach ($data['deductions'] ?? [] as $headId => $amount) {
                if ($amount <= 0) continue;
                SalaryHead::forReseller($resellerId)->deduction()->findOrFail($headId);
                $deductionTotal += $amount;
                $detailRows[] = ['salary_head_id' => $headId, 'amount' => -$amount];
            }

            // auto-apply any pending salary advance deduction due this month
            $advance = SalaryAdvance::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->forMonth($data['month'])
                ->first();

            $advanceDeduction = 0;
            if ($advance) {
                $advanceDeduction = $advance->getNextDeductionAmount();
                $deductionTotal  += $advanceDeduction;
            }

            $grossSalary = $basicSalary + $additionTotal;
            $netSalary   = $grossSalary - $deductionTotal;

            $payroll = Payroll::create([
                'employee_id'      => $employee->id,
                'month'            => $data['month'],
                'basic_salary'     => $basicSalary,
                'gross_salary'     => $grossSalary,
                'total_deduction'  => $deductionTotal,
                'net_salary'       => $netSalary,
                'paid_amount'      => 0,
                'due_amount'       => $netSalary,
                'payment_method'   => $data['payment_method'] ?? null,
                'status'           => 'pending',
                'note'             => $data['note'] ?? null,
                'created_by'       => null,
            ]);

            foreach ($detailRows as $row) {
                PayrollDetail::create([
                    'payroll_id'     => $payroll->id,
                    'salary_head_id' => $row['salary_head_id'],
                    'amount'         => $row['amount'],
                ]);
            }

            if ($advance && $advanceDeduction > 0) {
                $advance->deduct($advanceDeduction);

                // installment advances that aren't fully paid off yet should be
                // picked up again next month — the model's deduct() doesn't move
                // this pointer forward, so we do it here.
                if ($advance->isInstallment() && !$advance->isCompleted()) {
                    $nextMonth = \Carbon\Carbon::createFromFormat('Y-m', $data['month'])->addMonth()->format('Y-m');
                    $advance->update(['deduct_month' => $nextMonth]);
                }
            }

            return $payroll;
        });

        return redirect()->route('reseller.hr.payroll.show', $payroll)->with('success', 'Payroll generated successfully.');
    }

    public function show(Payroll $payroll)
    {
        $this->authorizeItem($payroll);
        $payroll->load(['employee', 'details.salaryHead', 'payments']);

        return view('reseller.hr.payroll.show', compact('payroll'));
    }

    public function pay(Request $request, Payroll $payroll)
    {
        $this->authorizeItem($payroll);

        abort_unless($payroll->isEditable(), 422, 'এই Payroll-এ আর পেমেন্ট নেওয়া যাবে না।');

        $data = $request->validate([
            'amount'         => 'required|numeric|min:1|max:' . max($payroll->due_amount, 0.01),
            'payment_date'   => 'required|date',
            'payment_method' => 'required|string|max:100',
            'transaction_no' => 'nullable|string|max:100',
            'note'           => 'nullable|string',
        ]);

        PayrollPayment::create([
            'payroll_id'     => $payroll->id,
            'amount'         => $data['amount'],
            'payment_date'   => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'transaction_no' => $data['transaction_no'] ?? null,
            'note'           => $data['note'] ?? null,
            'status'         => 'active',
            'created_by'     => null,
        ]);

        $payroll->recalculate();

        return back()->with('success', 'Payment recorded successfully.');
    }

    private function authorizeItem(Payroll $payroll): void
    {
        abort_unless($payroll->employee && $payroll->employee->mac_reseller_id === Auth::guard('mac_reseller')->id(), 403);
    }
}