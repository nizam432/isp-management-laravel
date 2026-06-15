{{-- resources/views/roles/create.blade.php --}}
@extends('layouts.app')
@section('page_title', 'নতুন Role তৈরি')

@section('page_actions')
    <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection

@section('page_content')

<form action="{{ route('roles.store') }}" method="POST">
@csrf

<div class="row">

    {{-- Left: Role Name --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tag mr-1"></i> Role Info</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-bold">Role Name <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="যেমন: Manager, Staff, Agent"
                           required>
                    <small class="text-muted">Auto lowercase হবে। Space → hyphen।</small>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="alert alert-info py-2 mb-0">
                    <small>
                        <i class="fas fa-info-circle mr-1"></i>
                        ডান পাশ থেকে permissions select করুন।
                    </small>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save mr-1"></i> Role তৈরি করুন
                </button>
            </div>
        </div>
    </div>

    {{-- Right: Permissions grouped --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-key mr-1"></i> Permissions</h3>
                <div>
                    <button type="button" class="btn btn-xs btn-success mr-1" onclick="selectAll()">সব Select</button>
                    <button type="button" class="btn btn-xs btn-secondary" onclick="deselectAll()">সব Deselect</button>
                </div>
            </div>
            <div class="card-body">

                @forelse($permissions as $group => $perms)
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title font-weight-bold">{{ $group }}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-xs btn-outline-primary"
                                    onclick="selectGroup('{{ Str::slug($group) }}')">
                                সব নিন
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="row">
                            @foreach($perms as $perm)
                            <div class="col-md-6 mb-1">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                           class="custom-control-input perm-{{ Str::slug($group) }}"
                                           id="perm_{{ $perm->id }}"
                                           name="permissions[]"
                                           value="{{ $perm->name }}"
                                           {{ in_array($perm->name, old('permissions', [])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="perm_{{ $perm->id }}">
                                        <code class="text-primary small">{{ $perm->name }}</code>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Super Admin কোনো permission তৈরি করেননি।
                </div>
                @endforelse

            </div>
        </div>
    </div>

</div>
</form>

@endsection

@push('js')
<script>
function selectAll() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = true);
}
function deselectAll() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);
}
function selectGroup(group) {
    document.querySelectorAll('.perm-' + group).forEach(cb => cb.checked = true);
}
</script>
@endpush
