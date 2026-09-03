@extends('reseller.layouts.app')

@section('title', 'SMS Reports')

@section('content')

<h4 class="mb-3">SMS Reports</h4>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="card text-center py-3" style="border-left:4px solid #00a65a;">
            <div class="text-success font-weight-bold" style="font-size:22px;">{{ $todaySent }}</div>
            <small class="text-muted">Today Sent</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3" style="border-left:4px solid #17a2b8;">
            <div class="text-info font-weight-bold" style="font-size:22px;">{{ number_format($totalSent) }}</div>
            <small class="text-muted">Total Success</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3" style="border-left:4px solid #dd4b39;">
            <div class="text-danger font-weight-bold" style="font-size:22px;">{{ number_format($totalFailed) }}</div>
            <small class="text-muted">Total Failed</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3" style="border-left:4px solid #6f42c1;">
            <div class="font-weight-bold" style="font-size:22px;color:#6f42c1;">{{ number_format($totalSent + $totalFailed) }}</div>
            <small class="text-muted">Total SMS</small>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="mobile" class="form-control form-control-sm" placeholder="01XXXXXXXXX" value="{{ request('mobile') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-control form-control-sm">
                        <option value="">All Types</option>
                        @foreach(\App\Models\SmsLog::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="gateway" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($gateways as $gw)
                            <option value="{{ $gw }}" {{ request('gateway') == $gw ? 'selected' : '' }}>{{ $gw }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                </div>
            </div>
            @if(request()->hasAny(['mobile','status','type','gateway','date_from','date_to']))
                <span class="badge badge-warning mt-2">Filtered: {{ $logs->total() }} results</span>
                <a href="{{ route('reseller.sms-service.reports') }}" class="btn btn-sm btn-secondary mt-2">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-list mr-1"></i> SMS Details</h3>
        <div>
            <span class="badge badge-success mr-1">Success: {{ $logs->getCollection()->where('status','sent')->count() }}</span>
            <span class="badge badge-danger">Failed: {{ $logs->getCollection()->where('status','failed')->count() }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr><th>#</th><th>Mobile</th><th>Type</th><th>Gateway</th><th>Message</th><th>Status</th><th>Response</th><th>Date & Time</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $i => $log)
                <tr>
                    <td class="text-muted small">{{ $logs->firstItem() + $i }}</td>
                    <td><code>{{ $log->mobile }}</code></td>
                    <td><span class="badge badge-info">{{ \App\Models\SmsLog::TYPES[$log->type] ?? $log->type }}</span></td>
                    <td><small><code>{{ $log->gateway }}</code></small></td>
                    <td style="max-width:220px"><small>{{ Str::limit($log->message, 55) }}</small></td>
                    <td>
                        <span class="badge badge-{{ $log->status === 'sent' ? 'success' : 'danger' }}">
                            {{ $log->status === 'sent' ? 'Sent' : 'Failed' }}
                        </span>
                    </td>
                    <td style="max-width:150px">
                        <a href="javascript:void(0)" class="text-dark view-response"
                           data-mobile="{{ $log->mobile }}" data-response="{{ $log->response }}"
                           title="ক্লিক করে সম্পূর্ণ response দেখো">
                            <small class="text-muted">{{ Str::limit($log->response, 30) }}</small>
                        </a>
                    </td>
                    <td>
                        <small>{{ $log->created_at->format('d M Y') }}</small>
                        <br><small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">SMS Log Empty.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $logs->withQueryString()->links() }}</div>
</div>

{{-- Full Response Modal --}}
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-sms mr-1"></i> Gateway Response</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-1">
                    <i class="fas fa-mobile-alt mr-1"></i> <code id="modalMobile"></code>
                </p>
                <div id="modalResponse" class="p-2 bg-light rounded border small text-muted"
                     style="white-space:pre-wrap; word-break:break-word;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
document.querySelectorAll('.view-response').forEach(function (el) {
    el.addEventListener('click', function () {
        document.getElementById('modalMobile').textContent   = this.dataset.mobile;
        document.getElementById('modalResponse').textContent = this.dataset.response || '—';
        $('#responseModal').modal('show');
    });
});
</script>
@endsection