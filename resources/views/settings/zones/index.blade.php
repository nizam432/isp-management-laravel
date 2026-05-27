@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('title', 'Zone Management')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"><i class="fas fa-map-marked-alt mr-2 text-primary"></i>Zones</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Zones</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">

 
    {{-- ══════════ SEARCH & FILTER ══════════ --}}
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> <span id="lbl-filter">Search & Filter</span></h3>
            {{-- Language Toggle --}}
            <div class="card-tools">
                <button class="btn btn-xs btn-outline-secondary" id="btnLangToggle" title="Toggle Language">
                    <i class="fas fa-language mr-1"></i><span id="lbl-lang-btn">বাংলা</span>
                </button>
            </div>
        </div>
        <div class="card-body pb-2">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="small font-weight-bold" id="lbl-search">Search</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="filter-search" class="form-control" placeholder="Zone name...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold" id="lbl-status">Status</label>
                    <select id="filter-status" class="form-control form-control-sm">
                        <option value="" id="opt-all-status">All Status</option>
                        <option value="active" id="opt-active">Active</option>
                        <option value="inactive" id="opt-inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-5 mt-2">
                    <button class="btn btn-sm btn-primary" id="btnSearch">
                        <i class="fas fa-search mr-1"></i><span id="lbl-btn-search">Search</span>
                    </button>
                    <button class="btn btn-sm btn-secondary ml-1" id="btnReset">
                        <i class="fas fa-redo mr-1"></i><span id="lbl-btn-reset">Reset</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════ ZONE TABLE ══════════ --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> <span id="lbl-zone-list">Zone List</span></h3>
            <div>
                <button class="btn btn-primary btn-sm" id="btnAddZone">
                    <i class="fas fa-plus mr-1"></i><span id="lbl-add-zone">Add Zone</span>
                </button>
                <span class="badge badge-info ml-2" id="badge-count">0</span>
            </div>
        </div>
        <div class="card-body p-0">
            <table id="zonesTable" class="table table-bordered table-striped table-hover table-sm mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th id="th-name">Zone Name</th>
                        <th id="th-details">Details</th>
                        <th width="100" class="text-center" id="th-subzones">Sub Zones</th>
                        <th width="90" class="text-center" id="th-status">Status</th>
                        <th width="100" class="text-center" id="th-action">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>

{{-- ══════════ ADD MODAL ══════════ --}}
<div class="modal fade" id="addZoneModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle mr-2"></i><span id="modal-add-title">Add Zone</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label id="lbl-add-name">Zone Name <span class="text-danger">*</span></label>
                    <input type="text" id="add_name" class="form-control" id="lbl-add-name-ph">
                    <div class="invalid-feedback" id="add_name_error"></div>
                </div>
                <div class="form-group mb-0">
                    <label id="lbl-add-details">Details <small class="text-muted" id="lbl-optional">(optional)</small></label>
                    <textarea id="add_details" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i><span id="lbl-cancel">Cancel</span>
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveZone">
                    <i class="fas fa-save mr-1"></i><span id="lbl-save">Save Zone</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════ EDIT MODAL ══════════ --}}
<div class="modal fade" id="editZoneModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit mr-2"></i><span id="modal-edit-title">Edit Zone</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <div class="form-group">
                    <label id="lbl-edit-name">Zone Name <span class="text-danger">*</span></label>
                    <input type="text" id="edit_name" class="form-control">
                    <div class="invalid-feedback" id="edit_name_error"></div>
                </div>
                <div class="form-group">
                    <label id="lbl-edit-details">Details <small class="text-muted">(optional)</small></label>
                    <textarea id="edit_details" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group mb-0">
                    <label id="lbl-edit-status">Status</label>
                    <select id="edit_active" class="form-control">
                        <option value="1" id="opt-edit-active">Active</option>
                        <option value="0" id="opt-edit-inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i><span id="lbl-cancel2">Cancel</span>
                </button>
                <button type="button" class="btn btn-info btn-sm" id="btnUpdateZone">
                    <i class="fas fa-save mr-1"></i><span id="lbl-update">Update Zone</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .small-box:hover { opacity: 0.9; }
    #zonesTable thead th { font-size: 13px; }
    .btn-xs { padding: 2px 7px; font-size: 12px; }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 3px 8px !important;
    }
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
            'lbl-total'        : 'Total Zones',
            'lbl-active'       : 'Active',
            'lbl-inactive'     : 'Inactive',
            'lbl-subzones'     : 'Total Sub Zones',
            'lbl-filter'       : 'Search & Filter',
            'lbl-search'       : 'Search',
            'lbl-status'       : 'Status',
            'lbl-btn-search'   : 'Search',
            'lbl-btn-reset'    : 'Reset',
            'lbl-zone-list'    : 'Zone List',
            'lbl-add-zone'     : 'Add Zone',
            'lbl-lang-btn'     : 'বাংলা',
            'opt-all-status'   : 'All Status',
            'opt-active'       : 'Active',
            'opt-inactive'     : 'Inactive',
            'modal-add-title'  : 'Add Zone',
            'lbl-add-name'     : 'Zone Name',
            'lbl-add-details'  : 'Details',
            'lbl-optional'     : '(optional)',
            'lbl-cancel'       : 'Cancel',
            'lbl-save'         : 'Save Zone',
            'modal-edit-title' : 'Edit Zone',
            'lbl-edit-name'    : 'Zone Name',
            'lbl-edit-details' : 'Details',
            'lbl-edit-status'  : 'Status',
            'lbl-cancel2'      : 'Cancel',
            'lbl-update'       : 'Update Zone',
            'opt-edit-active'  : 'Active',
            'opt-edit-inactive': 'Inactive',
            'th-name'          : 'Zone Name',
            'th-details'       : 'Details',
            'th-subzones'      : 'Sub Zones',
            'th-status'        : 'Status',
            'th-action'        : 'Action',
            // messages
            'msg-saved'        : 'Zone added successfully.',
            'msg-updated'      : 'Zone updated successfully.',
            'msg-deleted'      : 'Zone deleted.',
            'msg-save-fail'    : 'Failed to save.',
            'msg-update-fail'  : 'Failed to update.',
            'msg-delete-fail'  : 'Failed to delete.',
            'msg-empty'        : 'No zones found.',
            'msg-delete-title' : 'Delete Zone?',
            'msg-delete-text'  : 'This cannot be undone.',
            'msg-delete-yes'   : 'Yes, Delete!',
            'msg-cancel'       : 'Cancel',
            'saving'           : 'Saving...',
            'updating'         : 'Updating...',
            'filter-ph'        : 'Zone name...',
        },
        bn: {
            'lbl-total'        : 'মোট Zone',
            'lbl-active'       : 'Active',
            'lbl-inactive'     : 'Inactive',
            'lbl-subzones'     : 'মোট Sub Zone',
            'lbl-filter'       : 'খুঁজুন ও ফিল্টার করুন',
            'lbl-search'       : 'খুঁজুন',
            'lbl-status'       : 'স্ট্যাটাস',
            'lbl-btn-search'   : 'খুঁজুন',
            'lbl-btn-reset'    : 'রিসেট',
            'lbl-zone-list'    : 'Zone তালিকা',
            'lbl-add-zone'     : 'Zone যোগ করুন',
            'lbl-lang-btn'     : 'English',
            'opt-all-status'   : 'সব স্ট্যাটাস',
            'opt-active'       : 'সক্রিয়',
            'opt-inactive'     : 'নিষ্ক্রিয়',
            'modal-add-title'  : 'Zone যোগ করুন',
            'lbl-add-name'     : 'Zone এর নাম',
            'lbl-add-details'  : 'বিবরণ',
            'lbl-optional'     : '(ঐচ্ছিক)',
            'lbl-cancel'       : 'বাতিল',
            'lbl-save'         : 'সংরক্ষণ করুন',
            'modal-edit-title' : 'Zone সম্পাদনা',
            'lbl-edit-name'    : 'Zone এর নাম',
            'lbl-edit-details' : 'বিবরণ',
            'lbl-edit-status'  : 'স্ট্যাটাস',
            'lbl-cancel2'      : 'বাতিল',
            'lbl-update'       : 'আপডেট করুন',
            'opt-edit-active'  : 'সক্রিয়',
            'opt-edit-inactive': 'নিষ্ক্রিয়',
            'th-name'          : 'Zone নাম',
            'th-details'       : 'বিবরণ',
            'th-subzones'      : 'Sub Zone',
            'th-status'        : 'স্ট্যাটাস',
            'th-action'        : 'অ্যাকশন',
            // messages
            'msg-saved'        : 'Zone সফলভাবে যোগ হয়েছে।',
            'msg-updated'      : 'Zone আপডেট হয়েছে।',
            'msg-deleted'      : 'Zone মুছে ফেলা হয়েছে।',
            'msg-save-fail'    : 'সংরক্ষণ ব্যর্থ হয়েছে।',
            'msg-update-fail'  : 'আপডেট ব্যর্থ হয়েছে।',
            'msg-delete-fail'  : 'মুছতে ব্যর্থ হয়েছে।',
            'msg-empty'        : 'কোনো Zone পাওয়া যায়নি।',
            'msg-delete-title' : 'Zone মুছবেন?',
            'msg-delete-text'  : 'মুছে ফেললে ফিরিয়ে আনা যাবে না।',
            'msg-delete-yes'   : 'হ্যাঁ, মুছুন!',
            'msg-cancel'       : 'বাতিল',
            'saving'           : 'সংরক্ষণ হচ্ছে...',
            'updating'         : 'আপডেট হচ্ছে...',
            'filter-ph'        : 'Zone এর নাম...',
        }
    };

    function t(key) {
        return lang[currentLang][key] || key;
    }

    function applyLang() {
        // Update all text elements
        Object.keys(lang[currentLang]).forEach(function(key) {
            var el = document.getElementById(key);
            if (el && el.tagName !== 'INPUT') {
                el.textContent = lang[currentLang][key];
            }
        });

        // Placeholder
        $('#filter-search').attr('placeholder', t('filter-ph'));
        $('#add_name').attr('placeholder', t('filter-ph'));

        // DataTable empty text
        if (table) {
            table.settings()[0].oLanguage.sEmptyTable = t('msg-empty');
            table.settings()[0].oLanguage.sZeroRecords = t('msg-empty');
            table.draw();
        }
    }

    $('#btnLangToggle').on('click', function () {
        currentLang = currentLang === 'en' ? 'bn' : 'en';
        applyLang();
    });

    // ══════════════════════════════════════════════
    // DataTable
    // ══════════════════════════════════════════════
    var table = $('#zonesTable').DataTable({
        processing: true,
        ajax: {
            url: '{{ route("settings.zones.data") }}',
            dataSrc: function (json) {
                var data     = json.data;
                var total    = data.length;
                var active   = data.filter(r => r.is_active == 1).length;
                var inactive = total - active;

                $('#stat-total').text(total);
                $('#stat-active').text(active);
                $('#stat-inactive').text(inactive);
                $('#stat-subzones').text(json.total_subzones || 0);
                $('#badge-count').text(total);

                return data;
            }
        },
        columns: [
            { data: 'DT_RowIndex', className: 'text-center', orderable: false },
            { data: 'name' },
            {
                data: 'details',
                render: function(val) {
                    return val ? val : '<span class="text-muted">—</span>';
                }
            },
            { data: 'sub_zones_count', className: 'text-center' },
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
                        <button class="btn btn-xs btn-info btn-edit mr-1"
                            data-id="${row.id}"
                            data-name="${row.name}"
                            data-details="${row.details || ''}"
                            data-active="${row.is_active}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-xs btn-danger btn-delete" data-id="${row.id}">
                            <i class="fas fa-trash"></i>
                        </button>`;
                }
            },
        ],
        order: [[1, 'asc']],
        pageLength: 25,
        language: {
            emptyTable:  'No zones found.',
            zeroRecords: 'No zones found.',
            processing:  '<i class="fas fa-spinner fa-spin"></i> Loading...',
            lengthMenu:  'Show _MENU_',
            info:        '_START_ – _END_ of _TOTAL_',
            paginate:    { previous: '«', next: '»' }
        },
        dom: 'rt<"row mt-2 px-2"<"col-sm-6"i><"col-sm-6"p>>',
    });

    // ── Filter by stat box click ────────────────
    window.filterByStatus = function(status) {
        $('#filter-status').val(status);
        applyFilter();
    };

    // ── Search & Filter ──────────────────────────
    function applyFilter() {
        var search = $('#filter-search').val().trim().toLowerCase();
        var status = $('#filter-status').val();

        $.fn.dataTable.ext.search = [];
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData) {
            var nameOk   = !search || (rowData.name || '').toLowerCase().includes(search);
            var statusOk = !status
                || (status === 'active'   && rowData.is_active == 1)
                || (status === 'inactive' && rowData.is_active == 0);
            return nameOk && statusOk;
        });

        table.draw();
    }

    $('#btnSearch').on('click', applyFilter);
    $('#filter-search').on('keypress', function(e) { if (e.which === 13) applyFilter(); });
    $('#btnReset').on('click', function() {
        $('#filter-search').val('');
        $('#filter-status').val('');
        $.fn.dataTable.ext.search = [];
        table.draw();
    });

    // ══════════════════════════════════════════════
    // ADD Modal
    // ══════════════════════════════════════════════
    $('#btnAddZone').on('click', function() {
        $('#add_name').val('').removeClass('is-invalid');
        $('#add_details').val('');
        $('#add_name_error').text('');
        $('#addZoneModal').modal('show');
    });

    $('#btnSaveZone').on('click', function() {
        $('#add_name').removeClass('is-invalid');
        $('#add_name_error').text('');

        $('#btnSaveZone').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i>' + t('saving'));

        $.ajax({
            url:    '{{ route("settings.zones.store") }}',
            method: 'POST',
            data: {
                _token:  '{{ csrf_token() }}',
                name:    $('#add_name').val(),
                details: $('#add_details').val(),
            },
            success: function(res) {
                if (res.success) {
                    $('#addZoneModal').modal('hide');
                    table.ajax.reload(null, false);
                    toastr.success(t('msg-saved'));
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                if (errors.name) {
                    $('#add_name').addClass('is-invalid');
                    $('#add_name_error').text(errors.name[0]);
                }
                toastr.error(xhr.responseJSON?.message || t('msg-save-fail'));
            },
            complete: function() {
                $('#btnSaveZone').prop('disabled', false)
                    .html('<i class="fas fa-save mr-1"></i>' + t('lbl-save'));
            }
        });
    });

    // ══════════════════════════════════════════════
    // EDIT Modal
    // ══════════════════════════════════════════════
    $(document).on('click', '.btn-edit', function() {
        var d = $(this).data();
        $('#edit_id').val(d.id);
        $('#edit_name').val(d.name).removeClass('is-invalid');
        $('#edit_details').val(d.details);
        $('#edit_active').val(d.active == 1 || d.active === true ? '1' : '0');
        $('#edit_name_error').text('');
        $('#editZoneModal').modal('show');
    });

    $('#btnUpdateZone').on('click', function() {
        $('#edit_name').removeClass('is-invalid');
        $('#edit_name_error').text('');

        $('#btnUpdateZone').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i>' + t('updating'));

        $.ajax({
            url:    '/settings/zones/' + $('#edit_id').val(),
            method: 'PUT',
            data: {
                _token:    '{{ csrf_token() }}',
                name:      $('#edit_name').val(),
                details:   $('#edit_details').val(),
                is_active: $('#edit_active').val(),
            },
            success: function(res) {
                if (res.success) {
                    $('#editZoneModal').modal('hide');
                    table.ajax.reload(null, false);
                    toastr.success(t('msg-updated'));
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                if (errors.name) {
                    $('#edit_name').addClass('is-invalid');
                    $('#edit_name_error').text(errors.name[0]);
                }
                toastr.error(xhr.responseJSON?.message || t('msg-update-fail'));
            },
            complete: function() {
                $('#btnUpdateZone').prop('disabled', false)
                    .html('<i class="fas fa-save mr-1"></i>' + t('lbl-update'));
            }
        });
    });

    // ══════════════════════════════════════════════
    // DELETE
    // ══════════════════════════════════════════════
    $(document).on('click', '.btn-delete', function() {
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
                    url:    '/settings/zones/' + id,
                    method: 'DELETE',
                    data:   { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        table.ajax.reload(null, false);
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
    applyLang();

});
</script>
@endsection