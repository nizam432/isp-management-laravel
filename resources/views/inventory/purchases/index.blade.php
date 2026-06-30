@extends('adminlte::page')
@section('title', 'Purchases')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-shopping-cart mr-2 text-primary"></i>Purchases
            </h4>
            <small class="text-muted">Manage all inventory purchases</small>
        </div>
        <div>
            <button class="btn btn-success btn-sm px-3" id="btnXlsx">
                <i class="fas fa-file-excel mr-1"></i> XLSX
            </button>
            <button class="btn btn-danger btn-sm px-3 ml-1" id="btnPdf">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </button>
            <a href="{{ route('inventory.purchases.create') }}" class="btn btn-primary btn-sm px-3 ml-1">
                <i class="fas fa-plus mr-1"></i> New Purchase
            </a>
        </div>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" id="filterForm" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold">Vendor</label>
                <select name="vendor_id" class="form-control form-control-sm">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $v)
                    <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    <option value="received"  {{ request('status') == 'received'  ? 'selected' : '' }}>Received</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-3 mr-2">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('inventory.purchases.index') }}" class="btn btn-secondary btn-sm px-3">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Purchase List
        </h6>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Quick search..." style="width:220px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="purchaseTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th style="font-size:12px;font-weight:700;color:#555;padding:10px 12px;">Purchase No</th>
                        <th style="font-size:12px;font-weight:700;color:#555;padding:10px 12px;">Date</th>
                        <th style="font-size:12px;font-weight:700;color:#555;padding:10px 12px;">Vendor</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;color:#555;padding:10px 12px;">Total</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;color:#555;padding:10px 12px;">Paid</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;color:#555;padding:10px 12px;">Due</th>
                        <th class="text-center" style="font-size:12px;font-weight:700;color:#555;padding:10px 12px;">Payment</th>
                        <th class="text-center" style="font-size:12px;font-weight:700;color:#555;padding:10px 12px;">Status</th>
                        <th class="text-center" style="width:170px;">Action</th>
                    </tr>
                </thead>
                <tbody id="purchaseTableBody">
                    @forelse($purchases as $purchase)
                    <tr id="purchase-row-{{ $purchase->id }}">
                        <td style="padding:10px 12px;" class="font-weight-bold">{{ $purchase->purchase_no }}</td>
                        <td style="padding:10px 12px;" class="text-muted small">{{ $purchase->purchase_date->format('d M Y') }}</td>
                        <td style="padding:10px 12px;">{{ $purchase->vendor->name ?? '—' }}</td>
                        <td style="padding:10px 12px;" class="text-right font-weight-bold">৳{{ number_format($purchase->total_amount, 2) }}</td>
                        <td style="padding:10px 12px;" class="text-right text-success">৳{{ number_format($purchase->paid_amount, 2) }}</td>
                        <td style="padding:10px 12px;" class="text-right {{ $purchase->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                            ৳{{ number_format($purchase->due_amount, 2) }}
                        </td>
                        <td style="padding:10px 12px;" class="text-center">{!! $purchase->paymentStatusBadge !!}</td>
                        <td style="padding:10px 12px;" class="text-center">{!! $purchase->statusBadge !!}</td>
                        <td style="padding:10px 12px;" class="text-center">
                            <button class="btn btn-sm btn-info px-2 btn-view-purchase"
                                    data-id="{{ $purchase->id }}" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($purchase->due_amount > 0 && !$purchase->isCancelled())
                            <button class="btn btn-sm btn-success px-2 btn-pay-purchase"
                                    data-id="{{ $purchase->id }}"
                                    data-no="{{ $purchase->purchase_no }}"
                                    data-due="{{ $purchase->due_amount }}"
                                    title="Pay">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                            @endif
                            @if($purchase->isEditable())
                            <a href="{{ route('inventory.purchases.edit', $purchase) }}"
                               class="btn btn-sm btn-warning px-2" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-shopping-cart fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No purchases found. Click <strong>+ New Purchase</strong> to add one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($purchases->hasPages())
    <div class="card-footer bg-light py-2">{{ $purchases->links() }}</div>
    @endif
</div>

{{-- ══ VIEW MODAL ══ --}}
<div class="modal fade" id="viewPurchaseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:10px; overflow:hidden;">
            <div class="modal-header border-0 py-3" style="background:linear-gradient(135deg,#1a237e,#283593);">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-shopping-cart mr-2"></i>Purchase — <span id="viewPurchaseNoLabel" class="text-warning"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0" id="viewPurchaseBody">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light px-4">
                <button type="button" class="btn btn-light border px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ PAY MODAL ══ --}}
<div class="modal fade" id="payPurchaseModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-lg" style="border-radius:10px; overflow:hidden;">
            <div class="modal-header border-0 py-3" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Pay — <span id="payPurchaseNo" class="text-warning"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="alert border-0 mb-3 py-2 px-3"
                     style="background:#e8f5e9; border-left:4px solid #2e7d32 !important; border-radius:6px;">
                    <i class="fas fa-info-circle text-success mr-1"></i>
                    Balance Due: <strong class="text-danger" id="payPurchaseDueLabel"></strong>
                    <br><small class="text-muted">Payment save হলে Accounting → Expense এ automatically record হবে।</small>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Amount <span class="text-danger">*</span></label>
                    <input type="number" id="payPurchaseAmount" class="form-control" min="0.01" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" id="payPurchaseDate" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Payment Method <span class="text-danger">*</span></label>
                    <select id="payPurchaseMethod" class="form-control">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="mobile_banking">Mobile Banking</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Reference No</label>
                    <input type="text" id="payPurchaseRef" class="form-control" placeholder="Optional">
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Note</label>
                    <input type="text" id="payPurchaseNote" class="form-control" placeholder="Optional">
                </div>
            </div>
            <div class="modal-footer border-0 bg-light px-4 py-3">
                <button type="button" class="btn btn-light border px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success px-4" id="btnConfirmPurchasePay">
                    <i class="fas fa-check mr-1"></i>Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    #purchaseTable tbody td { vertical-align: middle; }
    #purchaseTable tbody tr:hover { background:#f0f4ff !important; }
    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color:#1a237e; }
</style>
@stop

@section('js')
@parent
<script>
var CSRF = '{{ csrf_token() }}';

$(function () {
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#purchaseTableBody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });
});

// ── Export XLSX/PDF ──────────────────────────────────────────
$('#btnXlsx').on('click', function () {
    var params = $('#filterForm').serialize();
    window.location.href = '{{ route("inventory.purchases.export-xlsx") }}?' + params;
});
$('#btnPdf').on('click', function () {
    var params = $('#filterForm').serialize();
    window.open('{{ route("inventory.purchases.export-pdf") }}?' + params, '_blank');
});

// ── View Purchase Modal ────────────────────────────────────────
var currentViewPurchaseId = null;
$(document).on('click', '.btn-view-purchase', function () {
    var id = $(this).data('id');
    currentViewPurchaseId = id;
    $('#viewPurchaseBody').html('<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#viewPurchaseModal').modal('show');

    $.get('/inventory/purchases/' + id + '/detail', function (res) {
        var p = res.purchase;
        $('#viewPurchaseNoLabel').text(p.purchase_no);

        var itemsHtml = '';
        p.items.forEach(function (item) {
            itemsHtml += `<tr>
                <td>${item.product_name}</td>
                <td>${item.quantity}</td>
                <td class="text-right">৳ ${item.unit_price}</td>
                <td class="text-right font-weight-bold">৳ ${item.total_price}</td>
            </tr>`;
        });

        var paymentsHtml = '';
        if (p.payments.length) {
            p.payments.forEach(function (pay) {
                paymentsHtml += `<tr id="modal-pay-row-${pay.id}" class="${pay.is_void ? 'text-muted' : ''}">
                    <td>${pay.payment_date}</td>
                    <td class="text-right font-weight-bold ${pay.is_void ? 'text-muted' : 'text-success'}">৳ ${pay.amount}</td>
                    <td>${pay.method}</td>
                    <td>${pay.is_void ? '<span class="badge badge-secondary">Void</span>' : '<span class="badge badge-success">Active</span>'}</td>
                    <td>${!pay.is_void ? `
                        <button class="btn btn-xs btn-danger btn-void-purchase-payment"
                                data-purchase="${currentViewPurchaseId}" data-payment="${pay.id}"
                                style="font-size:11px; padding:2px 8px;">
                            <i class="fas fa-ban mr-1"></i>Void
                        </button>` : '—'}</td>
                </tr>`;
            });
        } else {
            paymentsHtml = '<tr><td colspan="5" class="text-center text-muted py-2">No payments yet</td></tr>';
        }

        $('#viewPurchaseBody').html(`
            <div class="p-3">
                <div class="row mb-3">
                    <div class="col-6"><strong>Vendor:</strong> ${p.vendor_name}</div>
                    <div class="col-6"><strong>Date:</strong> ${p.purchase_date}</div>
                    <div class="col-6"><strong>Location:</strong> ${p.location}</div>
                    <div class="col-6"><strong>Status:</strong> ${p.status_badge}</div>
                </div>
                <h6 class="font-weight-bold border-bottom pb-1 mb-2">Items</h6>
                <table class="table table-sm">
                    <thead><tr><th>Product</th><th>Qty</th><th class="text-right">Price</th><th class="text-right">Total</th></tr></thead>
                    <tbody>${itemsHtml}</tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-right font-weight-bold">Subtotal</td><td class="text-right">৳ ${p.subtotal}</td></tr>
                        <tr><td colspan="3" class="text-right">Discount</td><td class="text-right text-warning">- ৳ ${p.discount}</td></tr>
                        <tr><td colspan="3" class="text-right font-weight-bold">Total</td><td class="text-right font-weight-bold text-primary">৳ ${p.total_amount}</td></tr>
                        <tr><td colspan="3" class="text-right">Paid</td><td class="text-right text-success">৳ ${p.paid_amount}</td></tr>
                        <tr><td colspan="3" class="text-right">Due</td><td class="text-right text-danger font-weight-bold">৳ ${p.due_amount}</td></tr>
                    </tfoot>
                </table>
                <h6 class="font-weight-bold border-bottom pb-1 mb-2 mt-3">Payment History</h6>
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th class="text-right">Amount</th><th>Method</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>${paymentsHtml}</tbody>
                </table>
            </div>
        `);
    });
});

// ── Pay Purchase Modal ────────────────────────────────────────
var currentPurchasePayId  = null;
var currentPurchasePayDue = 0;

$(document).on('click', '.btn-pay-purchase', function () {
    currentPurchasePayId  = $(this).data('id');
    currentPurchasePayDue = parseFloat($(this).data('due'));
    $('#payPurchaseNo').text($(this).data('no'));
    $('#payPurchaseDueLabel').text('৳ ' + currentPurchasePayDue.toLocaleString('en-US', {minimumFractionDigits:2}));
    $('#payPurchaseAmount').val('').attr('max', currentPurchasePayDue);
    $('#payPurchaseRef, #payPurchaseNote').val('');
    $('#payPurchaseDate').val('{{ date("Y-m-d") }}');
    $('#payPurchaseModal').modal('show');
});

$('#btnConfirmPurchasePay').off('click').on('click', function () {
    var amount = parseFloat($('#payPurchaseAmount').val()) || 0;
    if (amount <= 0 || amount > currentPurchasePayDue) {
        toastr.error('Invalid amount.');
        return;
    }
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Processing...');

    $.ajax({
        url:    '/inventory/purchases/' + currentPurchasePayId + '/payment',
        method: 'POST',
        data: {
            _token:         CSRF,
            amount:         amount,
            payment_date:   $('#payPurchaseDate').val(),
            payment_method: $('#payPurchaseMethod').val(),
            reference_no:   $('#payPurchaseRef').val(),
            note:           $('#payPurchaseNote').val(),
        },
        success: function (res) {
            toastr.success(res.message);
            $('#payPurchaseModal').modal('hide');
            setTimeout(() => location.reload(), 1500);
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Payment failed.');
            $('#btnConfirmPurchasePay').prop('disabled', false).html('<i class="fas fa-check mr-1"></i>Confirm Payment');
        }
    });
});

// ── Void Purchase Payment ─────────────────────────────────────
$(document).on('click', '.btn-void-purchase-payment', function () {
    var purchaseId = $(this).data('purchase');
    var payId      = $(this).data('payment');

    $('#viewPurchaseModal').modal('hide');

    setTimeout(function () {
        Swal.fire({
            title: 'Void Payment?',
            html: `Payment void হবে।<br><strong class="text-success">Accounting Expense ও void হবে।</strong>`,
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Void reason (required)',
            inputAttributes: { autocomplete: 'off' },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Void',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            preConfirm: function (val) {
                if (!val || !val.trim()) Swal.showValidationMessage('Reason required.');
                return val;
            }
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({
                url:    '/inventory/purchases/' + purchaseId + '/payment/' + payId + '/void',
                method: 'POST',
                data:   { _token: CSRF, void_reason: r.value },
                success: function () {
                    toastr.success('Payment voided successfully.');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Void failed.');
                }
            });
        });
    }, 400);
});
</script>
@stop
