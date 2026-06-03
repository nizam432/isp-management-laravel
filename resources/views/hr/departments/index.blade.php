{{-- resources/views/hr/departments/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Departments')
@section('page_actions')
    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addDeptModal">
        <i class="fas fa-plus mr-1"></i> Add Department
    </button>
@endsection
@section('page_content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building mr-1"></i> Department List</h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $departments->count() }} departments</span>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Department Name</th>
                            <th>Description</th>
                            <th class="text-center">Positions</th>
                            <th class="text-center">Employees</th>
                            <th>Status</th>
                            <th style="width:100px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $i => $dept)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td><strong>{{ $dept->name }}</strong></td>
                            <td><small class="text-muted">{{ $dept->description ?? '—' }}</small></td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $dept->positions_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary">{{ $dept->employees_count }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $dept->is_active ? 'success' : 'secondary' }}">
                                    {{ $dept->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-xs btn-warning"
                                        onclick="editDept({{ $dept->id }}, '{{ $dept->name }}', '{{ $dept->description }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('departments.destroy', $dept) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-xs btn-danger swal-delete"
                                            data-message="Delete department '{{ $dept->name }}'?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-building fa-2x d-block mb-2"></i>
                                No departments found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Add Department Modal trigger as inline form --}}
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title" id="deptFormTitle">
                    <i class="fas fa-plus mr-1"></i> New Department
                </h3>
            </div>
            <form id="deptForm" action="{{ route('departments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="deptMethod" value="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Department Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="deptName" class="form-control"
                               placeholder="e.g. Technical" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Description</label>
                        <textarea name="description" id="deptDesc" class="form-control"
                                  rows="2" placeholder="Optional..."></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block" id="deptSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Save Department
                    </button>
                    <button type="button" class="btn btn-secondary btn-block btn-sm mt-1 d-none"
                            id="deptCancelBtn" onclick="resetDeptForm()">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
function editDept(id, name, description) {
    document.getElementById('deptFormTitle').innerHTML = '<i class="fas fa-edit mr-1"></i> Edit Department';
    document.getElementById('deptMethod').value        = 'PUT';
    document.getElementById('deptForm').action         = '/departments/' + id;
    document.getElementById('deptName').value          = name;
    document.getElementById('deptDesc').value          = description || '';
    document.getElementById('deptSubmitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Update Department';
    document.getElementById('deptCancelBtn').classList.remove('d-none');
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetDeptForm() {
    document.getElementById('deptFormTitle').innerHTML = '<i class="fas fa-plus mr-1"></i> New Department';
    document.getElementById('deptMethod').value        = 'POST';
    document.getElementById('deptForm').action         = '{{ route("departments.store") }}';
    document.getElementById('deptForm').reset();
    document.getElementById('deptSubmitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Save Department';
    document.getElementById('deptCancelBtn').classList.add('d-none');
}
</script>
@endpush
