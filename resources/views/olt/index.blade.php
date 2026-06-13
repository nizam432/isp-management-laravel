@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('title', 'OLT Management')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-network-wired mr-2 text-primary"></i>OLT <small class="text-muted">OLTManage</small></h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">OLT</li>
            <li class="breadcrumb-item active">OLT Manage</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> OLT List</h3>
            <div>
                <button class="btn btn-info btn-sm mr-1" id="btnSyncAll">
                    <i class="fas fa-sync-alt mr-1"></i> Sync All OLTs
                </button>
                <button class="btn btn-primary btn-sm" id="btnAddOlt">
                    <i class="fas fa-plus mr-1"></i> Add OLT
                </button>
            </div>
        </div>

        <div class="card-body p-0">{{--oltTable--}}
            <table id="" class="table table-bordered table-striped table-hover mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th width="60" class="text-center">Serial</th>
                        <th>Ip</th>
                        <th>Community</th>
                        <th>OLTType</th>
                        <th width="90" class="text-center">Status</th>
                        <th width="120" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($olts as $i => $olt)
                    <tr id="olt-row-{{ $olt->id }}">
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td><code>{{ $olt->ip_address }}</code></td>
                        <td>{{ $olt->community ?? 'public' }}</td>
                        <td>
                            <span class="badge badge-dark">{{ $olt->oltType->name ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            @if($olt->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-xs btn-success btn-edit"
                                data-id="{{ $olt->id }}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-xs btn-info btn-sync"
                                data-id="{{ $olt->id }}" data-ip="{{ $olt->ip_address }}" title="Sync">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="btn btn-xs btn-danger btn-delete"
                                data-id="{{ $olt->id }}" data-ip="{{ $olt->ip_address }}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer text-muted small">
            Showing 1 to {{ $olts->count() }} of {{ $olts->count() }} entries
        </div>
    </div>

</div>

{{-- ══════════════ ADD / EDIT MODAL ══════════════ --}}
<div class="modal fade" id="modalOlt" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title" id="modalOltTitle">
                    <i class="fas fa-plus mr-1"></i> Add OLT
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="oltId">

                <div class="form-group">
                    <label class="font-weight-bold small">IP ADDRESS <span class="text-danger">*</span></label>
                    <input type="text" id="oltIp" class="form-control form-control-sm"
                        placeholder="Enter IP and Port (e.g., 192.168.43.241:23)">
                </div>

                <div class="form-group">
                    <label class="font-weight-bold small">COMMUNITY</label>
                    <input type="text" id="oltCommunity" class="form-control form-control-sm"
                        placeholder="public">
                </div>

                <div class="form-group">
                    <label class="font-weight-bold small">OLT TYPE <span class="text-danger">*</span></label>
                    <select id="oltTypeId" class="form-control form-control-sm">
                        <option value="">Select</option>
                        @foreach($oltTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold small">WEB IP <span class="text-danger">*</span></label>
                    <input type="text" id="oltWebIp" class="form-control form-control-sm"
                        placeholder="Enter Web IP and Port (e.g., 192.168.43.241:23)">
                </div>

                <div class="form-group">
                    <label class="font-weight-bold small">WEB USERNAME <span class="text-danger">*</span></label>
                    <input type="text" id="oltWebUsername" class="form-control form-control-sm" value="admin">
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold small">WEB PASSWORD <span class="text-danger">*</span></label>
                    <input type="password" id="oltWebPassword" class="form-control form-control-sm">
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-danger btn-sm" id="btnClearOlt">
                    <i class="fas fa-eraser mr-1"></i> Clear
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveOlt">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
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
$('#oltTable').DataTable({
    pageLength: 10,
    order: [],
    language: { search: 'SEARCH:', lengthMenu: 'SHOW _MENU_ ENTRIES' },
});

// ── Open Add Modal ─────────────────────────────────────────────
$('#btnAddOlt').on('click', function () {
    resetModal();
    $('#modalOltTitle').html('<i class="fas fa-plus mr-1"></i> Add OLT');
    $('#modalOlt').modal('show');
});

// ── Open Edit Modal ────────────────────────────────────────────
$(document).on('click', '.btn-edit', function () {
    const id = $(this).data('id');
    $.get(`/olt/${id}`, function (res) {
        if (!res.success) return toastError('Data লোড হয়নি।');
        const o = res.olt;
        $('#oltId').val(o.id);
        $('#oltIp').val(o.ip_address);
        $('#oltCommunity').val(o.community);
        $('#oltTypeId').val(o.olt_type_id);
        $('#oltWebIp').val(o.web_ip);
        $('#oltWebUsername').val(o.web_username);
        $('#oltWebPassword').val('');
        $('#modalOltTitle').html('<i class="fas fa-edit mr-1"></i> Edit OLT');
        $('#modalOlt').modal('show');
    });
});

// ── Save ───────────────────────────────────────────────────────
$('#btnSaveOlt').on('click', function () {
    const id = $('#oltId').val();
    const payload = {
        ip_address:   $('#oltIp').val().trim(),
        community:    $('#oltCommunity').val().trim() || 'public',
        olt_type_id:  $('#oltTypeId').val(),
        web_ip:       $('#oltWebIp').val().trim(),
        web_username: $('#oltWebUsername').val().trim(),
        web_password: $('#oltWebPassword').val(),
        _token: CSRF,
    };

    if (!payload.ip_address) return toastError('IP Address আবশ্যক।');
    if (!payload.olt_type_id) return toastError('OLT Type আবশ্যক।');

    $.ajax({
        url:    id ? `/olt/${id}` : '{{ route("olt.store") }}',
        method: id ? 'PUT' : 'POST',
        data:   payload,
        success(res) {
            if (res.success) {
                toastSuccess(res.message);
                $('#modalOlt').modal('hide');
                setTimeout(() => location.reload(), 900);
            }
        },
        error(xhr) {
            const errors = xhr.responseJSON?.errors;
            toastError(errors ? Object.values(errors).flat().join('\n') : (xhr.responseJSON?.message ?? 'Error'));
        }
    });
});

// ── Sync Single ────────────────────────────────────────────────
$(document).on('click', '.btn-sync', function () {
    const id = $(this).data('id');
    const ip = $(this).data('ip');
    const $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.post(`/olt/${id}/sync`, { _token: CSRF })
        .done(res => { toastSuccess(res.message); })
        .fail(() => { toastError(`Sync ব্যর্থ [${ip}]`); })
        .always(() => $btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i>'));
});

// ── Sync All ───────────────────────────────────────────────────
$('#btnSyncAll').on('click', function () {
    Swal.fire({
        title: 'Sync All OLTs?',
        text: 'সব active OLT sync করা হবে।',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-sync-alt mr-1"></i> Sync',
        confirmButtonColor: '#17a2b8',
    }).then(r => {
        if (!r.isConfirmed) return;
        $('#btnSyncAll').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Syncing...');
        $.post('{{ route("olt.sync-all") }}', { _token: CSRF })
            .done(res => { toastSuccess(res.message); })
            .always(() => $('#btnSyncAll').prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Sync All OLTs'));
    });
});

// ── Delete ─────────────────────────────────────────────────────
$(document).on('click', '.btn-delete', function () {
    const id = $(this).data('id');
    const ip = $(this).data('ip');
    Swal.fire({
        title: 'OLT Delete করবেন?',
        text: ip + ' মুছে ফেলা হবে।',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Cancel',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({
            url: `/olt/${id}`,
            method: 'DELETE',
            data: { _token: CSRF },
            success(res) {
                if (res.success) {
                    $(`#olt-row-${id}`).fadeOut(400, function () { $(this).remove(); });
                    toastSuccess(res.message);
                }
            }
        });
    });
});

// ── Clear ──────────────────────────────────────────────────────
$('#btnClearOlt').on('click', resetModal);

function resetModal() {
    $('#oltId').val('');
    $('#oltIp, #oltCommunity, #oltWebIp, #oltWebPassword').val('');
    $('#oltWebUsername').val('admin');
    $('#oltTypeId').val('');
}

function toastSuccess(msg) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: msg, showConfirmButton: false, timer: 2500 });
}
function toastError(msg) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: msg, showConfirmButton: false, timer: 3500 });
}
</script>
@endsection
