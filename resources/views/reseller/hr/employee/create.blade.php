@extends('reseller.layouts.app')

@section('title', 'Add Employee')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Add Employee</h4>
    <a href="{{ route('reseller.hr.employee.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form action="{{ route('reseller.hr.employee.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="card mb-3">
        <div class="card-header bg-primary text-white py-2"><h5 class="mb-0"><i class="fas fa-user mr-1"></i> Basic Info</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Department <span class="text-danger">*</span></label>
                        <select name="department_id" id="departmentSelect" class="form-control" required>
                            <option value="">-- Select --</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Position <span class="text-danger">*</span></label>
                        <select name="position_id" id="positionSelect" class="form-control" required>
                            <option value="">-- Select Department first --</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>NID Number</label>
                        <input type="text" name="nid_number" class="form-control" value="{{ old('nid_number') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Join Date <span class="text-danger">*</span></label>
                        <input type="date" name="join_date" class="form-control" value="{{ old('join_date', date('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Photo</label>
                        <input type="file" name="photo" class="form-control-file" accept="image/*">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Present Address</label>
                        <textarea name="present_address" class="form-control" rows="2">{{ old('present_address') }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Permanent Address</label>
                        <textarea name="permanent_address" class="form-control" rows="2">{{ old('permanent_address') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-success text-white py-2"><h5 class="mb-0"><i class="fas fa-money-bill mr-1"></i> Salary & Bank</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Basic Salary <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ old('basic_salary') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Salary Date (day of month)</label>
                        <input type="number" min="1" max="28" name="salary_date" class="form-control" value="{{ old('salary_date', 1) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Branch Name</label>
                        <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-info text-white py-2"><h5 class="mb-0"><i class="fas fa-phone mr-1"></i> Emergency Contact</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="emergency_name" class="form-control" value="{{ old('emergency_name') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="emergency_phone" class="form-control" value="{{ old('emergency_phone') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Relation</label>
                        <input type="text" name="emergency_relation" class="form-control" value="{{ old('emergency_relation') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Employee</button>
        </div>
    </div>
</form>

@endsection

@section('js')
<script>
var allPositions = @json($positions);

$('#departmentSelect').on('change', function () {
    var deptId = $(this).val();
    var $pos = $('#positionSelect');
    $pos.empty().append('<option value="">-- Select Position --</option>');
    allPositions.filter(function (p) { return String(p.department_id) === String(deptId); })
        .forEach(function (p) { $pos.append('<option value="' + p.id + '">' + p.name + '</option>'); });
});
</script>
@endsection