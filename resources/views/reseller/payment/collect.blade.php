@extends('reseller.layouts.app')

@section('title', 'Collect Payment')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Collect Payment</h4>
    <a href="{{ route('reseller.payment.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-list mr-1"></i> Payment History
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="row">

    {{-- Left — Customer Search + Form --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-primary">
                <h6 class="mb-0 text-white"><i class="fas fa-search mr-1"></i> Search Client</h6>
            </div>
            <div class="card-body">

                {{-- Plain, dependency-free searchable dropdown (no Select2) --}}
                <div class="form-group position-relative">
                    <label class="font-weight-bold">Client <span class="text-danger">*</span></label>
                    <input type="text" id="customerSearchInput" class="form-control" placeholder="Type name or phone to search..." autocomplete="off">
                    <input type="hidden" id="customerSelectedId">
                    <div id="customerSearchResults" class="list-group shadow-sm"
                         style="display:none; position:absolute; z-index:1050; width:100%; max-height:260px; overflow-y:auto;"></div>
                </div>

                <div id="customerInfo" class="d-none">
                    <div class="bg-light rounded p-3 mb-3">
                        <div class="row" style="font-size:13px;">
                            <div class="col-md-6 mb-1"><span class="text-muted">Name:</span> <strong id="info_name"></strong></div>
                            <div class="col-md-6 mb-1"><span class="text-muted">Phone:</span> <span id="info_phone"></span></div>
                            <div class="col-md-6 mb-1"><span class="text-muted">Username:</span> <span id="info_username"></span></div>
                            <div class="col-md-6 mb-1"><span class="text-muted">Package:</span> <span id="info_package"></span></div>
                            <div class="col-md-6 mb-1"><span class="text-muted">Total Due:</span> <strong class="text-danger" id="info_due"></strong></div>
                            <div class="col-md-6 mb-1"><span class="text-muted">Advance Balance:</span> <strong class="text-success" id="info_advance"></strong></div>
                        </div>
                    </div>

                    <form action="{{ route('reseller.payment.collect.store') }}" method="POST" id="collectForm">
                        @csrf
                        <input type="hidden" name="customer_id" id="customer_id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Payment Method <span class="text-danger">*</span></label>
                                    <select name="method" class="form-control form-control-sm" required>
                                        <option value="cash">Cash</option>
                                        <option value="bkash">bKash</option>
                                        <option value="nagad">Nagad</option>
                                        <option value="rocket">Rocket</option>
                                        <option value="bank">Bank Transfer</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Received By</label>
                                    <select name="reseller_employee_id" class="form-control form-control-sm">
                                        <option value="">— Myself —</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Transaction / Receipt No</label>
                                    <input type="text" name="transaction_id" class="form-control form-control-sm" placeholder="For bKash/Nagad/Bank">
                                </div>
                            </div>
                        </div>

                        <table class="table table-sm table-bordered mb-3">
                            <thead class="bg-dark text-white">
                                <tr><th>Details</th><th class="text-right" style="width:140px;">Amount (BDT)</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total Due</td>
                                    <td class="text-right text-danger font-weight-bold" id="tbl_due">0.00</td>
                                </tr>
                                <tr>
                                    <td>Advance Balance</td>
                                    <td class="text-right text-success" id="tbl_advance">0.00</td>
                                </tr>
                                <tr>
                                    <td>Received Amount <span class="text-danger">*</span></td>
                                    <td class="text-right">
                                        <input type="number" name="amount" id="tbl_amount" class="form-control form-control-sm text-right" min="1" step="0.01" required style="width:120px; float:right;">
                                    </td>
                                </tr>
                                <tr class="table-warning">
                                    <td><strong>Balance Due After Payment</strong></td>
                                    <td class="text-right"><strong id="tbl_balance">0.00</strong></td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Advance After Payment</strong></td>
                                    <td class="text-right"><strong id="tbl_advance_after">0.00</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <label class="font-weight-bold">Remarks / Note</label>
                            <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Optional">
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="send_sms" value="1" class="form-check-input" id="sendSms" checked>
                            <label class="form-check-label" for="sendSms">Send SMS confirmation</label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" id="btnSubmit">
                            <i class="fas fa-save mr-1"></i> Save Payment
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- Right — Unpaid Invoices --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-warning">
                <h6 class="mb-0 text-white"><i class="fas fa-file-invoice mr-1"></i> Unpaid Invoices</h6>
            </div>
            <div class="card-body p-0" id="unpaidInvoicesBox">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>Select a client to see unpaid invoices</p>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('js')
<script>
// ── Plain vanilla-JS searchable client list — no Select2, no library dependency ──
var CUSTOMERS = [
    @foreach($customers as $c)
    { id: {{ $c->id }}, text: {{ Js::from($c->name . ' — ' . $c->phone) }} },
    @endforeach
];

var $input   = $('#customerSearchInput');
var $results = $('#customerSearchResults');

function renderResults(list) {
    if (list.length === 0) {
        $results.html('<div class="list-group-item text-muted">No results found</div>').show();
        return;
    }
    var html = '';
    list.forEach(function (c) {
        html += '<a href="#" class="list-group-item list-group-item-action customer-result" data-id="' + c.id + '" data-text="' + c.text.replace(/"/g, '&quot;') + '">' + c.text + '</a>';
    });
    $results.html(html).show();
}

$input.on('focus', function () {
    renderResults(CUSTOMERS);
});

$input.on('input', function () {
    var term = $(this).val().toLowerCase().trim();
    if (term === '') {
        renderResults(CUSTOMERS);
        return;
    }
    var filtered = CUSTOMERS.filter(function (c) {
        return c.text.toLowerCase().indexOf(term) !== -1;
    });
    renderResults(filtered);
});

$(document).on('click', '.customer-result', function (e) {
    e.preventDefault();
    var id   = $(this).data('id');
    var text = $(this).data('text');
    $input.val(text);
    $('#customerSelectedId').val(id);
    $results.hide();
    onCustomerSelected(id);
});

// Hide results dropdown when clicking outside
$(document).on('click', function (e) {
    if (!$(e.target).closest('#customerSearchInput, #customerSearchResults').length) {
        $results.hide();
    }
});

function onCustomerSelected(customerId) {
    $('#customer_id').val(customerId);
    $('#unpaidInvoicesBox').html('<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

    $.get('{{ url("reseller/payment/customer") }}/' + customerId + '/due', function (data) {
        var due     = parseFloat(data.total_due).toFixed(2);
        var advance = parseFloat(data.advance_balance).toFixed(2);
        var c       = data.customer;

        $('#info_name').text(c.name);
        $('#info_phone').text(c.phone);
        $('#info_username').text(c.username ?? '-');
        $('#info_package').text(c.package ? c.package.name : '-');
        $('#info_due').text('BDT ' + due);
        $('#info_advance').text('BDT ' + advance);
        $('#tbl_due').text(due);
        $('#tbl_advance').text(advance);
        $('#tbl_amount').val(due > 0 ? due : '');
        recalculate();

        $('#customerInfo').removeClass('d-none');
        loadUnpaidInvoices(data.invoices);
    }).fail(function () {
        alert('Failed to load client data. Please try again.');
    });
}

function recalculate() {
    var due     = parseFloat($('#tbl_due').text()) || 0;
    var advance = parseFloat($('#tbl_advance').text()) || 0;
    var amount  = parseFloat($('#tbl_amount').val()) || 0;

    var dueAfterAdvance = Math.max(due - advance, 0);
    var advanceUsed     = Math.min(advance, due);
    var advanceLeft     = advance - advanceUsed;

    var balance  = dueAfterAdvance - amount;
    var advAfter = balance < 0 ? (advanceLeft + Math.abs(balance)) : advanceLeft;

    $('#tbl_balance').text(balance > 0 ? balance.toFixed(2) : '0.00');
    $('#tbl_advance_after').text(advAfter > 0 ? advAfter.toFixed(2) : '0.00');

    if (balance > 0) {
        $('#tbl_balance').closest('tr').removeClass('table-success').addClass('table-warning');
    } else {
        $('#tbl_balance').closest('tr').removeClass('table-warning').addClass('table-success');
    }
}

$('#tbl_amount').on('input', recalculate);

function loadUnpaidInvoices(invoices) {
    var html = '<table class="table table-sm table-hover mb-0">';
    html += '<thead class="bg-light"><tr><th>Invoice No</th><th>Month</th><th>Amount</th><th>Due</th><th>Status</th></tr></thead><tbody>';

    if (invoices && invoices.length > 0) {
        $.each(invoices, function (i, inv) {
            var badge = inv.status == 'partial' ? 'warning' : 'secondary';
            html += '<tr>';
            html += '<td><span class="text-danger font-weight-bold">' + inv.invoice_no + '</span></td>';
            html += '<td>' + inv.month + '</td>';
            html += '<td>BDT ' + parseFloat(inv.amount).toFixed(0) + '</td>';
            html += '<td class="text-danger font-weight-bold">BDT ' + parseFloat(inv.due_amount).toFixed(0) + '</td>';
            html += '<td><span class="badge badge-' + badge + '">' + inv.status.charAt(0).toUpperCase() + inv.status.slice(1) + '</span></td>';
            html += '</tr>';
        });
    } else {
        html += '<tr><td colspan="5" class="text-center text-success py-3"><i class="fas fa-check-circle mr-1"></i> No unpaid invoices</td></tr>';
    }

    html += '</tbody></table>';
    $('#unpaidInvoicesBox').html(html);
}

$('#collectForm').on('submit', function () {
    $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
});
</script>
@endsection