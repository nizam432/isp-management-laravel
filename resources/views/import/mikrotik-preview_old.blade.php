{{-- resources/views/import/mikrotik-preview.blade.php --}}
@extends('layouts.app')
@section('page_title', 'MikroTik Import Preview')
@section('page_actions')
    <a href="{{ route('import.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i>
    Router: <strong>{{ $router->name }} ({{ $router->ip_address }})</strong> —
    মোট <strong>{{ count($users) }}</strong> জন নতুন user পাওয়া গেছে।
    <strong>{{ $existing }}</strong> জন already আছে (skip হবে)।
</div>

<form action="{{ route('import.mikrotik.execute') }}" method="POST">
    @csrf

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">PPPoE Users — Import Preview</h3>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                    সব Select
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                    সব Deselect
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="check-all" checked onchange="toggleAll(this)">
                        </th>
                        <th>#</th>
                        <th>PPPoE Username</th>
                        <th>Profile → Package</th>
                        <th>Status</th>
                        <th>Password (editable)</th>
                        <th> Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $user)
                    @php
                        $profile    = $user['profile'] ?? 'default';
                        $disabled   = ($user['disabled'] ?? 'false') === 'true';
                        $pkgMatch   = $packages->firstWhere('mikrotik_profile', $profile);
                    @endphp
                    <tr>
                        <td>
                            <input type="checkbox" name="users[]"
                                   value="{{ $user['name'] }}"
                                   class="user-check" checked>
                            {{-- Hidden fields for profile and disabled status --}}
                            <input type="hidden" name="profile_{{ $user['name'] }}" value="{{ $profile }}">
                            <input type="hidden" name="disabled_{{ $user['name'] }}" value="{{ $disabled ? 'true' : 'false' }}">
                        </td>
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $user['name'] }}</code></td>
                        <td>
                            <span class="badge badge-info">{{ $profile }}</span>
                            @if($pkgMatch)
                                <i class="fas fa-arrow-right text-muted mx-1" style="font-size:10px"></i>
                                <span class="badge badge-success">{{ $pkgMatch->name }}</span>
                            @else
                                <i class="fas fa-arrow-right text-muted mx-1" style="font-size:10px"></i>
                                <span class="badge badge-warning" title="Package match নেই — default package ব্যবহার হবে">
                                    No Match
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($disabled)
                                <span class="badge badge-danger">Disabled → Suspended</span>
                            @else
                                <span class="badge badge-success">Active</span>
                            @endif
                        </td>
                        <td>
                            <input type="text"
                                   name="password_{{ $user['name'] }}"
                                   class="form-control form-control-sm"
                                   value="{{ $user['password'] ?? '' }}"
                                   placeholder="password">
                        </td>
                        <td>
                            {{-- Single Import --}}
                            <form action="{{ route('import.mikrotik.single') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="username"   value="{{ $user['name'] }}">
                                <input type="hidden" name="password"   value="{{ $user['password'] ?? '' }}">
                                <input type="hidden" name="profile"    value="{{ $profile }}">
                                <input type="hidden" name="disabled"   value="{{ $disabled ? 'true' : 'false' }}">
                                <input type="hidden" name="router_id"  value="{{ $router->id }}">
                                <button type="submit" class="btn btn-xs btn-success">
                                    <i class="fas fa-plus mr-1"></i> Add
                                </button>
                            </form>
                        </td>                        
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            কোনো নতুন user নেই — সব already imported।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="alert alert-warning py-2 mb-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        <small>
                            Profile → Package auto match হবে। <strong>No Match</strong> হলে default package ব্যবহার হবে।
                            Import এর পর customer এর নাম ও phone manually দিতে হবে।
                        </small>
                    </div>
                </div>
                <input type="hidden" name="router_id" value="{{ $router->id }}">
                <div class="col-md-4 text-right">
                    <button type="submit" class="btn btn-primary btn-lg"
                            onclick="return confirm('Selected users import করবেন?')">
                        <i class="fas fa-file-import mr-1"></i> Bulk Import করুন
                    </button>
                </div>
            </div>
        </div>
    </div>
    
</form>

<script>
function toggleAll(el) {
    document.querySelectorAll('.user-check').forEach(c => c.checked = el.checked);
}
function selectAll()   { document.querySelectorAll('.user-check').forEach(c => c.checked = true); }
function deselectAll() { document.querySelectorAll('.user-check').forEach(c => c.checked = false); }
</script>

@endsection