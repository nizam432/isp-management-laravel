@extends('reseller.layouts.app')

@section('title', 'Create Invoice')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Create Invoice</h4>
    <a href="{{ route('reseller.billing.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('reseller.billing.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Client <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customerSelect" class="form-control @error('customer_id') is-invalid @enderror" required>
                            <option value="">-- Select Client --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" data-bill="{{ $c->monthly_bill_amount }}"
                                        {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->customer_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Month <span class="text-danger">*</span></label>
                        <input type="month" name="month" class="form-control @error('month') is-invalid @enderror"
                               value="{{ old('month', now()->format('Y-m')) }}" required>
                        @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="amount" id="amountInput"
                               class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Discount</label>
                        <input type="number" step="0.01" min="0" name="discount" class="form-control" value="{{ old('discount', 0) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                        <small class="text-muted">Leave empty to auto-calculate</small>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Create Invoice</button>
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
$('#customerSelect').on('change', function () {
    var bill = $(this).find(':selected').data('bill');
    if (bill && !$('#amountInput').val()) {
        $('#amountInput').val(bill);
    }
});
</script>
@endsection