{{-- resources/views/sms/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Send SMS')
@section('page_content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-times-circle mr-1"></i> {{ session('error') }}
    </div>
@endif

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

{{-- Tabs --}}
<div class="card card-primary card-outline">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="smsTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#quick" role="tab">
                    <i class="fas fa-paper-plane mr-1"></i> Quick SMS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#bulk" role="tab">
                    <i class="fas fa-broadcast-tower mr-1"></i> Bulk SMS
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content">

            {{-- ── Tab 1: Quick SMS ───────────────────── --}}
            <div class="tab-pane fade show active" id="quick" role="tabpanel">
                <div class="row">

                    {{-- Form --}}
                    <div class="col-md-6">
                        <form action="{{ route('sms.test') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-mobile-alt mr-1"></i> Mobile Number
                                </label>
                                <input type="text" name="mobile" class="form-control"
                                       placeholder="01XXXXXXXXX" required
                                       value="{{ old('mobile') }}">
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-comment mr-1"></i> Message
                                </label>
                                <textarea name="message" id="quickMessage" class="form-control" rows="5"
                                          required maxlength="500"
                                          placeholder="আপনার message লিখুন...">{{ old('message') }}</textarea>
                                <small class="text-muted float-right">
                                    <span id="quickCount">0</span>/500
                                </small>
                            </div>
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-paper-plane mr-1"></i> SMS পাঠাও
                            </button>
                        </form>
                    </div>

                    {{-- Templates Panel --}}
                    <div class="col-md-6">
                        <label class="font-weight-bold">
                            <i class="fas fa-file-alt mr-1"></i> Templates
                            <a href="{{ route('sms.templates.index') }}" class="btn btn-xs btn-outline-secondary ml-2">
                                <i class="fas fa-cog"></i> Manage
                            </a>
                        </label>

                        {{-- Saved Templates --}}
                        @if($templates->count())
                        <div class="list-group mb-2" style="max-height:200px; overflow-y:auto;">
                            @foreach($templates as $tpl)
                            <button type="button"
                                    class="list-group-item list-group-item-action py-2 quick-tpl-btn"
                                    data-msg="{{ $tpl->body }}">
                                <strong class="d-block">{{ $tpl->title }}</strong>
                                <small class="text-muted">{{ Str::limit($tpl->body, 60) }}</small>
                            </button>
                            @endforeach
                        </div>
                        @else
                        <div class="alert alert-light border mb-2 py-2">
                            <small class="text-muted">
                                কোনো template নেই।
                                <a href="{{ route('sms.templates.index') }}">এখানে তৈরি করুন</a>।
                            </small>
                        </div>
                        @endif

                        {{-- Default Quick Buttons --}}
                        <label class="small text-muted">Default Templates</label>
                        <div class="d-flex flex-wrap">
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 mb-1 quick-tpl-btn"
                                    data-msg="প্রিয় গ্রাহক, আপনার বিল বাকি আছে। দ্রুত পরিশোধ করুন।">
                                Bill Due
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 mb-1 quick-tpl-btn"
                                    data-msg="প্রিয় গ্রাহক, আপনার পেমেন্ট সফলভাবে গ্রহণ করা হয়েছে। ধন্যবাদ।">
                                Payment OK
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 mb-1 quick-tpl-btn"
                                    data-msg="প্রিয় গ্রাহক, বিল বাকি থাকায় আপনার সংযোগ সাময়িকভাবে বন্ধ করা হয়েছে।">
                                Suspended
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 mb-1 quick-tpl-btn"
                                    data-msg="প্রিয় গ্রাহক, আপনার ইন্টারনেট সংযোগ পুনরায় চালু করা হয়েছে। ধন্যবাদ।">
                                Restored
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Tab 2: Bulk SMS ─────────────────────── --}}
            <div class="tab-pane fade" id="bulk" role="tabpanel">
                <div class="row">

                    {{-- Form --}}
                    <div class="col-md-6">
                        <form action="{{ route('sms.bulk') }}" method="POST"
                              onsubmit="return confirm('নিশ্চিত — সব selected customer কে SMS পাঠাবেন?')">
                            @csrf
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-users mr-1"></i> Customer Filter
                                </label>
                                <select name="status" class="form-control">
                                    <option value="all">সব Customer</option>
                                    <option value="active">Active Customer</option>
                                    <option value="suspended">Suspended Customer</option>
                                    <option value="expired">Expired Customer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-comment mr-1"></i> Message
                                </label>
                                <textarea name="message" id="bulkMessage" class="form-control" rows="5"
                                          maxlength="500" required
                                          placeholder="আপনার message লিখুন..."></textarea>
                                <small class="text-muted float-right">
                                    <span id="bulkCount">0</span>/500
                                </small>
                            </div>
                            <div class="alert alert-warning py-2 mb-2">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <small>Bulk SMS পাঠালে সব selected customer এর কাছে একসাথে SMS যাবে।</small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-broadcast-tower mr-1"></i> Bulk SMS পাঠাও
                            </button>
                        </form>
                    </div>

                    {{-- Templates Panel --}}
                    <div class="col-md-6">
                        <label class="font-weight-bold">
                            <i class="fas fa-file-alt mr-1"></i> Templates
                            <a href="{{ route('sms.templates.index') }}" class="btn btn-xs btn-outline-secondary ml-2">
                                <i class="fas fa-cog"></i> Manage
                            </a>
                        </label>

                        {{-- Saved Templates --}}
                        @if($templates->count())
                        <div class="list-group mb-2" style="max-height:200px; overflow-y:auto;">
                            @foreach($templates as $tpl)
                            <button type="button"
                                    class="list-group-item list-group-item-action py-2 bulk-tpl-btn"
                                    data-msg="{{ $tpl->body }}">
                                <strong class="d-block">{{ $tpl->title }}</strong>
                                <small class="text-muted">{{ Str::limit($tpl->body, 60) }}</small>
                            </button>
                            @endforeach
                        </div>
                        @else
                        <div class="alert alert-light border mb-2 py-2">
                            <small class="text-muted">
                                কোনো template নেই।
                                <a href="{{ route('sms.templates.index') }}">এখানে তৈরি করুন</a>।
                            </small>
                        </div>
                        @endif

                        {{-- Default Quick Buttons --}}
                        <label class="small text-muted">Default Templates</label>
                        <div class="d-flex flex-wrap">
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 mb-1 bulk-tpl-btn"
                                    data-msg="প্রিয় গ্রাহক, আপনার এই মাসের ইন্টারনেট বিল বাকি আছে। দ্রুত পরিশোধ করুন।">
                                Bill Due
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 mb-1 bulk-tpl-btn"
                                    data-msg="প্রিয় গ্রাহক, বিল বাকি থাকায় আপনার সংযোগ বন্ধ করা হয়েছে। বিল পরিশোধ করুন।">
                                Suspend Notice
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 mb-1 bulk-tpl-btn"
                                    data-msg="প্রিয় গ্রাহক, আপনার প্যাকেজের মেয়াদ শেষ হতে চলেছে। রিনিউ করুন।">
                                Expiry Notice
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 mb-1 bulk-tpl-btn"
                                    data-msg="প্রিয় গ্রাহক, আমাদের সেবা ব্যবহার করার জন্য ধন্যবাদ। যেকোনো সমস্যায় যোগাযোগ করুন।">
                                General
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('js')
<script>
// Character counters
document.getElementById('quickMessage').addEventListener('input', function() {
    document.getElementById('quickCount').textContent = this.value.length;
});
document.getElementById('bulkMessage').addEventListener('input', function() {
    document.getElementById('bulkCount').textContent = this.value.length;
});

// Quick SMS template click
document.querySelectorAll('.quick-tpl-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var msg = this.getAttribute('data-msg');
        document.getElementById('quickMessage').value = msg;
        document.getElementById('quickCount').textContent = msg.length;
    });
});

// Bulk SMS template click
document.querySelectorAll('.bulk-tpl-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var msg = this.getAttribute('data-msg');
        document.getElementById('bulkMessage').value = msg;
        document.getElementById('bulkCount').textContent = msg.length;
    });
});
</script>
@endpush