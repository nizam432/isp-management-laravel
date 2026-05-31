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
    Total <strong>{{ count($users) }}</strong> new users found.
    <strong>{{ $existing }}</strong> already exists (will be skipped).
</div>

{{-- Tabs --}}
<div class="card card-primary card-outline">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#single" role="tab">
                    <i class="fas fa-user-plus mr-1"></i> Single Import
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#bulk" role="tab">
                    <i class="fas fa-users mr-1"></i> Bulk Import
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">

        {{-- ── Filter ─────────────────────────────────── --}}
        <div class="row mb-3">
            {{-- Search --}}
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="filterSearch" class="form-control"
                           placeholder="Search username...">
                </div>
            </div>
            {{-- Status Filter --}}
            <div class="col-md-3">
                <select id="filterStatus" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>
            {{-- Profile Filter --}}
            <div class="col-md-3">
                <select id="filterProfile" class="form-control form-control-sm">
                    <option value="">All Profiles</option>
                   @foreach(collect($users)->unique('profile') as $u)
                        @if(!empty($u['profile']))
                            <option value="{{ $u['profile'] }}">{{ $u['profile'] }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            {{-- Result Count --}}
            <div class="col-md-2 d-flex align-items-center">
                <span class="badge badge-info" id="filterCount">{{ count($users) }} results</span>
            </div>
        </div>

        <div class="tab-content">

            {{-- ── Tab 1: Single Import ─────────────────── --}}
            <div class="tab-pane fade show active" id="single" role="tabpanel">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>PPPoE Username</th>
                            <th>Profile → Package</th>
                            <th>Status</th>
                            <th>Password</th>
                            <th style="width:80px">Action</th>
                        </tr>
                    </thead>
                    <tbody id="singleTableBody">
                        @forelse($users as $i => $user)
                        @php
                            $profile  = $user['profile'] ?? 'default';
                            $disabled = ($user['disabled'] ?? 'false') === 'true';
                            $pkgMatch = $packages->firstWhere('mikrotik_profile', $profile);
                        @endphp
                        <tr class="user-row"
                            data-username="{{ strtolower($user['name']) }}"
                            data-status="{{ $disabled ? 'disabled' : 'active' }}"
                            data-profile="{{ $profile }}">
                            <td class="text-muted row-num">{{ $i + 1 }}</td>
                            <td><code>{{ $user['name'] }}</code></td>
                            <td>
                                <span class="badge badge-info">{{ $profile }}</span>
                                <i class="fas fa-arrow-right text-muted mx-1" style="font-size:10px"></i>
                                @if($pkgMatch)
                                    <span class="badge badge-success">{{ $pkgMatch->name }}</span>
                                @else
                                    <span class="badge badge-warning">No Match</span>
                                @endif
                            </td>
                            <td>
                                @if($disabled)
                                    <span class="badge badge-danger">Disabled → Suspended</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                            <td><code>{{ $user['password'] ?? '—' }}</code></td>
                            <td>
                                <form action="{{ route('import.mikrotik.single') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="username"  value="{{ $user['name'] }}">
                                    <input type="hidden" name="password"  value="{{ $user['password'] ?? '' }}">
                                    <input type="hidden" name="profile"   value="{{ $profile }}">
                                    <input type="hidden" name="disabled"  value="{{ $disabled ? 'true' : 'false' }}">
                                    <input type="hidden" name="router_id" value="{{ $router->id }}">
                                    <button type="submit" class="btn btn-xs btn-success"
                                            onclick="return confirm('Import {{ $user['name'] }}?')">
                                        <i class="fas fa-plus mr-1"></i> Add
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No new users found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ── Tab 2: Bulk Import ───────────────────── --}}
            <div class="tab-pane fade" id="bulk" role="tabpanel">
                <form action="{{ route('import.mikrotik.execute') }}" method="POST">
                    @csrf
                    <input type="hidden" name="router_id" value="{{ $router->id }}">

                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-xs btn-outline-primary mr-1" onclick="selectVisible()">
                                Select Visible
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="deselectAll()">
                                Deselect All
                            </button>
                        </div>
                        <span class="badge badge-warning" id="selectedCount">0 selected</span>
                    </div>

                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="check-all" onchange="toggleAll(this)">
                                </th>
                                <th>#</th>
                                <th>PPPoE Username</th>
                                <th>Profile → Package</th>
                                <th>Status</th>
                                <th>Password (editable)</th>
                            </tr>
                        </thead>
                        <tbody id="bulkTableBody">
                            @forelse($users as $i => $user)
                            @php
                                $profile  = $user['profile'] ?? 'default';
                                $disabled = ($user['disabled'] ?? 'false') === 'true';
                                $pkgMatch = $packages->firstWhere('mikrotik_profile', $profile);
                            @endphp
                            <tr class="user-row"
                                data-username="{{ strtolower($user['name']) }}"
                                data-status="{{ $disabled ? 'disabled' : 'active' }}"
                                data-profile="{{ $profile }}">
                                <td>
                                    <input type="checkbox" name="users[]"
                                           value="{{ $user['name'] }}"
                                           class="user-check" checked
                                           onchange="updateSelectedCount()">
                                    <input type="hidden" name="profile_{{ $user['name'] }}"  value="{{ $profile }}">
                                    <input type="hidden" name="disabled_{{ $user['name'] }}" value="{{ $disabled ? 'true' : 'false' }}">
                                </td>
                                <td class="text-muted row-num">{{ $i + 1 }}</td>
                                <td><code>{{ $user['name'] }}</code></td>
                                <td>
                                    <span class="badge badge-info">{{ $profile }}</span>
                                    <i class="fas fa-arrow-right text-muted mx-1" style="font-size:10px"></i>
                                    @if($pkgMatch)
                                        <span class="badge badge-success">{{ $pkgMatch->name }}</span>
                                    @else
                                        <span class="badge badge-warning">No Match</span>
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No new users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div class="alert alert-warning py-2 mb-0 flex-grow-1 mr-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            <small>Profile → Package auto match. <strong>No Match</strong> = default package. Name & phone must be updated manually after import.</small>
                        </div>
                        <button type="submit" class="btn btn-primary"
                                onclick="return confirm('Import selected users?')">
                            <i class="fas fa-file-import mr-1"></i> Bulk Import
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection

@push('js')
<script>
// ── Filter ────────────────────────────────────────────
function applyFilter() {
    var search  = document.getElementById('filterSearch').value.toLowerCase();
    var status  = document.getElementById('filterStatus').value;
    var profile = document.getElementById('filterProfile').value;
    var visible = 0;

    document.querySelectorAll('.user-row').forEach(function(row) {
        var username    = row.getAttribute('data-username') || '';
        var rowStatus   = row.getAttribute('data-status') || '';
        var rowProfile  = row.getAttribute('data-profile') || '';

        var matchSearch  = !search  || username.includes(search);
        var matchStatus  = !status  || rowStatus === status;
        var matchProfile = !profile || rowProfile === profile;

        if (matchSearch && matchStatus && matchProfile) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update row numbers
    var num = 1;
    document.querySelectorAll('.user-row:not([style*="none"]) .row-num').forEach(function(el) {
        el.textContent = num++;
    });

    document.getElementById('filterCount').textContent = visible + ' results';
    updateSelectedCount();
}

document.getElementById('filterSearch').addEventListener('input', applyFilter);
document.getElementById('filterStatus').addEventListener('change', applyFilter);
document.getElementById('filterProfile').addEventListener('change', applyFilter);

// ── Bulk Select ───────────────────────────────────────
function toggleAll(el) {
    document.querySelectorAll('.user-check').forEach(function(cb) {
        var row = cb.closest('tr');
        if (row && row.style.display !== 'none') {
            cb.checked = el.checked;
        }
    });
    updateSelectedCount();
}

function selectVisible() {
    document.querySelectorAll('.user-row').forEach(function(row) {
        var cb = row.querySelector('.user-check');
        if (cb) cb.checked = row.style.display !== 'none';
    });
    updateSelectedCount();
}

function deselectAll() {
    document.querySelectorAll('.user-check').forEach(cb => cb.checked = false);
    updateSelectedCount();
}

function updateSelectedCount() {
    var count = document.querySelectorAll('.user-check:checked').length;
    var el = document.getElementById('selectedCount');
    if (el) el.textContent = count + ' selected';
}

// Init count
updateSelectedCount();
</script>
@endpush
