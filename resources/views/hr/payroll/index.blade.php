{{-- resources/views/hr/payroll/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Payroll')
@section('page_actions')
    <a href="{{ route('payroll.generate') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Generate Payroll
    </a>
@endsection
@section('page_content')

{{-- Month Filter --}}
<div class="card">
    <div class="card-body py-2">
        <form method="GET" class="form-inline">
            <label class="mr-2 font-weight-bold">Month:</label>
            <input type="month" name="month" class="form-control form-control-sm mr-2"
                   value="{{ $month }}">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-money-bill-wave mr-1"></i>
            Payroll — {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
        </h3>
        <div>
            <span class="badge badge-success mr-1">
                Paid: {{ $payrolls->where('status', 'paid')->count() }}
            </span>
            <span class="badge badge-warning">
                Pending: {{ $payrolls->where('status', 'pending')->count() }}
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Basic</th>
                    <th>Gross</th>
                    <th>Deduction</th>
                    <th>Net Salary</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th style="width:90px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $i => $payroll)
                <tr>
                    <td class="text-muted">{{ $payrolls->firstItem() + $i }}</td>
                    <td>
                        <a href="{{ route('employees.show', $payroll->employee) }}" class="font-weight-bold">
                            {{ $payroll->employee->name }}
                        </a>
                        <br><small class="text-muted"><code>{{ $payroll->employee->employee_code }}</code></small>
                    </td>
                    <td>৳ {{ number_format($payroll->basic_salary) }}</td>
                    <td>৳ {{ number_format($payroll->gross_salary) }}</td>
                    <td class="text-danger">৳ {{ number_format($payroll->total_deduction) }}</td>
                    <td><strong>৳ {{ number_format($payroll->net_salary) }}</strong></td>
                    <td>
                        @if($payroll->payment_date)
                            <span class="badge badge-light border">{{ ucfirst($payroll->payment_method) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $payroll->status === 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($payroll->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('payroll.payslip', $payroll) }}"
                           class="btn btn-xs btn-info" target="_blank" title="Payslip">
                            <i class="fas fa-print"></i>
                        </a>
                        @if($payroll->status === 'pending')
                            <button class="btn btn-xs btn-success"
                                    onclick="payNow({{ $payroll->id }})" title="Mark as Paid">
                                <i class="fas fa-check"></i>
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        No payroll records for this month.
                        <a href="{{ route('payroll.generate') }}" class="d-block mt-2">
                            Generate Payroll
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Total {{ $payrolls->total() }}</small>
        {{ $payrolls->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
</div>

{{-- Pay Modal --}}
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-money-bill mr-1"></i> Mark as Paid</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="payForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Payment Method</label>
                        <select name="payment_method" class="form-control form-control-sm">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control form-control-sm"
                               value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check mr-1"></i> Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
function payNow(id) {
    document.getElementById('payForm').action = '/payroll/' + id + '/pay';
    $('#payModal').modal('show');
}
</script>
@endpush
