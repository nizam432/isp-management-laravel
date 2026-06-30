@extends('adminlte::page')
@section('title', 'New Purchase Return')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-plus-circle mr-2 text-primary"></i>New Purchase Return
            </h4>
        </div>
        <a href="{{ route('inventory.purchase-returns.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@endsection

@section('content')
@include('inventory._partials.alerts')

<form action="{{ route('inventory.purchase-returns.store') }}" method="POST" id="returnForm">
@csrf

<div class="row">
    <div class="col-md-8">

        {{-- Purchase Select --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-shopping-cart mr-1"></i> Select Purchase</h6>
            </div>
            <div class="card-body">
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Purchase Invoice <span class="text-danger">*</span></label>
                    <select name="purchase_id" id="purchaseSelect" class="form-control" required>
                        <option value="">-- Select Purchase --</option>
                        @foreach($purchases as $p)
                        <option value="{{ $p->id }}" {{ $purchase && $purchase->id == $p->id ? 'selected' : '' }}>
                            {{ $p->purchase_no }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="card shadow-sm" id="itemsCard" style="display:none;">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#c62828 0%,#e53935 100%);">
                <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-boxes mr-1"></i> Return Items</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                            <th style="padding:8px 10px;font-size:11px;">
                                <input type="checkbox" id="selectAllItems">
                            </th>
                            <th style="padding:8px 10px;font-size:11px;">Product</th>
                            <th style="padding:8px 10px;font-size:11px;">Purchased Qty</th>
                            <th style="padding:8px 10px;font-size:11px;">Returnable</th>
                            <th style="padding:8px 10px;font-size:11px;width:120px;">Return Qty</th>
                            <th style="padding:8px 10px;font-size:11px;text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Return Info --}}
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-info-circle mr-1"></i> Return Info</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-bold small">Return Date <span class="text-danger">*</span></label>
                    <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Reason</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="Why is this returned?"></textarea>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Note</label>
                    <textarea name="note" class="form-control" rows="2"></textarea>
                </div>
                <div class="alert py-2 mb-3" style="background:#fff3e0; border-left:4px solid #f57c00;">
                    <small>Total Return Amount (Item Value):</small>
                    <h5 class="mb-0 font-weight-bold text-danger" id="totalReturnDisplay">৳ 0.00</h5>
                </div>
                <div class="alert py-2 mb-3" style="background:#e8f5e9; border-left:4px solid #2e7d32;" id="expenseImpactBox" hidden>
                    <small>Expense Adjustment (Already Paid):</small>
                    <h6 class="mb-0 font-weight-bold" style="color:#2e7d32;" id="expenseImpactDisplay">৳ 0.00</h6>
                    <small class="text-muted" id="refundDueNote"></small>
                </div>
                <button type="submit" class="btn btn-danger btn-block" id="btnSubmitReturn" disabled>
                    <i class="fas fa-undo mr-1"></i> Process Return
                </button>
            </div>
        </div>
    </div>
</div>

</form>
@endsection

@section('js')
@parent
<script>
$(function () {
    var purchaseData = { paid_amount: 0, total_amount: 0 };

    var rowTpl = function (item) {
        return `<tr data-product="${item.product_name}" data-price="${item.unit_price}">
            <td style="padding:8px 10px;">
                <input type="checkbox" class="item-check" data-idx="${item.purchase_item_id}">
            </td>
            <td style="padding:8px 10px;">${item.product_name}</td>
            <td style="padding:8px 10px;">${item.quantity} ${item.unit}</td>
            <td style="padding:8px 10px;">${item.returnable_qty} ${item.unit}</td>
            <td style="padding:8px 10px;">
                <input type="hidden" name="items[${item.purchase_item_id}][purchase_item_id]" value="${item.purchase_item_id}">
                <input type="number" name="items[${item.purchase_item_id}][quantity]"
                       class="form-control form-control-sm qty-input"
                       min="0" max="${item.returnable_qty}" step="0.01" value="0" disabled
                       data-price="${item.unit_price}">
            </td>
            <td style="padding:8px 10px; text-align:right;" class="row-total">৳ 0.00</td>
        </tr>`;
    };

    function loadItems(purchaseId) {
        if (!purchaseId) {
            $('#itemsCard').hide();
            return;
        }
        $.get('/inventory/purchase-returns/purchase/' + purchaseId + '/items', function (res) {
            purchaseData.paid_amount  = res.purchase.paid_amount;
            purchaseData.total_amount = res.purchase.total_amount;

            var html = '';
            res.items.forEach(function (item) {
                if (item.returnable_qty > 0) {
                    html += rowTpl(item);
                }
            });
            $('#itemsBody').html(html);
            $('#itemsCard').show();
            calcTotal();
        });
    }

    $('#purchaseSelect').on('change', function () {
        loadItems($(this).val());
    });

    @if($purchase)
    loadItems({{ $purchase->id }});
    @endif

    $(document).on('change', '.item-check', function () {
        var row = $(this).closest('tr');
        var qtyInput = row.find('.qty-input');
        if (this.checked) {
            qtyInput.prop('disabled', false).val(qtyInput.attr('max'));
        } else {
            qtyInput.prop('disabled', true).val(0);
        }
        calcRow(row);
    });

    $(document).on('input', '.qty-input', function () {
        calcRow($(this).closest('tr'));
    });

    function calcRow(row) {
        var qty   = parseFloat(row.find('.qty-input').val()) || 0;
        var price = parseFloat(row.find('.qty-input').data('price')) || 0;
        var total = qty * price;
        row.find('.row-total').text('৳ ' + total.toLocaleString('en-US', {minimumFractionDigits:2}));
        calcTotal();
    }

    function calcTotal() {
        var total = 0;
        $('.qty-input:not(:disabled)').each(function () {
            var qty   = parseFloat($(this).val()) || 0;
            var price = parseFloat($(this).data('price')) || 0;
            total += qty * price;
        });
        $('#totalReturnDisplay').text('৳ ' + total.toLocaleString('en-US', {minimumFractionDigits:2}));
        $('#btnSubmitReturn').prop('disabled', total <= 0);

        if (total > 0 && purchaseData.paid_amount > 0) {
            var expenseImpact = Math.min(total, purchaseData.paid_amount);
            $('#expenseImpactDisplay').text('৳ ' + expenseImpact.toLocaleString('en-US', {minimumFractionDigits:2}));
            $('#expenseImpactBox').prop('hidden', false);

            var newTotal = Math.max(0, purchaseData.total_amount - total);
            var refundDue = Math.max(0, purchaseData.paid_amount - newTotal);
            if (refundDue > 0) {
                $('#refundDueNote').text('Refund Due from vendor: ৳' + refundDue.toLocaleString('en-US', {minimumFractionDigits:2}));
            } else {
                $('#refundDueNote').text('');
            }
        } else {
            $('#expenseImpactBox').prop('hidden', true);
        }
    }

    $('#selectAllItems').on('change', function () {
        $('.item-check').prop('checked', this.checked).trigger('change');
    });
});
</script>
@stop
