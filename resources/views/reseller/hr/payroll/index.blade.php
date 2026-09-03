@extends('reseller.layouts.app')

@section('title', 'Payroll')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Payroll</h4>
    <a href="{{ route('reseller.hr.payroll.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Generate Payroll
    </a>
</div>

<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="card text-center py-3" style="border-left:4px solid #17a2b8;">
            <div class="text-info font-weight-bold" style="font-size:22px;">{{ $stats['total'] }}</div>
            <small class="text-muted">Total</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center py-3" style="border-left:4px solid #f39c12;">
            <div style="font-size:22px;color:#f39c12;font-weight:700;">{{ $stats['pending'] }}</div>
            <small class="text-muted">Pending</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center py-3" style="border-left:4px solid #00a65a;">
            <div class="text-success font-weight-bold" style="font-size:22px;">{{ $stats['paid'] }}</div>
            <small class="text-muted">Paid</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center py-3" style="border-left:4px solid #dd4b39;">
            <div class="text-danger font-weight-bold" style="font-size:22px;">৳{{ number_format($stats['due']) }}</div>
            <small class="text-muted">Total Due</small>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        @foreach(['pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid', 'void' => 'Void'] as $val => $label)
                            <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                    <a href="{{ route('reseller.hr.payroll.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr><th>Employee</th><th>Month</th><th>Net Salary</th><th>Paid</th><th>Due</th><th>Status</th><th style="width:60px">Action</th></tr>
            </thead>
            <tbody>
                @forelse($payrolls as $p)
                <tr>
                    <td>{{ $p->employee->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $p->month)->format('F Y') }}</td>
                    <td>৳{{ number_format($p->net_salary) }}</td>
                    <td>৳{{ number_format($p->paid_amount) }}</td>
                    <td class="{{ $p->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">৳{{ number_format($p->due_amount) }}</td>
                    <td>{!! $p->status_badge !!}</td>
                    <td><a href="{{ route('reseller.hr.payroll.show', $p) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No payroll records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $payrolls->withQueryString()->links() }}</div>
</div>

@endsection