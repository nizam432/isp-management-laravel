@extends('reseller.layouts.app')

@section('title', 'Apply Leave')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Apply Leave</h4>
    <a href="{{ route('reseller.hr.leave-application.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('reseller.hr.leave-application.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" id="employeeSelect" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type_id" id="leaveTypeSelect" class="form-control" required>
                            <option value="">-- Select Leave Type --</option>
                            @foreach($leaveTypes as $lt)
                                <option value="{{ $lt->id }}">{{ $lt->name }} ({{ $lt->days_per_year }} days/year)</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div id="balanceInfo" class="alert alert-info py-2" style="display:none;"></div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>From Date <span class="text-danger">*</span></label>
                        <input type="date" name="from_date" id="fromDate" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>To Date <span class="text-danger">*</span></label>
                        <input type="date" name="to_date" id="toDate" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Reason</label>
                        <textarea name="reason" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i> Submit Application</button>
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
function checkBalance() {
    var empId = $('#employeeSelect').val();
    var typeId = $('#leaveTypeSelect').val();
    if (!empId || !typeId) { $('#balanceInfo').hide(); return; }

    $.get('{{ route("reseller.hr.leave-application.balance") }}', { employee_id: empId, leave_type_id: typeId }, function (data) {
        $('#balanceInfo').show().html(
            'Total: <strong>' + data.total_days + '</strong> days | ' +
            'Used: <strong>' + data.used_days + '</strong> days | ' +
            'Remaining: <strong>' + data.remaining_days + '</strong> days'
        );
    });
}

$('#employeeSelect, #leaveTypeSelect').on('change', checkBalance);
</script>
@endsection