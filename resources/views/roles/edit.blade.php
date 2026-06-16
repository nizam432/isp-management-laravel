{{-- resources/views/roles/edit.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Edit Role — ' . ucfirst($role->name))

@section('page_actions')
    <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection

@section('page_content')

<form action="{{ route('roles.update', $role) }}" method="POST">
@csrf @method('PUT')

<div class="row">

    {{-- Left: Role Info --}}
    <div class="col-md-3">
        <div class="card sticky-top" style="top:70px">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tag mr-1"></i> Role Info</h3>
            </div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label class="font-weight-bold">Role Name</label>
                    <input type="text" class="form-control bg-light"
                           value="{{ ucfirst($role->name) }}" readonly>
                    <small class="text-muted">Role name cannot be changed.</small>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <button type="button" class="btn btn-xs btn-success" onclick="selectAll()">
                        <i class="fas fa-check-square mr-1"></i> Select All
                    </button>
                    <button type="button" class="btn btn-xs btn-secondary" onclick="deselectAll()">
                        <i class="fas fa-square mr-1"></i> Deselect All
                    </button>
                </div>

                <div id="selectedCount" class="alert alert-info py-1 text-center small mb-0">
                    0 permissions selected
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning btn-block">
                    <i class="fas fa-save mr-1"></i> Update Role
                </button>
            </div>
        </div>
    </div>

    {{-- Right: Permissions grouped --}}
    <div class="col-md-9">
        @forelse($permissions as $group => $perms)
        <div class="card mb-3">
            <div class="card-header py-2 bg-dark d-flex justify-content-between align-items-center">
                <h3 class="card-title text-white mb-0">
                    <i class="fas fa-layer-group mr-2"></i>{{ $group }}
                    <span class="badge badge-secondary ml-1">{{ $perms->count() }}</span>
                </h3>
                <div>
                    <input type="checkbox" class="group-master mr-1"
                           data-group="{{ Str::slug($group) }}"
                           id="master_{{ Str::slug($group) }}"
                           onchange="toggleGroup('{{ Str::slug($group) }}', this.checked)">
                    <label for="master_{{ Str::slug($group) }}" class="text-white mb-0 small">
                        Select All
                    </label>
                </div>
            </div>
            <div class="card-body py-2">
                <div class="row">
                    @foreach($perms as $perm)
                    <div class="col-md-4 mb-1">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox"
                                   class="custom-control-input perm-check perm-{{ Str::slug($group) }}"
                                   id="perm_{{ $perm->id }}"
                                   name="permissions[]"
                                   value="{{ $perm->name }}"
                                   onchange="updateCount()"
                                   {{ in_array($perm->name, $assignedPermissions) ? 'checked' : '' }}>
                            <label class="custom-control-label small" for="perm_{{ $perm->id }}">
                                <code class="text-primary">{{ $perm->name }}</code>
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="alert alert-warning">
            No permissions found.
        </div>
        @endforelse
    </div>

</div>
</form>

@endsection

@push('js')
<script>
function selectAll() {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = true);
    document.querySelectorAll('.group-master').forEach(cb => cb.checked = true);
    updateCount();
}

function deselectAll() {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
    document.querySelectorAll('.group-master').forEach(cb => cb.checked = false);
    updateCount();
}

function toggleGroup(group, checked) {
    document.querySelectorAll('.perm-' + group).forEach(cb => cb.checked = checked);
    updateCount();
}

function updateCount() {
    const count = document.querySelectorAll('.perm-check:checked').length;
    document.getElementById('selectedCount').textContent = count + ' permission' + (count !== 1 ? 's' : '') + ' selected';
}

document.querySelectorAll('.perm-check').forEach(cb => {
    cb.addEventListener('change', function() {
        const group = [...this.classList].find(c => c.startsWith('perm-') && c !== 'perm-check')?.replace('perm-', '');
        if (group) {
            const groupPerms = document.querySelectorAll('.perm-' + group);
            const groupMaster = document.getElementById('master_' + group);
            if (groupMaster) {
                groupMaster.checked = [...groupPerms].every(c => c.checked);
            }
        }
    });
});

updateCount();
</script>
@endpush
