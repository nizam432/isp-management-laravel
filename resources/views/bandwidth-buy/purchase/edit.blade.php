@extends('adminlte::page')
@section('title', 'Edit Purchase')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h4 class="mb-0 font-weight-bold" style="color:#1a237e;">
            <i class="fas fa-file-invoice-dollar mr-2"></i>Edit Purchase
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size:12px;">
                <li class="breadcrumb-item"><a href="{{ route('bandwidth-buy.purchase.index') }}">Purchase List</a></li>
                <li class="breadcrumb-item active">Edit Purchase</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('bandwidth-buy.purchase.index') }}"
       class="btn btn-sm btn-outline-secondary px-3">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>
@endsection

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissible shadow-sm border-0 mb-3" style="border-left:4px solid #c62828 !important; border-radius:8px;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-exclamation-circle mr-2 text-danger"></i>
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-1 pl-4">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form action="{{ route('bandwidth-buy.purchase.update', $purchase) }}" method="POST"
      enctype="multipart/form-data" id="purchaseForm">
@csrf @method('PUT')

<div class="row g-3">

<div class="col-lg-8">

    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px; overflow:hidden;">
        <div class="card-header border-0 d-flex align-items-center py-3 px-4"
             style="background:linear-gradient(135deg,#1a237e,#3949ab);">
            <div class="step-circle mr-3">1</div>
            <div>
                <h6 class="mb-0 text-white font-weight-bold">Invoice Information</h6>
                <small class="text-white-50">Provider, invoice number, date & attachment</small>
            </div>
        </div>
        <div class="card-body px-4 py-4">
            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="field-label">
                        <i class="fas fa-building mr-1 text-primary"></i>Provider
                        <span class="text-danger ml-1">*</span>
                    </label>
                    <select name="provider_id" id="providerSelect"
                            class="form-control select2 custom-select-style" required>
                        <option value="">— Select Provider —</option>
                        @foreach($providers as $prov)
                            <option value="{{ $prov->id }}"
                                {{ old('provider_id', $purchase->provider_id) == $prov->id ? 'selected' : '' }}>
                                {{ $prov->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="field-label">
                        <i class="fas fa-hashtag mr-1 text-primary"></i>Invoice No
                        <span class="text-danger ml-1">*</span>
                    </label>
                    <input type="text" name="invoice_no" class="form-control custom-input"
                           value="{{ old('invoice_no', $purchase->invoice_no) }}"
                           placeholder="e.g. INV-2026-001" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="field-label">
                        <i class="fas fa-calendar-alt mr-1 text-primary"></i>Billing Date
                        <span class="text-danger ml-1">*</span>
                    </label>
                    <div class="input-group">
                        <input type="text" name="billing_date" id="billingDate"
                               class="form-control custom-input datepicker"
                               value="{{ old('billing_date', $purchase->billing_date->format('m/d/Y')) }}"
                               required autocomplete="off" placeholder="MM/DD/YYYY">
                        <div class="input-group-append">
                            <span class="input-group-text" style="background:#f0f4ff; border-left:0; border-color:#d0d7e8; cursor:pointer;"
                                  onclick="$('#billingDate').datepicker('show')">
                                <i class="fas fa-calendar-alt text-primary"></i>
                            </span>
                        </div>
                    </div>
                    <small class="text-warning" style="font-size:11px;">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Changing date resets all service lines
                    </small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="field-label">
                        <i class="fas fa-paperclip mr-1 text-primary"></i>Attachment
                        <span class="text-muted" style="font-size:11px; font-weight:400;">(optional · PDF/Image · max 5MB)</span>
                    </label>
                    <div class="doc-upload-box" id="docUploadBox">
                        <input type="file" id="docInput" name="document"
                               accept=".jpg,.jpeg,.png,.pdf" style="display:none;">
                        <div id="docPlaceholder" class="text-center py-2" onclick="$('#docInput').click()" style="cursor:pointer;">
                            <i class="fas fa-cloud-upload-alt text-muted d-block mb-1" style="font-size:20px;"></i>
                            <span class="text-muted" style="font-size:12px;">Click to upload</span>
                        </div>
                        <div id="docPreview" style="display:none;" class="text-center py-1">
                            <img id="docImg" src="" style="max-height:48px; border-radius:6px;" alt="">
                            <div style="font-size:11px;" class="text-muted mt-1" id="docFileName"></div>
                            <button type="button" class="btn btn-xs btn-light border mt-1" id="docRemove">
                                <i class="fas fa-times mr-1"></i>Remove
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm service-lines-card" style="border-radius:12px; overflow:visible;">
        <div class="card-header border-0 py-3 px-4"
             style="background:linear-gradient(135deg,#1b5e20,#388e3c);">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="step-circle mr-3">2</div>
                    <div>
                        <h6 class="mb-0 text-white font-weight-bold">Service Lines</h6>
                        <small class="text-white-50">Add bandwidth services with quantity, rate & VAT</small>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light font-weight-bold shadow-sm px-3"
                            type="button" id="addSvcDropdown"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            style="border-radius:20px; font-size:13px;">
                        <i class="fas fa-plus mr-1 text-success"></i> Add Service
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow"
                         aria-labelledby="addSvcDropdown"
                         style="z-index: 2100; border-radius:10px; min-width:220px; max-height:320px; overflow-y:auto; border:none;">
                        <h6 class="dropdown-header" style="font-size:11px;">SELECT SERVICE</h6>
                        @foreach($services as $svc)
                        <a class="dropdown-item svc-pick py-2 {{ $svc->is_active ? '' : 'inactive-svc' }}"
                           data-id="{{ $svc->id }}" data-name="{{ $svc->name }}"
                           href="javascript:void(0)"
                           style="font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:space-between;">
                            <span>
                                <i class="fas fa-wifi mr-2 text-primary" style="font-size:11px;"></i>{{ $svc->name }}
                            </span>
                            @if(!$svc->is_active)
                                <span class="service-status">inactive</span>
                            @endif
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" id="linesTable">
                    <thead>
                        <tr style="background:#f1f8e9; border-bottom:2px solid #c8e6c9;">
                            <th class="lines-th" style="width:130px;">Service</th>
                            <th class="lines-th">From Date</th>
                            <th class="lines-th">To Date</th>
                            <th class="lines-th text-right">Qty (MB)</th>
                            <th class="lines-th text-right">Rate (৳)</th>
                            <th class="lines-th text-center">VAT %</th>
                            <th class="lines-th text-right">Total (৳)</th>
                            <th class="lines-th" style="width:44px;"></th>
                        </tr>
                    </thead>
                    <tbody id="linesBody">
                        @if(count($purchase->lines) === 0)
                        <tr id="emptyLinesRow">
                            <td colspan="8" class="text-center py-5">
                                <div style="opacity:.35;">
                                    <i class="fas fa-server fa-2x text-muted mb-2 d-block"></i>
                                    <span class="text-muted" style="font-size:13px;">
                                        No service lines yet — click <strong>Add Service</strong> above
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($purchase->lines as $line)
                        @php $idx = $line->id; $c = null; @endphp
                        <tr data-service="{{ $line->service_id }}" class="line-row">
                            <td>
                                <span class="svc-badge" style="background:#E3F2FD; color:#1565C0;">
                                    <i class="fas fa-wifi mr-1" style="font-size:10px;"></i>{{ $line->service->name }}
                                </span>
                                <input type="hidden" name="lines[{{ $idx }}][service_id]" value="{{ $line->service_id }}">
                            </td>
                            <td>
                                  <input type="text" name="lines[{{ $idx }}][from_date]"
                                      class="form-control form-control-sm datepicker line-date"
                                      value="{{ $line->from_date->format('m/d/Y') }}" required autocomplete="off" placeholder="MM/DD/YYYY">
                            </td>
                            <td>
                                  <input type="text" name="lines[{{ $idx }}][to_date]"
                                      class="form-control form-control-sm datepicker line-date"
                                      value="{{ $line->to_date->format('m/d/Y') }}" required autocomplete="off" placeholder="MM/DD/YYYY">
                            </td>
                            <td>
                                <input type="number" name="lines[{{ $idx }}][quantity_mb]"
                                       class="form-control form-control-sm line-qty text-right"
                                       value="{{ $line->quantity_mb }}" min="0" step="0.01" required>
                            </td>
                            <td>
                                <input type="number" name="lines[{{ $idx }}][rate]"
                                       class="form-control form-control-sm line-rate text-right"
                                       value="{{ $line->rate }}" min="0" step="0.01" required>
                            </td>
                            <td>
                                <input type="number" name="lines[{{ $idx }}][vat_percent]"
                                       class="form-control form-control-sm line-vat text-center"
                                       value="{{ $line->vat_percent }}" min="0" max="100" step="0.01" required>
                            </td>
                            <td>
                                <input type="number" name="lines[{{ $idx }}][line_total]"
                                       class="form-control form-control-sm line-total"
                                       value="{{ $line->line_total }}" readonly tabindex="-1">
                            </td>
                            <td class="text-center">
                                <button type="button" class="remove-line" title="Remove line">
                                    <i class="fas fa-times" style="font-size:11px;"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot id="linesSummaryFoot" style="{{ count($purchase->lines) ? '' : 'display:none;' }}">
                        <tr style="background:#e8f5e9;">
                            <td colspan="6" class="text-right font-weight-bold py-2 pr-3"
                                style="font-size:13px; color:#1b5e20;">
                                Subtotal
                            </td>
                            <td class="text-right font-weight-bold py-2 pr-3"
                                style="font-size:14px; color:#1b5e20;" id="footSubTotal">
                                ৳ {{ number_format($purchase->sub_total,2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="col-lg-4">
    <div style="position:sticky; top:68px;">

        <div class="card border-0 shadow-sm mb-3" style="border-radius:12px; overflow:hidden;">
            <div class="card-header border-0 py-3 px-4"
                 style="background:linear-gradient(135deg,#bf360c,#e64a19);">
                <div class="d-flex align-items-center">
                    <i class="fas fa-calculator text-white mr-2" style="font-size:16px;"></i>
                    <div>
                        <h6 class="mb-0 text-white font-weight-bold">Payment Summary</h6>
                        <small class="text-white-50">Auto-calculated from service lines</small>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 py-0">

                <div class="summary-row border-bottom">
                    <span class="summary-label">Sub Total</span>
                    <span class="summary-value" id="subTotalDisplay">৳ {{ number_format($purchase->sub_total,2) }}</span>
                    <input type="hidden" id="subTotalHidden" value="{{ $purchase->sub_total }}">
                </div>

                <div class="summary-row border-bottom flex-column align-items-start py-3 px-4">
                    <div class="d-flex justify-content-between w-100 mb-2">
                        <span class="summary-label mb-0">Paid Amount (৳)</span>
                        <small class="text-danger" style="font-size:11px;">max = sub total</small>
                    </div>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background:#fff5f5; border-color:#ffcccc; color:#c62828; font-weight:700; border-right:0;">৳</span>
                        </div>
                        <input type="number" name="paid" id="paidInput"
                               class="form-control" style="border-left:0; border-color:#ffcccc; font-size:16px; font-weight:700;"
                               value="{{ old('paid', $purchase->paid) }}" min="0" step="0.01" required>
                    </div>
                </div>

                <div class="summary-row border-bottom">
                    <span class="summary-label">Due</span>
                    <span class="font-weight-bold" id="dueDisplay"
                          style="font-size:20px; color:#c62828;">৳ {{ number_format($purchase->due,2) }}</span>
                </div>

                <div class="summary-row border-bottom">
                    <span class="summary-label">Service Lines</span>
                    <span class="badge badge-success px-2 py-1" id="lineCountBadge"
                          style="font-size:13px;">{{ count($purchase->lines) }}</span>
                </div>

                <div class="px-4 py-3">
                    <label class="field-label mb-2">
                        <i class="fas fa-university mr-1 text-muted"></i>Bank Account
                        <span class="text-muted" style="font-size:11px; font-weight:400;">(optional)</span>
                    </label>
                    <input type="text" name="bank_account" class="form-control custom-input"
                           value="{{ old('bank_account', $purchase->bank_account) }}"
                           placeholder="e.g. DBBL – 1234567890" style="font-size:13px;">
                    <small class="text-muted" style="font-size:11px;">
                        Purchase amount will be withdrawn from this account.
                    </small>
                </div>

            </div>
        </div>

        <button type="submit" id="submitBtn"
                class="btn btn-block font-weight-bold py-3 mb-2"
                style="background:linear-gradient(135deg,#1a237e,#283593); color:#fff;
                       border-radius:10px; font-size:15px; border:none; letter-spacing:.3px;">
            <i class="fas fa-save mr-2"></i>Update Purchase Bill
        </button>
        <a href="{{ route('bandwidth-buy.purchase.index') }}"
           class="btn btn-block btn-light border py-2"
           style="border-radius:10px; font-size:14px;">
            <i class="fas fa-times mr-1"></i>Cancel
        </a>

    </div>
</div>

</div>
</form>

@endsection


@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
/* include minimal styles from create page to keep design consistent */
.step-circle { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,.25); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0; }
.field-label { font-size: 13px; font-weight: 700; color: #37474f; margin-bottom: 5px; display: block; }
.custom-input { border-color: #d0d7e8; border-radius: 8px !important; font-size: 14px; height: 40px; }
.custom-input:focus { border-color: #3949ab !important; box-shadow: 0 0 0 3px rgba(57,73,171,.12) !important; }
.custom-select-style { border-color: #d0d7e8; border-radius: 8px !important; font-size: 14px; height: 40px; }
.doc-upload-box { border: 2px dashed #c5cae9; border-radius: 8px; background: #f8f9ff; min-height: 72px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.lines-th { padding: 9px 10px !important; font-size: 11px !important; font-weight: 700 !important; text-transform: uppercase; letter-spacing: .5px; color: #2e7d32 !important; }
.svc-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
.remove-line { width: 28px; height: 28px; border-radius: 50%; padding: 0; display: inline-flex; align-items: center; justify-content: center; border: none; background: #ffebee; color: #c62828; }
.remove-line:hover { background: #c62828; color: #fff; }
.summary-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; }
.summary-label { font-size: 13px; color: #78909c; font-weight: 600; }
.summary-value { font-size: 20px; font-weight: 800; color: #263238; }
.datepicker-dropdown { z-index: 9999 !important; }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
$(function () {

    // Init billing date & line datepickers (use mm/dd/yyyy)
    $('#billingDate').datepicker({ format: 'mm/dd/yyyy', autoclose: true });
    $('#linesBody .datepicker').datepicker({ format: 'mm/dd/yyyy', autoclose: true });

    $('#billingDate').on('changeDate', function () {
        if ($('#linesBody tr.line-row').length > 0) {
            if (confirm('Changing billing date will reset all service lines. Continue?')) {
                $('#linesBody tr.line-row').remove();
                $('#linesSummaryFoot').hide();
                $('#emptyLinesRow').show();
                recalc();
            }
        }
    });

    // Document upload preview
    if ('{{ $purchase->document ? true : false }}' === '1') {
        $('#docPlaceholder').hide();
        $('#docPreview').show();
        $('#docImg').attr('src', '{{ $purchase->document ? asset('storage/'.$purchase->document) : '' }}');
        $('#docFileName').text('{{ $purchase->document ? basename($purchase->document) : '' }}');
    }

    $('#docUploadBox').on('click', function (e) { if (!$(e.target).closest('#docRemove').length) $('#docInput').click(); });
    $('#docInput').on('change', function () {
        const file = this.files[0]; if (!file) return;
        $('#docFileName').text(file.name);
        if (file.type.startsWith('image/')) {
            const reader = new FileReader(); reader.onload = e => { $('#docImg').attr('src', e.target.result); $('#docImg').show(); };
            reader.readAsDataURL(file);
        } else { $('#docImg').hide(); }
        $('#docPlaceholder').hide(); $('#docPreview').show();
    });
    $('#docRemove').on('click', function (e) { e.stopPropagation(); $('#docInput').val(''); $('#docPreview').hide(); $('#docPlaceholder').show(); $('#docImg').attr('src','').hide(); });

    // Add service from dropdown
    $(document).on('click', '.svc-pick', function () {
        const id = $(this).data('id'); const name = $(this).data('name');
        if ($('#linesBody tr.line-row[data-service="' + id + '"]').length) { alert('Already added'); return; }
        const billingVal = $('#billingDate').val(); let fromDate = '', toDate = '';
        if (billingVal) { const p = billingVal.split('/'); if (p.length===3) { fromDate = p[0] + '/01/' + p[2]; toDate = p[0] + '/' + p[1] + '/' + p[2]; } }
        const idx = Date.now();
        const row = `
        <tr data-service="${id}" class="line-row" style="animation: fadeSlideIn .25s ease;">
            <td>
                <span class="svc-badge" style="background:#E3F2FD; color:#1565C0;">
                    <i class="fas fa-wifi mr-1" style="font-size:10px;"></i>${name}
                </span>
                <input type="hidden" name="lines[${idx}][service_id]" value="${id}">
            </td>
            <td>
                  <input type="text" name="lines[${idx}][from_date]"
                      class="form-control form-control-sm datepicker line-date"
                      value="${fromDate}" required autocomplete="off" placeholder="MM/DD/YYYY">
            </td>
            <td>
                  <input type="text" name="lines[${idx}][to_date]"
                      class="form-control form-control-sm datepicker line-date"
                      value="${toDate}" required autocomplete="off" placeholder="MM/DD/YYYY">
            </td>
            <td>
                <input type="number" name="lines[${idx}][quantity_mb]"
                       class="form-control form-control-sm line-qty text-right"
                       value="100" min="0" step="0.01" required>
            </td>
            <td>
                <input type="number" name="lines[${idx}][rate]"
                       class="form-control form-control-sm line-rate text-right"
                       value="0" min="0" step="0.01" required>
            </td>
            <td>
                <input type="number" name="lines[${idx}][vat_percent]"
                       class="form-control form-control-sm line-vat text-center"
                       value="5" min="0" max="100" step="0.01" required>
            </td>
            <td>
                <input type="number" name="lines[${idx}][line_total]"
                       class="form-control form-control-sm line-total"
                       value="0" readonly tabindex="-1">
            </td>
            <td class="text-center">
                <button type="button" class="remove-line" title="Remove line">
                    <i class="fas fa-times" style="font-size:11px;"></i>
                </button>
            </td>
        </tr>`;
        $('#emptyLinesRow').hide(); $('#linesBody').append(row); $('#linesSummaryFoot').show();
        $('#linesBody .datepicker:not(.hasDatepicker)').datepicker({ format: 'mm/dd/yyyy', autoclose: true });
        recalc();
    });

    $(document).on('click', '.remove-line', function () { $(this).closest('tr').remove(); if (!$('#linesBody tr.line-row').length) { showEmpty(); $('#linesSummaryFoot').hide(); } recalc(); });

    function computeLine($row) {
        const qty  = parseFloat($row.find('.line-qty').val())  || 0;
        const rate = parseFloat($row.find('.line-rate').val()) || 0;
        const vat  = parseFloat($row.find('.line-vat').val())  || 0;
        const base = qty * rate;
        const tot  = base + (base * vat / 100);
        $row.find('.line-total').val(tot.toFixed(2));
        return tot;
    }

    function recalc() {
        let sub = 0, count = 0;
        $('#linesBody tr.line-row').each(function () { sub += computeLine($(this)); count++; });
        const fmt = v => '৳ ' + v.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        $('#subTotalDisplay').text(fmt(sub)); $('#footSubTotal').text(fmt(sub)); $('#subTotalHidden').val(sub.toFixed(2)); $('#lineCountBadge').text(count);
        updateDue(sub);
    }

    function updateDue(sub) { if (sub === undefined) sub = parseFloat($('#subTotalHidden').val()) || 0; let paid = parseFloat($('#paidInput').val()) || 0; if (paid > sub) { paid = sub; $('#paidInput').val(sub.toFixed(2)); } const due = sub - paid; const fmt = v => '৳ ' + v.toLocaleString('en-US', {minimumFractionDigits:2}); $('#dueDisplay').text(fmt(due)).css('color', due > 0 ? '#c62828' : '#2e7d32'); }

    $(document).on('input', '.line-qty, .line-rate, .line-vat', function () { recalc(); });
    $('#paidInput').on('input', function () { updateDue(); });

    function showEmpty() { $('#emptyLinesRow').show(); }

    // Initial recalc
    recalc();

    $('#purchaseForm').on('submit', function (e) {
        if (!$('#linesBody tr.line-row').length) { e.preventDefault(); alert('Please add at least one service line before saving.'); return; }
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...');
    });

});
</script>
<style>
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(-6px); } to   { opacity: 1; transform: translateY(0); } }
</style>
@stop
