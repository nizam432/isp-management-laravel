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
@php
    $sentChange   = $yesterdaySent > 0   ? round((($todaySent - $yesterdaySent) / $yesterdaySent) * 100)     : ($todaySent > 0 ? 100 : 0);
    $failedChange = $yesterdayFailed > 0 ? round((($todayFailed - $yesterdayFailed) / $yesterdayFailed) * 100) : ($todayFailed > 0 ? 100 : 0);
    $sentMax      = max($yesterdaySent, $todaySent, 1);
    $failedMax    = max($yesterdayFailed, $todayFailed, 1);
@endphp

<style>
.stat-card { border-radius:6px; color:#fff; overflow:hidden; margin-bottom:12px; }
.stat-card .sc-top { padding:10px 14px 6px; }
.stat-card .sc-label { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px; color:#fff; }
.stat-card .sc-value { font-size:28px; font-weight:700; line-height:1.2; color:#fff; }
.stat-card .sc-sub { font-size:10px; font-weight:600; margin-top:1px; color:rgba(255,255,255,.85); }
.stat-card .sc-bottom { padding:3px 12px 5px; background:rgba(0,0,0,.12); }
.sc-bars { display:flex; align-items:flex-end; gap:3px; height:16px; }
.sc-bar { flex:1; border-radius:2px 2px 0 0; background:rgba(255,255,255,.3); }
.sc-bar.now { background:rgba(255,255,255,.9); }
.sc-badge { font-size:10px; padding:2px 7px; border-radius:20px; background:rgba(255,255,255,.25); font-weight:500; }
</style>

<div class="row">

    {{-- Today Sent --}}
    <div class="col-md-3">
        <div class="stat-card" style="background:#00a65a;">
            <div class="sc-top">
                <div class="sc-label"><i class="fas fa-paper-plane mr-1"></i> আজকে Sent</div>
                <div class="sc-value">{{ $todaySent }}</div>
                <div class="sc-sub">গতকাল: {{ $yesterdaySent }}</div>
            </div>
            <div class="sc-bottom d-flex justify-content-between align-items-center">
                <div class="sc-bars" style="width:50px;">
                    <div class="sc-bar" style="height:{{ ($yesterdaySent / $sentMax) * 100 }}%"></div>
                    <div class="sc-bar now" style="height:{{ ($todaySent / $sentMax) * 100 }}%"></div>
                </div>
                <span class="sc-badge">{{ $sentChange >= 0 ? '+' : '' }}{{ $sentChange }}%</span>
            </div>
        </div>
    </div>

    {{-- Today Failed --}}
    <div class="col-md-3">
        <div class="stat-card" style="background:#dd4b39;">
            <div class="sc-top">
                <div class="sc-label"><i class="fas fa-times-circle mr-1"></i> আজকে Failed</div>
                <div class="sc-value">{{ $todayFailed }}</div>
                <div class="sc-sub">গতকাল: {{ $yesterdayFailed }}</div>
            </div>
            <div class="sc-bottom d-flex justify-content-between align-items-center">
                <div class="sc-bars" style="width:50px;">
                    <div class="sc-bar" style="height:{{ ($yesterdayFailed / $failedMax) * 100 }}%"></div>
                    <div class="sc-bar now" style="height:{{ ($todayFailed / $failedMax) * 100 }}%"></div>
                </div>
                <span class="sc-badge">{{ $failedChange >= 0 ? '+' : '' }}{{ $failedChange }}%</span>
            </div>
        </div>
    </div>

    {{-- Total SMS --}}
    <div class="col-md-3">
        <div class="stat-card" style="background:#0073b7;">
            <div class="sc-top">
                <div class="sc-label"><i class="fas fa-sms mr-1"></i> মোট SMS</div>
                <div class="sc-value">{{ number_format($totalSmsAllTime) }}</div>
                <div class="sc-sub">All time</div>
            </div>
            <div class="sc-bottom">
                <span class="sc-badge">সর্বমোট</span>
            </div>
        </div>
    </div>

    {{-- SMS Balance --}}
    <div class="col-md-3">
        <div class="stat-card" style="background:#f39c12;">
            <div class="sc-top">
                <div class="sc-label"><i class="fas fa-coins mr-1"></i> SMS Balance</div>
                <div class="sc-value">{{ $smsBalance !== null ? number_format($smsBalance) : 'N/A' }}</div>
                <div class="sc-sub">{{ $smsBalance !== null ? 'বর্তমান ব্যালেন্স' : 'গেটওয়ে থেকে আনা যায়নি' }}</div>
            </div>
            <div class="sc-bottom">
                <span class="sc-badge">লাইভ</span>
            </div>
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
                                    <span id="quickCount">0</span>/500 &middot; <span id="quickSmsCount">0 SMS</span>
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
                                    <option value="all">সব Customer ({{ $filterCounts['all'] }})</option>
                                    <option value="active">Active Customer ({{ $filterCounts['active'] }})</option>
                                    <option value="suspended">Suspended Customer ({{ $filterCounts['suspended'] }})</option>
                                    <option value="paid">Paid Customer ({{ $filterCounts['paid'] }})</option>
                                    <option value="expired">Expired Customer ({{ $filterCounts['expired'] }})</option>
                                    <option value="due">Due Customer ({{ $filterCounts['due'] }})</option>
                                    <option value="overdue">Overdue Customer ({{ $filterCounts['overdue'] }})</option>
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
                                    <span id="bulkCount">0</span>/500 &middot; <span id="bulkSmsCount">0 SMS</span>
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
// SMS segment counter — ported from the PHP count_sms() logic.
// Detects Bengali/Unicode vs plain GSM-7 text and calculates how many SMS
// segments the message will actually consume (concatenated-SMS rules).
function countSms(message) {
    message = message.trim();
    if (message.length === 0) return 0;

    var totalLineBreaks = (message.match(/\n/g) || []).length;
    // JS strings are UTF-16; for Bengali/Unicode char counting we use the
    // string length after normalizing (close enough to mb_strlen for this purpose).
    var isAscii = /^[\x00-\x7F]*$/.test(message);

    var totalChar, totalMessage;

    if (!isAscii) {
        // Unicode (Bengali etc.) — 70 char single SMS, 66 char per segment after that
        totalChar = Array.from(message).length + totalLineBreaks;
        if (totalChar <= 70) totalMessage = 1;
        else if (totalChar <= 134) totalMessage = 2;
        else if (totalChar <= 200) totalMessage = 3;
        else if (totalChar <= 267) totalMessage = 4;
        else if (totalChar <= 334) totalMessage = 5;
        else if (totalChar <= 401) totalMessage = 6;
        else if (totalChar <= 468) totalMessage = 7;
        else if (totalChar <= 535) totalMessage = 8;
        else {
            var remaining = totalChar - 536;
            totalMessage = Math.floor(remaining / 66) + 8 + 1;
        }
    } else {
        // GSM-7 (plain English/numbers) — 160 char single SMS, 153 char per segment after
        totalChar = message.length;
        if (totalChar <= 160) totalMessage = 1;
        else if (totalChar <= 306) totalMessage = 2;
        else if (totalChar <= 459) totalMessage = 3;
        else if (totalChar <= 612) totalMessage = 4;
        else if (totalChar <= 765) totalMessage = 5;
        else if (totalChar <= 918) totalMessage = 6;
        else if (totalChar <= 1071) totalMessage = 7;
        else if (totalChar <= 1224) totalMessage = 8;
        else {
            var remaining2 = totalChar - 1224;
            totalMessage = Math.floor(remaining2 / 153) + 8 + 1;
        }
    }

    return totalMessage;
}

// If the message contains {something} placeholders, the real per-customer
// length varies (name/amount/month differ per recipient) — showing a fixed
// SMS count would be misleading, so show "Dynamic Count" instead.
function hasPlaceholder(message) {
    return /\{[a-zA-Z_]+\}/.test(message);
}

function updateSmsCounter(textareaId, charCountId, smsCountId) {
    var el = document.getElementById(textareaId);
    var msg = el.value;
    document.getElementById(charCountId).textContent = msg.length;

    var smsCountEl = document.getElementById(smsCountId);
    if (hasPlaceholder(msg)) {
        smsCountEl.textContent = 'Dynamic Count';
    } else {
        var count = countSms(msg);
        smsCountEl.textContent = count + (count === 1 ? ' SMS' : ' SMS');
    }
}

document.getElementById('quickMessage').addEventListener('input', function() {
    updateSmsCounter('quickMessage', 'quickCount', 'quickSmsCount');
});
document.getElementById('bulkMessage').addEventListener('input', function() {
    updateSmsCounter('bulkMessage', 'bulkCount', 'bulkSmsCount');
});

// Quick SMS template click
document.querySelectorAll('.quick-tpl-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var msg = this.getAttribute('data-msg');
        document.getElementById('quickMessage').value = msg;
        updateSmsCounter('quickMessage', 'quickCount', 'quickSmsCount');
    });
});

// Bulk SMS template click
document.querySelectorAll('.bulk-tpl-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var msg = this.getAttribute('data-msg');
        document.getElementById('bulkMessage').value = msg;
        updateSmsCounter('bulkMessage', 'bulkCount', 'bulkSmsCount');
    });
});
</script>
@endpush
