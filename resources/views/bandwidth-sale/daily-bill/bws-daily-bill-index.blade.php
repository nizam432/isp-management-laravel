{{-- resources/views/bandwidth-sale/daily-bill/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Bandwidth Daily Bill')

@section('page_actions')
    <button class="btn btn-sm btn-outline-secondary" id="btnCSV">
        <i class="fas fa-file-csv mr-1"></i> Generate CSV
    </button>
    <button class="btn btn-sm btn-outline-danger ml-1" id="btnPDF">
        <i class="fas fa-file-pdf mr-1"></i> Generate PDF
    </button>
    <button class="btn btn-sm btn-danger ml-2" id="btnDeleteSelected" disabled>
        <i class="fas fa-trash mr-1"></i> Delete Selected
    </button>
    <button class="btn btn-sm btn-success ml-1" id="btnApproveSelected" disabled>
        <i class="fas fa-check mr-1"></i> Approve Selected
    </button>
    <button class="btn btn-sm btn-primary ml-1" id="btnReceiveBill"
            data-toggle="modal" data-target="#billReceiveModal">
        <i class="fas fa-plus mr-1"></i> Receive Bill
    </button>
@endsection

@section('page_content')

{{-- ══ FILTER ══════════════════════════════════════════════════ --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-body py-2">
        <form method="GET" id="filterForm" class="row align-items-end">
            <div class="col-md-2">
                <label class="small font-weight-bold">POP</label>
                <select name="pop" class="form-control form-control-sm">
                    <option value="">All POPs</option>
                    @foreach($pops ?? [] as $pop)
                        <option value="{{ $pop }}" {{ request('pop') == $pop ? 'selected':'' }}>{{ $pop }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">From Month</label>
                <input type="month" name="from_month" class="form-control form-control-sm"
                       value="{{ request('from_month', now()->format('Y-m')) }}"
                       autocomplete="off">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">To Month</label>
                <input type="month" name="to_month" class="form-control form-control-sm"
                       value="{{ request('to_month', now()->format('Y-m')) }}"
                       autocomplete="off">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Received By</label>
                <select name="received_by" class="form-control form-control-sm">
                    <option value="">Select</option>
                    @foreach($employees ?? [] as $emp)
                        <option value="{{ $emp->user_id }}"
                            {{ request('received_by') == $emp->user_id ? 'selected':'' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Created By</label>
                <select name="created_by" class="form-control form-control-sm">
                    <option value="">Select</option>
                    @foreach($employees ?? [] as $emp)
                        <option value="{{ $emp->user_id }}"
                            {{ request('created_by') == $emp->user_id ? 'selected':'' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Transaction Status</label>
                <select name="tx_status" class="form-control form-control-sm">
                    <option value="">Select</option>
                    <option value="active" {{ request('tx_status') == 'active' ? 'selected':'' }}>Active</option>
                    <option value="void"   {{ request('tx_status') == 'void'   ? 'selected':'' }}>Void</option>
                </select>
            </div>
            <div class="col-12 mt-2">
                <button class="btn btn-sm btn-primary">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('bandwidth-sale.daily-bill.index') }}" class="btn btn-sm btn-secondary ml-1">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ══ TABLE ═══════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0 font-weight-bold">
            <i class="fas fa-list mr-1"></i> Daily Bill Transactions
            <span class="badge badge-info ml-1">{{ $payments->total() }}</span>
        </h6>
        <div class="d-flex align-items-center gap-2">
            <label class="small mb-0 mr-1">SHOW</label>
            <select class="form-control form-control-sm" id="perPage" style="width:70px;">
                @foreach([20,50,100] as $pp)
                    <option value="{{ $pp }}" {{ request('per_page',100) == $pp ? 'selected':'' }}>{{ $pp }}</option>
                @endforeach
            </select>
            <label class="small mb-0 mx-1">ENTRIES</label>
            <input type="text" id="tableSearch" class="form-control form-control-sm ml-2"
                   placeholder="Search..." style="width:160px;">
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="dailyBillTable"
                   style="font-size:12px;">
                <thead style="background:#2c3e50; color:#fff;">
                    <tr>
                        <th style="width:30px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>R.Date ↕</th>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Mobile No.</th>
                        <th>Invoice No.</th>
                        <th>Bill Month</th>
                        <th class="text-right">Bill Amount</th>
                        <th class="text-right">Received</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Balance Due</th>
                        <th>Received By ↕</th>
                        <th>Created By ↕</th>
                        <th>Created On ↕</th>
                        <th>Note/Remarks</th>
                        <th style="width:80px;">Action</th>
                        <th style="width:30px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalBill = 0; $totalReceived = 0; @endphp
                    @forelse($payments as $pay)
                    @php
                        $totalBill     += $pay->bwsInvoice->grand_total ?? 0;
                        $totalReceived += $pay->received_amount;
                        $isVoid = $pay->isVoid();
                    @endphp
                    <tr class="{{ $isVoid ? 'text-muted' : '' }}" id="pay-row-{{ $pay->id }}">
                        <td>
                            @if(!$isVoid)
                            <input type="checkbox" class="pay-check" value="{{ $pay->id }}"
                                   data-status="{{ $pay->status }}">
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            {{ optional($pay->received_date)->format('d-m-Y') }}
                        </td>
                        <td>
                            <a href="{{ route('bandwidth-sale.customers.show', $pay->bws_customer_id) }}"
                               class="font-weight-bold {{ $isVoid ? 'text-muted' : 'text-primary' }}">
                                {{ $pay->bwsCustomer->customer_name ?? '—' }}
                            </a>
                        </td>
                        <td>{{ $pay->bwsCustomer->contact_person ?? '—' }}</td>
                        <td>{{ $pay->bwsCustomer->mobile_number ?? '—' }}</td>
                        <td>
                            <a href="{{ route('bandwidth-sale.invoices.show', $pay->bws_invoice_id) }}"
                               class="text-info">
                                {{ $pay->bwsInvoice->invoice_no ?? '—' }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-light border">
                                {{ $pay->bwsInvoice->billing_month
                                    ? \Carbon\Carbon::parse($pay->bwsInvoice->billing_month.'-01')->format('M-y')
                                    : '—' }}
                            </span>
                        </td>
                        <td class="text-right">{{ number_format($pay->bwsInvoice->grand_total ?? 0, 2) }}</td>
                        <td class="text-right text-success font-weight-bold">
                            {{ number_format($pay->received_amount, 2) }}
                        </td>
                        <td class="text-right text-warning">
                            {{ number_format($pay->discount, 2) }}
                        </td>
                        <td class="text-right {{ ($pay->bwsInvoice->due_amount ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($pay->bwsInvoice->due_amount ?? 0, 2) }}
                        </td>
                        <td>{{ $pay->receivedBy->name ?? '—' }}</td>
                        <td>{{ $pay->createdBy->name ?? '—' }}</td>
                        <td style="white-space:nowrap;">
                            {{ optional($pay->created_at)->format('d/m/Y') }}
                        </td>
                        <td>
                            <small class="text-muted">{{ Str::limit($pay->remarks, 30) }}</small>
                        </td>
                        <td style="white-space:nowrap;">
                            @if(!$isVoid)
                                {{-- View Income --}}
                                @if($pay->income_id)
                                <a href="{{ route('incomes.show', $pay->income_id) }}"
                                   class="btn btn-xs btn-light border" title="View Income">
                                    <i class="fas fa-book text-success"></i>
                                </a>
                                @endif
                                {{-- Void --}}
                                <button class="btn btn-xs btn-light border btn-void"
                                        data-id="{{ $pay->id }}"
                                        data-no="{{ $pay->payment_no }}"
                                        title="Void">
                                    <i class="fas fa-ban text-danger"></i>
                                </button>
                            @else
                                <span class="badge badge-secondary">Void</span>
                            @endif
                        </td>
                        <td>
                            @if(!$isVoid)
                            <input type="checkbox" class="pay-check-2" value="{{ $pay->id }}">
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="17" class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-3x d-block mb-3 opacity-50"></i>
                            No data available in table
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot style="background:#f8f9fa; font-weight:bold;">
                    <tr>
                        <td colspan="7" class="text-right pr-3">Total</td>
                        <td class="text-right">{{ number_format($totalBill, 2) }}</td>
                        <td class="text-right text-success">{{ number_format($totalReceived, 2) }}</td>
                        <td colspan="8"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($payments->hasPages())
    <div class="card-footer py-2">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }}
                of {{ $payments->total() }} entries
            </small>
            {{ $payments->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>


{{-- ═══════════════════════════════════════════════════════════
     BILL RECEIVE MODAL
═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="billReceiveModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background:#2c3e50; color:#fff;">
                <h5 class="modal-title">
                    <i class="fas fa-hand-holding-usd mr-2"></i> Bill Receive
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                {{-- Top 3 selects --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="font-weight-bold small">POP (Customer) <span class="text-danger">*</span></label>
                        <select id="rc_customer" class="form-control select2">
                            <option value="">Select Customer</option>
                            @foreach($customers ?? [] as $c)
                                <option value="{{ $c->id }}"
                                        data-name="{{ $c->customer_name }}"
                                        data-mobile="{{ $c->mobile_number }}">
                                    {{ $c->customer_name }}
                                    @if($c->customer_code)({{ $c->customer_code }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold small">Bill Month</label>
                        <input type="month" id="rc_month" class="form-control"
                               value="{{ now()->format('Y-m') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold small">Due Invoices</label>
                        <select id="rc_invoice" class="form-control">
                            <option value="">— Select Invoice —</option>
                        </select>
                    </div>
                </div>

                {{-- Fields row 1 --}}
                <div class="row mb-2">
                    <div class="col-md-3">
                        <label class="font-weight-bold small">Received Date <span class="text-danger">*</span></label>
                        <input type="date" id="rc_received_date" class="form-control"
                               value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold small">POP Name</label>
                        <input type="text" id="rc_pop_name" class="form-control" readonly
                               style="background:#f8f9fa;">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold small">Received From</label>
                        <input type="text" id="rc_received_from" class="form-control"
                               placeholder="Person name">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold small">Mobile Number</label>
                        <input type="text" id="rc_mobile" class="form-control" readonly
                               style="background:#f8f9fa;">
                    </div>
                </div>

                {{-- Fields row 2 --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="font-weight-bold small">Received By</label>
                        <select id="rc_received_by" class="form-control">
                            <option value="">Select</option>
                            @foreach($employees ?? [] as $emp)
                                <option value="{{ $emp->user_id }}"
                                    {{ $emp->user_id == auth()->id() ? 'selected':'' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold small">Invoice Number</label>
                        <input type="text" id="rc_invoice_no" class="form-control" readonly
                               style="background:#f8f9fa;">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold small">Payment Method <span class="text-danger">*</span></label>
                        <select id="rc_method" class="form-control">
                            <option value="cash">Cash</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                            <option value="bank">Bank</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold small">Invoice Month</label>
                        <input type="text" id="rc_inv_month" class="form-control" readonly
                               style="background:#f8f9fa;">
                    </div>
                </div>

                {{-- Amount table --}}
                <table class="table table-sm table-bordered" style="font-size:13px;">
                    <thead style="background:#2c3e50; color:#fff;">
                        <tr>
                            <th>Details</th>
                            <th class="text-right">Amount Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Payable Amount</td>
                            <td class="text-right font-weight-bold" id="rc_payable">0.00</td>
                        </tr>
                        <tr>
                            <td>Previous (Paid + Discount)</td>
                            <td class="text-right" id="rc_previous">0</td>
                        </tr>
                        <tr>
                            <td>Approvable (Paid + Discount)</td>
                            <td class="text-right" id="rc_approvable">0</td>
                        </tr>
                        <tr class="table-warning">
                            <td class="font-weight-bold">Balance Due</td>
                            <td class="text-right font-weight-bold" id="rc_balance_due">0.00</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Received Amount <span class="text-danger">*</span></td>
                            <td>
                                <input type="number" id="rc_received_amount" class="form-control form-control-sm text-right"
                                       value="0" min="0" step="0.01">
                            </td>
                        </tr>
                        <tr>
                            <td>Discount</td>
                            <td>
                                <input type="number" id="rc_discount" class="form-control form-control-sm text-right"
                                       value="0" min="0" step="0.01">
                            </td>
                        </tr>
                        <tr>
                            <td>Receipt / Transaction No.</td>
                            <td>
                                <input type="text" id="rc_txn_no" class="form-control form-control-sm"
                                       placeholder="Transaction ID">
                            </td>
                        </tr>
                        <tr>
                            <td>Remarks / Note</td>
                            <td>
                                <input type="text" id="rc_remarks" class="form-control form-control-sm"
                                       placeholder="Optional">
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- Income auto-record notice --}}
                <div class="alert alert-info py-2 mb-0" style="font-size:12px;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Payment save হলে <strong>Accounting → Income</strong> এ automatically
                    <strong>"Bandwidth Sale"</strong> category তে record তৈরি হবে।
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary px-4" id="btnSubmitReceive">
                    <i class="fas fa-save mr-1"></i> Submit
                </button>
            </div>
        </div>
    </div>
</div>

@endsection


@section('extra_css')
<style>
#dailyBillTable thead th { font-size:11px; font-weight:700; white-space:nowrap;
    padding:9px 8px; letter-spacing:.3px; }
#dailyBillTable tbody td { padding:7px 8px; vertical-align:middle; }
#dailyBillTable tbody tr:hover { background:#f0f7ff; }
</style>
@endsection


@section('js')
<script>
const CSRF = '{{ csrf_token() }}';

// ── Per page ──────────────────────────────────────────────────
$('#perPage').on('change', function () {
    var url = new URL(window.location.href);
    url.searchParams.set('per_page', $(this).val());
    window.location.href = url.toString();
});

// ── Live search ───────────────────────────────────────────────
$('#tableSearch').on('keyup', function () {
    var val = $(this).val().toLowerCase();
    $('#dailyBillTable tbody tr').each(function () {
        $(this).toggle($(this).text().toLowerCase().includes(val));
    });
});

// ── Select All ────────────────────────────────────────────────
$('#selectAll').on('change', function () {
    $('.pay-check').prop('checked', this.checked);
    toggleBulkBtns();
});
$(document).on('change', '.pay-check', toggleBulkBtns);

function toggleBulkBtns() {
    var n = $('.pay-check:checked').length;
    $('#btnDeleteSelected, #btnApproveSelected').prop('disabled', n === 0);
}

// ── select2 ───────────────────────────────────────────────────
$('.select2').select2({ width:'100%' });
$('#rc_customer').select2({ dropdownParent: $('#billReceiveModal'), width:'100%' });

// ── Customer select → load due invoices ───────────────────────
$('#rc_customer').on('change', function () {
    var opt    = $(this).find(':selected');
    var custId = $(this).val();
    $('#rc_pop_name').val(opt.data('name') || '');
    $('#rc_mobile').val(opt.data('mobile') || '');
    $('#rc_invoice').html('<option value="">Loading...</option>');

    if (!custId) return;

    $.get('/bandwidth-sale/invoices/due-for-customer/' + custId, function (res) {
        var options = '<option value="">— Select Invoice —</option>';
        (res.invoices || []).forEach(function (inv) {
            options += `<option value="${inv.id}"
                            data-no="${inv.invoice_no}"
                            data-month="${inv.billing_month}"
                            data-payable="${inv.grand_total}"
                            data-previous="${inv.received_amount}"
                            data-due="${inv.due_amount}">
                            Invoice#${inv.invoice_no} (${inv.billing_month}) — Due: ${inv.due_amount}
                        </option>`;
        });
        $('#rc_invoice').html(options);
    }).fail(function () {
        $('#rc_invoice').html('<option value="">No invoices</option>');
    });
});

// ── Invoice select → fill amount fields ───────────────────────
$('#rc_invoice').on('change', function () {
    var opt = $(this).find(':selected');
    var id  = $(this).val();
    if (!id) return;

    var payable  = parseFloat(opt.data('payable')  || 0);
    var previous = parseFloat(opt.data('previous') || 0);
    var due      = parseFloat(opt.data('due')      || 0);

    $('#rc_invoice_no').val(opt.data('no') || '');
    $('#rc_inv_month').val(opt.data('month') || '');
    $('#rc_payable').text(payable.toFixed(2));
    $('#rc_previous').text(previous.toFixed(2));
    $('#rc_approvable').text(previous.toFixed(2));
    $('#rc_balance_due').text(due.toFixed(2));
    $('#rc_received_amount').val(due.toFixed(2));
});

// ── Submit Bill Receive ───────────────────────────────────────
$('#btnSubmitReceive').on('click', function () {
    var invoiceId = $('#rc_invoice').val();
    if (!invoiceId) {
        Swal.fire({ toast:true, position:'top-end', icon:'warning',
            title:'Please select an invoice.', showConfirmButton:false, timer:2000 });
        return;
    }

    var $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    $.ajax({
        url:    '/bandwidth-sale/invoices/' + invoiceId + '/receive',
        method: 'POST',
        data: {
            _token:                  CSRF,
            received_date:           $('#rc_received_date').val(),
            received_from:           $('#rc_received_from').val(),
            received_by:             $('#rc_received_by').val(),
            payment_method:          $('#rc_method').val(),
            received_amount:         $('#rc_received_amount').val(),
            discount:                $('#rc_discount').val(),
            receipt_transaction_no:  $('#rc_txn_no').val(),
            remarks:                 $('#rc_remarks').val(),
        },
        success: function (res) {
            if (res.success) {
                $('#billReceiveModal').modal('hide');
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: res.message + (res.income_no ? ' | Income: ' + res.income_no : ''),
                    showConfirmButton: false, timer: 3500
                });
                setTimeout(function () { location.reload(); }, 2000);
            } else {
                Swal.fire({ toast:true, position:'top-end', icon:'error',
                    title: res.message, showConfirmButton:false, timer:3000 });
            }
        },
        error: function (xhr) {
            Swal.fire({ toast:true, position:'top-end', icon:'error',
                title: xhr.responseJSON?.message || 'Something went wrong.',
                showConfirmButton:false, timer:3000 });
        },
        complete: function () {
            $btn.prop('disabled', false)
                .html('<i class="fas fa-save mr-1"></i> Submit');
        }
    });
});

// ── Void payment ──────────────────────────────────────────────
$(document).on('click', '.btn-void', function () {
    var id  = $(this).data('id');
    var no  = $(this).data('no');
    Swal.fire({
        title: 'Void Payment?',
        html: `<code>${no}</code> void হবে। <br>
               <strong class="text-success">Income record ও automatically void হবে।</strong>`,
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Void reason (required)',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Void',
        cancelButtonText: 'Cancel',
        preConfirm: function (val) {
            if (!val) Swal.showValidationMessage('Reason is required.');
        }
    }).then(function (r) {
        if (!r.isConfirmed) return;
        $.ajax({
            url:    '/bandwidth-sale/payments/' + id + '/void',
            method: 'POST',
            data:   { _token: CSRF, reason: r.value },
            success: function (res) {
                Swal.fire({ toast:true, position:'top-end', icon:'success',
                    title: res.message, showConfirmButton:false, timer:2500 });
                setTimeout(function () { location.reload(); }, 1500);
            }
        });
    });
});
</script>
@endsection
