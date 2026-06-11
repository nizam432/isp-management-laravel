@extends('adminlte::page')
@section('title', 'Service List')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-network-wired mr-2 text-primary"></i>Bandwidth Services
            </h4>
            <small class="text-muted">Manage service types (IIG, GGC, BDIX, etc.)</small>
        </div>
        <button class="btn btn-primary btn-sm px-3" id="btnAddService">
            <i class="fas fa-plus mr-1"></i> Add Service
        </button>
    </div>
@endsection

@section('content')

{{-- ── Summary Cards ──────────────────────────────────────────────────────── --}}
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-network-wired"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Services</span>
                <span class="info-box-number" id="statTotal">{{ $services->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active</span>
                <span class="info-box-number" id="statActive">{{ $services->where('is_active', 1)->count() }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Table Card ──────────────────────────────────────────────────────────── --}}
<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Service List
        </h6>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Search..." style="width:200px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="serviceTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="text-center" style="width:60px;">#</th>
                        <th style="width:160px;">Service Name</th>
                        <th>Description</th>
                        <th class="text-center" style="width:100px;">Action</th>
                    </tr>
                </thead>
                <tbody id="serviceTableBody">
                    @forelse($services as $i => $s)
                    <tr data-id="{{ $s->id }}">
                        <td class="text-center text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <span class="badge px-3 py-2 font-weight-bold"
                                  style="font-size:13px; background:{{ ['#E3F2FD','#E8F5E9','#FFF3E0','#F3E5F5','#FCE4EC','#E0F7FA'][($s->id - 1) % 6] }};
                                         color:{{ ['#1565C0','#2E7D32','#E65100','#6A1B9A','#B71C1C','#006064'][($s->id - 1) % 6] }};
                                         border-radius:8px; letter-spacing:.5px;">
                                <i class="fas fa-wifi mr-1"></i>{{ $s->name }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $s->description ?: '—' }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning btn-edit px-3"
                                    data-id="{{ $s->id }}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-network-wired fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No services found. Click <strong>+ Add Service</strong> to get started.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer py-1 bg-light">
        <small class="text-muted">Total <strong id="footerCount">{{ $services->count() }}</strong> service(s)</small>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- ADD / EDIT MODAL                                                           --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="serviceModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-lg">

            {{-- Header --}}
            <div class="modal-header text-white border-0 py-3" id="modalHeader"
                 style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%); border-radius:8px 8px 0 0;">
                <h5 class="modal-title font-weight-bold" id="modalTitle">
                    <i class="fas fa-plus-circle mr-2"></i> Add Service
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body px-4 py-3">
                <input type="hidden" id="serviceId">

                {{-- Service Name --}}
                <div class="form-group">
                    <label class="font-weight-bold text-dark">
                        Service Name <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-network-wired text-primary"></i>
                            </span>
                        </div>
                        <input type="text" id="fName" class="form-control"
                               placeholder="e.g. IIG, GGC, BDIX, FNA">
                    </div>
                    <small class="text-danger d-none" id="err_name"></small>
                </div>

                {{-- Description --}}
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-dark">
                        Description <span class="text-muted font-weight-normal">(optional)</span>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-align-left text-primary"></i>
                            </span>
                        </div>
                        <textarea id="fDescription" class="form-control" rows="3"
                                  placeholder="Brief description of this service type..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 bg-light px-4 py-3"
                 style="border-radius:0 0 8px 8px;">
                <button type="button" class="btn btn-light border px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary px-4" id="btnSaveService">
                    <i class="fas fa-save mr-1"></i> Save Service
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('css')
<style>
    #serviceTable tbody tr:hover { background: #f0f4ff !important; }
    #serviceTable thead th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #555;
        padding: 10px 14px;
    }
    #serviceTable tbody td { padding: 11px 14px; vertical-align: middle; }
    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color: #1a237e; }
    .modal-content { border-radius: 10px; overflow: hidden; }
    .input-group-text { border-right: 0; }
    .input-group .form-control, .input-group textarea { border-left: 0; }
    .input-group .form-control:focus,
    .input-group textarea:focus { border-color: #ced4da; box-shadow: none; }
</style>
@endpush

@push('js')
<script>
const CSRF = '{{ csrf_token() }}';

// Badge color palettes
const BG_COLORS  = ['#E3F2FD','#E8F5E9','#FFF3E0','#F3E5F5','#FCE4EC','#E0F7FA'];
const TXT_COLORS = ['#1565C0','#2E7D32','#E65100','#6A1B9A','#B71C1C','#006064'];

$(function () {

    // ── Live search ───────────────────────────────────────────────────────────
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#serviceTableBody tr[data-id]').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });

    // ── Open ADD modal ────────────────────────────────────────────────────────
    $('#btnAddService').on('click', function () {
        resetModal();
        $('#modalTitle').html('<i class="fas fa-plus-circle mr-2"></i> Add Service');
        $('#modalHeader').css('background', 'linear-gradient(135deg,#1a237e 0%,#283593 100%)');
        $('#btnSaveService').removeClass('btn-warning').addClass('btn-primary')
                           .html('<i class="fas fa-save mr-1"></i> Save Service');
        $('#serviceModal').modal('show');
    });

    // ── Open EDIT modal ───────────────────────────────────────────────────────
    $(document).on('click', '.btn-edit', function () {
        const id   = $(this).data('id');
        const $btn = $(this).html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        $.get(`/bandwidth-buy/service/${id}/edit`, function (res) {
            if (!res.success) { toastr.error('Failed to load service data.'); return; }

            const s = res.service;
            resetModal();
            $('#serviceId').val(s.id);
            $('#fName').val(s.name);
            $('#fDescription').val(s.description);

            $('#modalTitle').html('<i class="fas fa-edit mr-2"></i> Edit Service');
            $('#modalHeader').css('background', 'linear-gradient(135deg,#E65100 0%,#F57C00 100%)');
            $('#btnSaveService').removeClass('btn-primary').addClass('btn-warning')
                               .html('<i class="fas fa-save mr-1"></i> Update Service');
            $('#serviceModal').modal('show');

        }).fail(function () {
            toastr.error('Failed to load service data.');
        }).always(function () {
            $btn.html('<i class="fas fa-edit"></i>').prop('disabled', false);
        });
    });

    // ── Save ──────────────────────────────────────────────────────────────────
    $('#btnSaveService').on('click', function () {
        clearErrors();

        const id   = $('#serviceId').val();
        const data = {
            _token:      CSRF,
            name:        $('#fName').val().trim(),
            description: $('#fDescription').val().trim(),
        };
        if (id) data._method = 'PUT';

        const url = id
            ? `/bandwidth-buy/service/${id}`
            : '{{ route("bandwidth-buy.service.store") }}';

        const $btn = $(this).prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url:    url,
            method: 'POST',
            data:   data,
            success: function (res) {
                if (!res.success) return;
                toastr.success(res.message);
                $('#serviceModal').modal('hide');

                if (id) {
                    updateRow(res.service);
                } else {
                    addRow(res.service);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors || {};
                if (errors.name) {
                    $('#err_name').text(errors.name[0]).removeClass('d-none');
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Save failed.');
                }
            },
            complete: function () {
                $btn.prop('disabled', false)
                    .html(id
                        ? '<i class="fas fa-save mr-1"></i> Update Service'
                        : '<i class="fas fa-save mr-1"></i> Save Service');
            }
        });
    });

    // ── Helpers ───────────────────────────────────────────────────────────────
    function resetModal() {
        $('#serviceId').val('');
        $('#fName, #fDescription').val('');
        clearErrors();
    }

    function clearErrors() {
        $('#err_name').text('').addClass('d-none');
    }

    function badgeHtml(s) {
        const idx  = (s.id - 1) % 6;
        return `<span class="badge px-3 py-2 font-weight-bold"
                      style="font-size:13px; background:${BG_COLORS[idx]};
                             color:${TXT_COLORS[idx]}; border-radius:8px; letter-spacing:.5px;">
                    <i class="fas fa-wifi mr-1"></i>${s.name}
                </span>`;
    }

    function addRow(s) {
        const idx = $('#serviceTableBody tr[data-id]').length + 1;
        $('#emptyRow').remove();
        const html = `
        <tr data-id="${s.id}">
            <td class="text-center text-muted small">${idx}</td>
            <td>${badgeHtml(s)}</td>
            <td class="text-muted">${s.description || '—'}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-warning btn-edit px-3" data-id="${s.id}" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        </tr>`;
        $('#serviceTableBody').prepend(html);
        updateStats(1);
        updateFooter();
    }

    function updateRow(s) {
        const $row = $(`#serviceTableBody tr[data-id="${s.id}"]`);
        $row.find('td:eq(1)').html(badgeHtml(s));
        $row.find('td:eq(2)').text(s.description || '—');
        $row.addClass('table-success');
        setTimeout(() => $row.removeClass('table-success'), 1500);
    }

    function updateStats(delta) {
        $('#statTotal').text(parseInt($('#statTotal').text()) + delta);
        $('#statActive').text(parseInt($('#statActive').text()) + delta);
    }

    function updateFooter() {
        $('#footerCount').text($('#serviceTableBody tr[data-id]').length);
    }

});
</script>
@endpush
