{{-- resources/views/invoices/create.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Create Invoice')
@section('page_actions')
    <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
@endsection
@section('page_content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Invoice Information</h3></div>
    <form action="{{ route('invoices.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-control" id="customerSelect" required>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}"
                                    data-price="{{ $c->package->price ?? 0 }}"
                                    {{ old('customer_id', $customer?->id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} — {{ $c->phone }} ({{ $c->package->name ?? 'No Package' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Billing Month <span class="text-danger">*</span></label>
                        <input type="month" name="month" class="form-control" value="{{ old('month', date('Y-m')) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Amount (BDT) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" id="amountField" value="{{ old('amount', $customer?->package?->price ?? 0) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Discount (BDT)</label>
                        <input type="number" name="discount" class="form-control" value="{{ old('discount', 0) }}">
                    </div>
                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->endOfMonth()->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Invoice</button>
            <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('extra_js')
<script>
    // Auto-fill amount when customer is selected
    document.getElementById('customerSelect').addEventListener('change', function() {
        const price = this.options[this.selectedIndex].getAttribute('data-price');
        if (price) document.getElementById('amountField').value = price;
    });
</script>
@endsection
