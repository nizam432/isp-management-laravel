{{-- resources/views/hr/positions/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Positions')
@section('page_content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-briefcase mr-1"></i> Position List</h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $positions->count() }} positions</span>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Position Name</th>
                            <th>Department</th>
                            <th class="text-center">Employees</th>
                            <th>Status</th>
                            <th style="width:100px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($positions as $i => $pos)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td><strong>{{ $pos->name }}</strong></td>
                            <td>
                                <span class="badge badge-light border">
                                    {{ $pos->department->name ?? '—' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary">{{ $pos->employees_count }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $pos->is_active ? 'success' : 'secondary' }}">
                                    {{ $pos->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-xs btn-warning"
                                        onclick="editPos({{ $pos->id }}, '{{ $pos->name }}', {{ $pos->department_id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('positions.destroy', $pos) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-xs btn-danger swal-delete"
                                            data-message="Delete position '{{ $pos->name }}'?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-briefcase fa-2x d-block mb-2"></i>
                                No positions found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title" id="posFormTitle">
                    <i class="fas fa-plus mr-1"></i> New Position
                </h3>
            </div>
            <form id="posForm" action="{{ route('positions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="posMethod" value="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Department <span class="text-danger">*</span></label>
                        <select name="department_id" id="posDept" class="form-control" required>
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Position Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="posName" class="form-control"
                               placeholder="e.g. Network Engineer" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block" id="posSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Save Position
                    </button>
                    <button type="button" class="btn btn-secondary btn-block btn-sm mt-1 d-none"
                            id="posCancelBtn" onclick="resetPosForm()">
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
function editPos(id, name, deptId) {
    document.getElementById('posFormTitle').innerHTML = '<i class="fas fa-edit mr-1"></i> Edit Position';
    document.getElementById('posMethod').value        = 'PUT';
    document.getElementById('posForm').action         = '/positions/' + id;
    document.getElementById('posName').value          = name;
    document.getElementById('posDept').value          = deptId;
    document.getElementById('posSubmitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Update Position';
    document.getElementById('posCancelBtn').classList.remove('d-none');
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetPosForm() {
    document.getElementById('posFormTitle').innerHTML = '<i class="fas fa-plus mr-1"></i> New Position';
    document.getElementById('posMethod').value        = 'POST';
    document.getElementById('posForm').action         = '{{ route("positions.store") }}';
    document.getElementById('posForm').reset();
    document.getElementById('posSubmitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Save Position';
    document.getElementById('posCancelBtn').classList.add('d-none');
}
</script>
@endpush
