@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('title', 'Connection Type')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"><i class="fas fa-plug mr-2 text-primary"></i>Connection Type</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Connection Type</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">


    {{-- ══════ SEARCH & FILTER ══════ --}}
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Search & Filter</h3>
        </div>
        <div class="card-body pb-2">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <label class="small font-weight-bold">Search</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="filter-search" class="form-control" placeholder="Connection type name...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold">Status</label>
                    <select id="filter-status" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 mt-2">
                    <button class="btn btn-sm btn-primary" id="btnSearch">
                        <i class="fas fa-search mr-1"></i>Search
                    </button>
                    <button class="btn btn-sm btn-secondary ml-1" id="btnReset">
                        <i class="fas fa-redo mr-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════ TABLE ══════ --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> Connection Type List</h3>
            <div>
                <button class="btn btn-primary btn-sm" id="btnAdd">
                    <i class="fas fa-plus mr-1"></i> Connection Type
                </button>
                <span class="badge badge-info ml-2" id="badge-count">0</span>
            </div>
        </div>
        <div class="card-body p-0">
            <table id="ctTable" class="table table-bordered table-striped table-hover table-sm mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th width="60" class="text-center">Serial</th>
                        <th>Connection Type</th>
                        <th>Details</th>
                        <th width="90" class="text-center">Status</th>
                        <th width="120" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>

{{-- ══════ ADD MODAL ══════ --}}
<div class="modal fade" id="addModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle mr-2"></i>Add Connection Type</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Connection Type Name <span class="text-danger">*</span></label>
                    <input type="text" id="add_name" class="form-control" placeholder="e.g. Fiber, Wireless...">
                    <div class="invalid-feedback" id="add_name_error"></div>
                </div>
                <div class="form-group mb-0">
                    <label>Details <small class="text-muted">(optional)</small></label>
                    <textarea id="add_details" class="form-control" rows="3" placeholder="Description..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSave">
                    <i class="fas fa-save mr-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════ EDIT MODAL ══════ --}}
<div class="modal fade" id="editModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white"><i class="fas fa-edit mr-2"></i>Edit Connection Type</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <div class="form-group">
                    <label>Connection Type Name <span class="text-danger">*</span></label>
                    <input type="text" id="edit_name" class="form-control">
                    <div class="invalid-feedback" id="edit_name_error"></div>
                </div>
                <div class="form-group">
                    <label>Details <small class="text-muted">(optional)</small></label>
                    <textarea id="edit_details" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group mb-0">
                    <label>Status</label>
                    <select id="edit_active" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-info btn-sm" id="btnUpdate">
                    <i class="fas fa-save mr-1"></i>Update
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .btn-xs { padding: 2px 7px; font-size: 12px; }
    .toggle-switch { cursor: pointer; font-size: 22px; line-height: 1; }
    .toggle-switch .on  { color: #28a745; }
    .toggle-switch .off { color: #adb5bd; }
    #ctTable thead th { font-size: 13px; }
</style>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function () {

    toastr.options = {
        closeButton: true, progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3500, preventDuplicates: true,
    };

    // ── DataTable ──────────────────────────────────────────
    var table = $('#ctTable').DataTable({
        processing: true,
        ajax: {
            url: '{{ route("settings.connection-types.data") }}',
            dataSrc: function (json) {
                var data     = json.data;
                var total    = data.length;
                var active   = data.filter(r => r.is_active == 1).length;
                var customers = data.reduce((s, r) => s + (parseInt(r.customers_count) || 0), 0);

                $('#stat-total').text(total);
                $('#stat-active').text(active);
                $('#stat-inactive').text(total - active);
                $('#stat-customers').text(customers);
                $('#badge-count').text(total);

                return data;
            }
        },
        columns: [
            { data: 'DT_RowIndex', className: 'text-center', orderable: false },
            { data: 'name' },
            {
                data: 'details',
                render: v => v ? v : '<span class="text-muted">—</span>'
            },
            {
                data: 'is_active',
                className: 'text-center',
                orderable: false,
                render: function(val, type, row) {
                    var icon = val == 1
                        ? '<i class="fas fa-toggle-on on"></i>'
                        : '<i class="fas fa-toggle-off off"></i>';
                    return `<span class="toggle-switch btn-toggle" data-id="${row.id}" data-active="${val}">${icon}</span>`;
                }
            },
            {
                data: 'id',
                className: 'text-center',
                orderable: false,
                render: function(id, type, row) {
                    return `
                        <button class="btn btn-xs btn-success btn-edit mr-1"
                            data-id="${row.id}"
                            data-name="${row.name}"
                            data-details="${row.details || ''}"
                            data-active="${row.is_active}"
                            title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-xs btn-danger btn-delete" data-id="${row.id}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>`;
                }
            },
        ],
        order: [[1, 'asc']],
        pageLength: 10,
        language: {
            emptyTable:  'No connection types found.',
            zeroRecords: 'No connection types found.',
            processing:  '<i class="fas fa-spinner fa-spin"></i> Loading...',
            info:        'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate:    { previous: 'Previous', next: 'Next' }
        },
        dom: '<"row mb-2"<"col-sm-4"l><"col-sm-8 text-right"f>>rt<"row mt-2 px-2"<"col-sm-6"i><"col-sm-6"p>>',
    });

    // ── Stat filter ────────────────────────────────────────
    window.filterStatus = function(status) {
        $('#filter-status').val(status);
        applyFilter();
    };

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

    // ── Add ────────────────────────────────────────────────
    $('#btnAdd').on('click', function() {
        $('#add_name').val('').removeClass('is-invalid');
        $('#add_details').val('');
        $('#add_name_error').text('');
        $('#addModal').modal('show');
    });

    $('#btnSave').on('click', function() {
        $('#add_name').removeClass('is-invalid');
        $('#btnSave').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');

        $.ajax({
            url:    '{{ route("settings.connection-types.store") }}',
            method: 'POST',
            data: {
                _token:  '{{ csrf_token() }}',
                name:    $('#add_name').val(),
                details: $('#add_details').val(),
            },
            success: function(res) {
                if (res.success) {
                    $('#addModal').modal('hide');
                    table.ajax.reload(null, false);
                    toastr.success(res.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                if (errors.name) { $('#add_name').addClass('is-invalid'); $('#add_name_error').text(errors.name[0]); }
                toastr.error(xhr.responseJSON?.message || 'Failed to save.');
            },
            complete: function() {
                $('#btnSave').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save');
            }
        });
    });

    // ── Edit ───────────────────────────────────────────────
    $(document).on('click', '.btn-edit', function() {
        var d = $(this).data();
        $('#edit_id').val(d.id);
        $('#edit_name').val(d.name).removeClass('is-invalid');
        $('#edit_details').val(d.details);
        $('#edit_active').val(d.active == 1 ? '1' : '0');
        $('#edit_name_error').text('');
        $('#editModal').modal('show');
    });

    $('#btnUpdate').on('click', function() {
        $('#edit_name').removeClass('is-invalid');
        $('#btnUpdate').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Updating...');

        $.ajax({
            url:    '/settings/connection-types/' + $('#edit_id').val(),
            method: 'PUT',
            data: {
                _token:    '{{ csrf_token() }}',
                name:      $('#edit_name').val(),
                details:   $('#edit_details').val(),
                is_active: $('#edit_active').val(),
            },
            success: function(res) {
                if (res.success) {
                    $('#editModal').modal('hide');
                    table.ajax.reload(null, false);
                    toastr.success(res.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                if (errors.name) { $('#edit_name').addClass('is-invalid'); $('#edit_name_error').text(errors.name[0]); }
                toastr.error(xhr.responseJSON?.message || 'Failed to update.');
            },
            complete: function() {
                $('#btnUpdate').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Update');
            }
        });
    });

    // ── Toggle Active ──────────────────────────────────────
    $(document).on('click', '.btn-toggle', function() {
        var id = $(this).data('id');
        var el = $(this);

        $.ajax({
            url:    '/settings/connection-types/' + id + '/toggle',
            method: 'POST',
            data:   { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    table.ajax.reload(null, false);
                    toastr.info(res.message);
                }
            },
            error: function() { toastr.error('Toggle failed.'); }
        });
    });

    // ── Delete ─────────────────────────────────────────────
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete Connection Type?',
            text:  'This cannot be undone.',
            icon:  'warning',
            showCancelButton:   true,
            confirmButtonColor: '#d33',
            cancelButtonColor:  '#6c757d',
            confirmButtonText:  'Yes, Delete!',
            cancelButtonText:   'Cancel',
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url:    '/settings/connection-types/' + id,
                    method: 'DELETE',
                    data:   { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        table.ajax.reload(null, false);
                        toastr.success(res.message);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Failed to delete.');
                    }
                });
            }
        });
    });

});
</script>
@endsection
