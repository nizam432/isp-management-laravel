{{-- resources/views/super-admin/permissions/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Permission Management')

@section('page_content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted">Total <strong>{{ $totalCount }}</strong> permissions</span>
    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPermissionModal">
        <i class="fas fa-plus mr-1"></i> Add Permission
    </button>
</div>



@forelse($permissions as $group => $perms)
<div class="card mb-3">
    <div class="card-header py-2 bg-dark">
        <h3 class="card-title text-white">
            <i class="fas fa-layer-group mr-2"></i>{{ $group }}
            <span class="badge badge-secondary ml-2">{{ $perms->count() }}</span>
        </h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Permission Name</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($perms as $perm)
                <tr>
                    <td>
                        <code class="text-primary">{{ $perm->name }}</code>
                        @if($perm->roles_count > 0)
                            <span class="badge badge-warning ml-2">
                                Used in {{ $perm->roles_count }} {{ Str::plural('role', $perm->roles_count) }}
                            </span>
                        @else
                            <span class="badge badge-light ml-2">Not assigned</span>
                        @endif
                    </td>
                    <td class="text-right">

                        {{-- Edit Button --}}
                        <button class="btn btn-xs btn-warning mr-1"
                                data-toggle="modal"
                                data-target="#editModal{{ $perm->id }}">
                            <i class="fas fa-edit"></i> Edit
                        </button>

                        {{-- Delete Button --}}
                        <form action="{{ route('super-admin.permissions.destroy', $perm) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirmDelete('{{ $perm->name }}', {{ $perm->roles_count }})">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>

                    </td>
                </tr>

                {{-- Edit Modal --}}
                <div class="modal fade" id="editModal{{ $perm->id }}" tabindex="-1">
                    <div class="modal-dialog modal-sm">
                        <form action="{{ route('super-admin.permissions.update', $perm) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title">Rename Permission</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Permission Name</label>
                                        <input type="text" name="name" class="form-control"
                                               value="{{ $perm->name }}" required>
                                        <small class="text-muted">Use dot notation e.g. <code>mikrotik.import.customer</code></small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="fas fa-save mr-1"></i> Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @endforeach
            </tbody>
        </table>
    </div>
</div>
@empty
<div class="alert alert-info">
    No permissions found. Click "Add Permission" to create one.
</div>
@endforelse

{{-- Add Permission Modal --}}
<div class="modal fade" id="addPermissionModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('super-admin.permissions.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus mr-1"></i> New Permission</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label class="font-weight-bold">Group / Module <span class="text-danger">*</span></label>
                        <input type="text" name="group" class="form-control" required
                               placeholder="e.g. customer, billing, report"
                               list="groupSuggestions">
                        <small class="text-muted">Lowercase. This becomes the permission prefix.</small>
                        <datalist id="groupSuggestions">
                            @foreach($groupList as $g)
                                <option value="{{ strtolower($g) }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Action <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               placeholder="view, create, edit, delete, suspend">
                        <small class="text-muted">
                            Multiple actions supported. e.g. group=<code>customer</code> + action=<code>view, create, edit</code> creates 3 permissions at once.
                        </small>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Create
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<script>
function confirmDelete(name, rolesCount) {
    if (rolesCount > 0) {
        return confirm(
            'Delete "' + name + '"?\n\n' +
            'WARNING: This permission is used in ' + rolesCount + ' role(s).\n' +
            'All roles and users with this permission will lose access immediately.'
        );
    }
    return confirm('Delete "' + name + '"?');
}
</script>
@endpush
