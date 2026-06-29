{{-- resources/views/hr/payroll/show.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Payroll Details')
@section('page_actions')
    <a href="{{ route('payroll.payslip-pdf', $payroll) }}" class="btn btn-danger btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> Download PDF
    </a>
    <a href="{{ route('payroll.payslip', $payroll) }}" class="btn btn-info btn-sm ml-1" target="_blank">
        <i class="fas fa-print mr-1"></i> Print
    </a>
    <a href="{{ route('payroll.index', ['month' => $payroll->month]) }}" class="btn btn-secondary btn-sm ml-1">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<div class="row">
    {{-- ── Payroll Info ── --}}
    <div class="col-md-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e,#283593);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-user mr-2"></i>
                    {{ $payroll->employee->name }}
                    <small class="ml-2"><code class="text-warning">{{ $payroll->employee->employee_code }}</code></small>
                    <span class="badge badge-light ml-2">{{ \Carbon\Carbon::parse($payroll->month . '-01')->format('F Y') }}</span>
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted" style="width:140px;">Department</td>
                                <td>{{ $payroll->employee->department->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Position</td>
                                <td>{{ $payroll->employee->position->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Basic Salary</td>
                                <td class="font-weight-bold">৳ {{ number_format($payroll->basic_salary) }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted" style="width:130px;">Gross Salary</td>
                                <td class="font-weight-bold text-success">৳ {{ number_format($payroll->gross_salary) }}</td></tr>
                            <tr><td class="text-muted">Deduction</td>
                                <td class="font-weight-bold text-danger">৳ {{ number_format($payroll->total_deduction) }}</td></tr>
                            <tr><td class="text-muted">Net Salary</td>
                                <td class="font-weight-bold text-primary">৳ {{ number_format($payroll->net_salary) }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Salary Components ── --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-list mr-1"></i> Salary Components</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>Component</th>
                            <th>Type</th>
                            <th class="text-right">Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payroll->details as $detail)
                        <tr>
                            <td>{{ $detail->salaryHead->name ?? '—' }}</td>
                            <td>
                                @if($detail->salaryHead->type === 'addition')
                                    <span class="badge badge-success">Addition</span>
                                @else
                                    <span class="badge badge-danger">Deduction</span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold
                                {{ $detail->salaryHead->type === 'addition' ? 'text-success' : 'text-danger' }}">
                                ৳ {{ number_format($detail->amount, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background:#f8f9fa;">
                        <tr>
                            <td colspan="2" class="text-right font-weight-bold">Net Salary</td>
                            <td class="text-right font-weight-bold text-primary">৳ {{ number_format($payroll->net_salary, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Payment Summary & History ── --}}
    <div class="col-md-4">
        {{-- Summary Card --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-money-bill-wave mr-1"></i> Payment Summary
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>{!! $payroll->statusBadge !!}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Net Salary</td>
                        <td class="font-weight-bold">৳ {{ number_format($payroll->net_salary, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Paid</td>
                        <td class="font-weight-bold text-success">৳ {{ number_format($payroll->paid_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Balance Due</td>
                        <td class="font-weight-bold {{ $payroll->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            ৳ {{ number_format($payroll->due_amount, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Payment History --}}
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e,#283593);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-history mr-1"></i> Payment History
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>Date</th>
                            <th class="text-right">Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payroll->payments as $pay)
                        <tr class="{{ $pay->isVoid() ? 'text-muted' : '' }}">
                            <td>{{ optional($pay->payment_date)->format('d M Y') }}</td>
                            <td class="text-right font-weight-bold {{ $pay->isVoid() ? 'text-muted' : 'text-success' }}">
                                ৳ {{ number_format($pay->amount, 2) }}
                            </td>
                            <td><span class="badge badge-light border" style="font-size:10px;">{{ strtoupper($pay->payment_method) }}</span></td>
                            <td>
                                @if($pay->isVoid())
                                    <span class="badge badge-secondary" style="font-size:10px;">Void</span>
                                @else
                                    <span class="badge badge-success" style="font-size:10px;">Active</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No payments yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($payroll->payments->count())
                    <tfoot style="background:#e8f5e9;">
                        <tr>
                            <td class="text-right font-weight-bold">Total</td>
                            <td class="text-right font-weight-bold text-success">
                                ৳ {{ number_format($payroll->payments->where('status', 'active')->sum('amount'), 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
