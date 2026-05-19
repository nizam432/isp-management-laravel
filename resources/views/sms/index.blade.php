{{-- resources/views/sms/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'SMS Management')
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
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ $todayFailed }}</h3><p>আজকে Failed</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $logs->total() }}</h3><p>মোট SMS</p></div>
            <div class="icon"><i class="fas fa-sms"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $gateways->where('is_active', true)->count() }}</h3>
                <p>Active Gateway</p>
            </div>
            <div class="icon"><i class="fas fa-server"></i></div>
        </div>
    </div>
</div>

<div class="row">

    {{-- Left Column: Gateways + Test + Bulk --}}
    <div class="col-md-5">

        {{-- Gateway List --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-server mr-1"></i> SMS Gateways</h3>
            </div>
            <div class="card-body p-0">
                @foreach($gateways as $gw)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>{{ $gw->name }}</strong>
                            @if($gw->is_active)
                                <span class="badge badge-success ml-1">Active</span>
                            @else
                                <span class="badge badge-secondary ml-1">Inactive</span>
                            @endif
                            <br>
                            <small class="text-muted">{{ $gw->description }}</small>
                        </div>
                        <form action="{{ route('sms.gateway.toggle', $gw) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-{{ $gw->is_active ? 'danger' : 'success' }}">
                                <i class="fas fa-{{ $gw->is_active ? 'ban' : 'check' }} mr-1"></i>
                                {{ $gw->is_active ? 'বন্ধ করুন' : 'চালু করুন' }}
                            </button>
                        </form>
                    </div>

                    {{-- Config Form --}}
                    <form action="{{ route('sms.gateway.config', $gw) }}" method="POST">
                        @csrf
                        @foreach($gw->config ?? [] as $key => $value)
                        <div class="form-group mb-1">
                            <label class="small text-muted mb-0">
                                {{ strtoupper(str_replace('_', ' ', $key)) }}
                            </label>
                            <input type="{{ in_array($key, ['api_key', 'auth_token', 'password']) ? 'password' : 'text' }}"
                                   name="config[{{ $key }}]"
                                   class="form-control form-control-sm"
                                   value="{{ $value }}"
                                   placeholder="{{ strtoupper($key) }}">
                        </div>
                        @endforeach
                        <button type="submit" class="btn btn-xs btn-primary mt-1">
                            <i class="fas fa-save mr-1"></i> Save Config
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Test SMS --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-vial mr-1"></i> Test SMS</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('sms.test') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="mobile" class="form-control"
                               placeholder="01XXXXXXXXX" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="3" required>এটি একটি test SMS। - ISP Management</textarea>
                    </div>
                    <button type="submit" class="btn btn-info btn-block">
                        <i class="fas fa-paper-plane mr-1"></i> Test SMS পাঠাও
                    </button>
                </form>
            </div>
        </div>

        {{-- Bulk SMS --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-broadcast-tower mr-1"></i> Bulk SMS</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('sms.bulk') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Customer Filter</label>
                        <select name="status" class="form-control">
                            <option value="all">সব Customer</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="3"
                                  maxlength="500" required
                                  placeholder="আপনার message লিখুন..."></textarea>
                        <small class="text-muted">সর্বোচ্চ ৫০০ অক্ষর</small>
                    </div>
                    <button type="submit" class="btn btn-warning btn-block"
                            onclick="return confirm('সব customer কে SMS পাঠাবেন?')">
                        <i class="fas fa-broadcast-tower mr-1"></i> Bulk SMS পাঠাও
                    </button>
                </form>
            </div>
        </div>

    </div>

    {{-- Right Column: SMS Logs --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-list mr-1"></i> SMS Logs</h3>
                <form action="{{ route('sms.logs.clear') }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-danger"
                            onclick="return confirm('৩০ দিনের পুরনো log মুছবেন?')">
                        <i class="fas fa-trash mr-1"></i> Clear Old
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Mobile</th>
                            <th>Type</th>
                            <th>Gateway</th>
                            <th>Status</th>
                            <th>Time</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td><code>{{ $log->mobile }}</code></td>
                            <td>
                                <small>{{ \App\Models\SmsLog::TYPES[$log->type] ?? $log->type }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $log->gateway }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $log->status === 'success' ? 'success' : 'danger' }}">
                                    {{ $log->status === 'success' ? '✓ Sent' : '✗ Failed' }}
                                </span>
                            </td>
                            <td>
                                <small title="{{ $log->created_at }}">
                                    {{ $log->created_at->diffForHumans() }}
                                </small>
                            </td>
                            <td>
                                <small title="{{ $log->message }}">
                                    {{ Str::limit($log->message, 40) }}
                                </small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                কোনো SMS log নেই।
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $logs->links() }}
            </div>
        </div>
    </div>

</div>

@endsection