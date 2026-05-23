{{-- resources/views/sms/report-details.blade.php --}}
@extends('layouts.app')
@section('page_title', 'SMS Details')
@section('page_actions')
    <a href="{{ route('sms.reports') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back to Reports
    </a>
@endsection
@section('page_content')

{{-- Detail Header Info --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Date</span>
                <span class="info-box-number">
                    {{ \Carbon\Carbon::parse($request->date)->format('d M Y') }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-tag"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Type</span>
                <span class="info-box-number">
                    {{ \App\Models\SmsLog::TYPES[$request->type] ?? $request->type }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-server"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Gateway</span>
                <span class="info-box-number">{{ $request->gateway }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-sms"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Records</span>
                <span class="info-box-number">{{ $logs->total() }}</span>
            </div>
        </div>
    </div>
</div>

{{-- SMS Details Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i> SMS Details
        </h3>
        <div class="card-tools">
            <span class="badge badge-success mr-1">
                Success: {{ $logs->getCollection()->where('status', 'success')->count() }}
            </span>
            <span class="badge badge-danger">
                Failed: {{ $logs->getCollection()->where('status', 'failed')->count() }}
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Mobile</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Response</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $i => $log)
                <tr>
                    <td>{{ $logs->firstItem() + $i }}</td>
                    <td><code>{{ $log->mobile }}</code></td>
                    <td>
                        <small title="{{ $log->message }}">
                            {{ Str::limit($log->message, 50) }}
                        </small>
                    </td>
                    <td>
                        <span class="badge badge-{{ $log->status === 'success' ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $log->status === 'success' ? 'check' : 'times' }} mr-1"></i>
                            {{ $log->status === 'success' ? 'Sent' : 'Failed' }}
                        </span>
                    </td>
                    <td>
                        <small class="text-muted" title="{{ $log->response }}">
                            {{ Str::limit($log->response, 30) }}
                        </small>
                    </td>
                    <td>
                        <small>{{ $log->created_at->format('h:i:s A') }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">কোনো record নেই।</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $logs->appends($request->all())->links() }}
    </div>
</div>

@endsection
