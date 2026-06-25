@extends('layouts.app')
@section('title', 'Stock Adjustment')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Stock Adjustment</h4>
    </div>
    @include('inventory._partials.alerts')
    <div class="row g-3">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">New Adjustment</div>
                <div class="card-body">
                    <form action="{{ route('inventory.stock.adjustment') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Product *</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Product</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (Stock: {{ $p->stock_quantity }} {{ $p->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location *</label>
                            <select name="location_id" class="form-select" required>
                                <option value="">Select Location</option>
                                @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="adjustment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="add">Add (Stock বাড়াও)</option>
                                <option value="subtract">Subtract (Stock কমাও)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity *</label>
                            <input type="number" name="quantity" class="form-control" min="0.01" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason *</label>
                            <input type="text" name="reason" class="form-control" placeholder="Physical count correction..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Adjustment</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Adjustment History</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Product</th><th>Type</th><th>Qty</th><th>Reason</th><th>By</th></tr>
                        </thead>
                        <tbody>
                            @forelse($adjustments as $adj)
                            <tr>
                                <td>{{ $adj->adjustment_date->format('d M Y') }}</td>
                                <td>{{ $adj->product->name }}</td>
                                <td><span class="badge bg-{{ $adj->type == 'add' ? 'success' : 'danger' }}">{{ ucfirst($adj->type) }}</span></td>
                                <td>{{ $adj->quantity }}</td>
                                <td>{{ $adj->reason }}</td>
                                <td>{{ $adj->createdBy->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No adjustments</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">{{ $adjustments->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
