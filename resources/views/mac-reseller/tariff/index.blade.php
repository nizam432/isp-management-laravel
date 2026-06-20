@extends('adminlte::page')

@section('title', 'Tariff Configurations')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-tags mr-2 text-success"></i> Tariff Configurations
            </h1>
            <small class="text-muted ml-1">Manage tariff configurations for POPs, packages, servers, and profiles</small>
        </div>
    </div>
@stop

@section('css')
<style>
    /* ── Card ───────────────────────────────── */
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

    /* ── Add Button ─────────────────────────── */
    .btn-add-tariff {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none; border-radius: 8px; color: #fff;
        font-weight: 600; padding: 8px 18px; font-size: .875rem;
        transition: all .2s; box-shadow: 0 3px 10px rgba(40,167,69,.35);
        white-space: nowrap;
    }
    .btn-add-tariff:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(40,167,69,.45); color: #fff;
    }
    .btn-add-tariff i { margin-right: 6px; }

    /* ── Table ──────────────────────────────── */
    #tariffTable thead th {
        background: #2d3748; color: #e2e8f0;
        font-size: .75rem; font-weight: 600;
        letter-spacing: .6px; text-transform: uppercase;
        border: none; padding: 13px 14px; white-space: nowrap;
    }
    #tariffTable tbody tr { transition: background .15s; }
    #tariffTable tbody tr:hover { background-color: #f0f9ff !important; }
    #tariffTable tbody td {
        padding: 11px 14px; vertical-align: middle;
        font-size: .855rem; color: #374151; border-color: #f0f0f0;
    }

    /* ── Serial No ──────────────────────────── */
    .serial-no {
        background: #f8fafc; color: #64748b;
        font-weight: 700; font-size: .78rem;
        width: 36px; height: 36px; border-radius: 50%;
        display: inline-flex; align-items: center;
        justify-content: center; border: 2px solid #e2e8f0;
    }

    /* ── Tariff Name ────────────────────────── */
    .tariff-name {
        font-weight: 600; color: #1a1a2e; font-size: .875rem;
    }
    .tariff-type-badge {
        font-size: .68rem; font-weight: 700; padding: 2px 8px;
        border-radius: 20px; margin-left: 6px;
        text-transform: uppercase; letter-spacing: .3px;
    }
    .type-custom     { background: #fef3c7; color: #92400e; }
    .type-date       { background: #dbeafe; color: #1e40af; }

    /* ── Multi-value Badges ─────────────────── */
    .badge-pill-soft {
        display: inline-block; font-size: .7rem; font-weight: 600;
        padding: 3px 9px; border-radius: 20px; margin: 2px 2px;
        white-space: nowrap;
    }
    .badge-pop     { background: #ede9fe; color: #5b21b6; }
    .badge-pkg     { background: #d1fae5; color: #065f46; }
    .badge-server  { background: #dbeafe; color: #1e40af; }
    .badge-profile { background: #fef3c7; color: #92400e; }
    .badge-none    { background: #f3f4f6; color: #9ca3af; font-style: italic; }

    /* ── Count Badge (overflow) ─────────────── */
    .badge-count {
        background: #e5e7eb; color: #6b7280;
        font-size: .68rem; font-weight: 700;
        padding: 3px 7px; border-radius: 20px;
        cursor: default;
    }

    /* ── Date & Creator ─────────────────────── */
    .date-cell { font-size: .8rem; color: #6b7280; white-space: nowrap; }
    .creator-cell {
        display: flex; align-items: center; gap: 6px;
    }
    .creator-avatar {
        width: 26px; height: 26px; border-radius: 50%;
        background: linear-gradient(135deg,#6366f1,#8b5cf6);
        display: inline-flex; align-items: center;
        justify-content: center; font-size: .7rem;
        font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .creator-name { font-size: .82rem; color: #374151; font-weight: 500; }

    /* ── Action Buttons ─────────────────────── */
    .action-btn {
        width: 30px; height: 30px; border-radius: 7px; border: none;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .75rem; transition: all .18s; margin: 0 2px;
        cursor: pointer;
    }
    .action-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,.15); }
    .btn-sync-action   { background: #e0f2fe; color: #0284c7; }
    .btn-sync-action:hover   { background: #0284c7; color: #fff; }
    .btn-toggle-action { background: #f0fdf4; color: #16a34a; }
    .btn-toggle-action:hover { background: #16a34a; color: #fff; }
    .btn-toggle-action.inactive { background: #fef2f2; color: #dc2626; }
    .btn-toggle-action.inactive:hover { background: #dc2626; color: #fff; }
    .btn-edit-action   { background: #fefce8; color: #ca8a04; }
    .btn-edit-action:hover   { background: #ca8a04; color: #fff; }
    .btn-view-action   { background: #f3e8ff; color: #7c3aed; }
    .btn-view-action:hover   { background: #7c3aed; color: #fff; }
    .btn-delete-action { background: #fee2e2; color: #dc2626; }
    .btn-delete-action:hover { background: #dc2626; color: #fff; }

    /* ── Search ─────────────────────────────── */
    .table-controls label { font-size: .82rem; color: #6b7280; font-weight: 500; margin-bottom: 4px; }
    #searchInput {
        border-radius: 8px; border: 1.5px solid #e2e8f0;
        padding: 6px 12px 6px 35px; font-size: .875rem;
        transition: border .2s; width: 220px;
    }
    #searchInput:focus { border-color: #28a745; outline: none; box-shadow: 0 0 0 3px rgba(40,167,69,.1); }
    .search-wrapper { position: relative; display: inline-block; }
    .search-wrapper .search-icon {
        position: absolute; left: 10px; top: 50%;
        transform: translateY(-50%); color: #9ca3af; font-size: .8rem; pointer-events: none;
    }
    #perPageSelect {
        border-radius: 8px; border: 1.5px solid #e2e8f0;
        padding: 5px 10px; font-size: .875rem; width: auto;
    }

    /* ── Empty State ────────────────────────── */
    .empty-state { padding: 60px 20px; text-align: center; }
    .empty-state .empty-icon {
        width: 80px; height: 80px; background: #f0fdf4; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px; border: 2px dashed #86efac;
    }
    .empty-state .empty-icon i { font-size: 2rem; color: #22c55e; }
    .empty-state h5 { color: #374151; font-weight: 600; margin-bottom: 6px; }
    .empty-state p  { color: #9ca3af; font-size: .875rem; margin-bottom: 20px; }

    /* ── Table Footer ───────────────────────── */
    .table-footer {
        display: flex; justify-content: space-between; align-items: center;
        padding: 14px 4px 4px; border-top: 1px solid #f3f4f6; margin-top: 8px;
    }
    .entries-info { font-size: .82rem; color: #6b7280; }
    .entries-info span { font-weight: 700; color: #374151; }

    /* ── Modal ──────────────────────────────── */
    .modal-content { border: none; border-radius: 14px; overflow: hidden; }
    .modal-header-custom {
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        padding: 16px 22px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .modal-header-custom .modal-title { color: #fff; font-weight: 600; font-size: .95rem; margin: 0; }
    .modal-header-custom .close { color: #fff; opacity: .7; text-shadow: none; }
    .modal-header-custom .close:hover { opacity: 1; }
    .modal-body { padding: 22px 24px; }
    .form-label-custom {
        font-size: .75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .5px; color: #374151; margin-bottom: 5px; display: block;
    }
    .form-control-custom {
        border-radius: 8px; border: 1.5px solid #e2e8f0;
        padding: 8px 12px; font-size: .875rem; transition: border .2s; width: 100%;
    }
    .form-control-custom:focus { border-color: #28a745; box-shadow: 0 0 0 3px rgba(40,167,69,.1); outline: none; }
    .modal-footer-custom {
        padding: 14px 24px; border-top: 1px solid #f3f4f6;
        background: #fafafa; display: flex; justify-content: flex-end; gap: 10px;
    }
    .btn-modal-save {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none; color: #fff; font-weight: 600;
        padding: 8px 22px; border-radius: 8px; font-size: .875rem; transition: all .2s;
    }
    .btn-modal-save:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(40,167,69,.3); color: #fff; }
    .btn-modal-cancel {
        background: #f3f4f6; border: none; color: #6b7280;
        font-weight: 600; padding: 8px 18px; border-radius: 8px; font-size: .875rem;
    }

    /* ── Package Lines Table ────────────────── */
    #linesTable thead th {
        background: #2d3748; color: #e2e8f0;
        font-size: .7rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: .4px; padding: 9px 10px; border: none;
    }
    #linesTable tbody td { font-size: .82rem; padding: 8px 10px; vertical-align: middle; }

    /* ── Tariff Type Radio ──────────────────── */
    .tariff-type-options { display: flex; gap: 16px; }
    .type-option {
        flex: 1; border: 2px solid #e2e8f0; border-radius: 10px;
        padding: 12px 16px; cursor: pointer; transition: all .2s;
        display: flex; align-items: center; gap: 10px;
    }
    .type-option:hover { border-color: #28a745; background: #f0fdf4; }
    .type-option.selected { border-color: #28a745; background: #f0fdf4; }
    .type-option input[type="radio"] { accent-color: #28a745; width: 16px; height: 16px; }
    .type-option-label { font-weight: 600; font-size: .875rem; color: #374151; margin: 0; cursor: pointer; }
    .type-option-sub   { font-size: .75rem; color: #9ca3af; margin: 0; }



    @media (max-width: 576px) {
        .tariff-type-options { flex-direction: column; }
        #searchInput { width: 100%; }
        .table-footer { flex-direction: column; gap: 10px; }
    }
</style>
@stop

@section('content')

<div class="mb-2 text-right">
    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addTariffModal">
        <i class="fas fa-plus"></i> Add Tariff Configuration
    </button>
</div>

<div class="card">



    <div class="card-body px-4 py-3">

        {{-- Table Controls --}}
        <div class="row align-items-end mb-3 table-controls">
            <div class="col-sm-4 d-flex align-items-center" style="gap:10px">
                <div>
                    <label class="d-block">Show entries</label>
                    <select id="perPageSelect" class="form-control form-control-sm">
                        <option>25</option>
                        <option selected>100</option>
                        <option>200</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-8 text-right">
                <label class="d-block">Search</label>
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" placeholder="Search tariffs...">
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered" id="tariffTable" style="border-radius:10px;overflow:hidden">
                <thead>
                    <tr>
                        <th class="text-center" style="width:60px">S/N</th>
                        <th>Tariff Name</th>
                        <th>Assigned POPs</th>
                        <th>Packages</th>
                        <th>Servers</th>
                        <th>Profiles</th>
                        <th>Created On</th>
                        <th>Created By</th>
                        <th class="text-center" style="width:160px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tariffs as $i => $t)
                    @php
                        $pops     = $t->resellers->pluck('business_name');
                        $pkgs     = $t->packages->map(fn($p) => $p->package?->name)->filter();
                        $servers  = $t->packages->pluck('server_name')->filter()->unique();
                        $profiles = $t->packages->pluck('profile')->filter()->unique();
                        $typeClass = $t->tariff_type === 'custom' ? 'type-custom' : 'type-date';
                        $typeLabel = $t->tariff_type === 'custom' ? 'Custom' : 'Date To Date';
                        $creatorInitial = strtoupper(substr($t->createdBy?->name ?? 'A', 0, 1));
                    @endphp
                    <tr>
                        {{-- S/N --}}
                        <td class="text-center">
                            <span class="serial-no">{{ $i + 1 }}</span>
                        </td>

                        {{-- Tariff Name --}}
                        <td>
                            <span class="tariff-name">{{ $t->name }}</span>
                            <span class="tariff-type-badge {{ $typeClass }}">{{ $typeLabel }}</span>
                        </td>

                        {{-- Assigned POPs --}}
                        <td>
                            @forelse($pops->take(2) as $pop)
                                <span class="badge-pill-soft badge-pop">{{ $pop }}</span>
                            @empty
                                <span class="badge-pill-soft badge-none">None</span>
                            @endforelse
                            @if($pops->count() > 2)
                                <span class="badge-count" data-toggle="tooltip" title="{{ $pops->skip(2)->join(', ') }}">
                                    +{{ $pops->count() - 2 }} more
                                </span>
                            @endif
                        </td>

                        {{-- Packages --}}
                        <td>
                            @forelse($pkgs->take(2) as $pkg)
                                <span class="badge-pill-soft badge-pkg">{{ $pkg }}</span>
                            @empty
                                <span class="badge-pill-soft badge-none">None</span>
                            @endforelse
                            @if($pkgs->count() > 2)
                                <span class="badge-count" data-toggle="tooltip" title="{{ $pkgs->skip(2)->join(', ') }}">
                                    +{{ $pkgs->count() - 2 }} more
                                </span>
                            @endif
                        </td>

                        {{-- Servers --}}
                        <td>
                            @forelse($servers->take(2) as $srv)
                                <span class="badge-pill-soft badge-server">{{ $srv }}</span>
                            @empty
                                <span class="badge-pill-soft badge-none">None</span>
                            @endforelse
                            @if($servers->count() > 2)
                                <span class="badge-count">+{{ $servers->count() - 2 }}</span>
                            @endif
                        </td>

                        {{-- Profiles --}}
                        <td>
                            @forelse($profiles->take(2) as $prf)
                                <span class="badge-pill-soft badge-profile">{{ $prf }}</span>
                            @empty
                                <span class="badge-pill-soft badge-none">None</span>
                            @endforelse
                            @if($profiles->count() > 2)
                                <span class="badge-count">+{{ $profiles->count() - 2 }}</span>
                            @endif
                        </td>

                        {{-- Created On --}}
                        <td>
                            <span class="date-cell">
                                <i class="far fa-calendar-alt mr-1 text-muted"></i>
                                {{ $t->created_at->format('d M Y') }}
                            </span>
                        </td>

                        {{-- Created By --}}
                        <td>
                            <div class="creator-cell">
                                <div class="creator-avatar">{{ $creatorInitial }}</div>
                                <span class="creator-name">{{ $t->createdBy?->name ?? 'N/A' }}</span>
                            </div>
                        </td>

                        {{-- Actions --}}
                        <td class="text-center">
                            {{-- Sync --}}
                            <button class="action-btn btn-sync-action sync-btn"
                                data-id="{{ $t->id }}"
                                data-toggle="tooltip" data-placement="top" title="Sync Mikrotik">
                                <i class="fas fa-sync-alt"></i>
                            </button>

                            {{-- Toggle Active --}}
                            <button class="action-btn btn-toggle-action {{ $t->is_active ? '' : 'inactive' }} toggle-btn"
                                data-id="{{ $t->id }}"
                                data-toggle="tooltip" data-placement="top"
                                title="{{ $t->is_active ? 'Disable' : 'Enable' }}">
                                <i class="fas fa-{{ $t->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                            </button>

                            {{-- Edit --}}
                            <button class="action-btn btn-edit-action edit-tariff-btn"
                                data-id="{{ $t->id }}"
                                data-toggle="tooltip" data-placement="top" title="Edit Tariff">
                                <i class="fas fa-pen"></i>
                            </button>

                            {{-- View --}}
                            <button class="action-btn btn-view-action view-tariff-btn"
                                data-id="{{ $t->id }}"
                                data-name="{{ $t->name }}"
                                data-toggle="tooltip" data-placement="top" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Delete --}}
                            <button class="action-btn btn-delete-action delete-tariff-btn"
                                data-id="{{ $t->id }}"
                                data-name="{{ $t->name }}"
                                data-toggle="tooltip" data-placement="top" title="Delete Tariff">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr style="background:#fff !important">
                        <td colspan="9" class="p-0 border-0" style="background:#fff !important">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <h5>No Tariff Configurations Found</h5>
                                <p>
                                    Click <strong>'Add Tariff Configuration'</strong> to create your first tariff.
                                </p>
                                <button class="btn-add-tariff" data-toggle="modal" data-target="#addTariffModal">
                                    <i class="fas fa-plus-circle"></i> Add Tariff Configuration
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
                Showing <span id="visibleCount">{{ count($tariffs) }}</span>
                of <span>{{ count($tariffs) }}</span> entries
            </div>
            <div>
                @if(method_exists($tariffs, 'links'))
                    {{ $tariffs->links() }}
                @endif
            </div>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════
     Add Tariff Modal
══════════════════════════════════════════ --}}
<div class="modal fade" id="addTariffModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header-custom">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Add Tariff Configuration</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <form id="addTariffForm" autocomplete="off">
                    @csrf

                    {{-- Tariff Type --}}
                    <div class="form-group mb-4">
                        <label class="form-label-custom">
                            Tariff Type <span class="text-danger">*</span>
                            <i class="fas fa-info-circle text-info ml-1"
                               data-toggle="tooltip"
                               title="Custom: fixed validity days. Date To Date: billing from a specific date to another date."></i>
                        </label>
                        <div class="tariff-type-options">
                            <label class="type-option selected" id="optCustom">
                                <input type="radio" name="tariff_type" value="custom" checked>
                                <div>
                                    <p class="type-option-label"><i class="fas fa-sliders-h mr-1 text-warning"></i> Custom</p>
                                    <p class="type-option-sub">Fixed validity days per package</p>
                                </div>
                            </label>
                            <label class="type-option" id="optDateToDate">
                                <input type="radio" name="tariff_type" value="date_to_date">
                                <div>
                                    <p class="type-option-label"><i class="fas fa-calendar-alt mr-1 text-info"></i> Date To Date</p>
                                    <p class="type-option-sub">Billing from start date to end date</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Tariff Name --}}
                    <div class="form-group mb-3">
                        <label class="form-label-custom">Tariff Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control-custom" placeholder="e.g. Gold Plan 30MB" required>
                    </div>

                    {{-- Date To Date fields removed --}}

                    <hr class="my-3">

                    {{-- Add Package Line --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-box mr-1 text-success"></i> Add Package Lines
                        </h6>
                    </div>

                    <div class="row align-items-end mb-3">
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label-custom">Package Name <span class="text-danger">*</span></label>
                            <select id="linePackage" class="form-control form-control-sm" style="border-radius:8px;border:1.5px solid #e2e8f0">
                                <option value="">Select</option>
                                @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" data-name="{{ $pkg->name }}">{{ $pkg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label-custom">Server (Mikrotik) <span class="text-danger">*</span></label>
                            <select id="lineServer" class="form-control form-control-sm" style="border-radius:8px;border:1.5px solid #e2e8f0">
                                <option value="">Select</option>
                                @foreach($routers as $router)
                                <option value="{{ $router->name }}" data-id="{{ $router->id }}">{{ $router->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label-custom">Protocol Type <span class="text-danger">*</span></label>
                            <select id="lineProtocol" class="form-control form-control-sm" style="border-radius:8px;border:1.5px solid #e2e8f0" disabled>
                                <option value="">— Select Server First —</option>
                                <option value="pppoe">PPPoE</option>
                                <option value="hotspot">Hotspot</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label-custom">Profile (Speed) <span class="text-danger">*</span></label>
                            <select id="lineProfile" class="form-control form-control-sm" style="border-radius:8px;border:1.5px solid #e2e8f0" disabled>
                                <option value="">— Select Protocol First —</option>
                            </select>
                        </div>
                        <div class="col-md-1 col-sm-4">
                            <label class="form-label-custom">Rate <span class="text-danger">*</span></label>
                            <input type="number" id="lineRate" class="form-control form-control-sm" placeholder="0" min="1" style="border-radius:8px;border:1.5px solid #e2e8f0">
                        </div>
                        <div class="col-md-1 col-sm-4">
                            <label class="form-label-custom">Validity</label>
                            <input type="number" id="lineValidity" class="form-control form-control-sm" value="30" min="1" style="border-radius:8px;border:1.5px solid #e2e8f0">
                        </div>
                        <div class="col-md-1 col-sm-4">
                            <label class="form-label-custom">Min Act. Days</label>
                            <input type="number" id="lineMinActivation" class="form-control form-control-sm" value="1" min="1" style="border-radius:8px;border:1.5px solid #e2e8f0">
                        </div>
                        <div class="col-md-1 col-sm-12 d-flex align-items-end mt-2 mt-md-0">
                            <button type="button" id="addLineBtn" class="btn btn-dark btn-sm w-100" style="border-radius:8px;padding:7px 6px;font-size:.78rem;white-space:nowrap">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>

                    {{-- Lines Table --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="linesTable" style="border-radius:8px;overflow:hidden">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:50px">Sr.</th>
                                    <th>Package</th>
                                    <th>Server</th>
                                    <th>Protocol</th>
                                    <th>Profile</th>
                                    <th>Rate</th>
                                    <th>Validity Days</th>
                                    <th>Min Act. Days</th>
                                    <th class="text-center" style="width:60px">Action</th>
                                </tr>
                            </thead>
                            <tbody id="linesTbody">
                                <tr id="noLinesRow">
                                    <td colspan="9" class="text-center py-3 text-muted" style="font-size:.85rem">
                                        <i class="fas fa-info-circle mr-1"></i> No packages added yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="linesData"></div>

                </form>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-modal-cancel" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="submit" form="addTariffForm" class="btn-modal-save" id="saveTariffBtn">
                    <i class="fas fa-save mr-1"></i> Save Tariff
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     Edit Tariff Modal
══════════════════════════════════════════ --}}
<div class="modal fade" id="editTariffModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header-custom">
                <h5 class="modal-title"><i class="fas fa-pen mr-2"></i> Edit Tariff Configuration</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <form id="editTariffForm" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editTariffId">

                    {{-- Tariff Type --}}
                    <div class="form-group mb-4">
                        <label class="form-label-custom">Tariff Type <span class="text-danger">*</span></label>
                        <div class="tariff-type-options">
                            <label class="type-option" id="editOptCustom">
                                <input type="radio" name="tariff_type" value="custom" id="editTypeCustom">
                                <div>
                                    <p class="type-option-label"><i class="fas fa-sliders-h mr-1 text-warning"></i> Custom</p>
                                    <p class="type-option-sub">Fixed validity days per package</p>
                                </div>
                            </label>
                            <label class="type-option" id="editOptDateToDate">
                                <input type="radio" name="tariff_type" value="date_to_date" id="editTypeDateToDate">
                                <div>
                                    <p class="type-option-label"><i class="fas fa-calendar-alt mr-1 text-info"></i> Date To Date</p>
                                    <p class="type-option-sub">Billing from start date to end date</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Tariff Name --}}
                    <div class="form-group mb-3">
                        <label class="form-label-custom">Tariff Name <span class="text-danger">*</span></label>
                        <input type="text" id="editTariffName" name="name" class="form-control-custom" placeholder="e.g. Gold Plan 30MB" required>
                    </div>

                    <hr class="my-3">

                    {{-- Add Package Line --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-box mr-1 text-success"></i> Package Lines
                        </h6>
                    </div>

                    <div class="row align-items-end mb-3">
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label-custom">Package Name <span class="text-danger">*</span></label>
                            <select id="editLinePackage" class="form-control form-control-sm" style="border-radius:8px;border:1.5px solid #e2e8f0">
                                <option value="">Select</option>
                                @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" data-name="{{ $pkg->name }}">{{ $pkg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label-custom">Server (Mikrotik) <span class="text-danger">*</span></label>
                            <select id="editLineServer" class="form-control form-control-sm" style="border-radius:8px;border:1.5px solid #e2e8f0">
                                <option value="">Select</option>
                                @foreach($routers as $router)
                                <option value="{{ $router->name }}" data-id="{{ $router->id }}">{{ $router->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label-custom">Protocol Type <span class="text-danger">*</span></label>
                            <select id="editLineProtocol" class="form-control form-control-sm" style="border-radius:8px;border:1.5px solid #e2e8f0" disabled>
                                <option value="">— Select Server First —</option>
                                <option value="pppoe">PPPoE</option>
                                <option value="hotspot">Hotspot</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label-custom">Profile (Speed) <span class="text-danger">*</span></label>
                            <select id="editLineProfile" class="form-control form-control-sm" style="border-radius:8px;border:1.5px solid #e2e8f0" disabled>
                                <option value="">— Select Protocol First —</option>
                            </select>
                        </div>
                        <div class="col-md-1 col-sm-4">
                            <label class="form-label-custom">Rate <span class="text-danger">*</span></label>
                            <input type="number" id="editLineRate" class="form-control form-control-sm" placeholder="0" min="1" style="border-radius:8px;border:1.5px solid #e2e8f0">
                        </div>
                        <div class="col-md-1 col-sm-4">
                            <label class="form-label-custom">Validity</label>
                            <input type="number" id="editLineValidity" class="form-control form-control-sm" value="30" min="1" style="border-radius:8px;border:1.5px solid #e2e8f0">
                        </div>
                        <div class="col-md-1 col-sm-4">
                            <label class="form-label-custom">Min Act. Days</label>
                            <input type="number" id="editLineMinActivation" class="form-control form-control-sm" value="1" min="1" style="border-radius:8px;border:1.5px solid #e2e8f0">
                        </div>
                        <div class="col-md-1 col-sm-12 d-flex align-items-end mt-2 mt-md-0">
                            <button type="button" id="editAddLineBtn" class="btn btn-dark btn-sm w-100" style="border-radius:8px;padding:7px 6px;font-size:.78rem;white-space:nowrap">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>

                    {{-- Lines Table --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="editLinesTable" style="border-radius:8px;overflow:hidden">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:50px">Sr.</th>
                                    <th>Package</th>
                                    <th>Server</th>
                                    <th>Protocol</th>
                                    <th>Profile</th>
                                    <th>Rate</th>
                                    <th>Validity Days</th>
                                    <th>Min Act. Days</th>
                                    <th class="text-center" style="width:60px">Action</th>
                                </tr>
                            </thead>
                            <tbody id="editLinesTbody">
                                <tr id="editNoLinesRow">
                                    <td colspan="9" class="text-center py-3 text-muted" style="font-size:.85rem">
                                        <i class="fas fa-info-circle mr-1"></i> No packages added yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="editLinesData"></div>

                </form>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-modal-cancel" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="submit" form="editTariffForm" class="btn-modal-save" id="updateTariffBtn">
                    <i class="fas fa-save mr-1"></i> Update Tariff
                </button>
            </div>

        </div>
    </div>
</div>

@stop

@section('js')
<script>
$(function () {

    // ── Init Tooltips ──────────────────────────────────
    $('[data-toggle="tooltip"]').tooltip();

    // ── Server Change → Enable Protocol ───────────────
    $(document).on('change', '#lineServer', function () {
        const routerId = $(this).find('option:selected').data('id');
        const $protocol = $('#lineProtocol');
        const $profile  = $('#lineProfile');

        // Protocol reset
        $protocol.val('').prop('disabled', !routerId);
        if (!routerId) {
            $protocol.html('<option value="">— Select Server First —</option><option value="pppoe">PPPoE</option><option value="hotspot">Hotspot</option>').prop('disabled', true);
        } else {
            $protocol.html('<option value="">Select Protocol</option><option value="pppoe">PPPoE</option><option value="hotspot">Hotspot</option>').prop('disabled', false);
        }

        // Profile reset
        $profile.html('<option value="">— Select Protocol First —</option>').prop('disabled', true);
    });

    // ── Protocol Change → Load Profiles ───────────────
    $(document).on('change', '#lineProtocol', function () {
        const protocol  = $(this).val();
        const routerId  = $('#lineServer').find('option:selected').data('id');
        const $profile  = $('#lineProfile');

        if (!protocol || !routerId) {
            $profile.html('<option value="">— Select Protocol First —</option>').prop('disabled', true);
            return;
        }

        $profile.html('<option value=""><i class="fas fa-spinner fa-spin"></i> Loading...</option>').prop('disabled', true);

        const url = protocol === 'pppoe'
            ? `/mikrotik/${routerId}/profiles`
            : `/mikrotik/${routerId}/hotspot-profiles`;

        $.get(url, function (res) {
            $profile.empty().append('<option value="">Select Profile</option>');
            const profiles = res.data ?? res;
            if (!profiles || profiles.length === 0) {
                $profile.append('<option value="" disabled>No profiles found</option>');
            } else {
                profiles.forEach(p => {
                    const name  = p.name ?? p['.id'] ?? '';
                    const rate  = p['rate-limit'] ?? p['rate_limit'] ?? '';
                    const label = rate ? `${name} (${rate})` : name;
                    $profile.append(`<option value="${name}">${label}</option>`);
                });
            }
            $profile.prop('disabled', false);
        }).fail(function () {
            $profile.html('<option value="">Failed to load</option>').prop('disabled', true);
            toastr.error('Failed to load profiles from router.');
        });
    });

    // ── Tariff Type Toggle ─────────────────────────────
    $('input[name="tariff_type"]').on('change', function () {
        const isDate = $(this).val() === 'date_to_date';
        // Update selected style
        $('.type-option').removeClass('selected');
        $(this).closest('.type-option').addClass('selected');

        if (isDate) {
            // Date To Date: Validity=30 readonly, Min Activation=30 readonly
            $('#lineValidity').val(30).prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');
            $('#lineMinActivation').val(30).prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');
        } else {
            // Custom: Validity=30 editable, Min Activation=1 editable
            $('#lineValidity').val(30).prop('readonly', false).removeClass('bg-light').css('cursor', 'text');
            $('#lineMinActivation').val(1).prop('readonly', false).removeClass('bg-light').css('cursor', 'text');
        }
    });

    // ── Add Package Line ───────────────────────────────
    let lineCount = 0;
    let addedLines = {};

    $('#addLineBtn').on('click', function () {
        const pkgEl   = $('#linePackage');
        const pkgId   = pkgEl.val();
        const pkgName = pkgEl.find('option:selected').data('name');
        const server      = $('#lineServer').val().trim();
        const serverText  = $('#lineServer option:selected').text();
        const protocol    = $('#lineProtocol').val();
        const protocolText = protocol ? protocol.toUpperCase() : '';
        const profile     = $('#lineProfile').val();
        const profileText = $('#lineProfile option:selected').text();
        const rate    = $('#lineRate').val();
        const rateNum = parseFloat(rate);
        const validity = parseInt($('#lineValidity').val()) || 30;
        const minAct  = parseInt($('#lineMinActivation').val()) || 30;

        if (!pkgId) { toastr.warning('Please select a Package.'); return; }
        if (!server) { toastr.warning('Please select a Server.'); return; }
        if (!protocol) { toastr.warning('Please select a Protocol Type.'); return; }
        if (!profile) { toastr.warning('Please select a Profile.'); return; }
        if (!rate || rateNum <= 0) { toastr.warning('Package Rate must be greater than 0.'); return; }

        $('#noLinesRow').hide();
        lineCount++;
        addedLines[lineCount] = true;

        const row = `
        <tr id="line-${lineCount}" style="font-size:.82rem">
            <td class="text-center"><span class="serial-no" style="width:28px;height:28px;font-size:.72rem">${lineCount}</span></td>
            <td><span class="badge-pill-soft badge-pkg">${pkgName}</span></td>
            <td>${server ? `<span class="badge-pill-soft badge-server">${serverText}</span>` : '<span class="text-muted">—</span>'}</td>
            <td><span class="badge-pill-soft ${protocol === 'pppoe' ? 'badge-pop' : 'badge-profile'}">${protocolText}</span></td>
            <td>${profile ? `<span class="badge-pill-soft badge-profile">${profileText}</span>` : '<span class="text-muted">—</span>'}</td>
            <td><strong>${rateNum}</strong></td>
            <td>${validity} days</td>
            <td>${minAct} day(s)</td>
            <td class="text-center">
                <button type="button" class="action-btn btn-delete-action"
                    onclick="removeLine(${lineCount})" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>`;

        $('#linesTbody').append(row);
        $('#linesData').append(`
            <input type="hidden" name="lines[${lineCount}][package_id]" value="${pkgId}">
            <input type="hidden" name="lines[${lineCount}][server_name]" value="${server}">
            <input type="hidden" name="lines[${lineCount}][protocol]" value="${protocol}">
            <input type="hidden" name="lines[${lineCount}][profile]" value="${profile}">
            <input type="hidden" name="lines[${lineCount}][rate]" value="${rateNum}">
            <input type="hidden" name="lines[${lineCount}][validity_days]" value="${validity}">
            <input type="hidden" name="lines[${lineCount}][min_activation_days]" value="${minAct}">
        `);

        // Reset line inputs
        pkgEl.val('');
        $('#lineServer').val('');
        $('#lineProtocol').html('<option value="">— Select Server First —</option><option value="pppoe">PPPoE</option><option value="hotspot">Hotspot</option>').prop('disabled', true);
        $('#lineProfile').html('<option value="">— Select Protocol First —</option>').prop('disabled', true);
        $('#lineRate').val('');
        $('#lineValidity').val(30).prop('readonly', false).removeClass('bg-light').css('cursor', 'text');
        $('#lineMinActivation').val(1).prop('readonly', false).removeClass('bg-light').css('cursor', 'text');
    });

    window.removeLine = function (idx) {
        $(`#line-${idx}`).remove();
        $(`input[name^="lines[${idx}]"]`).remove();
        delete addedLines[idx];
        if (Object.keys(addedLines).length === 0) $('#noLinesRow').show();
    };

    // ── Add Tariff Submit ──────────────────────────────
    $('#addTariffForm').on('submit', function (e) {
        e.preventDefault();
        if (Object.keys(addedLines).length === 0) {
            toastr.warning('Please add at least one package line.');
            return;
        }
        const $btn = $('#saveTariffBtn').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
        $.ajax({
            url: "{{ route('mac-reseller.tariff.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    $('#addTariffModal').modal('hide');
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) toastr.error(Object.values(errors).flat().join('\n'));
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Tariff');
            }
        });
    });

    // Reset modal on close
    $('#addTariffModal').on('hidden.bs.modal', function () {
        $('#addTariffForm')[0].reset();
        $('#linesTbody').empty().append(`
            <tr id="noLinesRow">
                <td colspan="9" class="text-center py-3 text-muted" style="font-size:.85rem">
                    <i class="fas fa-info-circle mr-1"></i> No packages added yet.
                </td>
            </tr>`);
        $('#linesData').empty();
        lineCount = 0;
        addedLines = {};
        $('.type-option').removeClass('selected');
        $('#optCustom').addClass('selected');
        $('#lineProtocol').html('<option value="">— Select Server First —</option><option value="pppoe">PPPoE</option><option value="hotspot">Hotspot</option>').prop('disabled', true);
        $('#lineProfile').html('<option value="">— Select Protocol First —</option>').prop('disabled', true);
        $('#lineValidity').val(30).prop('readonly', false).removeClass('bg-light').css('cursor', 'text');
        $('#lineMinActivation').val(1).prop('readonly', false).removeClass('bg-light').css('cursor', 'text');
    });

    // ── Edit Tariff → Load Data ────────────────────────
    let editLineCount = 0;
    let editAddedLines = {};

    $(document).on('click', '.edit-tariff-btn', function () {
        const id = $(this).data('id');
        editLineCount = 0;
        editAddedLines = {};
        $('#editLinesTbody').empty().append(`
            <tr id="editNoLinesRow">
                <td colspan="9" class="text-center py-3 text-muted" style="font-size:.85rem">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Loading...
                </td>
            </tr>`);
        $('#editLinesData').empty();

        $.get(`/mac-reseller/tariff/${id}`, function (res) {
            $('#editTariffId').val(res.id);
            $('#editTariffName').val(res.name);

            // Tariff Type
            $('input[name="tariff_type"]', '#editTariffForm').val([res.tariff_type]);
            $('#editOptCustom, #editOptDateToDate').removeClass('selected');
            if (res.tariff_type === 'custom') {
                $('#editOptCustom').addClass('selected');
                $('#editLineValidity').val(30).prop('readonly', false).removeClass('bg-light');
                $('#editLineMinActivation').val(1).prop('readonly', false).removeClass('bg-light');
            } else {
                $('#editOptDateToDate').addClass('selected');
                $('#editLineValidity').val(30).prop('readonly', true).addClass('bg-light');
                $('#editLineMinActivation').val(30).prop('readonly', true).addClass('bg-light');
            }

            // Load existing lines (database থেকে — id সহ, real-time edit/delete হবে)
            $('#editLinesTbody').empty();
            if (res.packages && res.packages.length > 0) {
                res.packages.forEach((line) => {
                    renderEditLineRow(line, true);
                });
            } else {
                $('#editLinesTbody').append(`
                    <tr id="editNoLinesRow">
                        <td colspan="9" class="text-center py-3 text-muted" style="font-size:.85rem">
                            <i class="fas fa-info-circle mr-1"></i> No packages added yet.
                        </td>
                    </tr>`);
            }

            $('#editTariffModal').modal('show');
        }).fail(() => toastr.error('Failed to load tariff data.'));
    });

    // ── Render একটা existing (DB) line row ─────────────
    function renderEditLineRow(line, isExisting) {
        const pkgName  = line.package?.name ?? '';
        const server   = line.server_name ?? '';
        const protocol = line.protocol ?? '';
        const profile  = line.profile ?? '';
        const rate     = line.rate ?? '';
        const validity = line.validity_days ?? 30;
        const minAct   = line.min_activation_days ?? 1;
        const lineId   = line.id;

        $('#editLinesTbody').append(`
        <tr id="db-line-${lineId}" data-line-id="${lineId}" style="font-size:.82rem">
            <td class="text-center"><span class="serial-no" style="width:28px;height:28px;font-size:.72rem">${lineId}</span></td>
            <td><span class="badge-pill-soft badge-pkg">${pkgName}</span></td>
            <td>${server ? `<span class="badge-pill-soft badge-server">${server}</span>` : '<span class="text-muted">—</span>'}</td>
            <td>${protocol ? `<span class="badge-pill-soft badge-pop">${protocol.toUpperCase()}</span>` : '<span class="text-muted">—</span>'}</td>
            <td>${profile ? `<span class="badge-pill-soft badge-profile">${profile}</span>` : '<span class="text-muted">—</span>'}</td>
            <td class="view-rate"><strong>${rate}</strong></td>
            <td class="view-validity">${validity} days</td>
            <td class="view-minact">${minAct} day(s)</td>
            <td class="text-center">
                <button type="button" class="action-btn btn-edit-action db-edit-line-btn" data-id="${lineId}" title="Edit Rate/Validity">
                    <i class="fas fa-pen"></i>
                </button>
                <button type="button" class="action-btn btn-delete-action db-delete-line-btn" data-id="${lineId}" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>`);
    }

    // ── DB Line: Edit (inline) ──────────────────────────
    $(document).on('click', '.db-edit-line-btn', function () {
        const $row = $(`#db-line-${$(this).data('id')}`);
        if ($row.hasClass('editing')) return;
        $row.addClass('editing');

        const curRate     = $row.find('.view-rate strong').text().trim();
        const curValidity = parseInt($row.find('.view-validity').text());
        const curMinAct   = parseInt($row.find('.view-minact').text());

        $row.find('.view-rate').html(`<input type="number" class="form-control form-control-sm inline-edit-rate" value="${curRate}" min="1" style="width:90px">`);
        $row.find('.view-validity').html(`<input type="number" class="form-control form-control-sm inline-edit-validity" value="${curValidity}" min="1" style="width:80px">`);
        $row.find('.view-minact').html(`<input type="number" class="form-control form-control-sm inline-edit-minact" value="${curMinAct}" min="1" style="width:80px">`);

        $row.find('td:last').html(`
            <button type="button" class="action-btn btn-toggle-action db-save-line-btn" data-id="${$row.data('line-id')}" title="Save">
                <i class="fas fa-check"></i>
            </button>
            <button type="button" class="action-btn btn-delete-action db-cancel-line-btn" data-id="${$row.data('line-id')}" title="Cancel">
                <i class="fas fa-times"></i>
            </button>
        `);
    });

    // ── DB Line: Cancel inline edit ──────────────────────
    $(document).on('click', '.db-cancel-line-btn', function () {
        const id = $(this).data('id');
        $(`#db-line-${id}`).removeClass('editing');
        // আবার fresh data দিয়ে modal reload করাই সবচেয়ে নিরাপদ
        $('.edit-tariff-btn[data-id="' + $('#editTariffId').val() + '"]').trigger('click');
    });

    // ── DB Line: Save inline edit (AJAX → updateLine) ───
    $(document).on('click', '.db-save-line-btn', function () {
        const id   = $(this).data('id');
        const $row = $(`#db-line-${id}`);
        const rate     = $row.find('.inline-edit-rate').val();
        const validity = $row.find('.inline-edit-validity').val();
        const minAct   = $row.find('.inline-edit-minact').val();

        if (!rate || rate <= 0)     { toastr.warning('Rate must be greater than 0.'); return; }
        if (!validity || validity <= 0) { toastr.warning('Validity Days must be greater than 0.'); return; }
        if (!minAct || minAct <= 0) { toastr.warning('Min Activation Days must be greater than 0.'); return; }

        $.ajax({
            url: `/mac-reseller/tariff/line/${id}`,
            method: 'PUT',
            data: { _token: '{{ csrf_token() }}', rate, validity_days: validity, min_activation_days: minAct },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    $row.removeClass('editing');
                    $row.find('.view-rate').html(`<strong>${rate}</strong>`);
                    $row.find('.view-validity').text(`${validity} days`);
                    $row.find('.view-minact').text(`${minAct} day(s)`);
                    $row.find('td:last').html(`
                        <button type="button" class="action-btn btn-edit-action db-edit-line-btn" data-id="${id}" title="Edit Rate/Validity">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button type="button" class="action-btn btn-delete-action db-delete-line-btn" data-id="${id}" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    `);
                    // main table এও reflect করতে list reload (modal বন্ধ না করেই)
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                toastr.error(errors ? Object.values(errors).flat().join('\n') : 'Failed to update line.');
            }
        });
    });

    // ── DB Line: Delete (AJAX → destroyLine, reseller block check) ──
    $(document).on('click', '.db-delete-line-btn', function () {
        const id = $(this).data('id');
        $.ajax({
            url: `/mac-reseller/tariff/line/${id}`,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    $(`#db-line-${id}`).fadeOut(200, function () { $(this).remove(); });
                }
            },
            error: function (xhr) {
                const res = xhr.responseJSON;
                if (res && res.blocked) {
                    showLineDeleteBlockedWarning(res.resellers);
                } else {
                    toastr.error(res?.message ?? 'Failed to remove package line.');
                }
            }
        });
    });

    // ── Reseller-in-use Warning Modal ───────────────────
    function showLineDeleteBlockedWarning(resellers) {
        const list = resellers.map(r => `<li>${r}</li>`).join('');
        const html = `
        <div class="modal fade" id="lineBlockedModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius:14px;overflow:hidden">
                    <div class="modal-body text-center" style="padding:28px 24px">
                        <i class="fas fa-exclamation-triangle" style="font-size:2.2rem;color:#dc2626"></i>
                        <h5 class="font-weight-bold text-danger mt-3 mb-2">Warning!</h5>
                        <p class="font-weight-bold mb-2">Are you sure want to delete this package/profile/speed?</p>
                        <p class="mb-1"><strong>POP:</strong></p>
                        <ul class="text-left d-inline-block mb-3" style="font-size:.85rem">${list}</ul>
                        <p class="text-danger font-weight-bold mb-3">Please change the package of this users!</p>
                        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>`;
        $('#lineBlockedModal').remove();
        $('body').append(html);
        $('#lineBlockedModal').modal('show');
        $('#lineBlockedModal').on('hidden.bs.modal', function () { $(this).remove(); });
    }

    // ── Edit: Tariff Type Toggle ───────────────────────
    $('#editTariffForm').on('change', 'input[name="tariff_type"]', function () {
        const isDate = $(this).val() === 'date_to_date';
        $('#editOptCustom, #editOptDateToDate').removeClass('selected');
        $(this).closest('.type-option').addClass('selected');
        if (isDate) {
            $('#editLineValidity').val(30).prop('readonly', true).addClass('bg-light').css('cursor','not-allowed');
            $('#editLineMinActivation').val(30).prop('readonly', true).addClass('bg-light').css('cursor','not-allowed');
        } else {
            $('#editLineValidity').val(30).prop('readonly', false).removeClass('bg-light').css('cursor','text');
            $('#editLineMinActivation').val(1).prop('readonly', false).removeClass('bg-light').css('cursor','text');
        }
    });

    // ── Edit: Server → Protocol enable ────────────────
    $(document).on('change', '#editLineServer', function () {
        const routerId = $(this).find('option:selected').data('id');
        $('#editLineProtocol').val('').prop('disabled', !routerId)
            .html('<option value="">Select Protocol</option><option value="pppoe">PPPoE</option><option value="hotspot">Hotspot</option>');
        if (!routerId) $('#editLineProtocol').prop('disabled', true);
        $('#editLineProfile').html('<option value="">— Select Protocol First —</option>').prop('disabled', true);
    });

    // ── Edit: Protocol → Load Profiles ────────────────
    $(document).on('change', '#editLineProtocol', function () {
        const protocol = $(this).val();
        const routerId = $('#editLineServer').find('option:selected').data('id');
        const $profile = $('#editLineProfile');
        if (!protocol || !routerId) { $profile.html('<option value="">— Select Protocol First —</option>').prop('disabled', true); return; }
        $profile.html('<option value="">Loading...</option>').prop('disabled', true);
        const url = protocol === 'pppoe' ? `/mikrotik/${routerId}/profiles` : `/mikrotik/${routerId}/hotspot-profiles`;
        $.get(url, function (res) {
            $profile.empty().append('<option value="">Select Profile</option>');
            const profiles = res.data ?? res;
            if (profiles && profiles.length > 0) {
                profiles.forEach(p => {
                    const name = p.name ?? '';
                    const rate = p['rate-limit'] ?? p.rate_limit ?? '';
                    $profile.append(`<option value="${name}">${rate ? name+' ('+rate+')' : name}</option>`);
                });
            }
            $profile.prop('disabled', false);
        }).fail(() => { $profile.html('<option value="">Failed to load</option>').prop('disabled', true); toastr.error('Failed to load profiles.'); });
    });

    // ── Edit: Add Package Line ─────────────────────────
    $('#editAddLineBtn').on('click', function () {
        const pkgEl   = $('#editLinePackage');
        const pkgId   = pkgEl.val();
        const pkgName = pkgEl.find('option:selected').data('name');
        const server      = $('#editLineServer').val();
        const serverText  = $('#editLineServer option:selected').text();
        const protocol    = $('#editLineProtocol').val();
        const profile     = $('#editLineProfile').val();
        const profileText = $('#editLineProfile option:selected').text();
        const rate        = $('#editLineRate').val();
        const rateNum     = parseFloat(rate);
        const validity    = parseInt($('#editLineValidity').val()) || 30;
        const minAct      = parseInt($('#editLineMinActivation').val()) || 1;

        if (!pkgId)    { toastr.warning('Please select a Package.'); return; }
        if (!server)   { toastr.warning('Please select a Server.'); return; }
        if (!protocol) { toastr.warning('Please select a Protocol Type.'); return; }
        if (!profile)  { toastr.warning('Please select a Profile.'); return; }
        if (!rate || rateNum <= 0) { toastr.warning('Package Rate must be greater than 0.'); return; }

        $('#editNoLinesRow').hide();
        editLineCount++;
        editAddedLines[editLineCount] = true;
        const lc = editLineCount;

        $('#editLinesTbody').append(`
        <tr id="edit-line-${lc}" style="font-size:.82rem">
            <td class="text-center"><span class="serial-no" style="width:28px;height:28px;font-size:.72rem">${lc}</span></td>
            <td><span class="badge-pill-soft badge-pkg">${pkgName}</span></td>
            <td>${server ? `<span class="badge-pill-soft badge-server">${serverText}</span>` : '<span class="text-muted">—</span>'}</td>
            <td><span class="badge-pill-soft badge-pop">${protocol.toUpperCase()}</span></td>
            <td>${profile ? `<span class="badge-pill-soft badge-profile">${profileText}</span>` : '<span class="text-muted">—</span>'}</td>
            <td><strong>${rateNum}</strong></td>
            <td>${validity} days</td>
            <td>${minAct} day(s)</td>
            <td class="text-center">
                <button type="button" class="action-btn btn-delete-action" onclick="removeEditLine(${lc})" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>`);

        $('#editLinesData').append(`
            <input type="hidden" name="lines[${lc}][package_id]" value="${pkgId}">
            <input type="hidden" name="lines[${lc}][server_name]" value="${server}">
            <input type="hidden" name="lines[${lc}][protocol]" value="${protocol}">
            <input type="hidden" name="lines[${lc}][profile]" value="${profile}">
            <input type="hidden" name="lines[${lc}][rate]" value="${rateNum}">
            <input type="hidden" name="lines[${lc}][validity_days]" value="${validity}">
            <input type="hidden" name="lines[${lc}][min_activation_days]" value="${minAct}">
        `);

        pkgEl.val('');
        $('#editLineServer').val('');
        $('#editLineProtocol').html('<option value="">— Select Server First —</option><option value="pppoe">PPPoE</option><option value="hotspot">Hotspot</option>').prop('disabled', true);
        $('#editLineProfile').html('<option value="">— Select Protocol First —</option>').prop('disabled', true);
        $('#editLineRate').val('');
    });

    window.removeEditLine = function (idx) {
        $(`#edit-line-${idx}`).remove();
        $(`#editLinesData input[name^="lines[${idx}]"]`).remove();
        delete editAddedLines[idx];
        if (Object.keys(editAddedLines).length === 0) $('#editNoLinesRow').show();
    };

    // ── Edit Tariff Submit ─────────────────────────────
    $('#editTariffForm').on('submit', function (e) {
        e.preventDefault();
        const dbLineCount = $('#editLinesTbody tr[data-line-id]').length;
        const newLineCount = Object.keys(editAddedLines).length;
        if (dbLineCount === 0 && newLineCount === 0) {
            toastr.warning('Please add at least one package line.');
            return;
        }
        const id   = $('#editTariffId').val();
        const $btn = $('#updateTariffBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
        $.ajax({
            url: `/mac-reseller/tariff/${id}`,
            method: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            success: function (res) {
                if (res.success) {
                    $('#editTariffModal').modal('hide');
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) toastr.error(Object.values(errors).flat().join('\n'));
                else toastr.error('Failed to update tariff.');
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Tariff');
            }
        });
    });

    // ── Delete ─────────────────────────────────────────
    $(document).on('click', '.delete-tariff-btn', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        if (!confirm(`Delete tariff "${name}"?\n\nThis action cannot be undone.`)) return;
        $.ajax({
            url: `/mac-reseller/tariff/${id}`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: (res) => {
                if (res.success) {
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                }
            },
            error: (xhr) => {
                const msg = xhr.responseJSON?.message ?? 'Failed to delete tariff.';
                toastr.error(msg);
            }
        });
    });

    // ── Toggle Active ──────────────────────────────────
    $(document).on('click', '.toggle-btn', function () {
        const id = $(this).data('id');
        $.post(`/mac-reseller/tariff/${id}/toggle`, { _token: '{{ csrf_token() }}' },
            () => location.reload()
        );
    });

    // ── Sync Mikrotik ──────────────────────────────────
    $(document).on('click', '.sync-btn', function () {
        const id  = $(this).data('id');
        const $btn = $(this);
        $btn.html('<i class="fas fa-spinner fa-spin"></i>');
        $.post(`/mac-reseller/tariff/${id}/sync-mikrotik`, { _token: '{{ csrf_token() }}' },
            (res) => {
                toastr.success(res.message);
                $btn.html('<i class="fas fa-sync-alt"></i>');
            }
        );
    });

    // ── Live Search ────────────────────────────────────
    $('#searchInput').on('keyup', function () {
        const val = $(this).val().toLowerCase();
        let visible = 0;
        $('#tariffTable tbody tr:not(.empty-row)').each(function () {
            const match = $(this).text().toLowerCase().includes(val);
            $(this).toggle(match);
            if (match) visible++;
        });
        $('#visibleCount').text(visible);
    });

});
</script>
@stop
