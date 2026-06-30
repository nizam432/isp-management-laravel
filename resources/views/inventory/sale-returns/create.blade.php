@extends('adminlte::page')
@section('title', 'New Sale Return')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-plus-circle mr-2 text-primary"></i>New Sale Return
            </h4>
        </div>
        <a href="{{ route('inventory.sale-returns.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@endsection

@section('content')
@include('inventory._partials.alerts')

<form action="{{ route('inventory.sale-returns.store') }}" method="POST" id="returnForm">
@csrf

<div class="row">
    <div class="col-md-8">

        {{-- Sale Select --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-receipt mr-1"></i> Select Sale</h6>
            </div>
            <div class="card-body">
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Sale Invoice <span class="text-danger">*</span></label>
                    <select name="sale_id" id="saleSelect" class="form-control" required>
                        <option value="">-- Select Sale --</option>
                        @foreach($sales as $s)
                        <option value="{{ $s->id }}" {{ $sale && $sale->id == $s->id ? 'selected' : '' }}>
                            {{ $s->invoice_no }} ({{ $s->sale_no }})
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
                            <th style="padding:8px 10px;font-size:11px;">Sold Qty</th>
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
                    <label class="font-weight-bold small">Refund Type <span class="text-danger">*</span></label>
                    <select name="refund_type" class="form-control" required>
                        <option value="cash">Cash Refund</option>
                        <option value="adjust">Adjust Due</option>
                        <option value="none">No Refund</option>
                    </select>
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
                <div class="alert py-2 mb-3" style="background:#e8f5e9; border-left:4px solid #2e7d32;" id="incomeImpactBox" hidden>
                    <small>Income Adjustment (Already Received):</small>
                    <h6 class="mb-0 font-weight-bold" style="color:#2e7d32;" id="incomeImpactDisplay">৳ 0.00</h6>
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
    var saleData = { paid_amount: 0, total_amount: 0 };

    var rowTpl = function (item) {
        return `<tr data-product="${item.product_name}" data-price="${item.unit_price}">
            <td style="padding:8px 10px;">
                <input type="checkbox" class="item-check" data-idx="${item.sale_item_id}">
            </td>
            <td style="padding:8px 10px;">${item.product_name}</td>
            <td style="padding:8px 10px;">${item.quantity} ${item.unit}</td>
            <td style="padding:8px 10px;">${item.returnable_qty} ${item.unit}</td>
            <td style="padding:8px 10px;">
                <input type="hidden" name="items[${item.sale_item_id}][sale_item_id]" value="${item.sale_item_id}">
                <input type="number" name="items[${item.sale_item_id}][quantity]"
                       class="form-control form-control-sm qty-input"
                       min="0" max="${item.returnable_qty}" step="0.01" value="0" disabled
                       data-price="${item.unit_price}">
            </td>
            <td style="padding:8px 10px; text-align:right;" class="row-total">৳ 0.00</td>
        </tr>`;
    };

    function loadItems(saleId) {
        if (!saleId) {
            $('#itemsCard').hide();
            return;
        }
        $.get('/inventory/sale-returns/sale/' + saleId + '/items', function (res) {
            saleData.paid_amount  = res.sale.paid_amount;
            saleData.total_amount = res.sale.total_amount;

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

    $('#saleSelect').on('change', function () {
        loadItems($(this).val());
    });

    // Init if sale pre-selected
    @if($sale)
    loadItems({{ $sale->id }});
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

        // ── Income Impact = min(return total, paid amount) ──────
        if (total > 0 && saleData.paid_amount > 0) {
            var incomeImpact = Math.min(total, saleData.paid_amount);
            $('#incomeImpactDisplay').text('৳ ' + incomeImpact.toLocaleString('en-US', {minimumFractionDigits:2}));
            $('#incomeImpactBox').prop('hidden', false);

            var newTotal = Math.max(0, saleData.total_amount - total);
            var refundDue = Math.max(0, saleData.paid_amount - newTotal);
            if (refundDue > 0) {
                $('#refundDueNote').text('Refund Due to customer: ৳' + refundDue.toLocaleString('en-US', {minimumFractionDigits:2}));
            } else {
                $('#refundDueNote').text('');
            }
        } else {
            $('#incomeImpactBox').prop('hidden', true);
        }
    }

    $('#selectAllItems').on('change', function () {
        $('.item-check').prop('checked', this.checked).trigger('change');
    });
});
</script>
@stop
