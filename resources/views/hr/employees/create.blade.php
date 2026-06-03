{{-- resources/views/hr/employees/create.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Add Employee')
@section('page_actions')
    <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row">

        {{-- Left Column --}}
        <div class="col-md-8">

            {{-- Basic Info --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user mr-1"></i> Basic Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                       value="{{ old('name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Phone</label>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ old('phone') }}" placeholder="01XXXXXXXXX">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email') }}" required>
                                <small class="text-muted">Used for login</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">NID Number</label>
                                <input type="text" name="nid_number" class="form-control"
                                       value="{{ old('nid_number') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Department</label>
                                <select name="department_id" class="form-control" id="departmentSelect">
                                    <option value="">-- Select Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Position</label>
                                <select name="position_id" class="form-control" id="positionSelect">
                                    <option value="">-- Select Position --</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos->id }}"
                                            {{ old('position_id') == $pos->id ? 'selected' : '' }}>
                                            {{ $pos->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Join Date</label>
                                <input type="date" name="join_date" class="form-control"
                                       value="{{ old('join_date', now()->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-marker-alt mr-1"></i> Address</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Present Address</label>
                                <textarea name="present_address" class="form-control" rows="3"
                                          placeholder="Present address...">{{ old('present_address') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Permanent Address</label>
                                <textarea name="permanent_address" class="form-control" rows="3"
                                          placeholder="Permanent address...">{{ old('permanent_address') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Emergency Contact --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-phone-alt mr-1"></i> Emergency Contact</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Name</label>
                                <input type="text" name="emergency_name" class="form-control"
                                       value="{{ old('emergency_name') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Phone</label>
                                <input type="text" name="emergency_phone" class="form-control"
                                       value="{{ old('emergency_phone') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Relation</label>
                                <input type="text" name="emergency_relation" class="form-control"
                                       value="{{ old('emergency_relation') }}"
                                       placeholder="Father, Mother...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bank Account --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-university mr-1"></i> Bank Account</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control"
                                       value="{{ old('bank_name') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Account Number</label>
                                <input type="text" name="account_number" class="form-control"
                                       value="{{ old('account_number') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Branch</label>
                                <input type="text" name="branch_name" class="form-control"
                                       value="{{ old('branch_name') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Educational Qualification --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-graduation-cap mr-1"></i> Educational Qualification
                    </h3>
                    <button type="button" class="btn btn-xs btn-success" onclick="addEducation()">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" id="educationTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>Last Achieved Degree</th>
                                <th>Institution / Board</th>
                                <th>Passing Year</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="educationBody">
                            <tr id="edu-row-0">
                                <td>
                                    <input type="text" name="educations[0][degree]"
                                           class="form-control form-control-sm"
                                           placeholder="e.g. B.Sc">
                                </td>
                                <td>
                                    <input type="text" name="educations[0][institution]"
                                           class="form-control form-control-sm"
                                           placeholder="University / Board">
                                </td>
                                <td>
                                    <input type="text" name="educations[0][passing_year]"
                                           class="form-control form-control-sm"
                                           placeholder="2020">
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Documents --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Documents</h3>
                    <button type="button" class="btn btn-xs btn-success" onclick="addDocument()">
                        <i class="fas fa-plus mr-1"></i> Add Document
                    </button>
                </div>
                <div class="card-body" id="documentsContainer">
                    <p class="text-muted small mb-0">
                        Click <strong>+ Add Document</strong> to attach files (NID, Certificate, etc.)
                    </p>
                </div>
            </div>

        </div>

        {{-- Right Column --}}
        <div class="col-md-4">

            {{-- Photo --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-camera mr-1"></i> Photo</h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div id="photoPreview"
                             class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center"
                             style="width:120px;height:120px;font-size:48px;color:#fff;">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <input type="file" name="photo" class="form-control-file"
                           accept="image/*" onchange="previewPhoto(this)">
                    <small class="text-muted">PNG, JPG — max 2MB</small>
                </div>
            </div>

            {{-- Salary --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave mr-1"></i> Salary
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Basic Salary</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">৳</span>
                            </div>
                            <input type="number" name="basic_salary" class="form-control"
                                   value="{{ old('basic_salary', 0) }}" min="0">
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Salary Date</label>
                        <select name="salary_date" class="form-control">
                            @for($d = 1; $d <= 28; $d++)
                                <option value="{{ $d }}"
                                    {{ old('salary_date', 1) == $d ? 'selected' : '' }}>
                                    {{ $d }} of month
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-save mr-1"></i> Save Employee
                    </button>
                    <a href="{{ route('employees.index') }}"
                       class="btn btn-secondary btn-block mt-2">
                        Cancel
                    </a>
                </div>
            </div>

        </div>
    </div>

</form>

@endsection

@push('js')
<script>
var eduCount = 1;
var docCount = 0;

// ── Photo Preview ─────────────────────────────────────
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('photoPreview');
            preview.innerHTML = '';
            preview.style.background = 'none';
            var img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'rounded-circle';
            img.style = 'width:120px;height:120px;object-fit:cover;border:3px solid #dee2e6;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ── Add Education Row ─────────────────────────────────
function addEducation() {
    var tbody = document.getElementById('educationBody');
    var row   = document.createElement('tr');
    row.id    = 'edu-row-' + eduCount;
    row.innerHTML = `
        <td>
            <input type="text" name="educations[${eduCount}][degree]"
                   class="form-control form-control-sm" placeholder="e.g. B.Sc">
        </td>
        <td>
            <input type="text" name="educations[${eduCount}][institution]"
                   class="form-control form-control-sm" placeholder="University / Board">
        </td>
        <td>
            <input type="text" name="educations[${eduCount}][passing_year]"
                   class="form-control form-control-sm" placeholder="2020">
        </td>
        <td>
            <button type="button" class="btn btn-xs btn-danger"
                    onclick="removeRow('edu-row-${eduCount}')">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    eduCount++;
}

// ── Add Document Row ──────────────────────────────────
function addDocument() {
    var container = document.getElementById('documentsContainer');
    if (docCount === 0) container.innerHTML = '';

    var div   = document.createElement('div');
    div.id    = 'doc-' + docCount;
    div.className = 'border rounded p-2 mb-2';
    div.innerHTML = `
        <div class="row align-items-center">
            <div class="col-md-5">
                <input type="text" name="document_names[${docCount}]"
                       class="form-control form-control-sm"
                       placeholder="Document name e.g. NID Copy">
            </div>
            <div class="col-md-6">
                <input type="file" name="documents[${docCount}]"
                       class="form-control-file form-control-sm">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-xs btn-danger"
                        onclick="removeRow('doc-${docCount}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(div);
    docCount++;
}

// ── Remove Row ────────────────────────────────────────
function removeRow(id) {
    var el = document.getElementById(id);
    if (el) el.remove();
}

// ── Department → Position ─────────────────────────────
document.getElementById('departmentSelect').addEventListener('change', function() {
    var deptId    = this.value;
    var posSelect = document.getElementById('positionSelect');
    posSelect.innerHTML = '<option value="">-- Select Position --</option>';
    if (!deptId) return;

    fetch('/departments/' + deptId + '/positions')
        .then(res => res.json())
        .then(data => {
            data.forEach(function(pos) {
                posSelect.add(new Option(pos.name, pos.id));
            });
        });
});
</script>
@endpush
