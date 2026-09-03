@extends('reseller.layouts.app')

@section('title', 'Leave Applications')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Leave Applications</h4>
    <a href="{{ route('reseller.hr.leave-application.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Apply Leave
    </a>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card text-center py-3" style="border-left:4px solid #f39c12;">
            <div style="font-size:22px;color:#f39c12;font-weight:700;">{{ $stats['pending'] }}</div>
            <small class="text-muted">Pending</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center py-3" style="border-left:4px solid #00a65a;">
            <div class="text-success font-weight-bold" style="font-size:22px;">{{ $stats['approved'] }}</div>
            <small class="text-muted">Approved</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center py-3" style="border-left:4px solid #dd4b39;">
            <div class="text-danger font-weight-bold" style="font-size:22px;">{{ $stats['rejected'] }}</div>
            <small class="text-muted">Rejected</small>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <select name="status" class="form-control form-control-sm d-inline-block" style="width:200px">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr><th>Employee</th><th>Leave Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th style="width:120px">Action</th></tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                <tr>
                    <td>{{ $leave->employee->name ?? '—' }}</td>
                    <td>{{ $leave->leaveType->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($leave->from_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($leave->to_date)->format('d M Y') }}</td>
                    <td>{{ $leave->days }}</td>
                    <td><small>{{ Str::limit($leave->reason, 30) }}</small></td>
                    <td>
                        <span class="badge badge-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($leave->status) }}
                        </span>
                    </td>
                    <td>
                        @if($leave->status === 'pending')
                        <form action="{{ route('reseller.hr.leave-application.approve', $leave) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-success" title="Approve"><i class="fas fa-check"></i></button>
                        </form>
                        <button class="btn btn-xs btn-danger reject-btn" data-id="{{ $leave->id }}" title="Reject" data-toggle="modal" data-target="#rejectModal"><i class="fas fa-times"></i></button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No leave applications yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $leaves->withQueryString()->links() }}</div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title">Reject Leave</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label class="small">Reason (optional)</label>
                        <textarea name="note" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).on('click', '.reject-btn', function () {
    var id = $(this).data('id');
    $('#rejectForm').attr('action', "{{ url('reseller/hr/leave-application') }}/" + id + "/reject");
});
</script>
@endsection