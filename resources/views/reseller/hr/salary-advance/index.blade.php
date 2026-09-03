@extends('reseller.layouts.app')

@section('title', 'Salary Advance')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Salary Advance</h4>
    <a href="{{ route('reseller.hr.salary-advance.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Add Advance
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="deducted" {{ request('status') == 'deducted' ? 'selected' : '' }}>Deducted</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr><th>Employee</th><th>Amount</th><th>Type</th><th>Remaining</th><th>Deduct Month</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($advances as $adv)
                <tr>
                    <td>{{ $adv->employee->name ?? '—' }}</td>
                    <td>৳{{ number_format($adv->amount) }}</td>
                    <td>
                        <span class="badge badge-secondary">{{ $adv->payment_type === 'installment' ? 'Installment (' . $adv->paid_installments . '/' . $adv->total_installments . ')' : 'One Time' }}</span>
                    </td>
                    <td class="{{ $adv->remaining_amount > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">৳{{ number_format($adv->remaining_amount) }}</td>
                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $adv->deduct_month)->format('F Y') }}</td>
                    <td>
                        <span class="badge badge-{{ $adv->status === 'deducted' ? 'success' : 'warning' }}">
                            {{ ucfirst($adv->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No salary advances yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $advances->withQueryString()->links() }}</div>
</div>

@endsection