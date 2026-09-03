@extends('reseller.layouts.app')

@section('title', 'Employee List')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Employee List</h4>
    <a href="{{ route('reseller.hr.employee.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Add Employee
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr><th>Code</th><th>Name</th><th>Department</th><th>Position</th><th>Basic Salary</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td><code>{{ $emp->employee_code }}</code></td>
                    <td>
                        {{ $emp->name }}
                        <br><small class="text-muted">{{ $emp->phone }}</small>
                    </td>
                    <td>{{ $emp->department->name ?? '—' }}</td>
                    <td>{{ $emp->position->name ?? '—' }}</td>
                    <td>৳{{ number_format($emp->basic_salary) }}</td>
                    <td>
                        <span class="badge badge-{{ $emp->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($emp->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No employees added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $employees->links() }}</div>
</div>

@endsection