@extends('adminlte::page')
@section('title', 'Provider List')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-building mr-2 text-primary"></i>Bandwidth Providers
            </h4>
            <small class="text-muted">Manage upstream bandwidth provider companies</small>
        </div>
        <button class="btn btn-primary btn-sm px-3" id="btnAddProvider">
            <i class="fas fa-plus mr-1"></i> Add Provider
        </button>
    </div>
@endsection

@section('content')

{{-- ── Summary Cards ──────────────────────────────────────────────────────── --}}
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-building"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Providers</span>
                <span class="info-box-number" id="statTotal">{{ $providers->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active</span>
                <span class="info-box-number" id="statActive">{{ $providers->where('is_active',1)->count() }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Table Card ──────────────────────────────────────────────────────────── --}}
<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Provider List
        </h6>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm"
                   placeholder="Search..." style="width:200px; border-radius:20px;">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="providerTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="text-center" style="width:60px;">#</th>
                        <th>Company</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th class="text-center">Logo</th>
                        <th class="text-center" style="width:100px;">Action</th>
                    </tr>
                </thead>
                <tbody id="providerTableBody">
                    @forelse($providers as $i => $p)
                    <tr data-id="{{ $p->id }}">
                        <td class="text-center text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="provider-avatar mr-2"
                                     style="width:38px;height:38px;border-radius:50%;background:{{ ['#1976D2','#388E3C','#F57C00','#7B1FA2','#C62828','#00838F'][($p->id - 1) % 6] }};
                                            display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0;">
                                    {{ strtoupper(substr($p->company_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-weight-bold text-dark">{{ $p->company_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <i class="fas fa-user-tie text-muted mr-1"></i>
                            {{ $p->contact_person }}
                        </td>
                        <td>
                            <a href="mailto:{{ $p->email }}" class="text-primary">
                                <i class="fas fa-envelope mr-1 text-muted"></i>{{ $p->email }}
                            </a>
                        </td>
                        <td>
                            <i class="fas fa-phone text-muted mr-1"></i>{{ $p->phone_no }}
                        </td>
                        <td class="text-muted small">
                            {{ $p->address ?: '—' }}
                        </td>
                        <td class="text-center">
                            @if($p->document)
                                <img src="{{ asset('storage/'.$p->document) }}"
                                     alt="logo"
                                     style="height:44px;width:70px;object-fit:contain;border-radius:6px;border:1px solid #eee;padding:2px;background:#fff;">
                            @else
                                <span class="badge badge-light border text-muted">No Logo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning btn-edit px-3"
                                    data-id="{{ $p->id }}"
                                    data-company="{{ $p->company_name }}"
                                    data-contact="{{ $p->contact_person }}"
                                    data-email="{{ $p->email }}"
                                    data-phone="{{ $p->phone_no }}"
                                    data-address="{{ $p->address }}"
                                    data-doc="{{ $p->document ? asset('storage/'.$p->document) : '' }}"
                                    title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-building fa-3x mb-3 d-block opacity-25"></i>
                            No providers found. Click <strong>+ Add Provider</strong> to get started.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer py-1 bg-light">
        <small class="text-muted">Total <strong id="footerCount">{{ $providers->count() }}</strong> provider(s)</small>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- ADD / EDIT MODAL                                                           --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="providerModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">

            {{-- Header --}}
            <div class="modal-header text-white border-0 py-3"
                 id="modalHeader"
                 style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%); border-radius:8px 8px 0 0;">
                <h5 class="modal-title font-weight-bold" id="modalTitle">
                    <i class="fas fa-plus-circle mr-2"></i> Add Provider
                </h5>
                <button type="button" class="close text-white opacity-75" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body px-4 py-3">
                <input type="hidden" id="providerId">

                {{-- Error box --}}
                <div class="alert alert-danger d-none" id="modalErrors">
                    <ul class="mb-0" id="modalErrorList"></ul>
                </div>

                <div class="row">
                    {{-- Company Name --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">
                                Company Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light"><i class="fas fa-building text-primary"></i></span>
                                </div>
                                <input type="text" id="fCompanyName" class="form-control"
                                       placeholder="e.g. Summit Communications Ltd">
                            </div>
                            <small class="text-danger d-none" id="err_company_name"></small>
                        </div>
                    </div>

                    {{-- Contact Person --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">
                                Contact Person <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                </div>
                                <input type="text" id="fContactPerson" class="form-control"
                                       placeholder="e.g. Mr. Karim">
                            </div>
                            <small class="text-danger d-none" id="err_contact_person"></small>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">
                                Email <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                </div>
                                <input type="email" id="fEmail" class="form-control"
                                       placeholder="contact@company.com">
                            </div>
                            <small class="text-danger d-none" id="err_email"></small>
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">
                                Phone No <span class="text-danger">* (11 digits)</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone text-primary"></i></span>
                                </div>
                                <input type="text" id="fPhone" class="form-control"
                                       placeholder="01XXXXXXXXX" maxlength="11">
                            </div>
                            <small class="text-danger d-none" id="err_phone_no"></small>
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">
                                Address <span class="text-muted font-weight-normal">(optional)</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-primary"></i></span>
                                </div>
                                <input type="text" id="fAddress" class="form-control"
                                       placeholder="Company address">
                            </div>
                        </div>
                    </div>

                    {{-- Document / Logo --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">
                                Logo / Document <span class="text-muted font-weight-normal">(optional, max 5MB)</span>
                            </label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="fDocument"
                                       accept=".jpg,.jpeg,.png,.pdf">
                                <label class="custom-file-label" for="fDocument">Choose file...</label>
                            </div>
                            {{-- Preview --}}
                            <div id="docPreviewWrap" class="mt-2 d-none">
                                <img id="docPreviewImg" src=""
                                     style="height:50px;border-radius:6px;border:1px solid #ddd;padding:2px;background:#fff;"
                                     alt="preview">
                                <small class="text-muted ml-1">Current</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 bg-light px-4 py-3" style="border-radius:0 0 8px 8px;">
                <button type="button" class="btn btn-light border px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary px-4" id="btnSaveProvider">
                    <i class="fas fa-save mr-1"></i> Save Provider
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    /* Table row hover */
    #providerTable tbody tr:hover { background: #f0f4ff !important; }
    #providerTable thead th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #555;
        padding: 10px 12px;
    }
    #providerTable tbody td { padding: 10px 12px; vertical-align: middle; }

    /* Search input */
    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color: #1a237e; }

    /* Modal */
    .modal-content { border-radius: 10px; overflow: hidden; }
    .input-group-text { border-right: 0; }
    .input-group .form-control { border-left: 0; }
    .input-group .form-control:focus { border-color: #ced4da; box-shadow: none; }

    /* Avatar */
    .provider-avatar { box-shadow: 0 2px 6px rgba(0,0,0,.15); }

    /* Badge */
    .opacity-25 { opacity: .25; }

    /* Toastr সবসময় modal এর উপরে */
    #toast-container { z-index: 99999 !important; }
    .toast { z-index: 99999 !important; }
</style>
@stop

@section('js')
<script>
const CSRF = '{{ csrf_token() }}';

$(function () {

    // ── Toastr config ─────────────────────────────────────────────────────────
    toastr.options = {
        closeButton:       true,
        progressBar:       true,
        positionClass:     'toast-top-right',
        timeOut:           3500,
        preventDuplicates: true,
    };

    // ══════════════════════════════════════════════
    // Live search
    // ══════════════════════════════════════════════
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#providerTableBody tr[data-id]').each(function () {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(q));
        });
    });

    // ══════════════════════════════════════════════
    // Open ADD modal
    // ══════════════════════════════════════════════
    $('#btnAddProvider').on('click', function () {
        resetModal();
        $('#modalTitle').html('<i class="fas fa-plus-circle mr-2"></i> Add Provider');
        $('#modalHeader').css('background', 'linear-gradient(135deg,#1a237e 0%,#283593 100%)');
        $('#btnSaveProvider').removeClass('btn-warning').addClass('btn-primary')
                             .html('<i class="fas fa-save mr-1"></i> Save Provider');
        $('#providerModal').modal('show');
    });

    // ══════════════════════════════════════════════
    // Open EDIT modal
    // ══════════════════════════════════════════════
    $(document).on('click', '.btn-edit', function () {
        const $btn = $(this);

        resetModal();

        $('#providerId').val($btn.data('id'));
        $('#fCompanyName').val($btn.data('company'));
        $('#fContactPerson').val($btn.data('contact'));
        $('#fEmail').val($btn.data('email'));
        $('#fPhone').val($btn.data('phone'));
        $('#fAddress').val($btn.data('address') || '');

        // Show existing document preview
        const docUrl = $btn.data('doc');
        if (docUrl) {
            $('#docPreviewImg').attr('src', docUrl);
            $('#docPreviewWrap').removeClass('d-none');
        }

        $('#modalTitle').html('<i class="fas fa-edit mr-2"></i> Edit Provider');
        $('#modalHeader').css('background', 'linear-gradient(135deg,#E65100 0%,#F57C00 100%)');
        $('#btnSaveProvider').removeClass('btn-primary').addClass('btn-warning')
                             .html('<i class="fas fa-save mr-1"></i> Update Provider');
        $('#providerModal').modal('show');
    });

    // ══════════════════════════════════════════════
    // Document preview
    // ══════════════════════════════════════════════
    $('#fDocument').on('change', function () {
        const file = this.files[0];
        $('.custom-file-label').text(file ? file.name : 'Choose file...');
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                $('#docPreviewImg').attr('src', e.target.result);
                $('#docPreviewWrap').removeClass('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    // ══════════════════════════════════════════════
    // Save (Add or Update)
    // ══════════════════════════════════════════════
    $('#btnSaveProvider').on('click', function () {
        clearErrors();

        const id = $('#providerId').val();
        const formData = new FormData();
        formData.append('_token', CSRF);
        formData.append('company_name',   $('#fCompanyName').val().trim());
        formData.append('contact_person', $('#fContactPerson').val().trim());
        formData.append('email',          $('#fEmail').val().trim());
        formData.append('phone_no',       $('#fPhone').val().trim());
        formData.append('address',        $('#fAddress').val().trim());

        const docFile = $('#fDocument')[0].files[0];
        if (docFile) formData.append('document', docFile);

        if (id) formData.append('_method', 'PUT');

        const url = id
            ? `/bandwidth-buy/provider/${id}`
            : '{{ route("bandwidth-buy.provider.store") }}';

        const $btn = $(this).prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url:         url,
            method:      'POST',
            data:        formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (!res.success) return;

                $('#providerModal').modal('hide');

                // toastr modal hide হওয়ার পরে show করতে হবে — নইলে backdrop এর নিচে চলে যায়
                setTimeout(function () {
                    toastr.success(res.message);
                }, 400);

                const p = res.provider;
                if (id) {
                    updateRow(p);
                } else {
                    addRow(p);
                    updateStats(1);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors || {};
                if (Object.keys(errors).length) {
                    $.each(errors, function (field, msgs) {
                        const key = field.replace('.', '_');
                        $(`#err_${key}`).text(msgs[0]).removeClass('d-none');
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Save failed.');
                }
            },
            complete: function () {
                $btn.prop('disabled', false)
                    .html(id
                        ? '<i class="fas fa-save mr-1"></i> Update Provider'
                        : '<i class="fas fa-save mr-1"></i> Save Provider');
            }
        });
    });

    // ══════════════════════════════════════════════
    // Helpers
    // ══════════════════════════════════════════════
    function resetModal() {
        $('#providerId').val('');
        $('#fCompanyName, #fContactPerson, #fEmail, #fPhone, #fAddress').val('');
        $('#fDocument').val('');
        $('.custom-file-label').text('Choose file...');
        $('#docPreviewWrap').addClass('d-none');
        $('#docPreviewImg').attr('src', '');
        clearErrors();
    }

    function clearErrors() {
        $('[id^="err_"]').text('').addClass('d-none');
        $('#modalErrors').addClass('d-none');
    }

    const AVATAR_COLORS = ['#1976D2','#388E3C','#F57C00','#7B1FA2','#C62828','#00838F'];

    function makeAvatar(name, id) {
        const color = AVATAR_COLORS[(id - 1) % AVATAR_COLORS.length];
        const letter = (name || '?').charAt(0).toUpperCase();
        return `<div class="provider-avatar mr-2"
                     style="width:38px;height:38px;border-radius:50%;background:${color};
                            display:inline-flex;align-items:center;justify-content:center;
                            color:#fff;font-weight:700;font-size:14px;flex-shrink:0;">
                    ${letter}
                </div>`;
    }

    function docCell(p) {
        return p.document_url
            ? `<img src="${p.document_url}" alt="logo"
                    style="height:44px;width:70px;object-fit:contain;border-radius:6px;
                           border:1px solid #eee;padding:2px;background:#fff;">`
            : `<span class="badge badge-light border text-muted">No Logo</span>`;
    }

    function addRow(p) {
        const rowCount = $('#providerTableBody tr[data-id]').length + 1;
        const html = `
        <tr data-id="${p.id}">
            <td class="text-center text-muted small">${rowCount}</td>
            <td>
                <div class="d-flex align-items-center">
                    ${makeAvatar(p.company_name, p.id)}
                    <div><div class="font-weight-bold text-dark">${p.company_name}</div></div>
                </div>
            </td>
            <td><i class="fas fa-user-tie text-muted mr-1"></i>${p.contact_person}</td>
            <td><a href="mailto:${p.email}" class="text-primary"><i class="fas fa-envelope mr-1 text-muted"></i>${p.email}</a></td>
            <td><i class="fas fa-phone text-muted mr-1"></i>${p.phone_no}</td>
            <td class="text-muted small">${p.address || '—'}</td>
            <td class="text-center">${docCell(p)}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-warning btn-edit px-3"
                        data-id="${p.id}"
                        data-company="${p.company_name}"
                        data-contact="${p.contact_person}"
                        data-email="${p.email}"
                        data-phone="${p.phone_no}"
                        data-address="${p.address || ''}"
                        data-doc="${p.document_url || ''}"
                        title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        </tr>`;

        $('#emptyRow').remove();
        $('#providerTableBody').prepend(html);
        updateFooter();
    }

    function updateRow(p) {
        const $row = $(`#providerTableBody tr[data-id="${p.id}"]`);
        $row.find('td:eq(1)').html(`
            <div class="d-flex align-items-center">
                ${makeAvatar(p.company_name, p.id)}
                <div><div class="font-weight-bold text-dark">${p.company_name}</div></div>
            </div>`);
        $row.find('td:eq(2)').html(`<i class="fas fa-user-tie text-muted mr-1"></i>${p.contact_person}`);
        $row.find('td:eq(3)').html(`<a href="mailto:${p.email}" class="text-primary"><i class="fas fa-envelope mr-1 text-muted"></i>${p.email}</a>`);
        $row.find('td:eq(4)').html(`<i class="fas fa-phone text-muted mr-1"></i>${p.phone_no}`);
        $row.find('td:eq(5)').text(p.address || '—');
        $row.find('td:eq(6)').html(docCell(p));

        // Update data attributes on edit button so next edit gets fresh data
        $row.find('.btn-edit')
            .data('company',  p.company_name)
            .data('contact',  p.contact_person)
            .data('email',    p.email)
            .data('phone',    p.phone_no)
            .data('address',  p.address || '')
            .data('doc',      p.document_url || '');

        $row.addClass('table-success');
        setTimeout(() => $row.removeClass('table-success'), 1500);
    }

    function updateStats(delta) {
        const n = parseInt($('#statTotal').text()) + delta;
        $('#statTotal').text(n);
        $('#statActive').text(n);
        updateFooter();
    }

    function updateFooter() {
        $('#footerCount').text($('#providerTableBody tr[data-id]').length);
    }

});
</script>
@stop
