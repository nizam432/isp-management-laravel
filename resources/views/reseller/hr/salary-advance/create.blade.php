@extends('reseller.layouts.app')

@section('title', 'Add Salary Advance')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Add Salary Advance</h4>
    <a href="{{ route('reseller.hr.salary-advance.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('reseller.hr.salary-advance.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Advance Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Payment Type <span class="text-danger">*</span></label>
                        <select name="payment_type" id="paymentType" class="form-control" required>
                            <option value="one_time">One Time (deduct fully in one month)</option>
                            <option value="installment">Installment (spread across months)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6" id="installmentField" style="display:none;">
                    <div class="form-group">
                        <label>Total Installments</label>
                        <input type="number" min="1" name="total_installments" class="form-control">
                        <small class="text-muted">Each payroll cycle, one installment amount will be auto-deducted.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Advance Date <span class="text-danger">*</span></label>
                        <input type="date" name="advance_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>First Deduction Month <span class="text-danger">*</span></label>
                        <input type="month" name="deduct_month" class="form-control" value="{{ now()->format('Y-m') }}" required>
                        <small class="text-muted">This advance will be auto-deducted when you generate payroll for this month.</small>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Advance</button>
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
$('#paymentType').on('change', function () {
    $('#installmentField').toggle($(this).val() === 'installment');
});
</script>
@endsection