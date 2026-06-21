@extends('layouts.app')

@section('page_title', 'Box Management')

@section('page_content')

<style>
.box-page-header {
    display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;
}
.cust-stat-card {
    border-radius: 4px;
    color: #fff;
    padding: 14px 16px;
    margin-bottom: 16px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
}
.cust-stat-card .sc-left .sc-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(255,255,255,.85);
    margin-bottom: 4px;
}
.cust-stat-card .sc-left .sc-value {
    font-size: 32px;
    font-weight: 700;
    line-height: 1;
    color: #fff;
}
.cust-stat-card .sc-icon {
    font-size: 52px;
    color: rgba(255,255,255,.18);
}

.box-card {
    background: #fff; border: 1px solid #e9ecef; border-radius: 10px; overflow: hidden;
}
.box-toolbar {
    display: flex; gap: 8px; padding: 12px 16px; border-bottom: 1px solid #e9ecef; flex-wrap: wrap;
}
.box-search-wrap { position: relative; flex: 1; max-width: 280px; min-width: 200px; }
.box-search-wrap .search-icon {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    font-size: 14px; color: #adb5bd; pointer-events: none;
}
.box-search-wrap input { padding-left: 32px; }

.box-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 0; }
.box-table thead th {
    text-align: left; padding: 10px 16px; font-weight: 600; color: #6c757d;
    font-size: 12px; text-transform: uppercase; letter-spacing: .4px;
    background: #f8f9fa; border: none; white-space: nowrap;
}
.box-table tbody td { padding: 12px 16px; border-top: 1px solid #f1f3f5; vertical-align: middle; }
.box-name-cell { display: flex; align-items: center; gap: 8px; font-weight: 600; }
.box-name-cell i { color: #6c757d; font-size: 16px; }
.box-muted { color: #6c757d; }
.box-details-cell {
    max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #6c757d;
}

.box-status-badge {
    font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 20px;
    display: inline-flex; align-items: center; gap: 5px; cursor: pointer; border: none;
}
.box-status-badge.active { background: #d1fae5; color: #065f46; }
.box-status-badge.inactive { background: #f1f3f5; color: #6c757d; }
.box-status-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

.box-creator { display: flex; align-items: center; gap: 6px; }
.box-creator .avatar {
    width: 24px; height: 24px; border-radius: 50%; background: #e7f1ff;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700; color: #1971c2; flex-shrink: 0;
}
.box-creator span { font-size: 13px; color: #6c757d; }

.box-action-btn {
    width: 30px; height: 30px; border-radius: 7px; border: 1px solid #e9ecef;
    background: #fff; display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px; color: #495057; margin-right: 4px; cursor: pointer; transition: all .15s;
}
.box-action-btn:hover { background: #f8f9fa; }
.box-action-btn.danger { color: #e03131; border-color: #ffe3e3; }
.box-action-btn.danger:hover { background: #fff5f5; }

.box-empty { text-align: center; padding: 60px 20px; color: #adb5bd; }
.box-empty i { font-size: 2.5rem; display: block; margin-bottom: 12px; opacity: .5; }
</style>

<div class="box-page-header">
    <div>
        <p class="box-muted" style="font-size:13px;margin:0 0 2px">Settings / Box</p>
        <h4 class="m-0" style="font-weight:600">Box Management</h4>
    </div>
    <button class="btn btn-dark btn-sm" data-toggle="modal" data-target="#addBoxModal">
        <i class="fas fa-plus mr-1"></i> Add Box
    </button>
</div>

<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-box mr-1"></i> Total Boxes</div>
                <div class="sc-value">{{ $boxes->count() }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-box"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-check-circle mr-1"></i> Active</div>
                <div class="sc-value">{{ $boxes->where('is_active', true)->count() }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#6c757d;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-times-circle mr-1"></i> Inactive</div>
                <div class="sc-value">{{ $boxes->where('is_active', false)->count() }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#f39c12;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-map-marked-alt mr-1"></i> Zones Covered</div>
                <div class="sc-value">{{ $boxes->pluck('zone_id')->unique()->count() }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-map-marked-alt"></i></div>
        </div>
    </div>
</div>

<div class="box-card">
    <div class="box-toolbar">
        <div class="box-search-wrap">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="boxSearchInput" class="form-control form-control-sm" placeholder="Search box, zone or sub-zone">
        </div>
        <select id="boxZoneFilter" class="form-control form-control-sm" style="width:160px">
            <option value="">All Zones</option>
            @foreach($zones as $z)
            <option value="{{ $z->name }}">{{ $z->name }}</option>
            @endforeach
        </select>
        <select id="boxStatusFilter" class="form-control form-control-sm" style="width:140px">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="box-table" id="boxTable">
            <thead>
                <tr>
                    <th>Box</th>
                    <th>Zone</th>
                    <th>Sub Zone</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($boxes as $box)
                <tr class="box-row"
                    data-name="{{ strtolower($box->name) }}"
                    data-zone="{{ $box->zone?->name }}"
                    data-subzone="{{ strtolower($box->subZone?->name) }}"
                    data-status="{{ $box->is_active ? '1' : '0' }}">
                    <td>
                        <div class="box-name-cell">
                            <i class="fas fa-box-open"></i>
                            {{ $box->name }}
                        </div>
                    </td>
                    <td class="box-muted">{{ $box->zone?->name ?? '—' }}</td>
                    <td class="box-muted">{{ $box->subZone?->name ?? '—' }}</td>
                    <td class="box-details-cell" title="{{ $box->details }}">{{ $box->details ?: '—' }}</td>
                    <td>
                        <button class="box-status-badge {{ $box->is_active ? 'active' : 'inactive' }} toggle-status-btn"
                            data-id="{{ $box->id }}">
                            <span class="dot"></span>
                            {{ $box->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </td>
                    <td>
                        @php
                            $creatorName = $box->createdBy?->name ?? 'System';
                            $initials = collect(explode(' ', $creatorName))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                        @endphp
                        <div class="box-creator">
                            <div class="avatar">{{ $initials }}</div>
                            <span>{{ $creatorName }}</span>
                        </div>
                    </td>
                    <td class="text-right">
                        <button class="box-action-btn edit-box-btn" data-id="{{ $box->id }}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="box-action-btn danger delete-box-btn" data-id="{{ $box->id }}" data-name="{{ $box->name }}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr id="boxEmptyRow">
                    <td colspan="7" class="p-0 border-0">
                        <div class="box-empty">
                            <i class="fas fa-box-open"></i>
                            No boxes found. Click "Add Box" to create your first one.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Box Modal --}}
<div class="modal fade" id="addBoxModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Box</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="addBoxForm">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold small">ZONE <span class="text-danger">*</span></label>
                        <select name="zone_id" id="addZoneSelect" class="form-control select2" required style="width:100%">
                            <option value="">Select</option>
                            @foreach($zones as $z)
                            <option value="{{ $z->id }}">{{ $z->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">SUB ZONE <span class="text-danger">*</span></label>
                        <select name="sub_zone_id" id="addSubZoneSelect" class="form-control" required disabled>
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">BOX <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">DETAILS(OPTIONAL)</label>
                        <textarea name="details" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-danger px-4" onclick="document.getElementById('addBoxForm').reset()">Clear</button>
                        <button type="submit" class="btn btn-primary px-4">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Box Modal --}}
<div class="modal fade" id="editBoxModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Box</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editBoxForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editBoxId">
                    <div class="form-group">
                        <label class="font-weight-bold small">ZONE <span class="text-danger">*</span></label>
                        <select name="zone_id" id="editZoneSelect" class="form-control" required>
                            <option value="">Select</option>
                            @foreach($zones as $z)
                            <option value="{{ $z->id }}">{{ $z->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">SUB ZONE <span class="text-danger">*</span></label>
                        <select name="sub_zone_id" id="editSubZoneSelect" class="form-control" required>
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">BOX <span class="text-danger">*</span></label>
                        <input type="text" id="editName" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">DETAILS(OPTIONAL)</label>
                        <textarea id="editDetails" name="details" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-danger px-4" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary px-4">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra_js')
<script>
$(function () {
    $('#addZoneSelect').select2({ width: '100%', dropdownParent: $('#addBoxModal') });

    function loadSubZones($select, zoneId, selectedId = null) {
        $select.html('<option value="">Loading...</option>').prop('disabled', true);
        if (!zoneId) { $select.html('<option value="">Select</option>').prop('disabled', true); return; }

        $.get("{{ route('settings.box.sub-zones') }}", { zone_id: zoneId })
            .done(function (data) {
                let opts = '<option value="">Select</option>';
                data.forEach(s => opts += `<option value="${s.id}" ${s.id == selectedId ? 'selected' : ''}>${s.name}</option>`);
                $select.html(opts).prop('disabled', false);
            })
            .fail(function () {
                $select.html('<option value="">Failed to load</option>').prop('disabled', true);
            });
    }

    $('#addZoneSelect').on('change', function () {
        loadSubZones($('#addSubZoneSelect'), $(this).val());
    });

    $('#addBoxForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('settings.box.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    $('#addBoxModal').modal('hide');
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) toastr.error(Object.values(errors).flat().join('\n'));
            }
        });
    });

    $('#editZoneSelect').on('change', function () {
        loadSubZones($('#editSubZoneSelect'), $(this).val());
    });

    $(document).on('click', '.edit-box-btn', function () {
        const id = $(this).data('id');
        $.get(`/settings/box/${id}/edit`, function (box) {
            $('#editBoxId').val(box.id);
            $('#editName').val(box.name);
            $('#editDetails').val(box.details);
            $('#editZoneSelect').val(box.zone_id);
            loadSubZones($('#editSubZoneSelect'), box.zone_id, box.sub_zone_id);
            $('#editBoxModal').modal('show');
        });
    });

    $('#editBoxForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#editBoxId').val();
        $.ajax({
            url: `/settings/box/${id}`,
            method: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            success: function (res) {
                if (res.success) {
                    $('#editBoxModal').modal('hide');
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) toastr.error(Object.values(errors).flat().join('\n'));
            }
        });
    });

    $(document).on('click', '.toggle-status-btn', function () {
        const id = $(this).data('id');
        $.post(`/settings/box/${id}/toggle`, { _token: '{{ csrf_token() }}' }, function () {
            location.reload();
        });
    });

    $(document).on('click', '.delete-box-btn', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            title: 'Are you sure?',
            text: `Delete box "${name}"? This action cannot be undone.`,
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/settings/box/${id}`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function (res) {
                        if (res.success) {
                            toastr.success(res.message);
                            setTimeout(() => location.reload(), 800);
                        }
                    }
                });
            }
        });
    });

    // ── Live Search + Filter (client-side) ────────────────
    function applyFilters() {
        const search = $('#boxSearchInput').val().toLowerCase();
        const zone   = $('#boxZoneFilter').val();
        const status = $('#boxStatusFilter').val();

        $('.box-row').each(function () {
            const $row = $(this);
            const matchSearch = !search || $row.text().toLowerCase().includes(search);
            const matchZone   = !zone || $row.data('zone') === zone;
            const matchStatus = status === '' || String($row.data('status')) === status;
            $row.toggle(matchSearch && matchZone && matchStatus);
        });
    }

    $('#boxSearchInput, #boxZoneFilter, #boxStatusFilter').on('input change', applyFilters);
});
</script>
@endsection
