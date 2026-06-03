{{-- resources/views/hr/payroll/generate.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Generate Payroll')
@section('page_actions')
    <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<form action="{{ route('payroll.store') }}" method="POST">
    @csrf

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-calendar mr-1"></i> Payroll Month</h3>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Select Month <span class="text-danger">*</span></label>
                        <input type="month" name="month" class="form-control"
                               value="{{ $month }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0 mt-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="selectAllEmp"
                                   onchange="toggleAllEmployees(this)">
                            <label class="custom-control-label" for="selectAllEmp">
                                Select All Employees
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($employees as $emp)
    <div class="card">
        <div class="card-header py-2">
            <div class="d-flex align-items-center">
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" class="custom-control-input emp-check"
                           id="emp_{{ $emp->id }}"
                           name="employees[{{ $emp->id }}][include]" value="1" checked>
                    <label class="custom-control-label" for="emp_{{ $emp->id }}"></label>
                </div>
                <div>
                    <strong>{{ $emp->name }}</strong>
                    <small class="text-muted ml-2"><code>{{ $emp->employee_code }}</code></small>
                    @if($emp->department)
                        <span class="badge badge-light border ml-2">{{ $emp->department->name }}</span>
                    @endif
                    <span class="ml-2 text-muted small">Basic: ৳ {{ number_format($emp->basic_salary) }}</span>
                </div>
                <div class="ml-auto">
                    <span class="font-weight-bold text-success" id="net_{{ $emp->id }}">
                        Net: ৳ 0
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body py-2">
            <div class="row">
                @foreach($salaryHeads as $head)
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold">
                        <span class="text-{{ $head->type === 'addition' ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $head->type === 'addition' ? 'plus' : 'minus' }}"></i>
                        </span>
                        {{ $head->name }}
                    </label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">৳</span>
                        </div>
                        <input type="number"
                               name="employees[{{ $emp->id }}][heads][{{ $head->id }}]"
                               class="form-control salary-input"
                               data-employee="{{ $emp->id }}"
                               data-type="{{ $head->type }}"
                               value="{{ $head->name === 'Basic Salary' ? $emp->basic_salary : 0 }}"
                               min="0">
                    </div>
                </div>
                @endforeach

                @php
                    $pendingAdvance = $emp->advances->where('status', 'pending')->sum('amount');
                @endphp
                @if($pendingAdvance > 0)
                <div class="col-md-12 mt-1">
                    <div class="alert alert-warning py-1 mb-0">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i>
                            Pending advance: <strong>৳ {{ number_format($pendingAdvance) }}</strong>
                            — will be auto deducted.
                        </small>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach

    <div class="text-right mb-3">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save mr-1"></i> Generate Payroll
        </button>
    </div>

</form>

@endsection

@push('js')
<script>
function toggleAllEmployees(el) {
    document.querySelectorAll('.emp-check').forEach(cb => cb.checked = el.checked);
}

// Calculate net salary per employee
document.querySelectorAll('.salary-input').forEach(function(input) {
    input.addEventListener('input', function() {
        calculateNet(this.getAttribute('data-employee'));
    });
});

function calculateNet(empId) {
    var addition   = 0;
    var deduction  = 0;
    document.querySelectorAll('.salary-input[data-employee="' + empId + '"]').forEach(function(inp) {
        var val = parseFloat(inp.value) || 0;
        if (inp.getAttribute('data-type') === 'addition') {
            addition += val;
        } else {
            deduction += val;
        }
    });
    var net = addition - deduction;
    document.getElementById('net_' + empId).textContent = 'Net: ৳ ' + net.toLocaleString();
}

// Init
document.querySelectorAll('.salary-input').forEach(function(inp) {
    calculateNet(inp.getAttribute('data-employee'));
});
</script>
@endpush
