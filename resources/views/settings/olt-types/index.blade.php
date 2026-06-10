@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('title', 'OLT Types')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-server mr-2 text-info"></i>OLT Types</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Settings</li>
            <li class="breadcrumb-item active">OLT Types</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> OLT Type List</h3>
            <button class="btn btn-success btn-sm" id="btnAdd">
                <i class="fas fa-plus mr-1"></i> Add OLT Type
            </button>
        </div>
        <div class="card-body p-0">
            <table id="typeTable" class="table table-bordered table-striped table-hover mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th width="60" class="text-center">#</th>
                        <th>Name</th>
                        <th>Details</th>
                        <th width="80" class="text-center">OLTs</th>
                        <th width="120" class="text-center">Status</th>
                        <th width="100" class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

{{-- ══ ADD / EDIT MODAL ══ --}}
<div class="modal fade" id="modalType" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-info text-white py-2">
                <h5 class="modal-title" id="modalTitle">Add OLT Type</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="typeId">

                <div class="form-group">
                    <label class="font-weight-bold small">Name <span class="text-danger">*</span></label>
                    <input type="text" id="typeName" class="form-control form-control-sm"
                        placeholder="e.g. BDCOM_EPON">
                    <small class="text-muted">Uppercase এ লিখুন</small>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Details</label>
                    <textarea id="typeDetails" class="form-control form-control-sm" rows="2"
                        placeholder="Optional..."></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button class="btn btn-info btn-sm" id="btnSave">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
const CSRF = '{{ csrf_token() }}';

// ── DataTable ─────────────────────────────────────────────────
const DT = $('#typeTable').DataTable({
    ajax: { url: '{{ route("settings.olt-types.data") }}', dataSrc: 'data' },
    pageLength: 25,
    order: [[1, 'asc']],
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
        { data: 'name' },
        { data: 'details', defaultContent: '<span class="text-muted">-</span>' },
        { data: 'olts_count', className: 'text-center' },
        {
            data: 'is_active', className: 'text-center',
            render(val, _, row) {
                const checked = val ? 'checked' : '';
                const label = val
                    ? '<span class="text-success font-weight-bold">Active</span>'
                    : '<span class="text-danger">Inactive</span>';
                return `<div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input toggle-status"
                        id="tog${row.id}" data-id="${row.id}" ${checked}>
                    <label class="custom-control-label" for="tog${row.id}">${label}</label>
                </div>`;
            }
        },
        {
            data: null, orderable: false, searchable: false, className: 'text-center',
            render(_, __, row) {
                return `
                    <button class="btn btn-xs btn-warning btn-edit"
                        data-id="${row.id}" data-name="${row.name}"
                        data-details="${row.details ?? ''}" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-xs btn-danger btn-delete"
                        data-id="${row.id}" data-name="${row.name}" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>`;
            }
        },
    ],
});

// ── Add ───────────────────────────────────────────────────────
$('#btnAdd').on('click', function () {
    $('#typeId').val('');
    $('#typeName').val('');
    $('#typeDetails').val('');
    $('#modalTitle').text('Add OLT Type');
    $('#modalType').modal('show');
});

// ── Edit ──────────────────────────────────────────────────────
$(document).on('click', '.btn-edit', function () {
    $('#typeId').val($(this).data('id'));
    $('#typeName').val($(this).data('name'));
    $('#typeDetails').val($(this).data('details'));
    $('#modalTitle').text('Edit OLT Type');
    $('#modalType').modal('show');
});

// ── Save ──────────────────────────────────────────────────────
$('#btnSave').on('click', function () {
    const id = $('#typeId').val();
    const payload = {
        name:    $('#typeName').val().trim(),
        details: $('#typeDetails').val().trim(),
        _token:  CSRF,
    };

    if (!payload.name) return toastError('Name আবশ্যক।');

    $.ajax({
        url:    id ? `/settings/olt-types/${id}` : '{{ route("settings.olt-types.store") }}',
        method: id ? 'PUT' : 'POST',
        data:   payload,
        success(res) {
            if (res.success) {
                toastSuccess(res.message);
                $('#modalType').modal('hide');
                DT.ajax.reload();
            }
        },
        error(xhr) {
            const err = xhr.responseJSON;
            toastError(err?.errors ? Object.values(err.errors).flat().join('\n') : (err?.message ?? 'Error'));
        }
    });
});

// ── Toggle Status ─────────────────────────────────────────────
$(document).on('change', '.toggle-status', function () {
    const id = $(this).data('id');
    $.post(`/settings/olt-types/${id}/toggle`, { _token: CSRF }, res => {
        if (res.success) { toastSuccess(res.message); DT.ajax.reload(null, false); }
    });
});

// ── Delete ────────────────────────────────────────────────────
$(document).on('click', '.btn-delete', function () {
    const id = $(this).data('id');
    const name = $(this).data('name');
    Swal.fire({
        title: 'Delete করবেন?',
        text: `"${name}" মুছে ফেলা হবে।`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Cancel',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({
            url: `/settings/olt-types/${id}`,
            method: 'DELETE',
            data: { _token: CSRF },
            success(res) {
                if (res.success) { toastSuccess(res.message); DT.ajax.reload(); }
                else toastError(res.message);
            }
        });
    });
});

function toastSuccess(msg) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: msg, showConfirmButton: false, timer: 2500 });
}
function toastError(msg) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: msg, showConfirmButton: false, timer: 3500 });
}
</script>
@endsection
