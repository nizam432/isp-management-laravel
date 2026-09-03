@extends('reseller.layouts.app')

@section('title', 'Department')

@section('content')

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-plus mr-1"></i> Add Department</div>
            <div class="card-body">
                <form method="POST" action="{{ route('reseller.hr.department.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Department Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-block"><i class="fas fa-plus mr-1"></i> Add</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Your Departments</div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="bg-light"><tr><th>Name</th><th>Description</th><th>Active</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($departments as $d)
                        <tr>
                            <td>{{ $d->name }}</td>
                            <td>{{ $d->description ?? '—' }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input toggle-dept" id="d-{{ $d->id }}" data-id="{{ $d->id }}" {{ $d->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="d-{{ $d->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-btn" data-id="{{ $d->id }}" data-name="{{ $d->name }}" data-description="{{ $d->description }}" data-toggle="modal" data-target="#editModal"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('reseller.hr.department.destroy', $d->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this department?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No departments added yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Department</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit-description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).on('click', '.edit-btn', function () {
    var id = $(this).data('id');
    $('#edit-name').val($(this).data('name'));
    $('#edit-description').val($(this).data('description'));
    $('#editForm').attr('action', "{{ route('reseller.hr.department.update', ['__ID__']) }}".replace('__ID__', id));
});
$(document).on('change', '.toggle-dept', function () {
    var id = $(this).data('id');
    $.post("{{ route('reseller.hr.department.toggle', ['__ID__']) }}".replace('__ID__', id), { _token: '{{ csrf_token() }}' });
});
</script>
@endsection