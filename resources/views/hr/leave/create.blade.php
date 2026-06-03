{{-- resources/views/hr/leave/create.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Apply Leave')
@section('page_actions')
    <a href="{{ route('leave.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-plus mr-1"></i> Leave Application</h3>
            </div>
            <form action="{{ route('leave.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type_id" class="form-control" required>
                            <option value="">-- Select Type --</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->days_per_year }} days/year)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">From Date <span class="text-danger">*</span></label>
                                <input type="date" name="from_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">To Date <span class="text-danger">*</span></label>
                                <input type="date" name="to_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Reason</label>
                        <textarea name="reason" class="form-control" rows="3"
                                  placeholder="Reason for leave..."></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane mr-1"></i> Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
