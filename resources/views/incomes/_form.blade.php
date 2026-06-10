{{-- resources/views/incomes/_form.blade.php --}}
{{-- $formId: 'addIncomeForm' or 'editIncomeForm' --}}

<form id="{{ $formId }}" enctype="multipart/form-data">
    <div class="row">
        {{-- Date --}}
        <div class="col-md-4">
            <div class="form-group">
                <label class="font-weight-bold small">Date <span class="text-danger">*</span></label>
                <input type="date" name="income_date"
                       class="form-control form-control-sm"
                       value="{{ date('Y-m-d') }}" required>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- Category + Quick Add --}}
        <div class="col-md-4">
            <div class="form-group">
                <label class="font-weight-bold small">Category <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <select name="category_id"
                            id="{{ $formId }}_category"
                            class="form-control" required>
                        <option value="">— Select Category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <div class="input-group-append">
                        <button type="button"
                                class="btn btn-outline-success btn-quick-add-category"
                                data-type="income"
                                data-target="{{ $formId }}_category"
                                title="Add new category">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- Amount --}}
        <div class="col-md-4">
            <div class="form-group">
                <label class="font-weight-bold small">Amount (BDT) <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text">BDT</span>
                    </div>
                    <input type="number" name="amount" step="0.01" min="0"
                           class="form-control" placeholder="0.00" required>
                </div>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Payment Method --}}
        <div class="col-md-4">
            <div class="form-group">
                <label class="font-weight-bold small">Payment Method <span class="text-danger">*</span></label>
                <select name="payment_method" class="form-control form-control-sm" required>
                    <option value="cash">Cash</option>
                    <option value="bkash">bKash</option>
                    <option value="nagad">Nagad</option>
                    <option value="rocket">Rocket</option>
                    <option value="bank">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                    <option value="card">Card</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- Transaction ID --}}
        <div class="col-md-4">
            <div class="form-group">
                <label class="font-weight-bold small">Transaction ID</label>
                <input type="text" name="transaction_id"
                       class="form-control form-control-sm"
                       placeholder="bKash/Nagad TrxID">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- Reference No --}}
        <div class="col-md-4">
            <div class="form-group">
                <label class="font-weight-bold small">Reference No.</label>
                <input type="text" name="reference_no"
                       class="form-control form-control-sm"
                       placeholder="Optional reference">
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Customer (optional) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label class="font-weight-bold small">Customer <small class="text-muted">(optional)</small></label>
                <select name="customer_id" class="form-control form-control-sm">
                    <option value="">— No customer linked —</option>
                    @foreach(\App\Models\Customer::orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} — {{ $c->phone }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- Payer --}}
        <div class="col-md-6">
            <div class="form-group">
                <label class="font-weight-bold small">Payer <small class="text-muted">(if not a customer)</small></label>
                <input type="text" name="payer"
                       class="form-control form-control-sm"
                       placeholder="Person or company name">
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Description --}}
        <div class="col-md-6">
            <div class="form-group mb-0">
                <label class="font-weight-bold small">Description / Notes</label>
                <textarea name="description" rows="2"
                          class="form-control form-control-sm"
                          placeholder="Brief description of income..."></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- Receipt --}}
        <div class="col-md-6">
            <div class="form-group mb-0">
                <label class="font-weight-bold small">Receipt</label>
                @if($formId === 'editIncomeForm')
                <div class="mb-1" id="editIncomeReceiptWrap" style="display:none;">
                    <a href="#" target="_blank" id="editIncomeCurrentReceipt"
                       class="btn btn-xs btn-light border">
                        <i class="fas fa-paperclip mr-1"></i> Current Receipt
                    </a>
                    <small class="text-muted ml-1">New file will replace existing.</small>
                </div>
                @endif
                <div class="custom-file custom-file-sm">
                    <input type="file" name="receipt"
                           id="{{ $formId }}_receipt"
                           class="custom-file-input"
                           accept=".jpg,.jpeg,.png,.pdf">
                    <label class="custom-file-label" for="{{ $formId }}_receipt"
                           style="font-size:12px">
                        Choose file (JPG, PNG, PDF)
                    </label>
                </div>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</form>
