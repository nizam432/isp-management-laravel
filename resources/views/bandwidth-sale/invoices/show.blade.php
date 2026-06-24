{{-- resources/views/bandwidth-sale/invoices/show.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Invoice — ' . $bwsInvoice->invoice_no)

@section('page_actions')
    <a href="{{ route('bandwidth-sale.invoices.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
    <a href="{{ route('bandwidth-sale.invoices.pdf', $bwsInvoice->id) }}"
       class="btn btn-danger btn-sm ml-1" target="_blank">
        <i class="fas fa-file-pdf mr-1"></i> PDF
    </a>
    @if(!$bwsInvoice->isPaid())
    <a href="{{ route('bandwidth-sale.invoices.edit', $bwsInvoice->id) }}"
       class="btn btn-warning btn-sm ml-1">
        <i class="fas fa-edit mr-1"></i> Edit
    </a>
    <button class="btn btn-primary btn-sm ml-1" id="btnReceive">
        <i class="fas fa-hand-holding-usd mr-1"></i> Receive Payment
    </button>
    @endif
@endsection

@section('page_content')

@php
    $statusMap = [
        'paid'    => ['bg'=>'#00a65a', 'label'=>'Paid'],
        'partial' => ['bg'=>'#17a2b8', 'label'=>'Partial'],
        'overdue' => ['bg'=>'#dc3545', 'label'=>'Overdue'],
        'unpaid'  => ['bg'=>'#f39c12', 'label'=>'Unpaid'],
    ];
    $sc = $statusMap[$bwsInvoice->status] ?? $statusMap['unpaid'];
@endphp

<div class="row">

    {{-- ══ LEFT: Invoice Detail ══════════════════════════════════ --}}
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-weight-bold">
                        <i class="fas fa-file-invoice mr-1 text-primary"></i>
                        <code>{{ $bwsInvoice->invoice_no }}</code>
                    </h6>
                    <span class="badge px-3 py-2"
                          style="background:{{ $sc['bg'] }};color:#fff;border-radius:20px;font-size:12px;">
                        {{ $sc['label'] }}
                    </span>
                </div>
            </div>

            <div class="card-body pb-2">
                {{-- Invoice Meta --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless" style="font-size:13px;">
                            <tr>
                                <td class="text-muted" style="width:40%;">Customer</td>
                                <td>
                                    <a href="{{ route('bandwidth-sale.customers.show', $bwsInvoice->bws_customer_id) }}"
                                       class="font-weight-bold text-primary">
                                        {{ $bwsInvoice->bwsCustomer->customer_name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Contact</td>
                                <td>{{ $bwsInvoice->bwsCustomer->contact_person ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Mobile</td>
                                <td>{{ $bwsInvoice->bwsCustomer->mobile_number }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless" style="font-size:13px;">
                            <tr>
                                <td class="text-muted" style="width:45%;">Billing Month</td>
                                <td class="font-weight-bold">
                                    {{ \Carbon\Carbon::parse($bwsInvoice->billing_month.'-01')->format('F Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Payment Due</td>
                                <td class="{{ $bwsInvoice->payment_due && now()->gt($bwsInvoice->payment_due) && !$bwsInvoice->isPaid() ? 'text-danger font-weight-bold' : '' }}">
                                    {{ optional($bwsInvoice->payment_due)->format('d M Y') ?? '—' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Created By</td>
                                <td>{{ $bwsInvoice->createdBy->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Created On</td>
                                <td>{{ optional($bwsInvoice->created_at)->format('d M Y, h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" style="font-size:13px;">
                        <thead style="background:#2c3e50;color:#fff;">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th class="text-center">Unit</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Rate</th>
                                <th class="text-right">VAT%</th>
                                @if($bwsInvoice->daily_basis)
                                <th class="text-center">From</th>
                                <th class="text-center">To</th>
                                @endif
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bwsInvoice->items as $i => $item)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $item->item_name ?? '—' }}</td>
                                <td>{{ $item->description ?? '—' }}</td>
                                <td class="text-center">{{ $item->unit ?? '—' }}</td>
                                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                                <td class="text-right">{{ $item->vat_percent }}%</td>
                                @if($bwsInvoice->daily_basis)
                                <td class="text-center">
                                    {{ optional($item->from_date)->format('d M Y') ?? '—' }}
                                </td>
                                <td class="text-center">
                                    {{ optional($item->to_date)->format('d M Y') ?? '—' }}
                                </td>
                                @endif
                                <td class="text-right font-weight-bold">
                                    {{ number_format($item->total, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-3">No items.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot style="background:#f8f9fa;">
                            <tr>
                                <td colspan="{{ $bwsInvoice->daily_basis ? 9 : 7 }}"
                                    class="text-right font-weight-bold">Sub Total</td>
                                <td class="text-right font-weight-bold">
                                    {{ number_format($bwsInvoice->total_amount, 2) }}
                                </td>
                            </tr>
                            @if($bwsInvoice->vat_amount > 0)
                            <tr>
                                <td colspan="{{ $bwsInvoice->daily_basis ? 9 : 7 }}"
                                    class="text-right text-muted">VAT</td>
                                <td class="text-right text-muted">
                                    {{ number_format($bwsInvoice->vat_amount, 2) }}
                                </td>
                            </tr>
                            @endif
                            @if($bwsInvoice->discount > 0)
                            <tr>
                                <td colspan="{{ $bwsInvoice->daily_basis ? 9 : 7 }}"
                                    class="text-right text-warning">Discount</td>
                                <td class="text-right text-warning">
                                    ({{ number_format($bwsInvoice->discount, 2) }})
                                </td>
                            </tr>
                            @endif
                            <tr style="background:#e8f4fd;">
                                <td colspan="{{ $bwsInvoice->daily_basis ? 9 : 7 }}"
                                    class="text-right font-weight-bold" style="font-size:14px;">
                                    Grand Total
                                </td>
                                <td class="text-right font-weight-bold text-primary"
                                    style="font-size:15px;">
                                    ৳ {{ number_format($bwsInvoice->grand_total, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($bwsInvoice->notes)
                <div class="callout callout-info mt-2" style="font-size:13px;">
                    <strong>Notes:</strong> {{ $bwsInvoice->notes }}
                </div>
                @endif
            </div>
        </div>

        {{-- Payment History --}}
        <div class="card card-outline card-secondary">
            <div class="card-header py-2">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-money-bill-wave mr-1 text-success"></i> Payment History
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" style="font-size:13px;">
                    <thead style="background:#2c3e50;color:#fff;">
                        <tr>
                            <th>#</th>
                            <th>Payment No</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Trx No</th>
                            <th>Received By</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bwsInvoice->activePayments as $i => $pay)
                        <tr class="{{ $pay->isVoid() ? 'text-muted' : '' }}">
                            <td>{{ $i+1 }}</td>
                            <td><code>{{ $pay->payment_no }}</code></td>
                            <td>{{ optional($pay->received_date)->format('d M Y') }}</td>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ strtoupper($pay->payment_method) }}
                                </span>
                            </td>
                            <td>{{ $pay->receipt_transaction_no ?? '—' }}</td>
                            <td>{{ $pay->receivedBy->name ?? '—' }}</td>
                            <td class="text-right text-warning">
                                {{ number_format($pay->discount, 2) }}
                            </td>
                            <td class="text-right font-weight-bold text-success">
                                ৳ {{ number_format($pay->received_amount, 2) }}
                            </td>
                            <td>
                                @if($pay->isVoid())
                                    <span class="badge badge-secondary">Void</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                            <td>
                                @if(!$pay->isVoid())
                                <button class="btn btn-xs btn-light border btn-void-pay"
                                        data-id="{{ $pay->id }}"
                                        data-no="{{ $pay->payment_no }}"
                                        title="Void">
                                    <i class="fas fa-ban text-danger"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                No payments yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══ RIGHT: Summary ════════════════════════════════════════ --}}
    <div class="col-md-4">
        <div class="card card-outline card-info" style="position:sticky;top:70px;">
            <div class="card-header py-2">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-calculator mr-1 text-info"></i> Summary
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:13px;">
                    <tr>
                        <td class="text-muted pl-3">Grand Total</td>
                        <td class="text-right pr-3 font-weight-bold">
                            ৳ {{ number_format($bwsInvoice->grand_total, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Total Received</td>
                        <td class="text-right pr-3 text-success font-weight-bold">
                            ৳ {{ number_format($bwsInvoice->received_amount, 2) }}
                        </td>
                    </tr>
                    <tr style="border-top:2px solid #dee2e6;">
                        <td class="pl-3 font-weight-bold">Balance Due</td>
                        <td class="text-right pr-3 font-weight-bold
                            {{ $bwsInvoice->due_amount > 0 ? 'text-danger' : 'text-success' }}"
                            style="font-size:16px;">
                            ৳ {{ number_format($bwsInvoice->due_amount, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
            @if(!$bwsInvoice->isPaid())
            <div class="card-footer py-2">
                <button class="btn btn-primary btn-block" id="btnReceive2">
                    <i class="fas fa-hand-holding-usd mr-1"></i> Receive Payment
                </button>
            </div>
            @endif
        </div>

        {{-- Invoice Info --}}
        <div class="card card-outline card-secondary">
            <div class="card-header py-2">
                <h6 class="mb-0 font-weight-bold small">Invoice Details</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:12px;">
                    <tr>
                        <td class="text-muted pl-3">Invoice No</td>
                        <td class="pr-3"><code>{{ $bwsInvoice->invoice_no }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Billing Month</td>
                        <td class="pr-3">
                            {{ \Carbon\Carbon::parse($bwsInvoice->billing_month.'-01')->format('M Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Daily Basis</td>
                        <td class="pr-3">
                            <span class="badge badge-{{ $bwsInvoice->daily_basis ? 'info' : 'secondary' }}">
                                {{ $bwsInvoice->daily_basis ? 'Yes' : 'No' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Status</td>
                        <td class="pr-3">
                            <span class="badge px-2 py-1"
                                  style="background:{{ $sc['bg'] }};color:#fff;">
                                {{ $sc['label'] }}
                            </span>
                        </td>
                    </tr>
                    @if($bwsInvoice->is_recurring)
                    <tr>
                        <td class="text-muted pl-3">Recurring</td>
                        <td class="pr-3">
                            <span class="badge badge-info">Day {{ $bwsInvoice->repeat_date }}</span>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>


{{-- ══ RECEIVE PAYMENT MODAL ═══════════════════════════════════ --}}
<div class="modal fade" id="receiveModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#2c3e50;color:#fff;">
                <h5 class="modal-title">
                    <i class="fas fa-hand-holding-usd mr-2"></i> Receive Payment
                    <small class="ml-2 text-info">{{ $bwsInvoice->invoice_no }}</small>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="font-weight-bold small">Received Date <span class="text-danger">*</span></label>
                        <input type="date" id="rc_date" class="form-control"
                               value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold small">Received From</label>
                        <input type="text" id="rc_from" class="form-control"
                               value="{{ $bwsInvoice->bwsCustomer->contact_person }}"
                               placeholder="Person name">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold small">Received By</label>
                        <select id="rc_by" class="form-control">
                            <option value="">Select</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}"
                                    {{ $u->id == auth()->id() ? 'selected':'' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
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
                </div>

                <table class="table table-sm table-bordered" style="font-size:13px;">
                    <thead style="background:#2c3e50;color:#fff;">
                        <tr><th>Details</th><th class="text-right">Amount</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Grand Total</td>
                            <td class="text-right font-weight-bold">
                                ৳ {{ number_format($bwsInvoice->grand_total, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td>Previously Paid</td>
                            <td class="text-right text-success">
                                ৳ {{ number_format($bwsInvoice->received_amount, 2) }}
                            </td>
                        </tr>
                        <tr class="table-warning">
                            <td class="font-weight-bold">Balance Due</td>
                            <td class="text-right font-weight-bold">
                                ৳ {{ number_format($bwsInvoice->due_amount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">
                                Received Amount <span class="text-danger">*</span>
                            </td>
                            <td>
                                <input type="number" id="rc_amount" class="form-control form-control-sm text-right"
                                       value="{{ $bwsInvoice->due_amount }}" min="0.01" step="0.01">
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
                                <input type="text" id="rc_txn" class="form-control form-control-sm"
                                       placeholder="Optional">
                            </td>
                        </tr>
                        <tr>
                            <td>Remarks</td>
                            <td>
                                <input type="text" id="rc_remarks" class="form-control form-control-sm"
                                       placeholder="Optional">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="alert alert-info py-2 mb-0" style="font-size:12px;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Payment save হলে <strong>Accounting → Income</strong> এ automatically
                    <strong>"Bandwidth Sale"</strong> category তে record তৈরি হবে।
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
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
.callout { border-left:5px solid #17a2b8; padding:12px 15px; background:#f8f9fa; border-radius:4px; }
</style>
@endsection


@section('js')
<script>
const CSRF = '{{ csrf_token() }}';

function toastOk(msg)  { Swal.fire({toast:true,position:'top-end',icon:'success',title:msg,showConfirmButton:false,timer:2500}); }
function toastErr(msg) { Swal.fire({toast:true,position:'top-end',icon:'error',  title:msg,showConfirmButton:false,timer:3500}); }

// Open receive modal
$('#btnReceive, #btnReceive2').on('click', function () {
    $('#receiveModal').modal('show');
});

// Submit payment
$('#btnSubmitReceive').on('click', function () {
    var amount = $('#rc_amount').val();
    if (!amount || parseFloat(amount) <= 0) {
        toastErr('Received amount required.'); return;
    }
    var $btn = $(this).prop('disabled', true)
                      .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    $.ajax({
        url:    '/bandwidth-sale/invoices/{{ $bwsInvoice->id }}/receive',
        method: 'POST',
        data: {
            _token:                  CSRF,
            received_date:           $('#rc_date').val(),
            received_from:           $('#rc_from').val(),
            received_by:             $('#rc_by').val(),
            payment_method:          $('#rc_method').val(),
            received_amount:         amount,
            discount:                $('#rc_discount').val(),
            receipt_transaction_no:  $('#rc_txn').val(),
            remarks:                 $('#rc_remarks').val(),
        },
        success: function (res) {
            if (res.success) {
                $('#receiveModal').modal('hide');
                toastOk(res.message + (res.income_no ? ' | Income: '+res.income_no : ''));
                setTimeout(() => location.reload(), 1800);
            } else {
                toastErr(res.message);
            }
        },
        error: xhr => toastErr(xhr.responseJSON?.message || 'Error.'),
        complete: () => $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Submit')
    });
});

// Void payment
$(document).on('click', '.btn-void-pay', function () {
    var id = $(this).data('id');
    var no = $(this).data('no');
    Swal.fire({
        title: 'Void Payment?',
        html: `<code>${no}</code> void হবে।<br>
               <strong class="text-success">Income record ও void হবে।</strong>`,
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Void reason (required)',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Void',
        preConfirm: val => { if (!val) Swal.showValidationMessage('Reason required.'); }
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({
            url:    '/bandwidth-sale/payments/' + id + '/void',
            method: 'POST',
            data:   { _token: CSRF, reason: r.value },
            success: res => {
                toastOk(res.message);
                setTimeout(() => location.reload(), 1500);
            },
            error: xhr => toastErr(xhr.responseJSON?.message || 'Error.')
        });
    });
});
</script>
@endsection
