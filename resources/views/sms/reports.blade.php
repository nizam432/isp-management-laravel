{{-- resources/views/sms/reports.blade.php --}}
@extends('layouts.app')
@section('page_title', 'SMS Reports')
@section('page_content')

{{-- Stats --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $todaySent }}</h3><p>আজকে Sent</p></div>
            <div class="icon"><i class="fas fa-paper-plane"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($totalSent) }}</h3><p>মোট Success</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($totalFailed) }}</h3><p>মোট Failed</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($totalSent + $totalFailed) }}</h3><p>সর্বমোট SMS</p></div>
            <div class="icon"><i class="fas fa-sms"></i></div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Search & Filter</h3>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Mobile Number</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                            </div>
                            <input type="text" name="mobile" class="form-control"
                                   placeholder="01XXXXXXXXX"
                                   value="{{ request('mobile') }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                            <option value="failed"  {{ request('status') == 'failed'  ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Type</label>
                        <select name="type" class="form-control form-control-sm">
                            <option value="">All Types</option>
                            @foreach(\App\Models\SmsLog::TYPES as $key => $label)
                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Gateway</label>
                        <select name="gateway" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($gateways as $gw)
                                <option value="{{ $gw }}" {{ request('gateway') == $gw ? 'selected' : '' }}>
                                    {{ $gw }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Date Range</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="date_from" class="form-control"
                                   value="{{ request('date_from') }}">
                            <div class="input-group-prepend input-group-append">
                                <span class="input-group-text">→</span>
                            </div>
                            <input type="date" name="date_to" class="form-control"
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('sms.reports') }}" class="btn btn-sm btn-secondary ml-1">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
                @if(request()->hasAny(['mobile','status','type','gateway','date_from','date_to']))
                    <span class="badge badge-warning ml-2">
                        Filtered: {{ $logs->total() }} results
                    </span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- SMS Details Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i> SMS Details
        </h3>
        <div>
            <span class="badge badge-success mr-1">
                <i class="fas fa-check mr-1"></i>
                Success: {{ $logs->getCollection()->where('status','success')->count() }}
            </span>
            <span class="badge badge-danger">
                <i class="fas fa-times mr-1"></i>
                Failed: {{ $logs->getCollection()->where('status','failed')->count() }}
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Mobile</th>
                    <th>Type</th>
                    <th>Gateway</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Response</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $i => $log)
                <tr>
                    <td class="text-muted small">{{ $logs->firstItem() + $i }}</td>
                    <td>
                        <a href="{{ route('sms.reports', ['mobile' => $log->mobile]) }}"
                           class="text-primary">
                            <code>{{ $log->mobile }}</code>
                        </a>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            {{ \App\Models\SmsLog::TYPES[$log->type] ?? $log->type }}
                        </span>
                    </td>
                    <td><small><code>{{ $log->gateway }}</code></small></td>
                    <td style="max-width:220px">
                        <small title="{{ $log->message }}">
                            {{ Str::limit($log->message, 55) }}
                        </small>
                    </td>
                    <td>
                        <span class="badge badge-{{ $log->status === 'success' ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $log->status === 'success' ? 'check' : 'times' }} mr-1"></i>
                            {{ $log->status === 'success' ? 'Sent' : 'Failed' }}
                        </span>
                    </td>
                    <td style="max-width:120px">
                        <small class="text-muted" title="{{ $log->response }}">
                            {{ Str::limit($log->response, 25) }}
                        </small>
                    </td>
                    <td>
                        <small>{{ $log->created_at->format('d M Y') }}</small>
                        <br><small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                        কোনো SMS log পাওয়া যায়নি।
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            মোট {{ $logs->total() }} টি record — page {{ $logs->currentPage() }}/{{ $logs->lastPage() }}
        </small>
        {{ $logs->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
</div>

@endsection