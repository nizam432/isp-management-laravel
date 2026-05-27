@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('title', 'Sub Zone Management')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"><i class="fas fa-map-pin mr-2 text-success"></i>Sub Zones</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item"><a href="{{ url('settings/zones') }}">Zones</a></li>
                <li class="breadcrumb-item active">Sub Zones</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">


    {{-- ══════════ SEARCH & FILTER ══════════ --}}
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> <span id="sz-lbl-filter">Search & Filter</span></h3>
            <div class="card-tools">
                <button class="btn btn-xs btn-outline-secondary" id="szBtnLang" title="Toggle Language">
                    <i class="fas fa-language mr-1"></i><span id="sz-lbl-lang-btn">বাংলা</span>
                </button>
            </div>
        </div>
        <div class="card-body pb-2">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="small font-weight-bold" id="sz-lbl-search">Search</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="sz-filter-search" class="form-control" placeholder="Sub zone name...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold" id="sz-lbl-zone-filter">Zone</label>
                    <select id="sz-filter-zone" class="form-control form-control-sm">
                        <option value="" id="sz-opt-all-zone">All Zones</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold" id="sz-lbl-status">Status</label>
                    <select id="sz-filter-status" class="form-control form-control-sm">
                        <option value="" id="sz-opt-all-status">All Status</option>
                        <option value="active" id="sz-opt-active">Active</option>
                        <option value="inactive" id="sz-opt-inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 mt-2">
                    <button class="btn btn-sm btn-primary" id="szBtnSearch">
                        <i class="fas fa-search mr-1"></i><span id="sz-lbl-btn-search">Search</span>
                    </button>
                    <button class="btn btn-sm btn-secondary ml-1" id="szBtnReset">
                        <i class="fas fa-redo mr-1"></i><span id="sz-lbl-btn-reset">Reset</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════ SUB ZONE TABLE ══════════ --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> <span id="sz-lbl-list">Sub Zone List</span></h3>
            <div>
                <button class="btn btn-success btn-sm" id="szBtnAdd">
                    <i class="fas fa-plus mr-1"></i><span id="sz-lbl-add">Add Sub Zone</span>
                </button>
                <span class="badge badge-info ml-2" id="sz-badge-count">0</span>
            </div>
        </div>
        <div class="card-body p-0">
            <table id="subZonesTable" class="table table-bordered table-striped table-hover table-sm mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th id="sz-th-zone">Zone</th>
                        <th id="sz-th-name">Sub Zone Name</th>
                        <th id="sz-th-details">Details</th>
                        <th width="90" class="text-center" id="sz-th-status">Status</th>
                        <th width="100" class="text-center" id="sz-th-action">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>

{{-- ══════════ ADD MODAL ══════════ --}}
<div class="modal fade" id="szAddModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle mr-2"></i><span id="sz-modal-add-title">Add Sub Zone</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label id="sz-lbl-add-zone">Zone <span class="text-danger">*</span></label>
                    <select id="sz_add_zone_id" class="form-control">
                        <option value="">-- Select Zone --</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" id="sz_add_zone_error"></div>
                </div>
                <div class="form-group">
                    <label id="sz-lbl-add-name">Sub Zone Name <span class="text-danger">*</span></label>
                    <input type="text" id="sz_add_name" class="form-control" placeholder="Sub zone name...">
                    <div class="invalid-feedback" id="sz_add_name_error"></div>
                </div>
                <div class="form-group mb-0">
                    <label id="sz-lbl-add-details">Details <small class="text-muted" id="sz-lbl-optional">(optional)</small></label>
                    <textarea id="sz_add_details" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i><span id="sz-lbl-cancel">Cancel</span>
                </button>
                <button type="button" class="btn btn-success btn-sm" id="szBtnSave">
                    <i class="fas fa-save mr-1"></i><span id="sz-lbl-save">Save Sub Zone</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════ EDIT MODAL ══════════ --}}
<div class="modal fade" id="szEditModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit mr-2"></i><span id="sz-modal-edit-title">Edit Sub Zone</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sz_edit_id">
                <div class="form-group">
                    <label id="sz-lbl-edit-zone">Zone <span class="text-danger">*</span></label>
                    <select id="sz_edit_zone_id" class="form-control">
                        <option value="">-- Select Zone --</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label id="sz-lbl-edit-name">Sub Zone Name <span class="text-danger">*</span></label>
                    <input type="text" id="sz_edit_name" class="form-control">
                    <div class="invalid-feedback" id="sz_edit_name_error"></div>
                </div>
                <div class="form-group">
                    <label id="sz-lbl-edit-details">Details <small class="text-muted">(optional)</small></label>
                    <textarea id="sz_edit_details" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group mb-0">
                    <label id="sz-lbl-edit-status">Status</label>
                    <select id="sz_edit_active" class="form-control">
                        <option value="1" id="sz-opt-edit-active">Active</option>
                        <option value="0" id="sz-opt-edit-inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i><span id="sz-lbl-cancel2">Cancel</span>
                </button>
                <button type="button" class="btn btn-info btn-sm" id="szBtnUpdate">
                    <i class="fas fa-save mr-1"></i><span id="sz-lbl-update">Update Sub Zone</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .small-box:hover { opacity: 0.9; }
    #subZonesTable thead th { font-size: 13px; }
    .btn-xs { padding: 2px 7px; font-size: 12px; }
</style>
@endsection

@section('js')
<script>
$(function () {

    // ══════════════════════════════════════════════
    // Toastr Global Config
    // ══════════════════════════════════════════════
    toastr.options = {
        closeButton:       true,
        progressBar:       true,
        positionClass:     'toast-top-right',
        timeOut:           3500,
        extendedTimeOut:   1000,
        showMethod:        'fadeIn',
        hideMethod:        'fadeOut',
        preventDuplicates: true,
    };

    // ══════════════════════════════════════════════
    // Language System (default: English)
    // ══════════════════════════════════════════════
    var currentLang = 'en';

    var lang = {
        en: {
            'sz-lbl-total'        : 'Total Sub Zones',
            'sz-lbl-active'       : 'Active',
            'sz-lbl-inactive'     : 'Inactive',
            'sz-lbl-zones'        : 'Total Zones',
            'sz-lbl-filter'       : 'Search & Filter',
            'sz-lbl-search'       : 'Search',
            'sz-lbl-zone-filter'  : 'Zone',
            'sz-lbl-status'       : 'Status',
            'sz-lbl-btn-search'   : 'Search',
            'sz-lbl-btn-reset'    : 'Reset',
            'sz-lbl-list'         : 'Sub Zone List',
            'sz-lbl-add'          : 'Add Sub Zone',
            'sz-lbl-lang-btn'     : 'বাংলা',
            'sz-opt-all-zone'     : 'All Zones',
            'sz-opt-all-status'   : 'All Status',
            'sz-opt-active'       : 'Active',
            'sz-opt-inactive'     : 'Inactive',
            'sz-modal-add-title'  : 'Add Sub Zone',
            'sz-lbl-add-zone'     : 'Zone',
            'sz-lbl-add-name'     : 'Sub Zone Name',
            'sz-lbl-add-details'  : 'Details',
            'sz-lbl-optional'     : '(optional)',
            'sz-lbl-cancel'       : 'Cancel',
            'sz-lbl-save'         : 'Save Sub Zone',
            'sz-modal-edit-title' : 'Edit Sub Zone',
            'sz-lbl-edit-zone'    : 'Zone',
            'sz-lbl-edit-name'    : 'Sub Zone Name',
            'sz-lbl-edit-details' : 'Details',
            'sz-lbl-edit-status'  : 'Status',
            'sz-lbl-cancel2'      : 'Cancel',
            'sz-lbl-update'       : 'Update Sub Zone',
            'sz-opt-edit-active'  : 'Active',
            'sz-opt-edit-inactive': 'Inactive',
            'sz-th-zone'          : 'Zone',
            'sz-th-name'          : 'Sub Zone Name',
            'sz-th-details'       : 'Details',
            'sz-th-status'        : 'Status',
            'sz-th-action'        : 'Action',
            'msg-saved'           : 'Sub Zone added successfully.',
            'msg-updated'         : 'Sub Zone updated successfully.',
            'msg-deleted'         : 'Sub Zone deleted.',
            'msg-save-fail'       : 'Failed to save.',
            'msg-update-fail'     : 'Failed to update.',
            'msg-delete-fail'     : 'Failed to delete.',
            'msg-empty'           : 'No sub zones found.',
            'msg-delete-title'    : 'Delete Sub Zone?',
            'msg-delete-text'     : 'This cannot be undone.',
            'msg-delete-yes'      : 'Yes, Delete!',
            'msg-cancel'          : 'Cancel',
            'saving'              : 'Saving...',
            'updating'            : 'Updating...',
        },
        bn: {
            'sz-lbl-total'        : 'মোট Sub Zone',
            'sz-lbl-active'       : 'সক্রিয়',
            'sz-lbl-inactive'     : 'নিষ্ক্রিয়',
            'sz-lbl-zones'        : 'মোট Zone',
            'sz-lbl-filter'       : 'খুঁজুন ও ফিল্টার করুন',
            'sz-lbl-search'       : 'খুঁজুন',
            'sz-lbl-zone-filter'  : 'Zone',
            'sz-lbl-status'       : 'স্ট্যাটাস',
            'sz-lbl-btn-search'   : 'খুঁজুন',
            'sz-lbl-btn-reset'    : 'রিসেট',
            'sz-lbl-list'         : 'Sub Zone তালিকা',
            'sz-lbl-add'          : 'Sub Zone যোগ করুন',
            'sz-lbl-lang-btn'     : 'English',
            'sz-opt-all-zone'     : 'সব Zone',
            'sz-opt-all-status'   : 'সব স্ট্যাটাস',
            'sz-opt-active'       : 'সক্রিয়',
            'sz-opt-inactive'     : 'নিষ্ক্রিয়',
            'sz-modal-add-title'  : 'Sub Zone যোগ করুন',
            'sz-lbl-add-zone'     : 'Zone',
            'sz-lbl-add-name'     : 'Sub Zone এর নাম',
            'sz-lbl-add-details'  : 'বিবরণ',
            'sz-lbl-optional'     : '(ঐচ্ছিক)',
            'sz-lbl-cancel'       : 'বাতিল',
            'sz-lbl-save'         : 'সংরক্ষণ করুন',
            'sz-modal-edit-title' : 'Sub Zone সম্পাদনা',
            'sz-lbl-edit-zone'    : 'Zone',
            'sz-lbl-edit-name'    : 'Sub Zone এর নাম',
            'sz-lbl-edit-details' : 'বিবরণ',
            'sz-lbl-edit-status'  : 'স্ট্যাটাস',
            'sz-lbl-cancel2'      : 'বাতিল',
            'sz-lbl-update'       : 'আপডেট করুন',
            'sz-opt-edit-active'  : 'সক্রিয়',
            'sz-opt-edit-inactive': 'নিষ্ক্রিয়',
            'sz-th-zone'          : 'Zone',
            'sz-th-name'          : 'Sub Zone নাম',
            'sz-th-details'       : 'বিবরণ',
            'sz-th-status'        : 'স্ট্যাটাস',
            'sz-th-action'        : 'অ্যাকশন',
            'msg-saved'           : 'Sub Zone সফলভাবে যোগ হয়েছে।',
            'msg-updated'         : 'Sub Zone আপডেট হয়েছে।',
            'msg-deleted'         : 'Sub Zone মুছে ফেলা হয়েছে।',
            'msg-save-fail'       : 'সংরক্ষণ ব্যর্থ হয়েছে।',
            'msg-update-fail'     : 'আপডেট ব্যর্থ হয়েছে।',
            'msg-delete-fail'     : 'মুছতে ব্যর্থ হয়েছে।',
            'msg-empty'           : 'কোনো Sub Zone পাওয়া যায়নি।',
            'msg-delete-title'    : 'Sub Zone মুছবেন?',
            'msg-delete-text'     : 'মুছে ফেললে ফিরিয়ে আনা যাবে না।',
            'msg-delete-yes'      : 'হ্যাঁ, মুছুন!',
            'msg-cancel'          : 'বাতিল',
            'saving'              : 'সংরক্ষণ হচ্ছে...',
            'updating'            : 'আপডেট হচ্ছে...',
        }
    };

    function t(key) { return lang[currentLang][key] || key; }

    function szApplyLang() {
        Object.keys(lang[currentLang]).forEach(function(key) {
            var el = document.getElementById(key);
            if (el && el.tagName !== 'INPUT' && el.tagName !== 'SELECT') {
                el.textContent = lang[currentLang][key];
            }
        });
        if (szTable) {
            szTable.settings()[0].oLanguage.sEmptyTable  = t('msg-empty');
            szTable.settings()[0].oLanguage.sZeroRecords = t('msg-empty');
            szTable.draw();
        }
    }

    $('#szBtnLang').on('click', function() {
        currentLang = currentLang === 'en' ? 'bn' : 'en';
        szApplyLang();
    });

    // ══════════════════════════════════════════════
    // DataTable
    // ══════════════════════════════════════════════
    var szTable = $('#subZonesTable').DataTable({
        processing: true,
        ajax: {
            url: '{{ route("settings.sub-zones.data") }}',
            dataSrc: function(json) {
                var data     = json.data;
                var total    = data.length;
                var active   = data.filter(r => r.is_active == 1).length;
                var inactive = total - active;

                $('#sz-stat-total').text(total);
                $('#sz-stat-active').text(active);
                $('#sz-stat-inactive').text(inactive);
                $('#sz-badge-count').text(total);

                return data;
            }
        },
        columns: [
            { data: 'DT_RowIndex', className: 'text-center', orderable: false },
            { data: 'zone_name', defaultContent: '—' },
            { data: 'name' },
            {
                data: 'details',
                render: function(val) {
                    return val ? val : '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'is_active',
                className: 'text-center',
                orderable: false,
                render: function(val) {
                    return val == 1
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-secondary">Inactive</span>';
                }
            },
            {
                data: 'id',
                className: 'text-center',
                orderable: false,
                render: function(id, type, row) {
                    return `
                        <button class="btn btn-xs btn-info sz-btn-edit mr-1"
                            data-id="${row.id}"
                            data-zone_id="${row.zone_id}"
                            data-name="${row.name}"
                            data-details="${row.details || ''}"
                            data-active="${row.is_active}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-xs btn-danger sz-btn-delete" data-id="${row.id}">
                            <i class="fas fa-trash"></i>
                        </button>`;
                }
            },
        ],
        order: [[2, 'asc']],
        pageLength: 25,
        language: {
            emptyTable:  'No sub zones found.',
            zeroRecords: 'No sub zones found.',
            processing:  '<i class="fas fa-spinner fa-spin"></i> Loading...',
            lengthMenu:  'Show _MENU_',
            info:        '_START_ – _END_ of _TOTAL_',
            paginate:    { previous: '«', next: '»' }
        },
        dom: 'rt<"row mt-2 px-2"<"col-sm-6"i><"col-sm-6"p>>',
    });

    // ── Filter by stat box ───────────────────────
    window.szFilterByStatus = function(status) {
        $('#sz-filter-status').val(status);
        szApplyFilter();
    };

    // ── Search & Filter ──────────────────────────
    function szApplyFilter() {
        var search = $('#sz-filter-search').val().trim().toLowerCase();
        var zone   = $('#sz-filter-zone').val();
        var status = $('#sz-filter-status').val();

        $.fn.dataTable.ext.search = [];
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData) {
            var nameOk   = !search || (rowData.name || '').toLowerCase().includes(search);
            var zoneOk   = !zone   || rowData.zone_id == zone;
            var statusOk = !status
                || (status === 'active'   && rowData.is_active == 1)
                || (status === 'inactive' && rowData.is_active == 0);
            return nameOk && zoneOk && statusOk;
        });

        szTable.draw();
    }

    $('#szBtnSearch').on('click', szApplyFilter);
    $('#sz-filter-search').on('keypress', function(e) { if (e.which === 13) szApplyFilter(); });
    $('#szBtnReset').on('click', function() {
        $('#sz-filter-search').val('');
        $('#sz-filter-zone').val('');
        $('#sz-filter-status').val('');
        $.fn.dataTable.ext.search = [];
        szTable.draw();
    });

    // ══════════════════════════════════════════════
    // ADD
    // ══════════════════════════════════════════════
    $('#szBtnAdd').on('click', function() {
        $('#sz_add_zone_id, #sz_add_name').val('').removeClass('is-invalid');
        $('#sz_add_details').val('');
        $('#sz_add_zone_error, #sz_add_name_error').text('');
        $('#szAddModal').modal('show');
    });

    $('#szBtnSave').on('click', function() {
        $('#sz_add_zone_id, #sz_add_name').removeClass('is-invalid');
        $('#sz_add_zone_error, #sz_add_name_error').text('');

        $('#szBtnSave').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i>' + t('saving'));

        $.ajax({
            url:    '{{ route("settings.sub-zones.store") }}',
            method: 'POST',
            data: {
                _token:  '{{ csrf_token() }}',
                zone_id: $('#sz_add_zone_id').val(),
                name:    $('#sz_add_name').val(),
                details: $('#sz_add_details').val(),
            },
            success: function(res) {
                if (res.success) {
                    $('#szAddModal').modal('hide');
                    szTable.ajax.reload(null, false);
                    toastr.success(t('msg-saved'));
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                if (errors.zone_id) { $('#sz_add_zone_id').addClass('is-invalid'); $('#sz_add_zone_error').text(errors.zone_id[0]); }
                if (errors.name)    { $('#sz_add_name').addClass('is-invalid');    $('#sz_add_name_error').text(errors.name[0]); }
                toastr.error(xhr.responseJSON?.message || t('msg-save-fail'));
            },
            complete: function() {
                $('#szBtnSave').prop('disabled', false)
                    .html('<i class="fas fa-save mr-1"></i>' + t('sz-lbl-save'));
            }
        });
    });

    // ══════════════════════════════════════════════
    // EDIT
    // ══════════════════════════════════════════════
    $(document).on('click', '.sz-btn-edit', function() {
        var d = $(this).data();
        $('#sz_edit_id').val(d.id);
        $('#sz_edit_zone_id').val(d.zone_id);
        $('#sz_edit_name').val(d.name).removeClass('is-invalid');
        $('#sz_edit_details').val(d.details);
        $('#sz_edit_active').val(d.active == 1 || d.active === true ? '1' : '0');
        $('#sz_edit_name_error').text('');
        $('#szEditModal').modal('show');
    });

    $('#szBtnUpdate').on('click', function() {
        $('#sz_edit_name').removeClass('is-invalid');
        $('#sz_edit_name_error').text('');

        $('#szBtnUpdate').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i>' + t('updating'));

        $.ajax({
            url:    '/settings/sub-zones/' + $('#sz_edit_id').val(),
            method: 'PUT',
            data: {
                _token:    '{{ csrf_token() }}',
                zone_id:   $('#sz_edit_zone_id').val(),
                name:      $('#sz_edit_name').val(),
                details:   $('#sz_edit_details').val(),
                is_active: $('#sz_edit_active').val(),
            },
            success: function(res) {
                if (res.success) {
                    $('#szEditModal').modal('hide');
                    szTable.ajax.reload(null, false);
                    toastr.success(t('msg-updated'));
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                if (errors.name) { $('#sz_edit_name').addClass('is-invalid'); $('#sz_edit_name_error').text(errors.name[0]); }
                toastr.error(xhr.responseJSON?.message || t('msg-update-fail'));
            },
            complete: function() {
                $('#szBtnUpdate').prop('disabled', false)
                    .html('<i class="fas fa-save mr-1"></i>' + t('sz-lbl-update'));
            }
        });
    });

    // ══════════════════════════════════════════════
    // DELETE
    // ══════════════════════════════════════════════
    $(document).on('click', '.sz-btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title:              t('msg-delete-title'),
            text:               t('msg-delete-text'),
            icon:               'warning',
            showCancelButton:   true,
            confirmButtonColor: '#d33',
            cancelButtonColor:  '#6c757d',
            confirmButtonText:  t('msg-delete-yes'),
            cancelButtonText:   t('msg-cancel'),
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url:    '/settings/sub-zones/' + id,
                    method: 'DELETE',
                    data:   { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        szTable.ajax.reload(null, false);
                        toastr.success(res.message || t('msg-deleted'));
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || t('msg-delete-fail'));
                    }
                });
            }
        });
    });

    // Apply default lang on load
    szApplyLang();

});
</script>
@endsection