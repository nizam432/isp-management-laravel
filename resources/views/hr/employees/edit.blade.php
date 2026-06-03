{{-- resources/views/hr/employees/edit.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Edit Employee — ' . $employee->name)
@section('page_actions')
    <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="row">
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
                                       value="{{ $employee->name }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Phone</label>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ $employee->phone }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">NID Number</label>
                                <input type="text" name="nid_number" class="form-control"
                                       value="{{ $employee->nid_number }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Department</label>
                                <select name="department_id" class="form-control" id="departmentSelect">
                                    <option value="">-- Select Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ $employee->department_id == $dept->id ? 'selected' : '' }}>
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
                                            {{ $employee->position_id == $pos->id ? 'selected' : '' }}>
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
                                       value="{{ $employee->join_date?->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required id="statusSelect">
                                    <option value="active"     {{ $employee->status == 'active'     ? 'selected' : '' }}>Active</option>
                                    <option value="inactive"   {{ $employee->status == 'inactive'   ? 'selected' : '' }}>Inactive</option>
                                    <option value="resigned"   {{ $employee->status == 'resigned'   ? 'selected' : '' }}>Resigned</option>
                                    <option value="terminated" {{ $employee->status == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Leaving Info (show if resigned/terminated) --}}
                    <div id="leavingInfo" class="{{ in_array($employee->status, ['resigned', 'terminated']) ? '' : 'd-none' }}">
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Leaving Date</label>
                                    <input type="date" name="leaving_date" class="form-control"
                                           value="{{ $employee->leaving_date?->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Reason</label>
                                    <input type="text" name="leaving_reason" class="form-control"
                                           value="{{ $employee->leaving_reason }}"
                                           placeholder="e.g. Personal reason">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Note</label>
                                    <textarea name="leaving_note" class="form-control" rows="2">{{ $employee->leaving_note }}</textarea>
                                </div>
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
                                <textarea name="present_address" class="form-control" rows="3">{{ $employee->present_address }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Permanent Address</label>
                                <textarea name="permanent_address" class="form-control" rows="3">{{ $employee->permanent_address }}</textarea>
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
                                       value="{{ $employee->emergency_name }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Phone</label>
                                <input type="text" name="emergency_phone" class="form-control"
                                       value="{{ $employee->emergency_phone }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Relation</label>
                                <input type="text" name="emergency_relation" class="form-control"
                                       value="{{ $employee->emergency_relation }}">
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
                                       value="{{ $employee->bank_name }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Account Number</label>
                                <input type="text" name="account_number" class="form-control"
                                       value="{{ $employee->account_number }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Branch</label>
                                <input type="text" name="branch_name" class="form-control"
                                       value="{{ $employee->branch_name }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Educational Qualification --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-graduation-cap mr-1"></i> Educational Qualification</h3>
                    <button type="button" class="btn btn-xs btn-success" onclick="addEducation()">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-sm" id="educationTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>Last Achieved Degree</th>
                                <th>Institution/Board</th>
                                <th>Passing Year</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="educationBody">
                            @forelse($employee->educations as $i => $edu)
                            <tr id="edu-row-{{ $i }}">
                                <td><input type="text" name="educations[{{ $i }}][degree]"
                                           class="form-control form-control-sm" value="{{ $edu->degree }}"></td>
                                <td><input type="text" name="educations[{{ $i }}][institution]"
                                           class="form-control form-control-sm" value="{{ $edu->institution }}"></td>
                                <td><input type="text" name="educations[{{ $i }}][passing_year]"
                                           class="form-control form-control-sm" value="{{ $edu->passing_year }}"></td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-danger"
                                            onclick="removeRow('edu-row-{{ $i }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="edu-row-0">
                                <td><input type="text" name="educations[0][degree]" class="form-control form-control-sm"></td>
                                <td><input type="text" name="educations[0][institution]" class="form-control form-control-sm"></td>
                                <td><input type="text" name="educations[0][passing_year]" class="form-control form-control-sm"></td>
                                <td></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- New Documents --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Add New Documents</h3>
                    <button type="button" class="btn btn-xs btn-success" onclick="addDocument()">
                        <i class="fas fa-plus mr-1"></i> Add Document
                    </button>
                </div>
                <div class="card-body" id="documentsContainer">
                    <p class="text-muted small">Click "+ Add Document" to attach new files.</p>
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
                        @if($employee->photo)
                            <img id="photoPreview" src="{{ asset('storage/' . $employee->photo) }}"
                                 class="rounded-circle" width="120" height="120"
                                 style="object-fit:cover; border:3px solid #dee2e6;">
                        @else
                            <div id="photoPreview" class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center"
                                 style="width:120px;height:120px;font-size:48px;color:#fff">
                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <input type="file" name="photo" class="form-control-file"
                           accept="image/*" onchange="previewPhoto(this)">
                </div>
            </div>

            {{-- Salary --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave mr-1"></i> Salary</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Basic Salary</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">৳</span>
                            </div>
                            <input type="number" name="basic_salary" class="form-control"
                                   value="{{ $employee->basic_salary }}" min="0">
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Salary Date</label>
                        <select name="salary_date" class="form-control">
                            @for($d = 1; $d <= 28; $d++)
                                <option value="{{ $d }}" {{ $employee->salary_date == $d ? 'selected' : '' }}>
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
                        <i class="fas fa-save mr-1"></i> Update Employee
                    </button>
                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary btn-block mt-2">
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
var eduCount = {{ $employee->educations->count() ?: 1 }};
var docCount = 0;

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function addEducation() {
    var tbody = document.getElementById('educationBody');
    var row   = document.createElement('tr');
    row.id    = 'edu-row-' + eduCount;
    row.innerHTML = `
        <td><input type="text" name="educations[${eduCount}][degree]" class="form-control form-control-sm" placeholder="e.g. B.Sc"></td>
        <td><input type="text" name="educations[${eduCount}][institution]" class="form-control form-control-sm"></td>
        <td><input type="text" name="educations[${eduCount}][passing_year]" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-xs btn-danger" onclick="removeRow('edu-row-${eduCount}')"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    eduCount++;
}

function addDocument() {
    var container = document.getElementById('documentsContainer');
    if (docCount === 0) container.innerHTML = '';
    var div   = document.createElement('div');
    div.id    = 'doc-' + docCount;
    div.className = 'border rounded p-2 mb-2';
    div.innerHTML = `
        <div class="row align-items-center">
            <div class="col-md-5">
                <input type="text" name="document_names[${docCount}]" class="form-control form-control-sm"
                       placeholder="Document name">
            </div>
            <div class="col-md-6">
                <input type="file" name="documents[${docCount}]" class="form-control-file form-control-sm">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-xs btn-danger" onclick="removeRow('doc-${docCount}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(div);
    docCount++;
}

function removeRow(id) {
    var el = document.getElementById(id);
    if (el) el.remove();
}

// Show/hide leaving info based on status
document.getElementById('statusSelect').addEventListener('change', function() {
    var leaving = document.getElementById('leavingInfo');
    if (['resigned', 'terminated'].includes(this.value)) {
        leaving.classList.remove('d-none');
    } else {
        leaving.classList.add('d-none');
    }
});

// Department → Position
document.getElementById('departmentSelect').addEventListener('change', function() {
    var deptId    = this.value;
    var posSelect = document.getElementById('positionSelect');
    posSelect.innerHTML = '<option value="">-- Select Position --</option>';
    if (!deptId) return;
    fetch('/departments/' + deptId + '/positions')
        .then(res => res.json())
        .then(data => {
            data.forEach(pos => posSelect.add(new Option(pos.name, pos.id)));
        });
});
</script>
@endpush
