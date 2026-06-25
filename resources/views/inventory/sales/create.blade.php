@extends('layouts.app')
@section('title', 'New Purchase')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">New Purchase</h4>
        <a href="{{ route('inventory.purchases.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
    </div>
    <form action="{{ route('inventory.purchases.store') }}" method="POST" id="purchaseForm">
        @csrf
        <div class="row g-3">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-semibold">Purchase Info</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Vendor *</label>
                                <select name="vendor_id" class="form-select" required>
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $v)
                                    <option value="{{ $v->id }}" {{ old('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Store Location *</label>
                                <select name="location_id" class="form-select" required>
                                    <option value="">Select Location</option>
                                    @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Purchase Date *</label>
                                <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Invoice No</label>
                                <input type="text" name="invoice_no" class="form-control" value="{{ old('invoice_no') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                        Purchase Items
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">+ Add Item</button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th><th></th></tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr>
                                    <td>
                                        <select name="items[0][product_id]" class="form-select form-select-sm" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->purchase_price }}">{{ $p->name }} ({{ $p->unit }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm qty" min="0.01" step="0.01" required onchange="calcRow(this)"></td>
                                    <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm price" min="0" step="0.01" required onchange="calcRow(this)"></td>
                                    <td><input type="number" name="items[0][total]" class="form-control form-control-sm total-field" readonly></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">✕</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Summary --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Summary</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Subtotal</label>
                            <input type="number" id="subtotalDisplay" class="form-control" readonly value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount</label>
                            <input type="number" name="discount" class="form-control" value="0" min="0" step="0.01" onchange="calcTotal()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tax</label>
                            <input type="number" name="tax" class="form-control" value="0" min="0" step="0.01" onchange="calcTotal()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total</label>
                            <input type="number" id="totalDisplay" class="form-control fw-bold" readonly value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Purchase</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@push('scripts')
<script>
let rowIndex = 1;
function addRow() {
    const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name . ' (' . $p->unit . ')', 'price' => $p->purchase_price]));
    let opts = '<option value="">Select Product</option>' + products.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`).join('');
    const row = `<tr>
        <td><select name="items[${rowIndex}][product_id]" class="form-select form-select-sm" required onchange="fillPrice(this)">${opts}</select></td>
        <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm qty" min="0.01" step="0.01" required onchange="calcRow(this)"></td>
        <td><input type="number" name="items[${rowIndex}][unit_price]" class="form-control form-control-sm price" min="0" step="0.01" required onchange="calcRow(this)"></td>
        <td><input type="number" name="items[${rowIndex}][total]" class="form-control form-control-sm total-field" readonly></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">✕</button></td>
    </tr>`;
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);
    rowIndex++;
}
function fillPrice(sel) {
    const price = sel.options[sel.selectedIndex].dataset.price;
    const row = sel.closest('tr');
    row.querySelector('.price').value = price;
    calcRow(row.querySelector('.qty'));
}
function calcRow(input) {
    const row = input.closest('tr');
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
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
    const tax = parseFloat(document.querySelector('[name=tax]').value) || 0;
    document.getElementById('totalDisplay').value = (subtotal - discount + tax).toFixed(2);
}
document.querySelector('[name="items[0][product_id]"]').addEventListener('change', function() { fillPrice(this); });
</script>
@endpush
@endsection
