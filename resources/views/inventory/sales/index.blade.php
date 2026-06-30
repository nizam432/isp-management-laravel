@extends('adminlte::page')
@section('title', 'Sales')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-receipt mr-2 text-primary"></i>Sales
            </h4>
            <small class="text-muted">Manage all inventory sales</small>
        </div>
        <div>
            <button class="btn btn-success btn-sm px-3" id="btnXlsx">
                <i class="fas fa-file-excel mr-1"></i> XLSX
            </button>
            <button class="btn btn-danger btn-sm px-3 ml-1" id="btnPdf">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </button>
            <a href="{{ route('inventory.sales.create') }}" class="btn btn-primary btn-sm px-3 ml-1">
                <i class="fas fa-plus mr-1"></i> New Sale
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
                <label class="small font-weight-bold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Sale / Invoice No..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
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
                <a href="{{ route('inventory.sales.index') }}" class="btn btn-secondary btn-sm px-3">
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
            <i class="fas fa-list mr-1"></i> Sale List
        </h6>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Quick search..." style="width:220px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="saleTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Invoice No</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Date</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Customer</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Total</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Paid</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Due</th>
                        <th class="text-center" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Payment</th>
                        <th class="text-center" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Status</th>
                        <th class="text-center" style="width:170px;">Action</th>
                    </tr>
                </thead>
                <tbody id="saleTableBody">
                    @forelse($sales as $sale)
                    <tr id="sale-row-{{ $sale->id }}">
                        <td style="padding:10px 12px;">
                            <span class="font-weight-bold">{{ $sale->invoice_no }}</span>
                            <br><small class="text-muted">{{ $sale->sale_no }}</small>
                        </td>
                        <td style="padding:10px 12px;" class="text-muted small">{{ $sale->sale_date->format('d M Y') }}</td>
                        <td style="padding:10px 12px;">{{ $sale->customer_name }}</td>
                        <td style="padding:10px 12px;" class="text-right font-weight-bold">৳{{ number_format($sale->total_amount, 2) }}</td>
                        <td style="padding:10px 12px;" class="text-right text-success">৳{{ number_format($sale->paid_amount, 2) }}</td>
                        <td style="padding:10px 12px;" class="text-right {{ $sale->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                            ৳{{ number_format($sale->due_amount, 2) }}
                        </td>
                        <td style="padding:10px 12px;" class="text-center">{!! $sale->paymentStatusBadge !!}</td>
                        <td style="padding:10px 12px;" class="text-center">{!! $sale->statusBadge !!}</td>
                        <td style="padding:10px 12px;" class="text-center">
                            <button class="btn btn-sm btn-info px-2 btn-view-sale"
                                    data-id="{{ $sale->id }}" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($sale->due_amount > 0 && !$sale->isCancelled())
                            <button class="btn btn-sm btn-success px-2 btn-pay-sale"
                                    data-id="{{ $sale->id }}"
                                    data-invoice="{{ $sale->invoice_no }}"
                                    data-due="{{ $sale->due_amount }}"
                                    title="Pay">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                            @endif
                            <a href="{{ route('inventory.sales.invoice-pdf', $sale) }}"
                               class="btn btn-sm btn-danger px-2" title="Download Invoice">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            @if($sale->isEditable())
                            <a href="{{ route('inventory.sales.edit', $sale) }}"
                               class="btn btn-sm btn-warning px-2" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No sales found. Click <strong>+ New Sale</strong> to add one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sales->hasPages())
    <div class="card-footer bg-light py-2">{{ $sales->links() }}</div>
    @endif
</div>

{{-- ══ VIEW MODAL ══ --}}
<div class="modal fade" id="viewSaleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:10px; overflow:hidden;">
            <div class="modal-header border-0 py-3" style="background:linear-gradient(135deg,#1a237e,#283593);">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-receipt mr-2"></i>Sale — <span id="viewInvoiceLabel" class="text-warning"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0" id="viewSaleBody">
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
<div class="modal fade" id="paySaleModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-lg" style="border-radius:10px; overflow:hidden;">
            <div class="modal-header border-0 py-3" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Pay — <span id="paySaleInvoice" class="text-warning"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="alert border-0 mb-3 py-2 px-3"
                     style="background:#e8f5e9; border-left:4px solid #2e7d32 !important; border-radius:6px;">
                    <i class="fas fa-info-circle text-success mr-1"></i>
                    Balance Due: <strong class="text-danger" id="paySaleDueLabel"></strong>
                    <br><small class="text-muted">Payment save হলে Accounting → Income এ automatically record হবে।</small>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Amount <span class="text-danger">*</span></label>
                    <input type="number" id="paySaleAmount" class="form-control" min="0.01" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" id="paySaleDate" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Payment Method <span class="text-danger">*</span></label>
                    <select id="paySaleMethod" class="form-control">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="mobile_banking">Mobile Banking</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Reference No</label>
                    <input type="text" id="paySaleRef" class="form-control" placeholder="Optional">
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Note</label>
                    <input type="text" id="paySaleNote" class="form-control" placeholder="Optional">
                </div>
            </div>
            <div class="modal-footer border-0 bg-light px-4 py-3">
                <button type="button" class="btn btn-light border px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success px-4" id="btnConfirmSalePay">
                    <i class="fas fa-check mr-1"></i>Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    #saleTable tbody td { vertical-align: middle; }
    #saleTable tbody tr:hover { background:#f0f4ff !important; }
    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color:#1a237e; }
</style>
@stop

@section('js')
@parent
<script>
var CSRF = '{{ csrf_token() }}';

// ── Export XLSX ───────────────────────────────────────────────
$('#btnXlsx').on('click', function () {
    var params = $('#filterForm').serialize();
    window.location.href = '{{ route("inventory.sales.export-xlsx") }}?' + params;
});

// ── Export PDF ────────────────────────────────────────────────
$('#btnPdf').on('click', function () {
    var params = $('#filterForm').serialize();
    window.open('{{ route("inventory.sales.export-pdf") }}?' + params, '_blank');
});

$(function () {
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#saleTableBody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });
});

// ── View Sale Modal ───────────────────────────────────────────
var currentViewSaleId = null;
$(document).on('click', '.btn-view-sale', function () {
    var id = $(this).data('id');
    currentViewSaleId = id;
    $('#viewSaleBody').html('<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#viewSaleModal').modal('show');

    loadSaleDetail(id);
});

function loadSaleDetail(id) {
    $.get('/inventory/sales/' + id + '/detail', function (res) {
        var s = res.sale;
        $('#viewInvoiceLabel').text(s.invoice_no);

        var itemsHtml = '';
        s.items.forEach(function (item) {
            itemsHtml += `<tr>
                <td>${item.product_name}</td>
                <td>${item.quantity}</td>
                <td class="text-right">৳ ${item.unit_price}</td>
                <td class="text-right">৳ ${item.discount}</td>
                <td class="text-right font-weight-bold">৳ ${item.total_price}</td>
            </tr>`;
        });

        var paymentsHtml = '';
        if (s.payments.length) {
            s.payments.forEach(function (p) {
                paymentsHtml += `<tr id="modal-pay-row-${p.id}" class="${p.is_void ? 'text-muted' : ''}">
                    <td>${p.payment_date}</td>
                    <td class="text-right font-weight-bold ${p.is_void ? 'text-muted' : 'text-success'}">৳ ${p.amount}</td>
                    <td>${p.method}</td>
                    <td>${p.is_void ? '<span class="badge badge-secondary">Void</span>' : '<span class="badge badge-success">Active</span>'}</td>
                    <td>${!p.is_void ? `
                        <button class="btn btn-xs btn-danger btn-void-sale-payment"
                                data-sale="${currentViewSaleId}" data-payment="${p.id}"
                                style="font-size:11px; padding:2px 8px;">
                            <i class="fas fa-ban mr-1"></i>Void
                        </button>` : '—'}</td>
                </tr>`;
            });
        } else {
            paymentsHtml = '<tr><td colspan="5" class="text-center text-muted py-2">No payments yet</td></tr>';
        }

        $('#viewSaleBody').html(`
            <div class="p-3">
                <div class="row mb-3">
                    <div class="col-6"><strong>Customer:</strong> ${s.customer_name}</div>
                    <div class="col-6"><strong>Date:</strong> ${s.sale_date}</div>
                    <div class="col-6"><strong>Location:</strong> ${s.location}</div>
                    <div class="col-6"><strong>Status:</strong> ${s.status_badge}</div>
                </div>
                <h6 class="font-weight-bold border-bottom pb-1 mb-2">Items</h6>
                <table class="table table-sm">
                    <thead><tr><th>Product</th><th>Qty</th><th class="text-right">Price</th><th class="text-right">Discount</th><th class="text-right">Total</th></tr></thead>
                    <tbody>${itemsHtml}</tbody>
                    <tfoot>
                        <tr><td colspan="4" class="text-right font-weight-bold">Subtotal</td><td class="text-right">৳ ${s.subtotal}</td></tr>
                        <tr><td colspan="4" class="text-right">Discount</td><td class="text-right text-warning">- ৳ ${s.discount}</td></tr>
                        <tr><td colspan="4" class="text-right font-weight-bold">Total</td><td class="text-right font-weight-bold text-primary">৳ ${s.total_amount}</td></tr>
                        <tr><td colspan="4" class="text-right">Paid</td><td class="text-right text-success">৳ ${s.paid_amount}</td></tr>
                        <tr><td colspan="4" class="text-right">Due</td><td class="text-right text-danger font-weight-bold">৳ ${s.due_amount}</td></tr>
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
}

// ── Void Sale Payment ─────────────────────────────────────────
$(document).on('click', '.btn-void-sale-payment', function () {
    var saleId = $(this).data('sale');
    var payId  = $(this).data('payment');

    $('#viewSaleModal').modal('hide');

    setTimeout(function () {
        Swal.fire({
            title: 'Void Payment?',
            html: `Payment void হবে।<br><strong class="text-success">Accounting Income ও void হবে।</strong>`,
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
                url:    '/inventory/sales/' + saleId + '/payment/' + payId + '/void',
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
var currentSalePayId  = null;
var currentSalePayDue = 0;

$(document).on('click', '.btn-pay-sale', function () {
    currentSalePayId  = $(this).data('id');
    currentSalePayDue = parseFloat($(this).data('due'));
    $('#paySaleInvoice').text($(this).data('invoice'));
    $('#paySaleDueLabel').text('৳ ' + currentSalePayDue.toLocaleString('en-US', {minimumFractionDigits:2}));
    $('#paySaleAmount').val('').attr('max', currentSalePayDue);
    $('#paySaleRef, #paySaleNote').val('');
    $('#paySaleDate').val('{{ date("Y-m-d") }}');
    $('#paySaleModal').modal('show');
});

$('#btnConfirmSalePay').off('click').on('click', function () {
    var amount = parseFloat($('#paySaleAmount').val()) || 0;
    if (amount <= 0 || amount > currentSalePayDue) {
        toastr.error('Invalid amount.');
        return;
    }
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Processing...');

    $.ajax({
        url:    '/inventory/sales/' + currentSalePayId + '/payment',
        method: 'POST',
        data: {
            _token:         CSRF,
            amount:         amount,
            payment_date:   $('#paySaleDate').val(),
            payment_method: $('#paySaleMethod').val(),
            reference_no:   $('#paySaleRef').val(),
            note:           $('#paySaleNote').val(),
        },
        success: function (res) {
            toastr.success(res.message);
            $('#paySaleModal').modal('hide');
            setTimeout(() => location.reload(), 1500);
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Payment failed.');
            $('#btnConfirmSalePay').prop('disabled', false).html('<i class="fas fa-check mr-1"></i>Confirm Payment');
        }
    });
});
</script>
@stop
