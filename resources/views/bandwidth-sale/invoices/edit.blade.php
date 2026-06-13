{{-- resources/views/bandwidth-sale/invoices/edit.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Edit Invoice — ' . $bwsInvoice->invoice_no)

@section('page_actions')
    <a href="{{ route('bandwidth-sale.invoices.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back to List
    </a>
@endsection

@section('page_content')

<form id="editForm"
      action="{{ route('bandwidth-sale.invoices.update', $bwsInvoice->id) }}"
      method="POST">
@csrf @method('PUT')

<div class="row">
    {{-- ══ LEFT: Main Form ══════════════════════════════ --}}
    <div class="col-md-8">
        <div class="card card-outline card-warning">
            <div class="card-header py-2">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-edit mr-1 text-warning"></i>
                    Edit Bandwidth Sale Invoice
                    <code class="ml-2 text-info">{{ $bwsInvoice->invoice_no }}</code>
                </h6>
            </div>
            <div class="card-body pb-2">
                <div class="row">
                    {{-- Customer --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold small">Customer <span class="text-danger">*</span></label>
                            <select name="bws_customer_id" id="customerSelect" class="form-control select2" required>
                                <option value="">— Select —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                            data-contact="{{ $c->contact_person }}"
                                            data-mobile="{{ $c->mobile_number }}"
                                            {{ $bwsInvoice->bws_customer_id == $c->id ? 'selected' : '' }}>
                                        {{ $c->customer_name }}
                                        @if($c->customer_code)({{ $c->customer_code }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Invoice No (readonly) --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold small">Invoice No</label>
                            <input type="text" class="form-control"
                                   value="{{ $bwsInvoice->invoice_no }}" readonly
                                   style="background:#f8f9fa; font-weight:bold; color:#0073b7;">
                        </div>
                    </div>

                    {{-- Billing Month --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold small">Billing Month <span class="text-danger">*</span></label>
                            <input type="month" name="billing_month" class="form-control" required
                                   value="{{ $bwsInvoice->billing_month }}">
                        </div>
                    </div>

                    {{-- Payment Due --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold small">Payment Due Date</label>
                            <input type="date" name="payment_due" class="form-control"
                                   value="{{ optional($bwsInvoice->payment_due)->format('Y-m-d') }}">
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold small">Status</label>
                            <select name="status" class="form-control">
                                @foreach(['unpaid','paid','partial','overdue'] as $s)
                                    <option value="{{ $s }}" {{ $bwsInvoice->status == $s ? 'selected':'' }}>
                                        {{ ucfirst($s) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Daily Basis --}}
                <div class="mb-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="dailyBasis"
                               name="daily_basis" value="1"
                               {{ $bwsInvoice->daily_basis ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold small" for="dailyBasis">
                            Daily Basis Calculation
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- LINE ITEMS --}}
        <div class="card card-outline card-secondary">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-list mr-1 text-secondary"></i> Invoice Items
                </h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow">
                    <i class="fas fa-plus mr-1"></i> Add New
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" id="itemsTable">
                        <thead style="background:#2c3e50; color:#fff;">
                            <tr>
                                <th style="min-width:150px;">Item</th>
                                <th style="min-width:180px;">Description</th>
                                <th style="width:75px;">Unit</th>
                                <th style="width:80px;">Quantity</th>
                                <th style="width:90px;">Rate</th>
                                <th style="width:75px;">VAT(%)</th>
                                <th class="daily-col" style="width:110px;">From Date</th>
                                <th class="daily-col" style="width:110px;">To Date</th>
                                <th style="width:100px;">Total</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                        <tfoot>
                            <tr style="background:#f8f9fa;">
                                <td colspan="8" class="text-right font-weight-bold pr-3">Total</td>
                                <td class="font-weight-bold text-right pr-3 text-primary" id="invoiceTotal" style="font-size:15px;">
                                    {{ number_format($bwsInvoice->grand_total, 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ RIGHT: Summary ═════════════════════════════════ --}}
    <div class="col-md-4">
        <div class="card card-outline card-info" style="position:sticky; top:70px;">
            <div class="card-header py-2">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-calculator mr-1 text-info"></i> Summary
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-3">
                    <tr>
                        <td class="text-muted">Sub Total</td>
                        <td class="text-right font-weight-bold" id="sumSubtotal">
                            ৳ {{ number_format($bwsInvoice->total_amount, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">VAT</td>
                        <td class="text-right" id="sumVat">
                            ৳ {{ number_format($bwsInvoice->vat_amount, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Discount</td>
                        <td class="text-right">
                            <div class="input-group input-group-sm" style="width:120px; float:right;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="discount" id="discountInput"
                                       class="form-control text-right"
                                       value="{{ $bwsInvoice->discount }}"
                                       min="0" step="0.01" oninput="recalcAll()">
                            </div>
                        </td>
                    </tr>
                    <tr style="border-top:2px solid #dee2e6;">
                        <td class="font-weight-bold" style="font-size:15px;">Grand Total</td>
                        <td class="text-right font-weight-bold text-primary" id="sumGrand" style="font-size:16px;">
                            ৳ {{ number_format($bwsInvoice->grand_total, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
            <div class="card-footer py-2">
                <button type="submit" class="btn btn-warning btn-block" id="btnSave">
                    <i class="fas fa-save mr-1"></i> Update Invoice
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Remarks --}}
<div class="card card-outline card-secondary">
    <div class="card-header py-2">
        <h6 class="mb-0 font-weight-bold">
            <i class="fas fa-sticky-note mr-1 text-secondary"></i> Remarks / Note
        </h6>
    </div>
    <div class="card-body">
        <textarea name="notes" class="form-control" rows="3"
                  placeholder="Optional...">{{ $bwsInvoice->notes }}</textarea>
    </div>
</div>

<input type="hidden" name="total_amount" id="hidTotal">
<input type="hidden" name="vat_amount"   id="hidVat">
<input type="hidden" name="grand_total"  id="hidGrand">
<input type="hidden" name="due_amount"   id="hidDue">
<input type="hidden" name="items_json"   id="hidItems">

</form>

@endsection

@section('extra_css')
<style>
#itemsTable thead th { font-size:11px; font-weight:700; white-space:nowrap; padding:9px 8px; }
#itemsTable tbody td { padding:5px 6px; vertical-align:middle; }
</style>
@endsection

@section('js')
<script>
const CSRF     = '{{ csrf_token() }}';
const ITEMS_LIST = @json($items ?? []);
let rowIndex = 0;

// ── Existing items from DB ────────────────────────────────────
const EXISTING = @json($bwsInvoice->items->map(fn($i) => [
    'item_name'   => $i->item_name,
    'description' => $i->description,
    'unit'        => $i->unit,
    'quantity'    => $i->quantity,
    'rate'        => $i->rate,
    'vat'         => $i->vat_percent,
    'from_date'   => $i->from_date?->format('Y-m-d'),
    'to_date'     => $i->to_date?->format('Y-m-d'),
    'total'       => $i->total,
]));

$(function () {
    if (EXISTING.length > 0) {
        EXISTING.forEach(function(row) {
            addItemRow(row);
        });
    } else {
        addItemRow();
    }
    toggleDailyCols();
    $('#dailyBasis').on('change', toggleDailyCols);
    $('select.select2').select2({ width:'100%' });
});

function toggleDailyCols() {
    var on = $('#dailyBasis').is(':checked');
    $('.daily-col').toggle(on);
}

function addItemRow(data) {
    data = data || {};
    var i = rowIndex++;
    var options = ITEMS_LIST.map(function(item) {
        var sel = (data.item_name == item.id) ? 'selected' : '';
        return `<option value="${item.id}" data-unit="${item.unit||''}"
                    data-rate="${item.rate||0}" ${sel}>${item.name}</option>`;
    }).join('');

    var row = `
    <tr id="row-${i}">
        <td>
            <select class="form-control form-control-sm item-sel" data-row="${i}"
                    onchange="onItemChange(${i})">
                <option value="">Select</option>${options}
            </select>
        </td>
        <td><input type="text" class="form-control form-control-sm item-desc"
                   value="${data.description||''}" placeholder="Description"></td>
        <td><input type="text" class="form-control form-control-sm item-unit"
                   value="${data.unit||''}" placeholder="Unit" readonly></td>
        <td><input type="number" class="form-control form-control-sm item-qty text-right"
                   value="${data.quantity||1}" min="0" step="any" oninput="recalcRow(${i})"></td>
        <td><input type="number" class="form-control form-control-sm item-rate text-right"
                   value="${data.rate||0}" min="0" step="any" oninput="recalcRow(${i})"></td>
        <td><input type="number" class="form-control form-control-sm item-vat text-right"
                   value="${data.vat||0}" min="0" max="100" oninput="recalcRow(${i})"></td>
        <td class="daily-col"><input type="date" class="form-control form-control-sm item-from"
                   value="${data.from_date||''}" oninput="recalcRow(${i})"></td>
        <td class="daily-col"><input type="date" class="form-control form-control-sm item-to"
                   value="${data.to_date||''}" oninput="recalcRow(${i})"></td>
        <td><input type="text" class="form-control form-control-sm item-total text-right font-weight-bold"
                   value="${parseFloat(data.total||0).toFixed(2)}" readonly
                   style="background:#f8f9fa; color:#0073b7;"></td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-outline-danger"
                    onclick="removeRow(${i})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`;

    $('#itemsBody').append(row);
    var on = $('#dailyBasis').is(':checked');
    $(`#row-${i} .daily-col`).toggle(on);
    recalcRow(i);
}

function onItemChange(i) {
    var opt  = $(`#row-${i} .item-sel option:selected`);
    $(`#row-${i} .item-unit`).val(opt.data('unit') || '');
    $(`#row-${i} .item-rate`).val(parseFloat(opt.data('rate') || 0).toFixed(2));
    recalcRow(i);
}

function recalcRow(i) {
    var qty  = parseFloat($(`#row-${i} .item-qty`).val())  || 0;
    var rate = parseFloat($(`#row-${i} .item-rate`).val()) || 0;
    var vat  = parseFloat($(`#row-${i} .item-vat`).val())  || 0;

    if ($('#dailyBasis').is(':checked')) {
        var from = $(`#row-${i} .item-from`).val();
        var to   = $(`#row-${i} .item-to`).val();
        if (from && to) {
            qty = Math.max(0, (new Date(to) - new Date(from)) / 86400000 + 1);
            $(`#row-${i} .item-qty`).val(qty);
        }
    }

    var sub   = qty * rate;
    var total = sub + sub * (vat / 100);
    $(`#row-${i} .item-total`).val(total.toFixed(2));
    recalcAll();
}

function recalcAll() {
    var sub = 0, vat = 0;
    $('#itemsBody tr').each(function () {
        var qty  = parseFloat($(this).find('.item-qty').val())  || 0;
        var rate = parseFloat($(this).find('.item-rate').val()) || 0;
        var v    = parseFloat($(this).find('.item-vat').val())  || 0;
        var s    = qty * rate;
        sub += s; vat += s * (v / 100);
    });
    var discount = parseFloat($('#discountInput').val()) || 0;
    var grand    = sub + vat - discount;

    $('#invoiceTotal').text(grand.toFixed(2));
    $('#sumSubtotal').text('৳ ' + sub.toFixed(2));
    $('#sumVat').text('৳ ' + vat.toFixed(2));
    $('#sumGrand').text('৳ ' + grand.toFixed(2));
    $('#hidTotal').val(sub.toFixed(2));
    $('#hidVat').val(vat.toFixed(2));
    $('#hidGrand').val(grand.toFixed(2));
    $('#hidDue').val(grand.toFixed(2));
}

function removeRow(i) {
    if ($('#itemsBody tr').length <= 1) return;
    $(`#row-${i}`).remove();
    recalcAll();
}

$('#btnAddRow').on('click', function () {
    addItemRow();
    toggleDailyCols();
});

$('#editForm').on('submit', function () {
    var items = [];
    $('#itemsBody tr').each(function () {
        items.push({
            item_id:     $(this).find('.item-sel').val(),
            description: $(this).find('.item-desc').val(),
            unit:        $(this).find('.item-unit').val(),
            quantity:    $(this).find('.item-qty').val(),
            rate:        $(this).find('.item-rate').val(),
            vat:         $(this).find('.item-vat').val(),
            from_date:   $(this).find('.item-from').val(),
            to_date:     $(this).find('.item-to').val(),
            total:       $(this).find('.item-total').val(),
        });
    });
    $('#hidItems').val(JSON.stringify(items));
    $('#btnSave').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
});
</script>
@endsection
