{{-- resources/views/bandwidth-sale/recurring/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Bandwidth Sale — Recurring Invoices')

@section('page_actions')
    <button class="btn btn-primary btn-sm" id="btnAddRecurring">
        <i class="fas fa-plus mr-1"></i> + Recurring Invoice
    </button>
@endsection

@section('page_content')

{{-- ══ STAT CARDS ═══════════════════════════════════════════════ --}}
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#0073b7,#005a8e);">
            <div><div class="bs-label">Total Recurring</div><div class="bs-val">{{ $invoices->total() }}</div></div>
            <i class="fas fa-redo-alt bs-icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#00a65a,#007a42);">
            <div><div class="bs-label">Active</div><div class="bs-val">{{ $invoices->getCollection()->where('status','unpaid')->count() }}</div></div>
            <i class="fas fa-check-circle bs-icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#6f42c1,#4e2d8a);">
            <div><div class="bs-label">Total Amount</div><div class="bs-val">৳ {{ number_format($invoices->getCollection()->sum('grand_total')) }}</div></div>
            <i class="fas fa-money-bill-wave bs-icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#f39c12,#c07d0a);">
            <div><div class="bs-label">Total Due</div><div class="bs-val">৳ {{ number_format($invoices->getCollection()->sum('due_amount')) }}</div></div>
            <i class="fas fa-balance-scale bs-icon"></i>
        </div>
    </div>
</div>

{{-- ══ TABLE ═════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0 font-weight-bold">
            <i class="fas fa-redo-alt mr-1"></i> Recurring Invoice List
            <span class="badge badge-info ml-1">{{ $invoices->total() }}</span>
        </h6>
        <div class="d-flex align-items-center">
            <input type="text" id="tableSearch" class="form-control form-control-sm mr-2"
                   placeholder="Search..." style="width:180px;" autocomplete="off">
            <select id="perPage" class="form-control form-control-sm" style="width:70px;">
                @foreach([10,25,50] as $pp)
                    <option value="{{ $pp }}" {{ request('per_page',10)==$pp?'selected':'' }}>{{ $pp }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="recurringTable">
                <thead style="background:#2c3e50;color:#fff;">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Customer (POP Name)</th>
                        <th>Invoice No</th>
                        <th class="text-center">Start Date</th>
                        <th class="text-center">End Date</th>
                        <th class="text-center">Repeat Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Bill Amount</th>
                        <th style="width:100px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $i => $inv)
                    <tr>
                        <td>{{ $invoices->firstItem() + $i }}</td>
                        <td>
                            <a href="{{ route('bandwidth-sale.customers.show', $inv->bws_customer_id) }}"
                               class="font-weight-bold text-primary">
                                {{ $inv->bwsCustomer->customer_name ?? '—' }}
                            </a>
                            @if($inv->bwsCustomer->customer_code)
                                <br><small class="text-muted">{{ $inv->bwsCustomer->customer_code }}</small>
                            @endif
                        </td>
                        <td><code class="text-info">{{ $inv->invoice_no }}</code></td>
                        <td class="text-center">
                            {{ optional($inv->recurring_start)->format('d M Y') ?? '—' }}
                        </td>
                        <td class="text-center">
                            {{ optional($inv->recurring_end)->format('d M Y') ?? '—' }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info px-2 py-1" style="font-size:13px;">
                                {{ $inv->repeat_date ?? '—' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $inv->status === 'unpaid' ? 'success' : 'secondary' }} px-2">
                                {{ $inv->status === 'unpaid' ? 'Enabled' : ucfirst($inv->status) }}
                            </span>
                        </td>
                        <td class="text-right font-weight-bold">
                            {{ number_format($inv->grand_total, 2) }}
                        </td>
                        <td class="text-center" style="white-space:nowrap;">
                            <button class="btn btn-xs btn-light border btn-edit-rec"
                                    data-id="{{ $inv->id }}" title="Edit">
                                <i class="fas fa-edit text-success"></i>
                            </button>
                            <button class="btn btn-xs btn-light border btn-del-rec"
                                    data-id="{{ $inv->id }}"
                                    data-no="{{ $inv->invoice_no }}"
                                    title="Delete">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-redo-alt fa-3x d-block mb-3 opacity-50"></i>
                            No recurring invoices found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($invoices->hasPages())
    <div class="card-footer py-2">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }}
            </small>
            {{ $invoices->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>


{{-- ══════════════════════════════════════════════════════════════
     ADD / EDIT RECURRING INVOICE MODAL
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="recurringModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" id="rec_modal_header" style="background:#0073b7;color:#fff;">
                <h5 class="modal-title" id="rec_modal_title">
                    <i class="fas fa-plus-circle mr-2"></i> Create Recurring Invoice
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pb-1" style="max-height:82vh;overflow-y:auto;">

                {{-- ── Invoice Details ──────────────────────── --}}
                <div class="section-title">
                    <i class="fas fa-file-invoice mr-1 text-primary"></i> Invoice Details
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">Customer <span class="text-danger">*</span></label>
                            <select id="rec_customer" class="form-control form-control-sm" required>
                                <option value="">— Select Customer —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                            data-contact="{{ $c->contact_person }}"
                                            data-mobile="{{ $c->mobile_number }}">
                                        {{ $c->customer_name }}
                                        @if($c->customer_code)({{ $c->customer_code }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="small font-weight-bold">Invoice No</label>
                            <input type="text" id="rec_no" class="form-control form-control-sm"
                                   readonly style="background:#f8f9fa;font-weight:bold;color:#0073b7;"
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="small font-weight-bold">Billing Month <span class="text-danger">*</span></label>
                            <input type="month" id="rec_month" class="form-control form-control-sm"
                                   value="{{ now()->format('Y-m') }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="small font-weight-bold">Repeat Date <span class="text-danger">*</span></label>
                            <select id="rec_repeat" class="form-control form-control-sm">
                                <option value="">Select Day</option>
                                @for($d = 1; $d <= 28; $d++)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="small font-weight-bold">Payment Due</label>
                            <select id="rec_due" class="form-control form-control-sm">
                                <option value="">Select Day</option>
                                @for($d = 1; $d <= 28; $d++)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="small font-weight-bold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" id="rec_start" class="form-control form-control-sm"
                                   value="{{ now()->format('Y-m-d') }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="small font-weight-bold">End Date</label>
                            <input type="date" id="rec_end" class="form-control form-control-sm"
                                   autocomplete="off" placeholder="Optional">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group pt-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input"
                                       id="rec_daily" value="1" checked>
                                <label class="custom-control-label small font-weight-bold" for="rec_daily">
                                    Daily Basis Calculation
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Invoice Items (Full Width) ───────────── --}}
                <div class="section-title mt-1">
                    <i class="fas fa-list mr-1 text-secondary"></i> Invoice Items
                    <button type="button" class="btn btn-xs btn-outline-primary float-right"
                            id="btnAddRecItem">
                        <i class="fas fa-plus mr-1"></i> Add Row
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" id="recItemsTable" style="width:100%;">
                        <thead style="background:#2c3e50;color:#fff;">
                            <tr>
                                <th style="min-width:160px;">Item / Service</th>
                                <th style="min-width:150px;">Description</th>
                                <th style="width:65px;min-width:60px;">Unit</th>
                                <th style="width:75px;min-width:65px;">Qty</th>
                                <th style="width:90px;min-width:75px;">Rate</th>
                                <th style="width:70px;min-width:60px;">VAT%</th>
                                <th class="rec-daily-col" style="width:110px;">From</th>
                                <th class="rec-daily-col" style="width:110px;">To</th>
                                <th style="width:100px;">Total</th>
                                <th style="width:36px;"></th>
                            </tr>
                        </thead>
                        <tbody id="recItemsBody"></tbody>
                        <tfoot>
                            <tr style="background:#f8f9fa;">
                                <td colspan="8" class="text-right font-weight-bold pr-3"
                                    id="recTotalLabel">Invoice Total</td>
                                <td class="text-right font-weight-bold text-primary pr-2"
                                    id="recInvTotal" style="font-size:14px;">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- ── Notes + Summary ──────────────────────── --}}
                <div class="row mt-3">
                    <div class="col-md-7">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Remarks / Notes</label>
                            <textarea id="rec_notes" class="form-control form-control-sm" rows="3"
                                      placeholder="Optional..." autocomplete="off"></textarea>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card card-outline card-info mb-0">
                            <div class="card-header py-2">
                                <h6 class="mb-0 font-weight-bold small">
                                    <i class="fas fa-calculator mr-1 text-info"></i> Summary
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-borderless mb-0" style="font-size:13px;">
                                    <tr>
                                        <td class="text-muted pl-3">Sub Total</td>
                                        <td class="text-right pr-3 font-weight-bold" id="recSumSub">৳ 0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-3">VAT</td>
                                        <td class="text-right pr-3" id="recSumVat">৳ 0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-3">Discount</td>
                                        <td class="text-right pr-3">
                                            <div class="input-group input-group-sm justify-content-end">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text py-0">৳</span>
                                                </div>
                                                <input type="number" id="rec_discount"
                                                       class="form-control form-control-sm text-right"
                                                       style="max-width:100px;"
                                                       value="0" min="0" step="0.01"
                                                       oninput="recRecalcAll()" autocomplete="off">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr style="border-top:2px solid #dee2e6;">
                                        <td class="font-weight-bold pl-3" style="font-size:14px;">Grand Total</td>
                                        <td class="text-right pr-3 font-weight-bold text-primary"
                                            id="recSumGrand" style="font-size:15px;">৳ 0.00</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- end modal-body --}}

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveRecurring">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

@endsection


@section('extra_css')
<style>
.bws-stat { border-radius:8px; padding:14px 18px; color:#fff;
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:16px; box-shadow:0 3px 10px rgba(0,0,0,.15); }
.bs-label { font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.6px; color:rgba(255,255,255,.85); margin-bottom:4px; }
.bs-val   { font-size:26px; font-weight:700; line-height:1.1; }
.bws-stat .bs-icon { font-size:44px; color:rgba(255,255,255,.18); }

#recurringTable thead th { font-size:12px; font-weight:700; white-space:nowrap; padding:10px; }
#recurringTable tbody td { font-size:13px; padding:9px 10px; vertical-align:middle; }
#recurringTable tbody tr:hover { background:#f0f7ff; }

#recItemsTable thead th { font-size:11px; font-weight:700; white-space:nowrap; padding:8px 6px; }
#recItemsTable tbody td { padding:4px 5px; vertical-align:middle; }

.section-title { font-size:13px; font-weight:700; color:#2c3e50;
    padding:7px 12px; background:#f0f4f8; border-left:4px solid #0073b7;
    border-radius:4px; margin-bottom:10px; }
</style>
@endsection


@section('js')
<script>
const CSRF      = '{{ csrf_token() }}';
let editRecId   = null;
let recRowIdx   = 0;

const BWS_SERVICES = @json($bwsServices ?? []);

function toastOk(msg)  { Swal.fire({toast:true,position:'top-end',icon:'success',title:msg,showConfirmButton:false,timer:2500}); }
function toastErr(msg) { Swal.fire({toast:true,position:'top-end',icon:'error',  title:msg,showConfirmButton:false,timer:3500}); }

// ── Per page ──────────────────────────────────────────────────
$('#perPage').on('change', function () {
    var url = new URL(window.location.href);
    url.searchParams.set('per_page', $(this).val());
    window.location.href = url.toString();
});

// ── Live search ───────────────────────────────────────────────
$('#tableSearch').on('keyup', function () {
    var val = $(this).val().toLowerCase();
    $('#recurringTable tbody tr').each(function () {
        $(this).toggle($(this).text().toLowerCase().includes(val));
    });
});

// ── Toggle daily cols ─────────────────────────────────────────
function recToggleDailyCols() {
    var on = $('#rec_daily').is(':checked');
    $('.rec-daily-col').toggle(on);
    $('#recTotalLabel').attr('colspan', on ? 8 : 6);
}
$('#rec_daily').on('change', recToggleDailyCols);

// ── Service change ────────────────────────────────────────────
function onRecServiceChange(i) {
    var opt = $(`#rrow-${i} .item-service option:selected`);
    $(`#rrow-${i} .item-unit`).val(opt.data('unit') || '');
    recRecalcRow(i);
}

// ── Add item row ──────────────────────────────────────────────
function addRecItemRow(data) {
    data = data || {};
    var i = recRowIdx++;

    var serviceOpts = '<option value="">— Select Service —</option>';
    BWS_SERVICES.forEach(function(s) {
        var sel = (data.item_name == s.id) ? 'selected' : '';
        serviceOpts += `<option value="${s.id}" data-unit="${s.unit||''}" ${sel}>${s.name}</option>`;
    });
    if (data.item_name && isNaN(data.item_name)) {
        serviceOpts += `<option value="${data.item_name}" selected>${data.item_name}</option>`;
    }

    var row = `
    <tr id="rrow-${i}">
        <td>
            <select class="form-control form-control-sm item-service"
                    onchange="onRecServiceChange(${i})" style="min-width:150px;">
                ${serviceOpts}
            </select>
        </td>
        <td><input type="text" class="form-control form-control-sm item-desc"
                   value="${data.description||''}" placeholder="Description" autocomplete="off"></td>
        <td><input type="text" class="form-control form-control-sm item-unit"
                   value="${data.unit||''}" placeholder="Unit" autocomplete="off"
                   style="min-width:55px;"></td>
        <td><input type="number" class="form-control form-control-sm item-qty text-right"
                   value="${data.quantity||1}" min="0" step="any"
                   oninput="recRecalcRow(${i})" autocomplete="off"
                   style="min-width:60px;"></td>
        <td><input type="number" class="form-control form-control-sm item-rate text-right"
                   value="${data.rate||0}" min="0" step="any"
                   oninput="recRecalcRow(${i})" autocomplete="off"
                   style="min-width:70px;"></td>
        <td><input type="number" class="form-control form-control-sm item-vat text-right"
                   value="${data.vat_percent||0}" min="0" max="100"
                   oninput="recRecalcRow(${i})" autocomplete="off"
                   style="min-width:55px;"></td>
        <td class="rec-daily-col"><input type="date" class="form-control form-control-sm item-from"
                   value="${data.from_date||''}" oninput="recRecalcRow(${i})"></td>
        <td class="rec-daily-col"><input type="date" class="form-control form-control-sm item-to"
                   value="${data.to_date||''}" oninput="recRecalcRow(${i})"></td>
        <td><input type="text" class="form-control form-control-sm item-total text-right font-weight-bold"
                   value="${parseFloat(data.total||0).toFixed(2)}" readonly
                   style="background:#f8f9fa;color:#0073b7;min-width:80px;"></td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-outline-danger"
                    onclick="removeRecRow(${i})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`;
    $('#recItemsBody').append(row);
    var on = $('#rec_daily').is(':checked');
    $(`#rrow-${i} .rec-daily-col`).toggle(on);
    recRecalcRow(i);
}

function recRecalcRow(i) {
    var qty  = parseFloat($(`#rrow-${i} .item-qty`).val())  || 0;
    var rate = parseFloat($(`#rrow-${i} .item-rate`).val()) || 0;
    var vat  = parseFloat($(`#rrow-${i} .item-vat`).val())  || 0;
    if ($('#rec_daily').is(':checked')) {
        var from = $(`#rrow-${i} .item-from`).val();
        var to   = $(`#rrow-${i} .item-to`).val();
        if (from && to) {
            qty = Math.max(0, (new Date(to)-new Date(from))/86400000+1);
            $(`#rrow-${i} .item-qty`).val(qty);
        }
    }
    var sub   = qty * rate;
    var total = sub + sub*(vat/100);
    $(`#rrow-${i} .item-total`).val(total.toFixed(2));
    recRecalcAll();
}

function recRecalcAll() {
    var sub=0, vat=0;
    $('#recItemsBody tr').each(function() {
        var q = parseFloat($(this).find('.item-qty').val())  || 0;
        var r = parseFloat($(this).find('.item-rate').val()) || 0;
        var v = parseFloat($(this).find('.item-vat').val())  || 0;
        var s = q*r; sub+=s; vat+=s*(v/100);
    });
    var disc  = parseFloat($('#rec_discount').val()) || 0;
    var grand = sub + vat - disc;
    $('#recInvTotal').text(grand.toFixed(2));
    $('#recSumSub').text('৳ '+sub.toFixed(2));
    $('#recSumVat').text('৳ '+vat.toFixed(2));
    $('#recSumGrand').text('৳ '+grand.toFixed(2));
}

function removeRecRow(i) {
    if ($('#recItemsBody tr').length<=1) return;
    $(`#rrow-${i}`).remove(); recRecalcAll();
}

$('#btnAddRecItem').on('click', function() { addRecItemRow(); recToggleDailyCols(); });

function collectRecItems() {
    var items=[];
    $('#recItemsBody tr').each(function() {
        items.push({
            item_name:   $(this).find('.item-service').val(),
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
    return JSON.stringify(items);
}

// ── Fetch next invoice no ─────────────────────────────────────
function fetchRecNo() {
    $.get('/bandwidth-sale/invoices/next-no', function (r) {
        if (r.invoice_no) $('#rec_no').val(r.invoice_no);
    });
}

// ── Reset modal ───────────────────────────────────────────────
function resetRecModal() {
    editRecId = null;
    $('#rec_modal_header').css('background','#0073b7');
    $('#rec_modal_title').html('<i class="fas fa-plus-circle mr-2"></i> Create Recurring Invoice');
    $('#btnSaveRecurring').html('<i class="fas fa-save mr-1"></i> Save');
    $('#rec_customer').val('').trigger('change');
    $('#rec_month').val('{{ now()->format("Y-m") }}');
    $('#rec_repeat').val('');
    $('#rec_due').val('');
    $('#rec_start').val('{{ now()->format("Y-m-d") }}');
    $('#rec_end').val('');
    $('#rec_daily').prop('checked', true);
    $('#rec_discount').val(0);
    $('#rec_notes').val('');
    $('#recItemsBody').empty(); recRowIdx=0;
    addRecItemRow(); recToggleDailyCols();
    fetchRecNo(); recRecalcAll();
}

// ── OPEN ADD ──────────────────────────────────────────────────
$('#btnAddRecurring').on('click', function() {
    resetRecModal();
    $('#recurringModal').modal('show');
    setTimeout(() => $('#rec_customer').select2({
        dropdownParent: $('#recurringModal'), width:'100%'
    }), 200);
});

// ── OPEN EDIT ─────────────────────────────────────────────────
$(document).on('click', '.btn-edit-rec', function() {
    var id = $(this).data('id');
    editRecId = id;

    $('#rec_modal_header').css('background','#f39c12');
    $('#rec_modal_title').html('<i class="fas fa-edit mr-2"></i> Edit Recurring Invoice');
    $('#btnSaveRecurring').html('<i class="fas fa-save mr-1"></i> Update');

    $.ajax({
        url:     '/bandwidth-sale/recurring/'+id+'/edit',
        method:  'GET',
        headers: {'X-Requested-With':'XMLHttpRequest'},
        success: function(res) {
            if (!res.success) { toastErr('Load failed.'); return; }
            var inv = res.invoice;

            $('#rec_no').val(inv.invoice_no);
            $('#rec_customer').val(inv.bws_customer_id).trigger('change');
            $('#rec_month').val(inv.billing_month);
            $('#rec_repeat').val(inv.repeat_date);
            $('#rec_start').val(inv.recurring_start || '');
            $('#rec_end').val(inv.recurring_end || '');
            $('#rec_daily').prop('checked', inv.daily_basis == 1);
            $('#rec_discount').val(inv.discount);
            $('#rec_notes').val(inv.notes || '');

            $('#recItemsBody').empty(); recRowIdx=0;
            (inv.items||[]).forEach(item => addRecItemRow(item));
            recToggleDailyCols(); recRecalcAll();

            $('#recurringModal').modal('show');
            setTimeout(() => $('#rec_customer').select2({
                dropdownParent: $('#recurringModal'), width:'100%'
            }), 200);
        },
        error: () => toastErr('Failed to load.')
    });
});

// ── SAVE ──────────────────────────────────────────────────────
$('#btnSaveRecurring').on('click', function() {
    if (!$('#rec_customer').val())  { toastErr('Customer is required.'); return; }
    if (!$('#rec_month').val())     { toastErr('Billing Month is required.'); return; }
    if (!$('#rec_repeat').val())    { toastErr('Repeat Date is required.'); return; }
    if (!$('#rec_start').val())     { toastErr('Start Date is required.'); return; }

    var sub=0, vat=0;
    $('#recItemsBody tr').each(function() {
        var q = parseFloat($(this).find('.item-qty').val())  || 0;
        var r = parseFloat($(this).find('.item-rate').val()) || 0;
        var v = parseFloat($(this).find('.item-vat').val())  || 0;
        var s = q*r; sub+=s; vat+=s*(v/100);
    });
    var disc  = parseFloat($('#rec_discount').val()) || 0;
    var grand = sub + vat - disc;

    var payload = {
        _token:          CSRF,
        bws_customer_id: $('#rec_customer').val(),
        billing_month:   $('#rec_month').val(),
        repeat_date:     $('#rec_repeat').val(),
        payment_due:     $('#rec_due').val(),
        start_date:      $('#rec_start').val(),
        end_date:        $('#rec_end').val(),
        daily_basis:     $('#rec_daily').is(':checked') ? 1 : 0,
        total_amount:    sub.toFixed(2),
        vat_amount:      vat.toFixed(2),
        discount:        disc.toFixed(2),
        grand_total:     grand.toFixed(2),
        due_amount:      grand.toFixed(2),
        notes:           $('#rec_notes').val(),
        items_json:      collectRecItems(),
    };

    if (editRecId) payload['_method'] = 'PUT';

    var url = editRecId
        ? '/bandwidth-sale/recurring/'+editRecId
        : '/bandwidth-sale/recurring';

    var $btn = $(this).prop('disabled',true)
                      .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    $.ajax({
        url:     url,
        method:  'POST',
        data:    payload,
        headers: {'X-Requested-With':'XMLHttpRequest'},
        success: function(res) {
            if (res.success || res.id) {
                $('#recurringModal').modal('hide');
                toastOk(res.message || 'Recurring invoice saved.');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastErr(res.message || 'Failed.');
            }
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors;
            toastErr(errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message||'Error.'));
        },
        complete: () => $btn.prop('disabled',false)
                            .html(editRecId
                                ? '<i class="fas fa-save mr-1"></i> Update'
                                : '<i class="fas fa-save mr-1"></i> Save')
    });
});

// ── DELETE ────────────────────────────────────────────────────
$(document).on('click', '.btn-del-rec', function() {
    var id = $(this).data('id');
    var no = $(this).data('no');
    Swal.fire({
        title: 'Delete Recurring Invoice?',
        html: `<code>${no}</code> permanently delete হবে।`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor:  '#6c757d',
        confirmButtonText:  'Delete',
        cancelButtonText:   'Cancel',
        reverseButtons: true,
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({
            url:     '/bandwidth-sale/recurring/'+id,
            method:  'POST',
            data:    { _token:CSRF, _method:'DELETE' },
            headers: { 'X-Requested-With':'XMLHttpRequest' },
            success: function(res) {
                if (res.success) {
                    toastOk(res.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastErr(res.message);
                }
            },
            error: xhr => toastErr(xhr.responseJSON?.message || 'Delete failed.')
        });
    });
});
</script>
@endsection
