@extends('adminlte::page')
@section('title', 'Purchase Add')

@section('content_header')
    <h1 class="m-0 text-dark">Purchase Add</h1>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span class="font-weight-bold">Purchase Add</span>
        <a href="{{ route('bandwidth-buy.purchase.index') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
    <div class="card-body">

        <form action="{{ route('bandwidth-buy.purchase.store') }}" method="POST" enctype="multipart/form-data" id="purchaseForm">
            @csrf

            {{-- ── Header Row ─────────────────────────────────────────────── --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Provider <span class="text-danger">(require)</span></label>
                    <select name="provider_id" class="form-control select2" required id="providerSelect">
                        <option value=""></option>
                        @foreach($providers as $prov)
                            <option value="{{ $prov->id }}" {{ old('provider_id') == $prov->id ? 'selected':'' }}>
                                {{ $prov->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Invoice No <span class="text-danger">(require)</span></label>
                    <input type="text" name="invoice_no" class="form-control"
                           value="{{ old('invoice_no') }}" required>
                </div>
                <div class="col-md-3">
                    <label>Billing Date <span class="text-danger">(require)</span></label>
                    <input type="text" name="billing_date" id="billingDate" class="form-control datepicker"
                           value="{{ old('billing_date', now()->format('m/d/Y')) }}" required autocomplete="off">
                </div>
                <div class="col-md-3">
                    <label>Document <span class="text-muted">(optional)</span></label>
                    <input type="file" name="document" class="form-control-file"
                           accept=".jpg,.jpeg,.png,.pdf" id="docInput">
                    <div id="docPreview" class="mt-1" style="display:none;">
                        <img id="docImg" src="" style="height:60px;" alt="preview">
                    </div>
                </div>
            </div>

            {{-- ── Service Select ─────────────────────────────────────────── --}}
            <div class="form-group">
                <label>Select Service</label>
                <select id="serviceSelect" class="form-control" style="max-width:300px;">
                    <option value="">Select One</option>
                    @foreach($services as $svc)
                        <option value="{{ $svc->id }}" data-name="{{ $svc->name }}">
                            {{ $svc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ── Warning about billing date reset ───────────────────────── --}}
            <p class="text-danger font-weight-bold">
                Note: By changing Billing Date all data will be reset
            </p>

            {{-- ── Line Items Table ────────────────────────────────────────── --}}
            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="linesTable">
                    <thead style="background:#5a6268; color:#fff;">
                        <tr>
                            <th>Service</th>
                            <th style="min-width:130px;">From Date</th>
                            <th style="min-width:130px;">To Date</th>
                            <th style="min-width:110px;">Quantity(MB)</th>
                            <th style="min-width:110px;">Rate(TK)</th>
                            <th style="min-width:90px;">Vat(%)</th>
                            <th style="min-width:140px;">Value(Line Total)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="linesBody">
                        {{-- rows added dynamically --}}
                    </tbody>
                </table>
            </div>

            {{-- ── Totals ──────────────────────────────────────────────────── --}}
            <div class="row justify-content-end mt-3">
                <div class="col-md-5">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th>Sub Total</th>
                            <td>
                                <input type="text" id="subTotalDisplay" class="form-control form-control-sm"
                                       readonly value="0.00" style="background:#f4f4f4;">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Paid
                                <small class="text-danger d-block font-weight-normal">
                                    (The paid amount will not be better than the total amount)
                                </small>
                            </th>
                            <td>
                                <input type="number" name="paid" id="paidInput" class="form-control form-control-sm"
                                       value="{{ old('paid', 0) }}" min="0" step="0.01" required>
                            </td>
                        </tr>
                        <tr>
                            <th>Due</th>
                            <td>
                                <input type="text" id="dueDisplay" class="form-control form-control-sm"
                                       readonly value="0.00" style="background:#f4f4f4;">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Bank Account
                                <small class="text-muted d-block font-weight-normal">(Optional)</small>
                                <small class="text-muted font-weight-normal">
                                    If filled, purchase amount will be withdrawn from this account.
                                </small>
                            </th>
                            <td>
                                <input type="text" name="bank_account"
                                       class="form-control form-control-sm"
                                       value="{{ old('bank_account') }}"
                                       placeholder="e.g. Dutch Bangla Bank - 1234567890">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary px-5" id="submitBtn">Submit</button>
            </div>

        </form>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
    #linesBody tr td { vertical-align: middle; }
</style>
@endpush

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
$(function () {
    // ── Datepicker ────────────────────────────────────────────────────────────
    $('.datepicker').datepicker({ format: 'mm/dd/yyyy', autoclose: true });

    // When billing date changes → clear all lines
    $('#billingDate').on('changeDate', function () {
        if ($('#linesBody tr').length > 0) {
            if (confirm('Changing the billing date will reset all service lines. Continue?')) {
                $('#linesBody').empty();
                recalc();
            } else {
                // Restore old value — just leave as-is
            }
        }
    });

    // ── Document preview ──────────────────────────────────────────────────────
    $('#docInput').on('change', function () {
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                $('#docImg').attr('src', e.target.result);
                $('#docPreview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#docPreview').hide();
        }
    });

    // ── Add service row when service is selected ──────────────────────────────
    $('#serviceSelect').on('change', function () {
        const id   = $(this).val();
        const name = $(this).find('option:selected').data('name');
        if (!id) return;

        // Check duplicate
        if ($('#linesBody').find(`tr[data-service="${id}"]`).length) {
            alert('This service is already added.');
            $(this).val('');
            return;
        }

        const billingDate = $('#billingDate').val();
        // Default from_date = 1st of billing month, to_date = billing date
        let fromDate = billingDate, toDate = billingDate;
        if (billingDate) {
            const parts = billingDate.split('/'); // mm/dd/yyyy
            if (parts.length === 3) {
                fromDate = parts[0] + '-01-' + parts[2]; // mm-01-yyyy
                toDate   = parts[0] + '-' + parts[1] + '-' + parts[2];
            }
        }

        const idx  = Date.now(); // unique index
        const row  = `
        <tr data-service="${id}">
            <td>
                ${name}
                <input type="hidden" name="lines[${idx}][service_id]" value="${id}">
            </td>
            <td>
                <input type="text" name="lines[${idx}][from_date]" class="form-control form-control-sm datepicker line-date"
                       value="${fromDate}" required autocomplete="off" style="min-width:120px;">
            </td>
            <td>
                <input type="text" name="lines[${idx}][to_date]" class="form-control form-control-sm datepicker line-date"
                       value="${toDate}" required autocomplete="off" style="min-width:120px;">
            </td>
            <td>
                <input type="number" name="lines[${idx}][quantity_mb]" class="form-control form-control-sm line-qty"
                       value="100" min="0" step="0.01" required style="min-width:90px;">
            </td>
            <td>
                <input type="number" name="lines[${idx}][rate]" class="form-control form-control-sm line-rate"
                       value="0" min="0" step="0.01" required style="min-width:90px;">
            </td>
            <td>
                <input type="number" name="lines[${idx}][vat_percent]" class="form-control form-control-sm line-vat"
                       value="5" min="0" max="100" step="0.01" required style="min-width:70px;">
            </td>
            <td>
                <input type="number" name="lines[${idx}][line_total]" class="form-control form-control-sm line-total"
                       value="0" readonly style="background:#f4f4f4; min-width:120px;">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-line">
                    <i class="fas fa-minus-circle"></i>
                </button>
            </td>
        </tr>`;

        $('#linesBody').append(row);

        // Init datepicker on new inputs
        $('#linesBody .datepicker').datepicker({ format: 'mm-dd-yyyy', autoclose: true });

        $(this).val('');
        recalc();
    });

    // ── Remove line ───────────────────────────────────────────────────────────
    $(document).on('click', '.remove-line', function () {
        $(this).closest('tr').remove();
        recalc();
    });

    // ── Recalculate line totals and sub-total ─────────────────────────────────
    function computeLineTotal($row) {
        const qty  = parseFloat($row.find('.line-qty').val()) || 0;
        const rate = parseFloat($row.find('.line-rate').val()) || 0;
        const vat  = parseFloat($row.find('.line-vat').val()) || 0;
        const base = qty * rate;
        const total = base + (base * vat / 100);
        $row.find('.line-total').val(total.toFixed(2));
        return total;
    }

    function recalc() {
        let sub = 0;
        $('#linesBody tr').each(function () {
            sub += computeLineTotal($(this));
        });
        $('#subTotalDisplay').val(sub.toFixed(2));
        updateDue();
    }

    function updateDue() {
        const sub  = parseFloat($('#subTotalDisplay').val()) || 0;
        let paid   = parseFloat($('#paidInput').val()) || 0;
        if (paid > sub) {
            paid = sub;
            $('#paidInput').val(sub.toFixed(2));
        }
        $('#dueDisplay').val((sub - paid).toFixed(2));
    }

    $(document).on('input', '.line-qty, .line-rate, .line-vat', function () { recalc(); });
    $('#paidInput').on('input', updateDue);

    // ── Validate at least one line before submit ──────────────────────────────
    $('#purchaseForm').on('submit', function (e) {
        if ($('#linesBody tr').length === 0) {
            e.preventDefault();
            alert('Please add at least one service line before submitting.');
        }
    });
});
</script>
@endpush
