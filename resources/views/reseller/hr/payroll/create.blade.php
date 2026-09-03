@extends('reseller.layouts.app')

@section('title', 'Generate Payroll')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Generate Payroll</h4>
    <a href="{{ route('reseller.hr.payroll.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form action="{{ route('reseller.hr.payroll.store') }}" method="POST">
    @csrf

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" id="employeeSelect" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}" data-basic="{{ $e->basic_salary }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Month <span class="text-danger">*</span></label>
                        <input type="month" name="month" class="form-control" value="{{ old('month', now()->format('Y-m')) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Basic Salary</label>
                        <input type="text" id="basicSalaryDisplay" class="form-control bg-light" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Payment Method (optional, for reference)</label>
                        <input type="text" name="payment_method" class="form-control" placeholder="e.g. Bank Transfer">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-success text-white py-2">Additions</div>
                <div class="card-body">
                    @forelse($additions as $head)
                    <div class="form-group row align-items-center">
                        <label class="col-7 mb-0">{{ $head->name }}</label>
                        <div class="col-5">
                            <input type="number" step="0.01" min="0" name="additions[{{ $head->id }}]" class="form-control form-control-sm addition-input">
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small mb-0">No addition heads. <a href="{{ route('reseller.hr.salary-head.index') }}">Add one</a>.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-danger text-white py-2">Deductions</div>
                <div class="card-body">
                    @forelse($deductions as $head)
                    <div class="form-group row align-items-center">
                        <label class="col-7 mb-0">{{ $head->name }}</label>
                        <div class="col-5">
                            <input type="number" step="0.01" min="0" name="deductions[{{ $head->id }}]" class="form-control form-control-sm deduction-input">
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small mb-0">No deduction heads. <a href="{{ route('reseller.hr.salary-head.index') }}">Add one</a>.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Gross Salary:</strong> ৳<span id="grossDisplay">0</span></div>
                <div class="col-md-4"><strong>Total Deduction:</strong> ৳<span id="deductionDisplay">0</span></div>
                <div class="col-md-4"><strong>Net Salary:</strong> ৳<span id="netDisplay">0</span></div>
            </div>
            <small class="text-muted">Note: any pending Salary Advance due this month will be auto-deducted on save (not reflected in this live preview).</small>
        </div>
    </div>

    <div class="form-group">
        <label>Note</label>
        <textarea name="note" class="form-control" rows="2"></textarea>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Generate Payroll</button>
</form>

@endsection

@section('js')
<script>
function recalc() {
    var basic = parseFloat($('#basicSalaryDisplay').val()) || 0;
    var additions = 0, deductions = 0;
    $('.addition-input').each(function () { additions += parseFloat($(this).val()) || 0; });
    $('.deduction-input').each(function () { deductions += parseFloat($(this).val()) || 0; });

    var gross = basic + additions;
    var net   = gross - deductions;

    $('#grossDisplay').text(gross.toLocaleString());
    $('#deductionDisplay').text(deductions.toLocaleString());
    $('#netDisplay').text(net.toLocaleString());
}

$('#employeeSelect').on('change', function () {
    var basic = $(this).find(':selected').data('basic') || 0;
    $('#basicSalaryDisplay').val(basic);
    recalc();
});

$(document).on('input', '.addition-input, .deduction-input', recalc);
</script>
@endsection