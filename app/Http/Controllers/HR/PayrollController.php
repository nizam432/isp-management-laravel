<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HR\Employee;
use App\Models\HR\Payroll;
use App\Models\HR\PayrollDetail;
use App\Models\HR\PayrollPayment;
use App\Models\HR\SalaryHead;
use App\Models\HR\SalaryAdvance;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    // ── HR Payroll expense category ───────────────────────────────
    private function getPayrollCategoryId(): ?int
    {
        return ExpenseCategory::where('slug', 'hr-payroll')
            ->orWhere('slug', 'salary')
            ->orWhere('name', 'Salary')
            ->orWhere('name', 'HR & Payroll')
            ->value('id');
    }

    // ── Create Expense for a payroll payment ──────────────────────
    private function createExpenseForPayment(Payroll $payroll, PayrollPayment $payment): ?Expense
    {
        $categoryId = $this->getPayrollCategoryId();
        if (! $categoryId) return null;

        return Expense::create([
            'category_id'       => $categoryId,
            'amount'            => $payment->amount,
            'expense_date'      => $payment->payment_date,
            'payment_method'    => $payment->payment_method,
            'payee'             => $payroll->employee->name,
            'reference_no'      => 'PAY-' . $payroll->id,
            'description'       => "Salary Payment — {$payroll->employee->name} ({$payroll->month})"
                                   . " [Payment: ৳" . number_format($payment->amount, 2) . "]",
            'status'            => 'approved',
            'source_type'       => 'hr_payroll',
            'source_id'         => $payroll->id,
            'source_invoice_id' => $payroll->id,
            'created_by'        => auth()->id(),
            'approved_by'       => auth()->id(),
            'approved_at'       => now(),
        ]);
    }

    // =========================================================================
    // INDEX
    // =========================================================================
    public function index(Request $request)
    {
        $month    = $request->month ?? now()->format('Y-m');
        $payrolls = Payroll::with(['employee', 'payments'])
            ->where('month', $month)
            ->latest()
            ->paginate(20);

        return view('hr.payroll.index', compact('payrolls', 'month'));
    }

    // =========================================================================
    // GENERATE
    // =========================================================================
    public function generate(Request $request)
    {
        $month       = $request->month ?? now()->format('Y-m');
        $employees   = Employee::active()->with('advances')->get();
        $salaryHeads = SalaryHead::active()->get();

        return view('hr.payroll.generate', compact('employees', 'salaryHeads', 'month'));
    }

    // =========================================================================
    // STORE
    // =========================================================================
    public function store(Request $request)
    {
        $request->validate([
            'month'     => 'required|string',
            'employees' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $skipped = 0;
            $created = 0;

            foreach ($request->employees as $employeeId => $data) {
                if (empty($data['include'])) continue;

                // ── Duplicate check ────────────────────────────────
                $exists = Payroll::where('employee_id', $employeeId)
                    ->where('month', $request->month)
                    ->whereNotIn('status', ['void'])
                    ->exists();

                if ($exists) { $skipped++; continue; }

                $employee = Employee::findOrFail($employeeId);

                $grossSalary    = 0;
                $totalDeduction = 0;
                $heads          = [];

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

                $advance = SalaryAdvance::where('employee_id', $employeeId)
                    ->where('deduct_month', $request->month)
                    ->where('status', 'pending')
                    ->sum('amount');
                $totalDeduction += $advance;

                $netSalary = $grossSalary - $totalDeduction;

                $payroll = Payroll::create([
                    'employee_id'     => $employeeId,
                    'month'           => $request->month,
                    'basic_salary'    => $employee->basic_salary,
                    'gross_salary'    => $grossSalary,
                    'total_deduction' => $totalDeduction,
                    'net_salary'      => $netSalary,
                    'paid_amount'     => 0,
                    'due_amount'      => $netSalary,
                    'status'          => 'pending',
                    'created_by'      => auth()->id(),
                ]);

                foreach ($heads as $headId => $amount) {
                    PayrollDetail::create([
                        'payroll_id'     => $payroll->id,
                        'salary_head_id' => $headId,
                        'amount'         => $amount,
                    ]);
                }

                SalaryAdvance::where('employee_id', $employeeId)
                    ->where('deduct_month', $request->month)
                    ->where('status', 'pending')
                    ->update(['status' => 'deducted']);

                $created++;
            }

            DB::commit();

            $msg = "{$created} payroll(s) generated.";
            if ($skipped > 0) $msg .= " {$skipped} skipped (already exists).";

            return redirect()->route('payroll.index', ['month' => $request->month])
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // SHOW
    // =========================================================================
    public function show(Payroll $payroll)
    {
        $payroll->load(['employee.department', 'employee.position', 'details.salaryHead', 'payments.createdBy']);
        return view('hr.payroll.show', compact('payroll'));
    }

    // =========================================================================
    // EDIT
    // =========================================================================
    public function edit(Payroll $payroll)
    {
        if (! $payroll->isPending()) {
            return redirect()->route('payroll.index')
                ->with('error', 'Only pending payroll can be edited.');
        }

        $payroll->load('details.salaryHead');
        $salaryHeads = SalaryHead::active()->get();
        return view('hr.payroll.edit', compact('payroll', 'salaryHeads'));
    }

    // =========================================================================
    // UPDATE
    // =========================================================================
    public function update(Request $request, Payroll $payroll)
    {
        if (! $payroll->isPending()) {
            return response()->json(['success' => false, 'message' => 'Only pending payroll can be edited.'], 422);
        }

        $request->validate([
            'heads' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $grossSalary    = 0;
            $totalDeduction = 0;

            foreach ($request->heads as $headId => $amount) {
                $head   = SalaryHead::find($headId);
                $amount = (float) $amount;
                if ($head->type === 'addition') {
                    $grossSalary += $amount;
                } else {
                    $totalDeduction += $amount;
                }
            }

            $netSalary = $grossSalary - $totalDeduction;

            $payroll->update([
                'gross_salary'    => $grossSalary,
                'total_deduction' => $totalDeduction,
                'net_salary'      => $netSalary,
                'due_amount'      => max(0, $netSalary - $payroll->paid_amount),
            ]);

            $payroll->details()->delete();
            foreach ($request->heads as $headId => $amount) {
                PayrollDetail::create([
                    'payroll_id'     => $payroll->id,
                    'salary_head_id' => $headId,
                    'amount'         => (float) $amount,
                ]);
            }

            DB::commit();
            return redirect()->route('payroll.index')->with('success', 'Payroll updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // =========================================================================
    // PAY — AJAX (Partial/Full payment)
    // =========================================================================
    public function pay(Request $request, Payroll $payroll)
    {
        if ($payroll->isVoid()) {
            return response()->json(['success' => false, 'message' => 'Void payroll cannot be paid.'], 422);
        }
        if ($payroll->isPaid()) {
            return response()->json(['success' => false, 'message' => 'Already fully paid.'], 422);
        }

        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,bkash,nagad,rocket,cheque',
            'payment_date'   => 'required|date',
            'transaction_no' => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:255',
        ]);

        $due = (float) $payroll->due_amount;
        if ((float) $request->amount > $due) {
            return response()->json([
                'success' => false,
                'message' => "Amount (৳{$request->amount}) cannot exceed Due (৳{$due}).",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payroll->load('employee');

            $payment = PayrollPayment::create([
                'payroll_id'     => $payroll->id,
                'amount'         => $request->amount,
                'payment_date'   => $request->payment_date,
                'payment_method' => $request->payment_method,
                'transaction_no' => $request->transaction_no,
                'note'           => $request->note,
                'created_by'     => auth()->id(),
            ]);

            $expense = $this->createExpenseForPayment($payroll, $payment);
            if ($expense) {
                $payment->update(['expense_id' => $expense->id]);
            }

            $payroll->recalculate();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Payment of ৳" . number_format($request->amount, 2) . " recorded.",
                'status'  => $payroll->fresh()->status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PAYMENT HISTORY — AJAX
    // =========================================================================
    public function paymentHistory(Payroll $payroll)
    {
        $payments = $payroll->payments()->with('createdBy')->latest()->get();

        return response()->json([
            'success'  => true,
            'payments' => $payments->map(fn($p) => [
                'id'           => $p->id,
                'payment_date' => optional($p->payment_date)->format('d M Y'),
                'amount'       => number_format($p->amount, 2),
                'method'       => strtoupper($p->payment_method),
                'transaction_no' => $p->transaction_no ?? '—',
                'note'         => $p->note ?? '—',
                'created_by'   => $p->createdBy->name ?? '—',
                'is_void'      => $p->isVoid(),
            ]),
            'total' => number_format($payments->where('status', 'active')->sum('amount'), 2),
        ]);
    }

    // =========================================================================
    // EXPORT XLSX
    // =========================================================================
    public function exportXlsx(Request $request)
    {
        $month    = $request->month ?? now()->format('Y-m');
        $payrolls = Payroll::with('employee')->where('month', $month)->latest()->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll');

        $headers = ['A'=>'#','B'=>'Employee','C'=>'Code','D'=>'Basic',
                    'E'=>'Gross','F'=>'Deduction','G'=>'Net Salary',
                    'H'=>'Paid','I'=>'Due','J'=>'Status'];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col.'1', $label);
            $sheet->getStyle($col.'1')->getFont()->setBold(true);
            $sheet->getStyle($col.'1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1a237e');
            $sheet->getStyle($col.'1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach ($payrolls as $i => $p) {
            $row = $i + 2;
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, $p->employee->name ?? '—');
            $sheet->setCellValue('C'.$row, $p->employee->employee_code ?? '—');
            $sheet->setCellValue('D'.$row, (float) $p->basic_salary);
            $sheet->setCellValue('E'.$row, (float) $p->gross_salary);
            $sheet->setCellValue('F'.$row, (float) $p->total_deduction);
            $sheet->setCellValue('G'.$row, (float) $p->net_salary);
            $sheet->setCellValue('H'.$row, (float) $p->paid_amount);
            $sheet->setCellValue('I'.$row, (float) $p->due_amount);
            $sheet->setCellValue('J'.$row, ucfirst($p->status));
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'payroll-' . $month . '.xlsx';
        $tmpPath  = storage_path('app/' . $filename);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // =========================================================================
    // EXPORT PDF
    // =========================================================================
    public function exportPdf(Request $request)
    {
        $month    = $request->month ?? now()->format('Y-m');
        $payrolls = Payroll::with('employee')->where('month', $month)->latest()->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'hr.payroll.export-pdf',
            compact('payrolls', 'month')
        )->setPaper('a4', 'landscape');

        return $pdf->download('payroll-' . $month . '.pdf');
    }

    // =========================================================================
    // VOID PAYMENT
    // =========================================================================
    public function voidPayment(Request $request, PayrollPayment $payment)
    {
        if ($payment->isVoid()) {
            return response()->json(['success' => false, 'message' => 'Already voided.'], 422);
        }

        $request->validate(['reason' => 'required|string|max:255']);

        $payroll = Payroll::find($payment->payroll_id);
        if (! $payroll) {
            return response()->json(['success' => false, 'message' => 'Payroll not found.'], 422);
        }

        DB::beginTransaction();
        try {
            if ($payment->expense_id) {
                $expense = \App\Models\Expense::find($payment->expense_id);
                if ($expense && ! $expense->isVoid()) {
                    $expense->update([
                        'status'        => 'void',
                        'reject_reason' => $request->reason,
                        'void_date'     => now(),
                        'void_by'       => auth()->id(),
                    ]);
                }
            }

            $payment->update([
                'status'      => 'void',
                'void_reason' => $request->reason,
                'void_date'   => now(),
                'void_by'     => auth()->id(),
            ]);

            $payroll->recalculate();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Payment voided. Linked expense also voided. Payroll updated.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // DELETE
    // =========================================================================
    public function destroy(Payroll $payroll)
    {
        if (! $payroll->isPending()) {
            return response()->json(['success' => false, 'message' => 'Only pending payroll can be deleted.'], 422);
        }

        if ($payroll->payments()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'This payroll has payment history. Cannot delete.'], 422);
        }

        DB::beginTransaction();
        try {
            // Return advance
            SalaryAdvance::where('employee_id', $payroll->employee_id)
                ->where('deduct_month', $payroll->month)
                ->where('status', 'deducted')
                ->update(['status' => 'pending']);

            $payroll->details()->delete();
            $payroll->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payroll deleted. Advance returned.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // BULK DELETE
    // =========================================================================
    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        DB::beginTransaction();
        try {
            $payrolls = Payroll::whereIn('id', $request->ids)
                ->where('status', 'pending')
                ->whereDoesntHave('payments')
                ->get();

            foreach ($payrolls as $payroll) {
                SalaryAdvance::where('employee_id', $payroll->employee_id)
                    ->where('deduct_month', $payroll->month)
                    ->where('status', 'deducted')
                    ->update(['status' => 'pending']);

                $payroll->details()->delete();
                $payroll->delete();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => count($payrolls) . ' payroll(s) deleted.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PAYSLIP PDF DOWNLOAD
    // =========================================================================
    public function payslipPdf(Payroll $payroll)
    {
        $payroll->load(['employee.department', 'employee.position', 'details.salaryHead', 'payments']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'hr.payroll.payslip',
            compact('payroll')
        )->setPaper('a4', 'portrait');

        return $pdf->download('payslip-' . $payroll->employee->employee_code . '-' . $payroll->month . '.pdf');
    }

    // =========================================================================
    // PAYSLIP
    // =========================================================================
    public function payslip(Payroll $payroll)
    {
        $payroll->load(['employee.department', 'employee.position', 'details.salaryHead', 'payments']);
        return view('hr.payroll.payslip', compact('payroll'));
    }
}
