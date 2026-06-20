@extends('reseller.layouts.app')

@section('title', 'Employees')

@section('content')

@if(session('employee_block'))
    <div class="alert alert-warning">{{ session('employee_block') }}</div>
@endif

<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="font-weight-bold mb-0"><i class="fas fa-users text-primary mr-1"></i> My Employees</h6>
            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addEmployeeModal">
                <i class="fas fa-plus mr-1"></i> Add Employee
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered" id="empTable" style="font-size:.85rem">
                <thead style="background:#f4f6f9">
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Designation</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $e)
                    <tr>
                        <td>{{ $e->name }}</td>
                        <td>{{ $e->username }}</td>
                        <td>{{ $e->designation ?? '—' }}</td>
                        <td>{{ $e->phone ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $e->is_active ? 'success' : 'secondary' }}">
                                {{ $e->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary edit-emp-btn"
                                data-id="{{ $e->id }}"
                                data-name="{{ $e->name }}"
                                data-email="{{ $e->email }}"
                                data-phone="{{ $e->phone }}"
                                data-designation="{{ $e->designation }}"
                                data-username="{{ $e->username }}"
                                data-menus="{{ json_encode($e->allowed_menus ?? []) }}"
                                data-active="{{ $e->is_active ? 1 : 0 }}"
                                data-toggle="modal" data-target="#editEmployeeModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-emp-btn" data-id="{{ $e->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No employees added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $employees->links() }}
    </div>
</div>

{{-- Add Employee Modal --}}
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Employee</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="addEmpForm">
                    @csrf
                    <div class="form-group">
                        <label class="small font-weight-bold">Name *</label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Designation</label>
                        <input type="text" name="designation" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Phone</label>
                        <input type="text" name="phone" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Username *</label>
                        <input type="text" name="username" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Password *</label>
                        <input type="password" name="password" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Allowed Menus</label>
                        <div class="row">
                            @foreach(['CONFIGURATION','MIKROTIK CLIENT','CLIENT','BILLING','MONITORING','CLIENT SUPPORT','SMS SERVICE','REPORT','FUND HISTORY','TUTORIALS'] as $m)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allowed_menus[]" value="{{ $m }}" id="add-{{ Str::slug($m) }}">
                                    <label class="form-check-label small" for="add-{{ Str::slug($m) }}">{{ ucwords(strtolower($m)) }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm px-4">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Employee Modal --}}
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Employee</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editEmpForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editEmpId">
                    <div class="form-group">
                        <label class="small font-weight-bold">Name *</label>
                        <input type="text" id="editName" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Designation</label>
                        <input type="text" id="editDesignation" name="designation" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Phone</label>
                        <input type="text" id="editPhone" name="phone" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Email</label>
                        <input type="email" id="editEmail" name="email" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Username *</label>
                        <input type="text" id="editUsername" name="username" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Status</label>
                        <select id="editActive" name="is_active" class="form-control form-control-sm">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Allowed Menus</label>
                        <div class="row" id="editMenusWrap">
                            @foreach(['CONFIGURATION','MIKROTIK CLIENT','CLIENT','BILLING','MONITORING','CLIENT SUPPORT','SMS SERVICE','REPORT','FUND HISTORY','TUTORIALS'] as $m)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input edit-menu-cb" type="checkbox" name="allowed_menus[]" value="{{ $m }}" id="edit-{{ Str::slug($m) }}">
                                    <label class="form-check-label small" for="edit-{{ Str::slug($m) }}">{{ ucwords(strtolower($m)) }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm px-4">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$('#addEmpForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: "{{ route('reseller.employees.store') }}",
        method: 'POST',
        data: $(this).serialize(),
        success: (res) => { if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 800); } },
        error: (xhr) => {
            const errors = xhr.responseJSON?.errors;
            if (errors) toastr.error(Object.values(errors).flat().join('\n'));
        }
    });
});

$(document).on('click', '.edit-emp-btn', function() {
    $('#editEmpId').val($(this).data('id'));
    $('#editName').val($(this).data('name'));
    $('#editDesignation').val($(this).data('designation'));
    $('#editPhone').val($(this).data('phone'));
    $('#editEmail').val($(this).data('email'));
    $('#editUsername').val($(this).data('username'));
    $('#editActive').val($(this).data('active'));

    const menus = $(this).data('menus') || [];
    $('.edit-menu-cb').prop('checked', false);
    menus.forEach(m => $(`#edit-${m.toLowerCase().replace(/ /g, '-')}`).prop('checked', true));
});

$('#editEmpForm').on('submit', function(e) {
    e.preventDefault();
    const id = $('#editEmpId').val();
    $.ajax({
        url: `/reseller/employees/${id}`,
        method: 'POST',
        data: $(this).serialize() + '&_method=PUT',
        success: (res) => { if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 800); } },
        error: (xhr) => {
            const errors = xhr.responseJSON?.errors;
            if (errors) toastr.error(Object.values(errors).flat().join('\n'));
        }
    });
});

$(document).on('click', '.delete-emp-btn', function() {
    const id = $(this).data('id');
    if (!confirm('Remove this employee?')) return;
    $.ajax({
        url: `/reseller/employees/${id}`,
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
        success: (res) => { if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 800); } }
    });
});
</script>
@stop
