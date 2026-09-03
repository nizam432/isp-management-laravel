@extends('reseller.layouts.app')

@section('title', 'Send SMS')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<h4 class="mb-3">Send SMS</h4>

@unless($activeGateway)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle mr-1"></i>
    কোনো SMS Gateway সক্রিয় নেই।
    <a href="{{ route('reseller.sms-service.settings.index') }}">এখানে গিয়ে একটা Gateway কনফিগার করুন</a>।
</div>
@endunless

<div class="row mb-3">
    <div class="col-md-6">
        <div class="card text-center py-3" style="border-left:4px solid #00a65a;">
            <div class="text-success font-weight-bold" style="font-size:24px;">{{ $todaySent }}</div>
            <small class="text-muted">Today Sent</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-center py-3" style="border-left:4px solid #dd4b39;">
            <div class="text-danger font-weight-bold" style="font-size:24px;">{{ $todayFailed }}</div>
            <small class="text-muted">Today Failed</small>
        </div>
    </div>
</div>

<div class="row">
    {{-- Test SMS --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-vial mr-1"></i> Test SMS</h3></div>
            <form action="{{ route('reseller.sms-service.send.test') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Mobile Number</label>
                        <input type="text" name="mobile" class="form-control" placeholder="01XXXXXXXXX" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Message</label>
                        <textarea name="message" class="form-control" rows="4" maxlength="500" required></textarea>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary" {{ $activeGateway ? '' : 'disabled' }}>
                        <i class="fas fa-paper-plane mr-1"></i> Send Test
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk SMS --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-users mr-1"></i> Send to Clients</h3></div>
            <form action="{{ route('reseller.sms-service.send.bulk') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Send To</label>
                        <select name="status" class="form-control">
                            <option value="all">All My Clients</option>
                            <option value="active">Active Clients Only</option>
                            <option value="suspended">Suspended Clients Only</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Template (optional)</label>
                        <select id="tplSelect" class="form-control">
                            <option value="">-- Write custom message --</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->body }}">{{ $tpl->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Message</label>
                        <textarea name="message" id="bulkMessage" class="form-control" rows="4" maxlength="500" required></textarea>
                        <small class="text-muted">Note: variables like {name} only resolve per-client when actually sent.</small>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-success" {{ $activeGateway ? '' : 'disabled' }}
                            onclick="return confirm('আপনার সব ম্যাচ করা ক্লায়েন্টের কাছে SMS পাঠানো হবে। নিশ্চিত?');">
                        <i class="fas fa-paper-plane mr-1"></i> Send to Clients
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
document.getElementById('tplSelect').addEventListener('change', function () {
    document.getElementById('bulkMessage').value = this.value;
});
</script>
@endsection
