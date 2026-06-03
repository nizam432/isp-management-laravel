{{-- resources/views/hr/leave/types.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Leave Types')
@section('page_actions')
    <a href="{{ route('leave.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-1"></i> Leave Types</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Leave Type</th>
                            <th>Days Per Year</th>
                            <th>Status</th>
                            <th style="width:80px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $i => $type)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $type->name }}</strong></td>
                            <td><span class="badge badge-info">{{ $type->days_per_year }} days</span></td>
                            <td>
                                <span class="badge badge-{{ $type->is_active ? 'success' : 'secondary' }}">
                                    {{ $type->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-xs btn-warning"
                                        onclick="editType({{ $type->id }}, '{{ $type->name }}', {{ $type->days_per_year }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('leave.types.destroy', $type) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-xs btn-danger swal-delete"
                                            data-message="Delete leave type '{{ $type->name }}'?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No leave types found.</td>
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
                <h3 class="card-title" id="typeFormTitle">
                    <i class="fas fa-plus mr-1"></i> New Leave Type
                </h3>
            </div>
            <form id="typeForm" action="{{ route('leave.types.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="typeMethod" value="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Leave Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="typeName" class="form-control"
                               placeholder="e.g. Annual Leave" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Days Per Year</label>
                        <input type="number" name="days_per_year" id="typeDays" class="form-control"
                               value="0" min="0">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block" id="typeSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                    <button type="button" class="btn btn-secondary btn-block btn-sm mt-1 d-none"
                            id="typeCancelBtn" onclick="resetTypeForm()">
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
function editType(id, name, days) {
    document.getElementById('typeFormTitle').innerHTML = '<i class="fas fa-edit mr-1"></i> Edit Leave Type';
    document.getElementById('typeMethod').value        = 'PUT';
    document.getElementById('typeForm').action         = '/leave/types/' + id;
    document.getElementById('typeName').value          = name;
    document.getElementById('typeDays').value          = days;
    document.getElementById('typeSubmitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Update';
    document.getElementById('typeCancelBtn').classList.remove('d-none');
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetTypeForm() {
    document.getElementById('typeFormTitle').innerHTML = '<i class="fas fa-plus mr-1"></i> New Leave Type';
    document.getElementById('typeMethod').value        = 'POST';
    document.getElementById('typeForm').action         = '{{ route("leave.types.store") }}';
    document.getElementById('typeForm').reset();
    document.getElementById('typeSubmitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Save';
    document.getElementById('typeCancelBtn').classList.add('d-none');
}
</script>
@endpush
