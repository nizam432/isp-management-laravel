@extends('reseller.layouts.app')

@section('title', 'Salary Head')

@section('content')

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-plus mr-1"></i> Add Salary Head</div>
            <div class="card-body">
                <form method="POST" action="{{ route('reseller.hr.salary-head.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. House Rent, Provident Fund" required>
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-control" required>
                            <option value="addition">Addition</option>
                            <option value="deduction">Deduction</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-block"><i class="fas fa-plus mr-1"></i> Add</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Your Salary Heads</div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="bg-light"><tr><th>Name</th><th>Type</th><th>Active</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($salaryHeads as $sh)
                        <tr>
                            <td>{{ $sh->name }}</td>
                            <td><span class="badge badge-{{ $sh->type === 'addition' ? 'success' : 'danger' }}">{{ ucfirst($sh->type) }}</span></td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input toggle-sh" id="sh-{{ $sh->id }}" data-id="{{ $sh->id }}" {{ $sh->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="sh-{{ $sh->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-btn" data-id="{{ $sh->id }}" data-name="{{ $sh->name }}" data-type="{{ $sh->type }}" data-toggle="modal" data-target="#editModal"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('reseller.hr.salary-head.destroy', $sh->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this salary head?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No salary heads added yet.</td></tr>
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
                    <h5 class="modal-title">Edit Salary Head</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" id="edit-type" class="form-control" required>
                            <option value="addition">Addition</option>
                            <option value="deduction">Deduction</option>
                        </select>
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
    $('#edit-type').val($(this).data('type'));
    $('#editForm').attr('action', "{{ route('reseller.hr.salary-head.update', ['__ID__']) }}".replace('__ID__', id));
});
$(document).on('change', '.toggle-sh', function () {
    var id = $(this).data('id');
    $.post("{{ route('reseller.hr.salary-head.toggle', ['__ID__']) }}".replace('__ID__', id), { _token: '{{ csrf_token() }}' });
});
</script>
@endsection