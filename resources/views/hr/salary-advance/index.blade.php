{{-- resources/views/hr/salary-advance/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Salary Advance')
@section('page_content')

<div class="row">

    {{-- Advance List --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-hand-holding-usd mr-1"></i> Salary Advance List</h3>
                <span class="badge badge-info">{{ $advances->total() }} records</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Progress</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th style="width:80px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($advances as $i => $adv)
                        <tr>
                            <td class="text-muted">{{ $advances->firstItem() + $i }}</td>
                            <td>
                                <strong>{{ $adv->employee->name }}</strong>
                                <br><small class="text-muted"><code>{{ $adv->employee->employee_code }}</code></small>
                            </td>
                            <td>
                                <strong>{{ number_format($adv->amount) }}</strong>
                                @if($adv->remaining_amount > 0 && $adv->status === 'pending')
                                    <br><small class="text-danger">Remaining: {{ number_format($adv->remaining_amount) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($adv->payment_type === 'installment')
                                    <span class="badge badge-info">Installment</span>
                                    <br><small class="text-muted">{{ number_format($adv->installment_amount) }}/month</small>
                                @else
                                    <span class="badge badge-secondary">One Time</span>
                                @endif
                            </td>
                            <td>
                                @if($adv->payment_type === 'installment')
                                    <small>{{ $adv->paid_installments }}/{{ $adv->total_installments }} installments</small>
                                    <div class="progress mt-1" style="height:6px;">
                                        <div class="progress-bar bg-success"
                                             style="width:{{ $adv->total_installments > 0 ? ($adv->paid_installments / $adv->total_installments * 100) : 0 }}%">
                                        </div>
                                    </div>
                                @else
                                    <small class="text-muted">—</small>
                                @endif
                            </td>
                            <td>
                                <small>{{ $adv->advance_date->format('d M Y') }}</small>
                                @if($adv->deduct_month)
                                    <br><small class="text-muted">
                                        Deduct: {{ \Carbon\Carbon::parse($adv->deduct_month . '-01')->format('M Y') }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $adv->status === 'deducted' ? 'success' : 'warning' }}">
                                    {{ $adv->status === 'deducted' ? 'Completed' : 'Pending' }}
                                </span>
                            </td>
                            <td>
                                @if($adv->status === 'pending')
                                    <form action="{{ route('salary-advance.deduct', $adv) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="button" class="btn btn-xs btn-success swal-deduct"
                                                data-message="{{ $adv->payment_type === 'installment' ? 'Deduct installment of ' . number_format($adv->getNextDeductionAmount()) . '?' : 'Deduct full amount of ' . number_format($adv->amount) . '?' }}">
                                            <i class="fas fa-check mr-1"></i> Deduct
                                        </button>
                                    </form>
                                @else
                                    <span class="text-success small"><i class="fas fa-check-circle"></i> Done</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-hand-holding-usd fa-2x d-block mb-2"></i>
                                No advance records found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">Total {{ $advances->total() }}</small>
                {{ $advances->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- Add Advance Form --}}
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus mr-1"></i> New Advance</h3>
            </div>
            <form action="{{ route('salary-advance.store') }}" method="POST">
                @csrf
                <div class="card-body">

                    {{-- Employee --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->name }} ({{ $emp->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Amount --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Total Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">৳</span>
                            </div>
                            <input type="number" name="amount" id="advAmount"
                                   class="form-control" min="1" placeholder="0" required
                                   oninput="calculateInstallments()">
                        </div>
                    </div>

                    {{-- Payment Type --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Payment Type <span class="text-danger">*</span></label>
                        <div>
                            <div class="custom-control custom-radio d-inline-block mr-3">
                                <input type="radio" id="type_one_time" name="payment_type"
                                       value="one_time" class="custom-control-input" checked
                                       onchange="toggleInstallment(false)">
                                <label class="custom-control-label" for="type_one_time">
                                    One Time
                                </label>
                            </div>
                            <div class="custom-control custom-radio d-inline-block">
                                <input type="radio" id="type_installment" name="payment_type"
                                       value="installment" class="custom-control-input"
                                       onchange="toggleInstallment(true)">
                                <label class="custom-control-label" for="type_installment">
                                    Installment
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Installment Fields --}}
                    <div id="installmentFields" style="display:none;">
                        <div class="form-group">
                            <label class="font-weight-bold">Installment Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="installment_amount" id="installmentAmount"
                                       class="form-control" min="1" placeholder="Per month"
                                       oninput="calculateInstallments()">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Total Installments</label>
                            <input type="number" name="total_installments" id="totalInstallments"
                                   class="form-control" min="1" placeholder="Auto calculated" readonly>
                            <small class="text-muted">Auto calculated from amount / installment</small>
                        </div>
                    </div>

                    {{-- Advance Date --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Advance Date <span class="text-danger">*</span></label>
                        <input type="date" name="advance_date" class="form-control"
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    {{-- Deduct Month --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Start Deduct Month</label>
                        <input type="month" name="deduct_month" class="form-control"
                               value="{{ now()->format('Y-m') }}">
                        <small class="text-muted">Month when deduction starts</small>
                    </div>

                    {{-- Note --}}
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Note</label>
                        <textarea name="note" class="form-control" rows="2"
                                  placeholder="Optional..."></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save mr-1"></i> Save Advance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
// ── Toggle installment fields ─────────────────────────
function toggleInstallment(show) {
    var fields = document.getElementById('installmentFields');
    fields.style.display = show ? 'block' : 'none';
    document.getElementById('installmentAmount').required = show;
    if (!show) {
        document.getElementById('totalInstallments').value = 1;
    }
}

// ── Auto calculate total installments ────────────────
function calculateInstallments() {
    var amount      = parseFloat(document.getElementById('advAmount').value) || 0;
    var installment = parseFloat(document.getElementById('installmentAmount').value) || 0;
    if (amount > 0 && installment > 0) {
        var total = Math.ceil(amount / installment);
        document.getElementById('totalInstallments').value = total;
    }
}

// ── SweetAlert for deduct ─────────────────────────────
document.querySelectorAll('.swal-deduct').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var msg  = this.getAttribute('data-message');
        var form = this.closest('form');
        Swal.fire({
            title: 'Confirm Deduction',
            text: msg,
            icon: false,
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Deduct',
            cancelButtonText: 'Cancel',
            width: '340px',
            padding: '1rem',
        }).then(function(result) {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush