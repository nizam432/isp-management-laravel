{{-- resources/views/hr/payroll/edit.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Edit Payroll')
@section('page_actions')
    <a href="{{ route('payroll.index', ['month' => $payroll->month]) }}"
       class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<form action="{{ route('payroll.update', $payroll) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Info Card --}}
    <div class="card mb-3">
        <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e,#283593);">
            <h5 class="m-0 text-white font-weight-bold">
                <i class="fas fa-user mr-2"></i>
                {{ $payroll->employee->name }}
                <small class="ml-2 text-warning">
                    <code>{{ $payroll->employee->employee_code }}</code>
                </small>
                <span class="badge badge-light ml-2">{{ \Carbon\Carbon::parse($payroll->month . '-01')->format('F Y') }}</span>
            </h5>
        </div>
        <div class="card-body py-2">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted">Basic Salary</small>
                    <div class="font-weight-bold">৳ {{ number_format($payroll->basic_salary) }}</div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Current Net Salary</small>
                    <div class="font-weight-bold text-primary">৳ {{ number_format($payroll->net_salary) }}</div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Paid Amount</small>
                    <div class="font-weight-bold text-success">৳ {{ number_format($payroll->paid_amount) }}</div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Status</small>
                    <div>{!! $payroll->statusBadge !!}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Salary Heads --}}
    <div class="card">
        <div class="card-header py-2">
            <h5 class="m-0 font-weight-bold">
                <i class="fas fa-list mr-1"></i> Salary Components
                <span class="float-right font-weight-bold text-success" id="netDisplay">
                    Net: ৳ 0
                </span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($salaryHeads as $head)
                @php
                    $detail = $payroll->details->firstWhere('salary_head_id', $head->id);
                    $amount = $detail ? $detail->amount : 0;
                @endphp
                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold">
                        <span class="text-{{ $head->type === 'addition' ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $head->type === 'addition' ? 'plus' : 'minus' }}"></i>
                        </span>
                        {{ $head->name }}
                        <span class="badge badge-{{ $head->type === 'addition' ? 'success' : 'danger' }} ml-1"
                              style="font-size:9px;">{{ ucfirst($head->type) }}</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">৳</span>
                        </div>
                        <input type="number"
                               name="heads[{{ $head->id }}]"
                               class="form-control salary-input"
                               data-type="{{ $head->type }}"
                               value="{{ $amount }}"
                               min="0" step="0.01">
                    </div>
                </div>
                @endforeach
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="alert py-2 mb-0" style="background:#f8f9fa; border:1px solid #dee2e6;">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <small class="text-muted d-block">Gross Salary</small>
                                <strong class="text-success" id="grossDisplay">৳ 0</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Total Deduction</small>
                                <strong class="text-danger" id="deductDisplay">৳ 0</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Net Salary</small>
                                <strong class="text-primary" id="netDisplay2">৳ 0</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Due After Edit</small>
                                <strong class="text-warning" id="dueDisplay">৳ 0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('payroll.index', ['month' => $payroll->month]) }}"
               class="btn btn-secondary mr-2">
                <i class="fas fa-times mr-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Update Payroll
            </button>
        </div>
    </div>

</form>

@endsection

@push('js')
<script>
var paidAmount = {{ $payroll->paid_amount }};

function calculate() {
    var gross   = 0;
    var deduct  = 0;

    document.querySelectorAll('.salary-input').forEach(function (inp) {
        var val = parseFloat(inp.value) || 0;
        if (inp.getAttribute('data-type') === 'addition') {
            gross += val;
        } else {
            deduct += val;
        }
    });

    var net = gross - deduct;
    var due = Math.max(0, net - paidAmount);
    var fmt = function(v) { return '৳ ' + v.toLocaleString('en-US', {minimumFractionDigits:2}); };

    document.getElementById('grossDisplay').textContent  = fmt(gross);
    document.getElementById('deductDisplay').textContent = fmt(deduct);
    document.getElementById('netDisplay').textContent    = 'Net: ' + fmt(net);
    document.getElementById('netDisplay2').textContent   = fmt(net);
    document.getElementById('dueDisplay').textContent    = fmt(due);
}

document.querySelectorAll('.salary-input').forEach(function (inp) {
    inp.addEventListener('input', calculate);
});

// Init
calculate();
</script>
@endpush
