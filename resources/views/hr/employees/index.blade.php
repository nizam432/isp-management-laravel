{{-- resources/views/hr/employees/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Employees')
@section('page_actions')
    <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Add Employee
    </a>
@endsection
@section('page_content')

{{-- Stats --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $employees->total() }}</h3><p>Total Employees</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $employees->getCollection()->where('status', 'active')->count() }}</h3><p>Active</p></div>
            <div class="icon"><i class="fas fa-user-check"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $employees->getCollection()->where('status', 'inactive')->count() }}</h3><p>Inactive</p></div>
            <div class="icon"><i class="fas fa-user-slash"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $employees->getCollection()->whereIn('status', ['resigned', 'terminated'])->count() }}</h3>
                <p>Resigned / Terminated</p>
            </div>
            <div class="icon"><i class="fas fa-user-times"></i></div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card">
    <div class="card-body py-2">
        <form method="GET" class="form-inline flex-wrap">
            <div class="input-group input-group-sm mr-2 mb-1">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <input type="text" name="search" class="form-control"
                       placeholder="Name / Phone / Code"
                       value="{{ request('search') }}">
            </div>
            <select name="department_id" class="form-control form-control-sm mr-2 mb-1">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}"
                        {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-control form-control-sm mr-2 mb-1">
                <option value="">All Status</option>
                <option value="active"     {{ request('status') == 'active'     ? 'selected' : '' }}>Active</option>
                <option value="inactive"   {{ request('status') == 'inactive'   ? 'selected' : '' }}>Inactive</option>
                <option value="resigned"   {{ request('status') == 'resigned'   ? 'selected' : '' }}>Resigned</option>
                <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary mr-1 mb-1">
                <i class="fas fa-search mr-1"></i> Search
            </button>
            <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary mb-1">
                <i class="fas fa-redo"></i>
            </a>
        </form>
    </div>
</div>

{{-- Employee Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Employee List</h3>
        <span class="badge badge-info">{{ $employees->total() }} employees</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Department / Position</th>
                    <th>Contact</th>
                    <th>Join Date</th>
                    <th>Basic Salary</th>
                    <th>Status</th>
                    <th style="width:90px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $i => $emp)
                <tr>
                    <td class="text-muted small">{{ $employees->firstItem() + $i }}</td>

                    {{-- Employee --}}
                    <td>
                        <div class="d-flex align-items-center">
                            @if($emp->photo)
                                <img src="{{ asset('storage/' . $emp->photo) }}"
                                     class="rounded-circle mr-2" width="36" height="36"
                                     style="object-fit:cover;">
                            @else
                                <div class="rounded-circle bg-secondary mr-2 d-flex align-items-center justify-content-center"
                                     style="width:36px;height:36px;font-size:15px;color:#fff;flex-shrink:0;">
                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('employees.show', $emp) }}" class="font-weight-bold">
                                    {{ $emp->name }}
                                </a>
                                <br>
                                <small class="text-muted"><code>{{ $emp->employee_code }}</code></small>
                            </div>
                        </div>
                    </td>

                    {{-- Department / Position --}}
                    <td>
                        @if($emp->department)
                            <span class="d-block">{{ $emp->department->name }}</span>
                        @endif
                        @if($emp->position)
                            <small class="text-muted">{{ $emp->position->name }}</small>
                        @endif
                    </td>

                    {{-- Contact --}}
                    <td>
                        @if($emp->phone)
                            <small><i class="fas fa-phone-alt mr-1"></i>{{ $emp->phone }}</small>
                        @endif
                        @if($emp->email)
                            <br><small class="text-muted"><i class="fas fa-envelope mr-1"></i>{{ $emp->email }}</small>
                        @endif
                    </td>

                    {{-- Join Date --}}
                    <td>
                        <small>{{ $emp->join_date ? $emp->join_date->format('d M Y') : '—' }}</small>
                    </td>

                    {{-- Salary --}}
                    <td>
                        <strong>৳ {{ number_format($emp->basic_salary) }}</strong>
                    </td>

                    {{-- Status --}}
                    <td>
                        @php
                            $statusColor = match($emp->status) {
                                'active'     => 'success',
                                'inactive'   => 'secondary',
                                'resigned'   => 'warning',
                                'terminated' => 'danger',
                                default      => 'secondary',
                            };
                        @endphp
                        <span class="badge badge-{{ $statusColor }}">
                            {{ ucfirst($emp->status) }}
                        </span>
                    </td>

                    {{-- Action --}}
                    <td>
                        <a href="{{ route('employees.show', $emp) }}"
                           class="btn btn-xs btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('employees.edit', $emp) }}"
                           class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('employees.destroy', $emp) }}"
                              method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button"
                                    class="btn btn-xs btn-danger swal-delete"
                                    data-message="Employee '{{ $emp->name }}' will be permanently deleted.">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x d-block mb-2"></i>
                        No employees found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Total {{ $employees->total() }} — page {{ $employees->currentPage() }}/{{ $employees->lastPage() }}
        </small>
        {{ $employees->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
</div>

@endsection