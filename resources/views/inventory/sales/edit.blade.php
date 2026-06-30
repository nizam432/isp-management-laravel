@extends('adminlte::page')
@section('title', 'Edit Sale')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-edit mr-2 text-warning"></i>Edit Sale — {{ $sale->invoice_no }}
            </h4>
            <small class="text-muted">No payments yet — safe to edit</small>
        </div>
        <a href="{{ route('inventory.sales.show', $sale) }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<form action="{{ route('inventory.sales.update', $sale) }}" method="POST" id="saleForm">
@csrf
@method('PUT')

<div class="row">
    <div class="col-md-8">

        {{-- Sale Info --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-info-circle mr-1"></i> Sale Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold small">Customer <span class="text-danger">*</span></label>
                        <select name="client_id" id="customerSelect" class="form-control">
                            <option value="">-- Walk-in Customer --</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}"
                                    data-name="{{ $c->name }}"
                                    data-phone="{{ $c->phone }}"
                                    {{ old('client_id', $sale->client_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} @if($c->phone)({{ $c->phone }})@endif
                            </option>
                            @endforeach
                        </select>
                        <input type="text" name="walk_in_name" id="walkInName"
                               class="form-control mt-2"
                               value="{{ old('walk_in_name', $sale->walk_in_name ?? 'Walk-in Customer') }}"
                               placeholder="Walk-in customer name"
                               {{ $sale->client_id ? 'readonly' : '' }}>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold small">Store Location <span class="text-danger">*</span></label>
                        <select name="location_id" class="form-control @error('location_id') is-invalid @enderror" required>
                            <option value="">-- Select Location --</option>
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('location_id', $sale->location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        @error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold small">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" name="sale_date" class="form-control"
                               value="{{ old('sale_date', $sale->sale_date->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4 form-group mb-0">
                        <label class="font-weight-bold small">Sale Type</label>
                        <select name="sale_type" class="form-control">
                            <option value="cash"   {{ old('sale_type', $sale->sale_type) == 'cash'   ? 'selected' : '' }}>Cash</option>
                            <option value="credit" {{ old('sale_type', $sale->sale_type) == 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="card shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between align-items-center"
                 style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-boxes mr-1"></i> Sale Items
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
                                <th style="padding:10px 12px;font-size:12px;color:#555;width:100px;">Qty</th>
                                <th style="padding:10px 12px;font-size:12px;color:#555;width:120px;">Unit Price (৳)</th>
                                <th style="padding:10px 12px;font-size:12px;color:#555;width:110px;">Discount (৳)</th>
                                <th style="padding:10px 12px;font-size:12px;color:#555;width:120px;">Total (৳)</th>
                                <th style="width:46px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @foreach($sale->items as $i => $item)
                            <tr>
                                <td style="padding:6px 10px;">
                                    <select name="items[{{ $i }}][product_id]" class="form-control form-control-sm" required onchange="fillPrice(this)">
                                        <option value="">-- Select Product --</option>
                                        @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->sell_price }}"
                                            {{ $item->product_id == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }} (Stock: {{ $p->stock_quantity }} {{ $p->unit }})
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="padding:6px 10px;">
                                    <input type="number" name="items[{{ $i }}][quantity]" class="form-control form-control-sm qty"
                                           min="0.01" step="0.01" required onchange="calcRow(this)" value="{{ $item->quantity }}">
                                </td>
                                <td style="padding:6px 10px;">
                                    <input type="number" name="items[{{ $i }}][unit_price]" class="form-control form-control-sm price"
                                           min="0" step="0.01" required onchange="calcRow(this)" value="{{ $item->unit_price }}">
                                </td>
                                <td style="padding:6px 10px;">
                                    <input type="number" name="items[{{ $i }}][discount]" class="form-control form-control-sm item-discount"
                                           min="0" step="0.01" value="{{ $item->discount }}" onchange="calcRow(this)">
                                </td>
                                <td style="padding:6px 10px;">
                                    <input type="number" name="items[{{ $i }}][total]" class="form-control form-control-sm total-field"
                                           readonly value="{{ $item->total_price }}">
                                </td>
                                <td style="padding:6px 10px;">
                                    <button type="button" class="btn btn-sm btn-danger px-2" onclick="removeRow(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
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
                        <input type="number" id="subtotalDisplay" class="form-control" readonly value="{{ $sale->subtotal }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Extra Discount</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                        <input type="number" name="discount" class="form-control" value="{{ old('discount', $sale->discount) }}" min="0" step="0.01" onchange="calcTotal()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Grand Total</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                        <input type="number" id="totalDisplay" class="form-control font-weight-bold" readonly value="{{ $sale->total_amount }}"
                               style="background:#e8f4fd; color:#1a237e;">
                    </div>
                </div>
                <div class="alert py-2" style="background:#fff3e0; border-left:4px solid #f57c00;">
                    <small><i class="fas fa-info-circle mr-1"></i>
                    Payment add করা যাবে Sale view page থেকে, এখান থেকে নয়।</small>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Note</label>
                    <textarea name="note" class="form-control" rows="3">{{ old('note', $sale->note) }}</textarea>
                </div>
                <button type="submit" class="btn btn-warning btn-block">
                    <i class="fas fa-save mr-1"></i> Update Sale
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
let rowIndex = {{ $sale->items->count() }};
const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name . ' (Stock: ' . $p->stock_quantity . ' ' . $p->unit . ')', 'price' => $p->sell_price]));

// ── Customer select handler ─────────────────────────────────────
$('#customerSelect').on('change', function () {
    var opt = $(this).find(':selected');
    if ($(this).val()) {
        $('#walkInName').val('').prop('readonly', true).attr('placeholder', opt.data('name'));
    } else {
        $('#walkInName').val('Walk-in Customer').prop('readonly', false);
    }
});

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
            <input type="number" name="items[${rowIndex}][discount]" class="form-control form-control-sm item-discount" min="0" step="0.01" value="0" onchange="calcRow(this)" placeholder="0.00">
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
    const qty      = parseFloat(row.querySelector('.qty').value)           || 0;
    const price    = parseFloat(row.querySelector('.price').value)         || 0;
    const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
    row.querySelector('.total-field').value = Math.max(0, (qty * price) - discount).toFixed(2);
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
    document.getElementById('totalDisplay').value = Math.max(0, subtotal - discount).toFixed(2);
}
</script>
@stop
