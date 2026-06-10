{{-- resources/views/expenses/create.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Add Expense')
@section('page_actions')
    <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i> নতুন Expense</h3>
            </div>
            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">

                    <div class="row">
                        {{-- তারিখ --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">তারিখ <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date"
                                       class="form-control @error('expense_date') is-invalid @enderror"
                                       value="{{ old('expense_date', date('Y-m-d')) }}" required>
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Category --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Category <span class="text-danger">*</span></label>
                                <select name="category_id"
                                        class="form-control @error('category_id') is-invalid @enderror" required>
                                    <option value="">— Category বেছে নিন —</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                                {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- পরিমাণ --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">পরিমাণ (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">৳</span>
                                    </div>
                                    <input type="number" name="amount" step="0.01" min="0"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           placeholder="0.00"
                                           value="{{ old('amount') }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Payment Method --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method"
                                        class="form-control @error('payment_method') is-invalid @enderror" required>
                                    @foreach(['cash' => 'Cash', 'bkash' => 'bKash', 'nagad' => 'Nagad',
                                              'rocket' => 'Rocket', 'bank' => 'Bank Transfer',
                                              'cheque' => 'Cheque', 'card' => 'Card'] as $val => $label)
                                        <option value="{{ $val }}"
                                                {{ old('payment_method', 'cash') == $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Transaction ID --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Transaction ID</label>
                                <input type="text" name="transaction_id"
                                       class="form-control @error('transaction_id') is-invalid @enderror"
                                       placeholder="bKash/Nagad TrxID বা cheque no."
                                       value="{{ old('transaction_id') }}">
                                @error('transaction_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Reference No --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Reference No.</label>
                                <input type="text" name="reference_no"
                                       class="form-control @error('reference_no') is-invalid @enderror"
                                       placeholder="Vendor invoice no. (optional)"
                                       value="{{ old('reference_no') }}">
                                @error('reference_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Payee --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Payee (কাকে দেওয়া হলো)</label>
                                <input type="text" name="payee"
                                       class="form-control @error('payee') is-invalid @enderror"
                                       placeholder="ব্যক্তি বা প্রতিষ্ঠানের নাম"
                                       value="{{ old('payee') }}">
                                @error('payee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Receipt Upload --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Receipt Upload</label>
                                <div class="custom-file">
                                    <input type="file" name="receipt" id="receiptFile"
                                           class="custom-file-input @error('receipt') is-invalid @enderror"
                                           accept=".jpg,.jpeg,.png,.pdf">
                                    <label class="custom-file-label" for="receiptFile">
                                        ফাইল বেছে নিন (JPG, PNG, PDF · max 2MB)
                                    </label>
                                    @error('receipt')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="form-group">
                        <label class="font-weight-bold">বিবরণ / নোট</label>
                        <textarea name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Expense-এর সংক্ষিপ্ত বিবরণ...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> বাতিল
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    // Show filename in custom file input
    document.getElementById('receiptFile').addEventListener('change', function () {
        var fileName = this.files[0] ? this.files[0].name : 'ফাইল বেছে নিন';
        this.nextElementSibling.textContent = fileName;
    });
</script>
@endpush

@endsection
