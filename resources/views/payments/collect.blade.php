@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@extends('layouts.app')

@section('title', 'Collect Payment')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Collect Payment</h1>
        <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-list mr-1"></i> Payment History
        </a>
    </div>
@endsection

@section('content')

<div class="row">

    {{-- Left — Customer Search + Form --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-primary">
                <h6 class="mb-0 text-white"><i class="fas fa-search mr-1"></i> Search Customer</h6>
            </div>
            <div class="card-body">

                {{-- Customer Select --}}
                <div class="form-group">
                    <label class="font-weight-bold">Customer <span class="text-danger">*</span></label>
                    <select id="customerSelect" class="form-control select2" style="width:100%;">
                        <option value="">— Type name or phone to search —</option>
                        @foreach(\App\Models\Customer::active()->with('package')->get() as $c)
                            <option value="{{ $c->id }}"
                                data-name="{{ $c->name }}"
                                data-phone="{{ $c->phone }}"
                                data-username="{{ $c->username ?? '-' }}"
                                data-package="{{ $c->package->name ?? '-' }}"
                                data-advance="{{ $c->advance_balance ?? 0 }}">
                                {{ $c->name }} — {{ $c->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Customer Info --}}
                <div id="customerInfo" class="d-none">
                    <div class="bg-light rounded p-3 mb-3">
                        <div class="row" style="font-size:13px;">
                            <div class="col-md-6 mb-1">
                                <span class="text-muted">Name:</span>
                                <strong id="info_name"></strong>
                            </div>
                            <div class="col-md-6 mb-1">
                                <span class="text-muted">Phone:</span>
                                <span id="info_phone"></span>
                            </div>
                            <div class="col-md-6 mb-1">
                                <span class="text-muted">Username:</span>
                                <span id="info_username"></span>
                            </div>
                            <div class="col-md-6 mb-1">
                                <span class="text-muted">Package:</span>
                                <span id="info_package"></span>
                            </div>
                            <div class="col-md-6 mb-1">
                                <span class="text-muted">Total Due:</span>
                                <strong class="text-danger" id="info_due"></strong>
                            </div>
                            <div class="col-md-6 mb-1">
                                <span class="text-muted">Advance Balance:</span>
                                <strong class="text-success" id="info_advance"></strong>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Form --}}
                    <form action="{{ route('payments.collect-store') }}" method="POST" id="collectForm">
                        @csrf
                        <input type="hidden" name="customer_id" id="customer_id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control form-control-sm"
                                        value="{{ date('Y-m-d') }}" required>
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
                                    <select name="received_by" class="form-control form-control-sm select2">
                                        <option value="">— Select —</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Transaction / Receipt No</label>
                                    <input type="text" name="transaction_id" class="form-control form-control-sm"
                                        placeholder="Optional">
                                </div>
                            </div>
                        </div>

                        {{-- Amount Table --}}
                        <table class="table table-sm table-bordered mb-3">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th>Details</th>
                                    <th class="text-right" style="width:140px;">Amount (BDT)</th>
                                </tr>
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
                                        <input type="number" name="amount" id="tbl_amount"
                                            class="form-control form-control-sm text-right"
                                            min="1" step="0.01" required
                                            style="width:120px; float:right;">
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
                            <input type="text" name="remarks" class="form-control form-control-sm"
                                placeholder="Optional">
                        </div>

                        <div class="d-flex mb-3" style="gap:20px;">
                            <div class="form-check">
                                <input type="checkbox" name="set_next_billing_date" value="1"
                                    class="form-check-input" id="setNextBilling" checked>
                                <label class="form-check-label" for="setNextBilling">Set next billing date</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="send_sms" value="1"
                                    class="form-check-input" id="sendSms" checked>
                                <label class="form-check-label" for="sendSms">Send SMS</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
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
                    <p>Select a customer to see unpaid invoices</p>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {

    // Select2 init
    $('#customerSelect').select2({
        width: '100%',
        placeholder: '— Type name or phone to search —',
    });

    $('.select2').select2({ width: '100%' });

    // Customer select change
    $('#customerSelect').on('change', function () {
        var customerId = $(this).val();
        if (!customerId) {
            $('#customerInfo').addClass('d-none');
            $('#unpaidInvoicesBox').html('<div class="text-center text-muted py-5"><i class="fas fa-search fa-2x mb-2"></i><p>Select a customer to see unpaid invoices</p></div>');
            return;
        }

        $('#customer_id').val(customerId);

        // Show loading
        $('#unpaidInvoicesBox').html('<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

        // Fetch customer data via AJAX
        $.get('/payments/customer-due/' + customerId, function (data) {
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

            // Show form
            $('#customerInfo').removeClass('d-none');

            // Load unpaid invoices
            loadUnpaidInvoices(data.invoices);
        }).fail(function () {
            alert('Failed to load customer data. Please try again.');
        });
    });

    // Recalculate balance
    function recalculate() {
        var due      = parseFloat($('#tbl_due').text()) || 0;
        var advance  = parseFloat($('#tbl_advance').text()) || 0;
        var amount   = parseFloat($('#tbl_amount').val()) || 0;

        // Advance first deducts from due, then received amount covers the rest
        var dueAfterAdvance = Math.max(due - advance, 0);
        var advanceUsed     = Math.min(advance, due);
        var advanceLeft     = advance - advanceUsed;

        // After advance, remaining due covered by received amount
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

    // Load unpaid invoices table
    function loadUnpaidInvoices(invoices) {
        var html = '<table class="table table-sm table-hover mb-0">';
        html += '<thead class="bg-light"><tr><th>Invoice No</th><th>Month</th><th>Amount</th><th>Due</th><th>Status</th></tr></thead>';
        html += '<tbody>';

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

    // Success message
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

});
</script>
@endpush