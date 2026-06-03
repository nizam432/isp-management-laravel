{{-- resources/views/hr/leave/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Leave Applications')
@section('page_actions')
    <a href="{{ route('leave.create') }}" class="btn btn-primary btn-sm mr-1">
        <i class="fas fa-plus mr-1"></i> Apply Leave
    </a>
    <a href="{{ route('leave.types') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-list mr-1"></i> Leave Types
    </a>
@endsection
@section('page_content')

{{-- Filter --}}
<div class="card">
    <div class="card-body py-2">
        <form method="GET" class="form-inline">
            <select name="status" class="form-control form-control-sm mr-2">
                <option value="">All Status</option>
                <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <select name="employee_id" class="form-control form-control-sm mr-2">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary mr-1">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="{{ route('leave.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-redo"></i>
            </a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-times mr-1"></i> Leave Applications</h3>
        <div class="card-tools">
            <span class="badge badge-info">{{ $leaves->total() }} applications</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th style="width:100px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $i => $leave)
                <tr>
                    <td class="text-muted">{{ $leaves->firstItem() + $i }}</td>
                    <td>
                        <strong>{{ $leave->employee->name }}</strong>
                        <br><small class="text-muted"><code>{{ $leave->employee->employee_code }}</code></small>
                    </td>
                    <td><span class="badge badge-light border">{{ $leave->leaveType->name }}</span></td>
                    <td>{{ $leave->from_date->format('d M Y') }}</td>
                    <td>{{ $leave->to_date->format('d M Y') }}</td>
                    <td><span class="badge badge-secondary">{{ $leave->days }} days</span></td>
                    <td><small>{{ Str::limit($leave->reason, 30) ?? '—' }}</small></td>
                    <td>
                        <span class="badge badge-{{
                            $leave->status === 'approved' ? 'success' :
                            ($leave->status === 'rejected' ? 'danger' : 'warning')
                        }}">{{ ucfirst($leave->status) }}</span>
                    </td>
                    <td>
                        @if($leave->status === 'pending')
                            <form action="{{ route('leave.approve', $leave) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-success" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <button class="btn btn-xs btn-danger" title="Reject"
                                    onclick="rejectLeave({{ $leave->id }})">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        No leave applications found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Total {{ $leaves->total() }}</small>
        {{ $leaves->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Reject Leave</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Reason</label>
                        <textarea name="note" class="form-control" rows="3"
                                  placeholder="Reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-times mr-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
function rejectLeave(id) {
    document.getElementById('rejectForm').action = '/leave/' + id + '/reject';
    $('#rejectModal').modal('show');
}
</script>
@endpush
