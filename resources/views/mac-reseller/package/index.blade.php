@extends('adminlte::page')

@section('title', 'Reseller Packages')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-box-open mr-2 text-success"></i> Reseller Packages
            </h1>
            <small class="text-muted ml-1">Manage reseller bandwidth packages</small>
        </div>

    </div>
@stop

@section('css')
<style>
    /* ── Page & Card ───────────────────────── */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.08);
    }
    .card-header-custom {
        background: #ffffff;
        border-radius: 12px 12px 0 0;
        border-bottom: 1px solid #dee2e6;
        padding: 14px 24px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }
    .card-header-custom h5 { display: none; }
    .card-header-custom small { display: none; }

    /* ── Add Button ────────────────────────── */
    .btn-add-package {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        border-radius: 8px;
        color: #fff;
        font-weight: 600;
        padding: 8px 18px;
        font-size: .875rem;
        transition: all .2s ease;
        box-shadow: 0 3px 10px rgba(40,167,69,.35);
    }
    .btn-add-package:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(40,167,69,.45);
        color: #fff;
    }
    .btn-add-package i { margin-right: 6px; }

    /* ── Table ─────────────────────────────── */
    #packageTable thead th {
        background: #343a40 !important;
        color: #ffffff !important;
        font-size: .78rem;
        font-weight: 600;
        letter-spacing: .6px;
        text-transform: uppercase;
        border: none;
        padding: 13px 16px;
        white-space: nowrap;
    }
    #packageTable thead th:first-child { border-radius: 0; }
    #packageTable tbody tr {
        transition: background .15s ease;
    }
    #packageTable tbody tr:hover {
        background-color: #f0f9ff !important;
    }
    #packageTable tbody td {
        padding: 12px 16px;
        vertical-align: middle;
        font-size: .875rem;
        color: #374151;
        border-color: #f0f0f0;
    }

    /* ── Package Name cell ─────────────────── */
    .pkg-name {
        font-weight: 600;
        color: #1a1a2e;
    }
    .pkg-name i {
        color: #6c757d;
        margin-right: 6px;
        font-size: .8rem;
    }

    /* ── Bandwidth Badge ───────────────────── */
    .badge-bandwidth {
        font-size: .78rem;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 20px;
        letter-spacing: .3px;
    }
    .bw-low    { background:#d1fae5; color:#065f46; }
    .bw-medium { background:#dbeafe; color:#1e40af; }
    .bw-high   { background:#ede9fe; color:#5b21b6; }
    .bw-ultra  { background:#fee2e2; color:#991b1b; }

    /* ── Details cell ──────────────────────── */
    .details-text {
        color: #6b7280;
        font-size: .83rem;
        max-width: 280px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        cursor: default;
    }
    .no-details {
        color: #d1d5db;
        font-style: italic;
        font-size: .82rem;
    }

    /* ── Action Buttons ────────────────────── */
    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 7px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        transition: all .18s ease;
        margin: 0 2px;
    }
    .action-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,.15); }
    .btn-edit-action   { background: #e0f2fe; color: #0284c7; }
    .btn-edit-action:hover   { background: #0284c7; color: #fff; }
    .btn-delete-action { background: #fee2e2; color: #dc2626; }
    .btn-delete-action:hover { background: #dc2626; color: #fff; }
    .btn-view-action   { background: #f3e8ff; color: #7c3aed; }
    .btn-view-action:hover   { background: #7c3aed; color: #fff; }

    /* ── Serial No ─────────────────────────── */
    .serial-no {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        font-size: .8rem;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #e2e8f0;
    }

    /* ── Search & Show entries ─────────────── */
    .table-controls label {
        font-size: .82rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 4px;
    }
    #searchInput {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        padding: 6px 12px 6px 35px;
        font-size: .875rem;
        transition: border .2s;
        width: 220px;
    }
    #searchInput:focus {
        border-color: #28a745;
        outline: none;
        box-shadow: 0 0 0 3px rgba(40,167,69,.1);
    }
    .search-wrapper {
        position: relative;
        display: inline-block;
    }
    .search-wrapper .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: .8rem;
        pointer-events: none;
    }
    #perPage {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        padding: 5px 10px;
        font-size: .875rem;
        width: auto;
    }

    /* ── Empty State ───────────────────────── */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }
    .empty-state .empty-icon {
        width: 80px; height: 80px;
        background: #f0fdf4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        border: 2px dashed #86efac;
    }
    .empty-state .empty-icon i {
        font-size: 2rem;
        color: #22c55e;
    }
    .empty-state h5 {
        color: #374151;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .empty-state p {
        color: #9ca3af;
        font-size: .875rem;
        margin-bottom: 20px;
    }

    /* ── Pagination info ───────────────────── */
    .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 4px 4px;
        border-top: 1px solid #f3f4f6;
        margin-top: 8px;
    }
    .entries-info {
        font-size: .82rem;
        color: #6b7280;
    }
    .entries-info span {
        font-weight: 700;
        color: #374151;
    }

    /* ── Modal ─────────────────────────────── */
    .modal-content { border: none; border-radius: 14px; overflow: hidden; }
    .modal-header-custom {
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        padding: 18px 24px;
    }
    .modal-header-custom .modal-title { color: #fff; font-weight: 600; font-size: .95rem; }
    .modal-header-custom .close { color: #fff; opacity: .7; }
    .modal-header-custom .close:hover { opacity: 1; }
    .modal-body { padding: 24px; }
    .form-label-custom {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #374151;
        margin-bottom: 6px;
    }
    .form-control-custom {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        padding: 9px 13px;
        font-size: .875rem;
        transition: border .2s;
    }
    .form-control-custom:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40,167,69,.1);
    }
    .modal-footer-custom {
        padding: 16px 24px;
        border-top: 1px solid #f3f4f6;
        background: #fafafa;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .btn-modal-save {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none; color: #fff; font-weight: 600;
        padding: 8px 22px; border-radius: 8px; font-size: .875rem;
        transition: all .2s;
    }
    .btn-modal-save:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(40,167,69,.3); color:#fff; }
    .btn-modal-clear {
        background: #f3f4f6; border: none; color: #6b7280;
        font-weight: 600; padding: 8px 18px; border-radius: 8px; font-size: .875rem;
    }
    .btn-modal-close {
        background: #fee2e2; border: none; color: #dc2626;
        font-weight: 600; padding: 8px 18px; border-radius: 8px; font-size: .875rem;
    }

    /* ── Responsive ────────────────────────── */
    @media (max-width: 576px) {
        .card-header-custom { flex-direction: column; gap: 10px; }
        #searchInput { width: 100%; }
        .table-footer { flex-direction: column; gap: 10px; text-align: center; }
    }
</style>
@stop

@section('content')

{{-- ── Main Card ─────────────────────────────────────────────── --}}
<div class="mb-2 text-right">
    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addPackageModal">
        <i class="fas fa-plus"></i> Add Reseller Package
    </button>
</div>

<div class="card">



    {{-- Card Body --}}
    <div class="card-body px-4 py-3">

        {{-- Table Controls --}}
        <div class="row align-items-end mb-3 table-controls">
            <div class="col-sm-4 d-flex align-items-center" style="gap:10px">
                <div>
                    <label class="d-block">Show entries</label>
                    <select id="perPage" class="form-control form-control-sm">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100" selected>100</option>
                        <option value="200">200</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-8 text-right">
                <label class="d-block">Search</label>
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" placeholder="Search packages...">
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered" id="packageTable" style="border-radius:10px;overflow:hidden">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-center" style="width:70px">#</th>
                        <th>Package Name</th>
                        <th class="text-center" style="width:200px">Bandwidth Allocation (Mbps)</th>
                        <th>Package Details</th>
                        <th class="text-center" style="width:130px">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($packages as $i => $pkg)
                    @php
                        $bw = $pkg->bandwidth_mb;
                        $badgeClass = $bw <= 10 ? 'bw-low' : ($bw <= 50 ? 'bw-medium' : ($bw <= 200 ? 'bw-high' : 'bw-ultra'));
                        $bwLabel = $bw >= 1000 ? number_format($bw/1000, 1).' Gbps' : $bw.' Mbps';
                        $shortDetails = Str::limit($pkg->details ?? '', 70);
                    @endphp
                    <tr>
                        {{-- Serial No --}}
                        <td class="text-center">
                            <span class="serial-no">{{ $i + 1 }}</span>
                        </td>

                        {{-- Package Name --}}
                        <td>
                            <span class="pkg-name">
                                <i class="fas fa-circle"></i>{{ $pkg->name }}
                            </span>
                        </td>

                        {{-- Bandwidth Badge --}}
                        <td class="text-center">
                            <span class="badge-bandwidth {{ $badgeClass }}">
                                <i class="fas fa-tachometer-alt mr-1"></i>{{ $bwLabel }}
                            </span>
                        </td>

                        {{-- Package Details --}}
                        <td>
                            @if($pkg->details)
                                <span class="details-text" data-toggle="tooltip" data-placement="top" title="{{ $pkg->details }}">
                                    {{ $shortDetails }}
                                </span>
                            @else
                                <span class="no-details"><i class="fas fa-minus mr-1"></i>No details provided</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="text-center">
                            {{-- View --}}
                            <button class="action-btn btn-view-action view-btn"
                                data-toggle="tooltip" data-placement="top" title="View Details"
                                data-details="{{ $pkg->details }}" data-name="{{ $pkg->name }}">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Edit --}}
                            <button class="action-btn btn-edit-action edit-btn"
                                data-toggle="tooltip" data-placement="top" title="Edit Package"
                                data-id="{{ $pkg->id }}"
                                data-name="{{ $pkg->name }}"
                                data-bandwidth="{{ $pkg->bandwidth_mb }}"
                                data-details="{{ $pkg->details }}"
                                data-target="#editPackageModal" data-toggle2="modal">
                                <i class="fas fa-pen"></i>
                            </button>

                            {{-- Delete --}}
                            <button class="action-btn btn-delete-action delete-btn"
                                data-toggle="tooltip" data-placement="top" title="Delete Package"
                                data-id="{{ $pkg->id }}"
                                data-name="{{ $pkg->name }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="5" class="p-0 border-0">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <h5>No Packages Found</h5>
                                <p>You haven't added any reseller packages yet.<br>Click the button below to get started.</p>
                                <button class="btn-add-package" data-toggle="modal" data-target="#addPackageModal">
                                    <i class="fas fa-plus-circle"></i> Add Your First Package
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Footer --}}
        <div class="table-footer">
            <div class="entries-info">
                Showing <span id="visibleCount">{{ count($packages) }}</span>
                of <span>{{ count($packages) }}</span> entries
            </div>
            <div>
                {{-- Pagination if needed --}}
                @if(method_exists($packages, 'links'))
                    {{ $packages->links() }}
                @endif
            </div>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════
     Add Package Modal
══════════════════════════════════════════ --}}
<div class="modal fade" id="addPackageModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header-custom d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="addModalLabel">
                    <i class="fas fa-plus-circle mr-2"></i> Add Reseller Package
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="addPackageForm" autocomplete="off">
                    @csrf

                    <div class="form-group">
                        <label class="form-label-custom">
                            Package Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name"
                            class="form-control form-control-custom"
                            placeholder="e.g. Gold 50Mbps" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label-custom">
                            Bandwidth Allocation (MB — BTRC Report) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" name="bandwidth_mb"
                                class="form-control form-control-custom"
                                placeholder="e.g. 50" min="1" required
                                style="border-radius:8px 0 0 8px">
                            <div class="input-group-append">
                                <span class="input-group-text" style="border-radius:0 8px 8px 0;border-left:0;background:#f3f4f6;color:#6b7280;font-size:.8rem;font-weight:600">MB</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label-custom">Details <span class="text-muted font-weight-normal text-lowercase">(optional)</span></label>
                        <textarea name="details"
                            class="form-control form-control-custom"
                            rows="3"
                            placeholder="Add a short description for this package..."></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-modal-clear" onclick="document.getElementById('addPackageForm').reset()">
                    <i class="fas fa-undo mr-1"></i> Clear
                </button>
                <button type="button" class="btn-modal-close" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button type="submit" form="addPackageForm" class="btn-modal-save">
                    <i class="fas fa-save mr-1"></i> Save Package
                </button>
            </div>

        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════
     Edit Package Modal
══════════════════════════════════════════ --}}
<div class="modal fade" id="editPackageModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header-custom d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="fas fa-pen mr-2"></i> Edit Reseller Package
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="editPackageForm" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editPackageId">

                    <div class="form-group">
                        <label class="form-label-custom">
                            Package Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="editName" name="name"
                            class="form-control form-control-custom"
                            placeholder="e.g. Gold 50Mbps" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label-custom">
                            Bandwidth Allocation (MB — BTRC Report) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" id="editBandwidth" name="bandwidth_mb"
                                class="form-control form-control-custom"
                                placeholder="e.g. 50" min="1" required
                                style="border-radius:8px 0 0 8px">
                            <div class="input-group-append">
                                <span class="input-group-text" style="border-radius:0 8px 8px 0;border-left:0;background:#f3f4f6;color:#6b7280;font-size:.8rem;font-weight:600">MB</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label-custom">Details <span class="text-muted font-weight-normal text-lowercase">(optional)</span></label>
                        <textarea id="editDetails" name="details"
                            class="form-control form-control-custom"
                            rows="3"
                            placeholder="Add a short description for this package..."></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-modal-clear" onclick="document.getElementById('editPackageForm').reset()">
                    <i class="fas fa-undo mr-1"></i> Clear
                </button>
                <button type="button" class="btn-modal-close" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button type="submit" form="editPackageForm" class="btn-modal-save">
                    <i class="fas fa-save mr-1"></i> Update Package
                </button>
            </div>

        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════
     View Details Modal
══════════════════════════════════════════ --}}
<div class="modal fade" id="viewPackageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-custom d-flex justify-content-between align-items-center">
                <h5 class="modal-title">
                    <i class="fas fa-eye mr-2"></i> Package Details
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <h6 id="viewPkgName" class="font-weight-bold text-dark mb-3"></h6>
                <p id="viewPkgDetails" class="text-muted" style="font-size:.9rem;line-height:1.7"></p>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-modal-close" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
$(function () {
    // ── Init Tooltips ──────────────────────────
    $('[data-toggle="tooltip"]').tooltip();

    // ── Routes ────────────────────────────────
    const ROUTES = {
        store:   "{{ route('mac-reseller.package.store') }}",
        update:  (id) => `/mac-reseller/package/${id}`,
        destroy: (id) => `/mac-reseller/package/${id}`,
    };

    // ── Add Package ────────────────────────────
    $('#addPackageForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('[form="addPackageForm"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
        $.ajax({
            url: ROUTES.store,
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    $('#addPackageModal').modal('hide');
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) toastr.error(Object.values(errors).flat().join('\n'));
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Package');
            }
        });
    });

    // ── Edit Button → Fill Modal ───────────────
    $(document).on('click', '.edit-btn', function () {
        $('#editPackageId').val($(this).data('id'));
        $('#editName').val($(this).data('name'));
        $('#editBandwidth').val($(this).data('bandwidth'));
        $('#editDetails').val($(this).data('details'));
        $('#editPackageModal').modal('show');
    });

    // ── Update Package ─────────────────────────
    $('#editPackageForm').on('submit', function (e) {
        e.preventDefault();
        const id   = $('#editPackageId').val();
        const $btn = $('[form="editPackageForm"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
        $.ajax({
            url: ROUTES.update(id),
            method: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            success: function (res) {
                if (res.success) {
                    $('#editPackageModal').modal('hide');
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) toastr.error(Object.values(errors).flat().join('\n'));
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Package');
            }
        });
    });

    // ── View Details ───────────────────────────
    $(document).on('click', '.view-btn', function () {
        const name    = $(this).data('name');
        const details = $(this).data('details') || 'No details provided for this package.';
        $('#viewPkgName').text(name);
        $('#viewPkgDetails').text(details);
        $('#viewPackageModal').modal('show');
    });

    // ── Delete ─────────────────────────────────
    $(document).on('click', '.delete-btn', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        if (!confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone.`)) return;
        $.ajax({
            url: ROUTES.destroy(id),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message ?? 'Failed to delete. Please try again.';
                toastr.error(msg);
            }
        });
    });

    // ── Live Search ────────────────────────────
    $('#searchInput').on('keyup', function () {
        const val = $(this).val().toLowerCase();
        let visible = 0;
        $('#packageTable tbody tr:not(#emptyRow)').each(function () {
            const match = $(this).text().toLowerCase().includes(val);
            $(this).toggle(match);
            if (match) visible++;
        });
        $('#visibleCount').text(visible);
    });

    // ── Per Page (client-side filter for demo) ─
    $('#perPage').on('change', function () {
        // If server-side pagination is used, reload with ?per_page=X
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', $(this).val());
        window.location.href = url.toString();
    });
});
</script>
@stop
