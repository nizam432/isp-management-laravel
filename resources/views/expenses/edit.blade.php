{{-- resources/views/expenses/edit.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Edit Expense: ' . $expense->expense_no)
@section('page_actions')
    <a href="{{ route('expenses.show', $expense) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-1"></i>
                    Edit — <code>{{ $expense->expense_no }}</code>
                </h3>
            </div>
            <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">তারিখ <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date"
                                       class="form-control @error('expense_date') is-invalid @enderror"
                                       value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Category <span class="text-danger">*</span></label>
                                <select name="category_id"
                                        class="form-control @error('category_id') is-invalid @enderror" required>
                                    <option value="">— Category বেছে নিন —</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                                {{ old('category_id', $expense->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">পরিমাণ (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">৳</span>
                                    </div>
                                    <input type="number" name="amount" step="0.01" min="0"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount', $expense->amount) }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method"
                                        class="form-control @error('payment_method') is-invalid @enderror" required>
                                    @foreach(['cash' => 'Cash', 'bkash' => 'bKash', 'nagad' => 'Nagad',
                                              'rocket' => 'Rocket', 'bank' => 'Bank Transfer',
                                              'cheque' => 'Cheque', 'card' => 'Card'] as $val => $label)
                                        <option value="{{ $val }}"
                                                {{ old('payment_method', $expense->payment_method) == $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Transaction ID</label>
                                <input type="text" name="transaction_id"
                                       class="form-control @error('transaction_id') is-invalid @enderror"
                                       value="{{ old('transaction_id', $expense->transaction_id) }}">
                                @error('transaction_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Reference No.</label>
                                <input type="text" name="reference_no"
                                       class="form-control @error('reference_no') is-invalid @enderror"
                                       value="{{ old('reference_no', $expense->reference_no) }}">
                                @error('reference_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Payee</label>
                                <input type="text" name="payee"
                                       class="form-control @error('payee') is-invalid @enderror"
                                       value="{{ old('payee', $expense->payee) }}">
                                @error('payee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Receipt Upload</label>
                                @if($expense->receipt_path)
                                    <div class="mb-2">
                                        <a href="{{ asset('storage/' . $expense->receipt_path) }}"
                                           target="_blank" class="btn btn-sm btn-light border">
                                            <i class="fas fa-paperclip mr-1"></i> Current receipt
                                        </a>
                                        <small class="text-muted ml-1">নতুন file upload করলে replace হবে।</small>
                                    </div>
                                @endif
                                <div class="custom-file">
                                    <input type="file" name="receipt" id="receiptFile"
                                           class="custom-file-input @error('receipt') is-invalid @enderror"
                                           accept=".jpg,.jpeg,.png,.pdf">
                                    <label class="custom-file-label" for="receiptFile">
                                        নতুন ফাইল বেছে নিন (optional)
                                    </label>
                                    @error('receipt')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">বিবরণ / নোট</label>
                        <textarea name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Expense-এর সংক্ষিপ্ত বিবরণ...">{{ old('description', $expense->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('expenses.show', $expense) }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> বাতিল
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Update Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    document.getElementById('receiptFile').addEventListener('change', function () {
        var fileName = this.files[0] ? this.files[0].name : 'নতুন ফাইল বেছে নিন';
        this.nextElementSibling.textContent = fileName;
    });
</script>
@endpush

@endsection
