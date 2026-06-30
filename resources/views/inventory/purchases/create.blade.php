@extends('adminlte::page')
@section('title', 'New Purchase')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-plus-circle mr-2 text-primary"></i>New Purchase
            </h4>
            <small class="text-muted">Create a new inventory purchase</small>
        </div>
        <a href="{{ route('inventory.purchases.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back to Purchases
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<form action="{{ route('inventory.purchases.store') }}" method="POST" id="purchaseForm">
@csrf

<div class="row">
    <div class="col-md-8">

        {{-- Purchase Info --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-info-circle mr-1"></i> Purchase Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold small">Vendor <span class="text-danger">*</span></label>
                        <select name="vendor_id" class="form-control @error('vendor_id') is-invalid @enderror" required>
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ old('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                            @endforeach
                        </select>
                        @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold small">Store Location <span class="text-danger">*</span></label>
                        <select name="location_id" class="form-control @error('location_id') is-invalid @enderror" required>
                            <option value="">-- Select Location --</option>
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        @error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold small">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" class="form-control"
                               value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4 form-group mb-0">
                        <label class="font-weight-bold small">Invoice No</label>
                        <input type="text" name="invoice_no" class="form-control"
                               value="{{ old('invoice_no') }}" placeholder="Vendor invoice number">
                    </div>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="card shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between align-items-center"
                 style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-boxes mr-1"></i> Purchase Items
                </h6>
                <button type="button" class="btn btn-light btn-sm px-3" onclick="addRow()">
                    <i class="fas fa-plus mr-1"></i> Add Item
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="itemsTable">
                        <thead>
                            <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                                <th style="padding:10px 12px;font-size:12px;color:#555;">Product</th>
                                <th style="padding:10px 12px;font-size:12px;color:#555;width:110px;">Qty</th>
                                <th style="padding:10px 12px;font-size:12px;color:#555;width:130px;">Unit Price (৳)</th>
                                <th style="padding:10px 12px;font-size:12px;color:#555;width:130px;">Total (৳)</th>
                                <th style="width:46px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr>
                                <td style="padding:6px 10px;">
                                    <select name="items[0][product_id]" class="form-control form-control-sm" required onchange="fillPrice(this)">
                                        <option value="">-- Select Product --</option>
                                        @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->purchase_price }}">
                                            {{ $p->name }} ({{ $p->unit }})
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="padding:6px 10px;">
                                    <input type="number" name="items[0][quantity]" class="form-control form-control-sm qty"
                                           min="0.01" step="0.01" required onchange="calcRow(this)" placeholder="0">
                                </td>
                                <td style="padding:6px 10px;">
                                    <input type="number" name="items[0][unit_price]" class="form-control form-control-sm price"
                                           min="0" step="0.01" required onchange="calcRow(this)" placeholder="0.00">
                                </td>
                                <td style="padding:6px 10px;">
                                    <input type="number" name="items[0][total]" class="form-control form-control-sm total-field"
                                           readonly placeholder="0.00">
                                </td>
                                <td style="padding:6px 10px;">
                                    <button type="button" class="btn btn-sm btn-danger px-2" onclick="removeRow(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Summary --}}
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-calculator mr-1"></i> Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-bold small">Subtotal</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                        <input type="number" id="subtotalDisplay" class="form-control" readonly value="0.00">
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Discount</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                        <input type="number" name="discount" class="form-control" value="0" min="0" step="0.01" onchange="calcTotal()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Tax</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                        <input type="number" name="tax" class="form-control" value="0" min="0" step="0.01" onchange="calcTotal()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Grand Total</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                        <input type="number" id="totalDisplay" class="form-control font-weight-bold" readonly value="0.00"
                               style="background:#e8f4fd; color:#1a237e;">
                    </div>
                </div>

                {{-- Paid input row --}}
                <div class="form-group">
                    <label class="font-weight-bold small">Paid Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                        <input type="number" name="paid_amount" id="paidInput" class="form-control" value="0" min="0" step="0.01" onchange="updatePaymentVisibility()">
                    </div>
                </div>

                {{-- Payment method — paid > 0 হলে দেখাবে --}}
                <div class="form-group" id="paymentMethodWrap" style="display:none;">
                    <label class="font-weight-bold small">Payment Method</label>
                    <select name="payment_method" id="paymentMethodSelect" class="form-control">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="mobile_banking">Mobile Banking</option>
                    </select>
                    <input type="text" name="payment_reference" class="form-control mt-2"
                           placeholder="Reference No (optional)">
                </div>

                <div class="form-group">
                    <label class="font-weight-bold small">Note</label>
                    <textarea name="note" class="form-control" rows="3"
                              placeholder="Optional note for this purchase">{{ old('note') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save mr-1"></i> Save Purchase
                </button>
            </div>
        </div>
    </div>
</div>

</form>

@endsection

@section('css')
<style>
    .card-header h6 { font-size: 13px; letter-spacing: .3px; }
    .form-group label { color: #555; }
    .input-group-text { background:#f4f6f9; border-color:#ced4da; }
    #itemsTable tbody tr:hover { background:#f0f4ff; }
</style>
@stop

@section('js')
@parent
<script>
let rowIndex = 1;
const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name . ' (' . $p->unit . ')', 'price' => $p->purchase_price]));

function addRow() {
    let opts = '<option value="">-- Select Product --</option>' +
        products.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`).join('');
    const row = `<tr>
        <td style="padding:6px 10px;">
            <select name="items[${rowIndex}][product_id]" class="form-control form-control-sm" required onchange="fillPrice(this)">${opts}</select>
        </td>
        <td style="padding:6px 10px;">
            <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm qty" min="0.01" step="0.01" required onchange="calcRow(this)" placeholder="0">
        </td>
        <td style="padding:6px 10px;">
            <input type="number" name="items[${rowIndex}][unit_price]" class="form-control form-control-sm price" min="0" step="0.01" required onchange="calcRow(this)" placeholder="0.00">
        </td>
        <td style="padding:6px 10px;">
            <input type="number" name="items[${rowIndex}][total]" class="form-control form-control-sm total-field" readonly placeholder="0.00">
        </td>
        <td style="padding:6px 10px;">
            <button type="button" class="btn btn-sm btn-danger px-2" onclick="removeRow(this)">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>`;
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);
    rowIndex++;
}

function fillPrice(sel) {
    const price = sel.options[sel.selectedIndex].dataset.price || 0;
    const row = sel.closest('tr');
    row.querySelector('.price').value = price;
    calcRow(row.querySelector('.qty'));
}

function calcRow(input) {
    const row = input.closest('tr');
    const qty   = parseFloat(row.querySelector('.qty').value)   || 0;
    const price = parseFloat(row.querySelector('.price').value) || 0;
    row.querySelector('.total-field').value = (qty * price).toFixed(2);
    calcTotal();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calcTotal();
}

function calcTotal() {
    let subtotal = 0;
    document.querySelectorAll('.total-field').forEach(f => subtotal += parseFloat(f.value) || 0);
    document.getElementById('subtotalDisplay').value = subtotal.toFixed(2);
    const discount = parseFloat(document.querySelector('[name=discount]').value) || 0;
    const tax      = parseFloat(document.querySelector('[name=tax]').value)      || 0;
    const total    = Math.max(0, subtotal - discount + tax);
    document.getElementById('totalDisplay').value = total.toFixed(2);

    var paidInput = document.getElementById('paidInput');
    if (parseFloat(paidInput.value) > total) {
        paidInput.value = total.toFixed(2);
    }
    paidInput.max = total;
}

function updatePaymentVisibility() {
    var paid = parseFloat(document.getElementById('paidInput').value) || 0;
    var wrap = document.getElementById('paymentMethodWrap');
    if (paid > 0) {
        wrap.style.display = 'block';
        document.getElementById('paymentMethodSelect').setAttribute('required', true);
    } else {
        wrap.style.display = 'none';
        document.getElementById('paymentMethodSelect').removeAttribute('required');
    }
}

document.querySelector('[name="items[0][product_id]"]').addEventListener('change', function () {
    fillPrice(this);
});
</script>
@stop
