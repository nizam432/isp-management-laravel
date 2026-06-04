{{-- resources/views/hr/employees/show.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Employee — ' . $employee->name)
@section('page_actions')
    @if($employee->status === 'active')
    <button class="btn btn-danger btn-sm mr-1"
            data-toggle="modal" data-target="#resignTerminateModal">
        <i class="fas fa-user-times mr-1"></i> Resign / Terminate
    </button>
    @endif
    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning btn-sm mr-1">
        <i class="fas fa-edit mr-1"></i> Edit
    </a>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<div class="row">
    {{-- Left Column --}}
    <div class="col-md-4">

        {{-- Profile Card --}}
        <div class="card">
            <div class="card-body text-center">
                @if($employee->photo)
                    <img src="{{ asset('storage/' . $employee->photo) }}"
                         class="rounded-circle mb-3" width="120" height="120"
                         style="object-fit:cover; border:4px solid #dee2e6;">
                @else
                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3"
                         style="width:120px;height:120px;font-size:48px;color:#fff">
                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                    </div>
                @endif
                <h4 class="mb-1">{{ $employee->name }}</h4>
                <p class="text-muted mb-1">{{ $employee->position->name ?? '—' }}</p>
                <p class="text-muted small">{{ $employee->department->name ?? '—' }}</p>
                @php
                    $statusColor = match($employee->status) {
                        'active'     => 'success',
                        'inactive'   => 'secondary',
                        'resigned'   => 'warning',
                        'terminated' => 'danger',
                        default      => 'secondary',
                    };
                @endphp
                <span class="badge badge-{{ $statusColor }} px-3 py-2">
                    {{ ucfirst($employee->status) }}
                </span>
            </div>
            <div class="card-footer p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted small">Employee ID</td>
                        <td><code>{{ $employee->employee_code }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Phone</td>
                        <td>{{ $employee->phone ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Email</td>
                        <td><small>{{ $employee->email ?? '—' }}</small></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Join Date</td>
                        <td>{{ $employee->join_date ? $employee->join_date->format('d M Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Basic Salary</td>
                        <td><strong>{{ number_format($employee->basic_salary) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Emergency Contact --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title small"><i class="fas fa-phone-alt mr-1"></i> Emergency Contact</h3>
            </div>
            <div class="card-body py-2">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted small">Name</td>
                        <td>{{ $employee->emergency_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Phone</td>
                        <td>{{ $employee->emergency_phone ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Relation</td>
                        <td>{{ $employee->emergency_relation ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Bank Account --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title small"><i class="fas fa-university mr-1"></i> Bank Account</h3>
            </div>
            <div class="card-body py-2">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted small">Bank</td>
                        <td>{{ $employee->bank_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Account</td>
                        <td>{{ $employee->account_number ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Branch</td>
                        <td>{{ $employee->branch_name ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>

    </div>

    {{-- Right Column --}}
    <div class="col-md-8">

        {{-- Address --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt mr-1"></i> Address</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="text-muted small font-weight-bold">Present Address</label>
                        <p>{{ $employee->present_address ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small font-weight-bold">Permanent Address</label>
                        <p>{{ $employee->permanent_address ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Educational Qualification --}}
        @if($employee->educations->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-graduation-cap mr-1"></i> Educational Qualification</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Degree</th>
                            <th>Institution / Board</th>
                            <th>Passing Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee->educations as $edu)
                        <tr>
                            <td>{{ $edu->degree }}</td>
                            <td>{{ $edu->institution ?? '—' }}</td>
                            <td>{{ $edu->passing_year ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Documents --}}
        @if($employee->documents->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Documents</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Document Name</th>
                            <th style="width:120px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee->documents as $doc)
                        <tr>
                            <td><i class="fas fa-file mr-2 text-muted"></i>{{ $doc->document_name }}</td>
                            <td>
                                <a href="{{ asset('storage/' . $doc->file_path) }}"
                                   target="_blank" class="btn btn-xs btn-info">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                                <form action="{{ route('employees.documents.destroy', $doc) }}"
                                      method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-xs btn-danger swal-delete"
                                            data-message="Delete this document?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Payroll History --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-money-bill-wave mr-1"></i> Payroll History</h3>
                <a href="{{ route('payroll.generate') }}" class="btn btn-xs btn-success">
                    <i class="fas fa-plus mr-1"></i> Generate
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Month</th>
                            <th>Gross</th>
                            <th>Deduction</th>
                            <th>Net</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employee->payrolls->take(6) as $payroll)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($payroll->month . '-01')->format('M Y') }}</td>
                            <td>{{ number_format($payroll->gross_salary) }}</td>
                            <td>{{ number_format($payroll->total_deduction) }}</td>
                            <td><strong>{{ number_format($payroll->net_salary) }}</strong></td>
                            <td>
                                <span class="badge badge-{{ $payroll->status === 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('payroll.payslip', $payroll) }}"
                                   class="btn btn-xs btn-info" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No payroll records.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Leave History --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-calendar-times mr-1"></i> Leave History</h3>
                <a href="{{ route('leave.create') }}" class="btn btn-xs btn-success">
                    <i class="fas fa-plus mr-1"></i> Apply
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employee->leaves->take(5) as $leave)
                        <tr>
                            <td>{{ $leave->leaveType->name ?? '—' }}</td>
                            <td>{{ $leave->from_date->format('d M Y') }}</td>
                            <td>{{ $leave->to_date->format('d M Y') }}</td>
                            <td>{{ $leave->days }}</td>
                            <td>
                                <span class="badge badge-{{
                                    $leave->status === 'approved' ? 'success' :
                                    ($leave->status === 'rejected' ? 'danger' : 'warning')
                                }}">{{ ucfirst($leave->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No leave records.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Leaving Info --}}
        @if(in_array($employee->status, ['resigned', 'terminated']))
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title"><i class="fas fa-user-times mr-1"></i> Leaving Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Status</td>
                        <td><span class="badge badge-danger">{{ ucfirst($employee->status) }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Leaving Date</td>
                        <td>{{ $employee->leaving_date ? $employee->leaving_date->format('d M Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Reason</td>
                        <td>{{ $employee->leaving_reason ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Note</td>
                        <td>{{ $employee->leaving_note ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Resign / Terminate Modal --}}
<div class="modal fade" id="resignTerminateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title text-danger">
                    <i class="fas fa-user-times mr-1"></i> Resign / Terminate — {{ $employee->name }}
                </h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('employees.resign-terminate', $employee) }}" method="POST">
                @csrf
                <div class="modal-body">

                    {{-- Action --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Action <span class="text-danger">*</span></label>
                        <div class="mt-1">
                            <div class="custom-control custom-radio d-inline-block mr-4">
                                <input type="radio" id="action_resign" name="status"
                                       value="resigned" class="custom-control-input" checked>
                                <label class="custom-control-label text-warning" for="action_resign">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Resign
                                </label>
                            </div>
                            <div class="custom-control custom-radio d-inline-block">
                                <input type="radio" id="action_terminate" name="status"
                                       value="terminated" class="custom-control-input">
                                <label class="custom-control-label text-danger" for="action_terminate">
                                    <i class="fas fa-ban mr-1"></i> Terminate
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Effective Date --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Effective Date <span class="text-danger">*</span></label>
                        <input type="date" name="leaving_date" class="form-control"
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    {{-- Reason --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Reason</label>
                        <input type="text" name="leaving_reason" class="form-control"
                               placeholder="e.g. Personal reason, Policy violation...">
                    </div>

                    {{-- Note --}}
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Note</label>
                        <textarea name="leaving_note" class="form-control" rows="2"
                                  placeholder="Additional details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-check mr-1"></i> Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection