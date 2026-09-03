@extends('reseller.layouts.app')

@section('title', 'Payroll Details')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Payroll — {{ $payroll->employee->name ?? '' }} ({{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->month)->format('F Y') }})</h4>
    <div>
        @if((float) $payroll->paid_amount == 0.0)
        <a href="{{ route('reseller.hr.payroll.edit', $payroll) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit mr-1"></i> Edit
        </a>
        @endif
        <a href="{{ route('reseller.hr.payroll.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-2">Summary</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Basic Salary</td><td>৳{{ number_format($payroll->basic_salary) }}</td></tr>
                    <tr><td class="text-muted">Gross Salary</td><td>৳{{ number_format($payroll->gross_salary) }}</td></tr>
                    <tr><td class="text-muted">Total Deduction</td><td>৳{{ number_format($payroll->total_deduction) }}</td></tr>
                    <tr><td class="text-muted">Net Salary</td><td><strong>৳{{ number_format($payroll->net_salary) }}</strong></td></tr>
                    <tr><td class="text-muted">Paid Amount</td><td class="text-success">৳{{ number_format($payroll->paid_amount) }}</td></tr>
                    <tr><td class="text-muted">Due Amount</td><td class="{{ $payroll->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">৳{{ number_format($payroll->due_amount) }}</td></tr>
                    <tr><td class="text-muted">Status</td><td>{!! $payroll->status_badge !!}</td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-2">Salary Head Breakdown</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="bg-light"><tr><th>Head</th><th>Amount</th></tr></thead>
                    <tbody>
                        @forelse($payroll->details as $d)
                        <tr>
                            <td>{{ $d->salaryHead->name ?? '—' }}</td>
                            <td class="{{ $d->amount < 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($d->amount) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No additional heads.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        @if($payroll->isEditable())
        <div class="card">
            <div class="card-header py-2 bg-success text-white">Record Payment</div>
            <form action="{{ route('reseller.hr.payroll.pay', $payroll) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" max="{{ $payroll->due_amount }}" name="amount" class="form-control" value="{{ $payroll->due_amount }}" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Transaction No</label>
                        <input type="text" name="transaction_no" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-success"><i class="fas fa-money-bill mr-1"></i> Record Payment</button>
                </div>
            </form>
        </div>
        @endif

        <div class="card">
            <div class="card-header py-2">Payment History</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="bg-light"><tr><th>Date</th><th>Amount</th><th>Method</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($payroll->payments as $pay)
                        <tr>
                            <td>{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') : '' }}</td>
                            <td>৳{{ number_format($pay->amount) }}</td>
                            <td><span class="badge badge-secondary">{{ ucfirst($pay->payment_method) }}</span></td>
                            <td>
                                @if($pay->isVoid())
                                    <span class="badge badge-danger">Voided</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                            <td>
                                @if($pay->isActive())
                                <button type="button" class="btn btn-xs btn-outline-danger void-payment-btn" data-id="{{ $pay->id }}" title="Void this payment">
                                    <i class="fas fa-undo"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No payments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Void Payment Modal --}}
<div class="modal fade" id="voidPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="voidPaymentForm" method="POST">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title">Void Payment</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label class="small">Reason <span class="text-danger">*</span></label>
                        <textarea name="void_reason" class="form-control form-control-sm" rows="2" required></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Void Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).on('click', '.void-payment-btn', function () {
    var id = $(this).data('id');
    $('#voidPaymentForm').attr('action', "{{ url('reseller/hr/payroll/' . $payroll->id . '/payment') }}/" + id + "/void");
    $('#voidPaymentModal').modal('show');
});
</script>
@endsection