{{-- resources/views/hr/salary-heads/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Salary Heads')
@section('page_content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-1"></i> Salary Head List</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Head Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th style="width:100px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($heads as $i => $head)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td><strong>{{ $head->name }}</strong></td>
                            <td>
                                <span class="badge badge-{{ $head->type === 'addition' ? 'success' : 'danger' }}">
                                    <i class="fas fa-{{ $head->type === 'addition' ? 'plus' : 'minus' }} mr-1"></i>
                                    {{ ucfirst($head->type) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $head->is_active ? 'success' : 'secondary' }}">
                                    {{ $head->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-xs btn-warning"
                                        onclick="editHead({{ $head->id }}, '{{ $head->name }}', '{{ $head->type }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('salary-heads.destroy', $head) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-xs btn-danger swal-delete"
                                            data-message="Delete salary head '{{ $head->name }}'?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-list fa-2x d-block mb-2"></i>
                                No salary heads found.
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
                <h3 class="card-title" id="headFormTitle">
                    <i class="fas fa-plus mr-1"></i> New Salary Head
                </h3>
            </div>
            <form id="headForm" action="{{ route('salary-heads.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="headMethod" value="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Head Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="headName" class="form-control"
                               placeholder="e.g. Basic Salary" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Type <span class="text-danger">*</span></label>
                        <div>
                            <div class="custom-control custom-radio d-inline-block mr-3">
                                <input type="radio" id="type_addition" name="type"
                                       value="addition" class="custom-control-input" checked>
                                <label class="custom-control-label text-success" for="type_addition">
                                    <i class="fas fa-plus mr-1"></i> Addition
                                </label>
                            </div>
                            <div class="custom-control custom-radio d-inline-block">
                                <input type="radio" id="type_deduction" name="type"
                                       value="deduction" class="custom-control-input">
                                <label class="custom-control-label text-danger" for="type_deduction">
                                    <i class="fas fa-minus mr-1"></i> Deduction
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block" id="headSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                    <button type="button" class="btn btn-secondary btn-block btn-sm mt-1 d-none"
                            id="headCancelBtn" onclick="resetHeadForm()">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-muted small">Default Salary Heads</h3>
            </div>
            <div class="card-body py-2">
                <small class="text-success d-block">+ Basic Salary</small>
                <small class="text-success d-block">+ House Rent</small>
                <small class="text-success d-block">+ Transport</small>
                <small class="text-success d-block">+ Medical</small>
                <small class="text-success d-block">+ Bonus</small>
                <small class="text-danger d-block">- Tax</small>
                <small class="text-danger d-block">- Advance Deduction</small>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
function editHead(id, name, type) {
    document.getElementById('headFormTitle').innerHTML = '<i class="fas fa-edit mr-1"></i> Edit Salary Head';
    document.getElementById('headMethod').value        = 'PUT';
    document.getElementById('headForm').action         = '/salary-heads/' + id;
    document.getElementById('headName').value          = name;
    document.getElementById('type_' + type).checked   = true;
    document.getElementById('headSubmitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Update';
    document.getElementById('headCancelBtn').classList.remove('d-none');
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetHeadForm() {
    document.getElementById('headFormTitle').innerHTML = '<i class="fas fa-plus mr-1"></i> New Salary Head';
    document.getElementById('headMethod').value        = 'POST';
    document.getElementById('headForm').action         = '{{ route("salary-heads.store") }}';
    document.getElementById('headForm').reset();
    document.getElementById('type_addition').checked  = true;
    document.getElementById('headSubmitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Save';
    document.getElementById('headCancelBtn').classList.add('d-none');
}
</script>
@endpush
