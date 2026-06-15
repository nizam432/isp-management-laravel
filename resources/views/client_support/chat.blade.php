{{-- resources/views/client_support/chat.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Ticket Chat — ' . $ticket->ticket_no)
@section('page_actions')
    <a href="{{ route('client-support.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection

@push('css')
<style>
.chat-wrapper {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
}
@media (max-width: 768px) { .chat-wrapper { grid-template-columns: 1fr; } }

.chat-box {
    display: flex; flex-direction: column; gap: 12px;
    padding: 16px; max-height: 520px; overflow-y: auto;
    background: #f8f9fa; border-radius: 4px;
}
.chat-msg { display: flex; gap: 10px; align-items: flex-start; max-width: 85%; }
.chat-msg.admin    { flex-direction: row-reverse; margin-left: auto; }
.chat-msg.customer { flex-direction: row; }

.chat-avatar {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff;
}
.chat-avatar.av-admin    { background: #007bff; }
.chat-avatar.av-customer { background: #17a2b8; }

.chat-bubble {
    padding: 10px 14px; border-radius: 12px;
    font-size: 13px; line-height: 1.5; max-width: 100%;
}
.chat-msg.admin    .chat-bubble { background: #007bff; color: #fff; border-bottom-right-radius: 3px; }
.chat-msg.customer .chat-bubble { background: #fff; color: #333; border-bottom-left-radius: 3px; border: 1px solid #e0e4ef; }

.chat-meta { font-size: 11px; color: #aaa; margin-top: 4px; }
.chat-msg.admin .chat-meta { text-align: right; }

.reply-box {
    border-top: 1px solid #dee2e6;
    padding: 14px;
    background: #fff;
}
.reply-area {
    width: 100%; border: 1.5px solid #dee2e6; border-radius: 6px;
    padding: 10px 12px; font-size: 13px; resize: vertical;
    outline: none; min-height: 80px; font-family: inherit;
}
.reply-area:focus { border-color: #007bff; }
</style>
@endpush

@section('page_content')
<div class="chat-wrapper">

    {{-- LEFT: Chat --}}
    <div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-comments mr-1 text-primary"></i>
                    Discussion
                    <small class="text-muted ml-2">{{ $ticket->ticket_no }}</small>
                </h3>
                <span class="badge badge-{{ $ticket->status_badge }}">{{ ucfirst($ticket->status) }}</span>
            </div>

            {{-- Messages --}}
            <div class="chat-box" id="chatBox">
                @forelse($ticket->replies as $reply)
                    @php $isAdmin = $reply->sender_type === 'admin'; @endphp
                    <div class="chat-msg {{ $isAdmin ? 'admin' : 'customer' }}" id="msg-{{ $reply->id }}">
                        <div class="chat-avatar {{ $isAdmin ? 'av-admin' : 'av-customer' }}">
                            {{ strtoupper(substr($isAdmin ? ($reply->user->name ?? 'A') : ($reply->customer->name ?? 'C'), 0, 2)) }}
                        </div>
                        <div>
                            <div class="chat-meta">
                                <strong>{{ $isAdmin ? ($reply->user->name ?? 'Admin') : ($reply->customer->name ?? 'Customer') }}</strong>
                                <span class="badge badge-{{ $isAdmin ? 'primary' : 'info' }}" style="font-size:9px;">{{ $isAdmin ? 'Admin' : 'Client' }}</span>
                            </div>
                            <div class="chat-bubble">{{ $reply->message }}</div>
                            @if($reply->attachment)
                            <div class="mt-1">
                                <a href="{{ asset('storage/'.$reply->attachment) }}" target="_blank" class="text-info small">
                                    <i class="fas fa-paperclip"></i> Attachment
                                </a>
                            </div>
                            @endif
                            <div class="chat-meta">{{ $reply->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4" id="no-msg">
                        <i class="fas fa-comments fa-2x mb-2 d-block"></i>
                        No messages yet.
                    </div>
                @endforelse
            </div>

            {{-- Reply Form --}}
            @if(!in_array($ticket->status, ['solved', 'closed']))
            <div class="reply-box">
                <textarea id="replyMessage" class="reply-area" placeholder="Type your reply..."></textarea>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <label class="small text-muted mb-0" style="cursor:pointer;">
                        <i class="fas fa-paperclip mr-1"></i> Attachment
                        <input type="file" id="replyAttachment" class="d-none" accept=".png,.jpg,.jpeg,.pdf">
                        <span id="attachName" class="ml-1"></span>
                    </label>
                    <button class="btn btn-primary btn-sm" id="btnSendReply">
                        <i class="fas fa-paper-plane mr-1"></i> Send
                    </button>
                </div>
            </div>
            @else
            <div class="text-center text-muted py-3 border-top">
                <i class="fas fa-lock mr-1"></i> Ticket is {{ $ticket->status }}. Cannot reply.
            </div>
            @endif
        </div>
    </div>

    {{-- RIGHT: Ticket Info --}}
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Ticket Info</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><th class="pl-3">Ticket No</th><td><code>{{ $ticket->ticket_no }}</code></td></tr>
                    <tr><th class="pl-3">Customer</th><td>{{ $ticket->customer->name ?? '—' }}</td></tr>
                    <tr><th class="pl-3">Phone</th><td>{{ $ticket->customer->phone ?? '—' }}</td></tr>
                    <tr><th class="pl-3">Category</th><td>{{ $ticket->category->name ?? '—' }}</td></tr>
                    <tr><th class="pl-3">Priority</th>
                        <td><span class="badge badge-{{ $ticket->priority_badge }}">{{ ucfirst($ticket->priority) }}</span></td>
                    </tr>
                    <tr><th class="pl-3">Status</th>
                        <td><span class="badge badge-{{ $ticket->status_badge }}">{{ ucfirst($ticket->status) }}</span></td>
                    </tr>
                    <tr><th class="pl-3">Complained No</th><td>{{ $ticket->complained_no ?? '—' }}</td></tr>
                    <tr><th class="pl-3">Created</th><td><small>{{ $ticket->created_at->format('d M Y h:i A') }}</small></td></tr>
                    <tr><th class="pl-3">Assigned To</th>
                        <td><small>{{ $ticket->assignees->pluck('name')->implode(', ') ?: '—' }}</small></td>
                    </tr>
                </table>
            </div>
        </div>

        @if($ticket->remarks)
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note mr-1"></i> Remarks</h3></div>
            <div class="card-body"><small>{{ $ticket->remarks }}</small></div>
        </div>
        @endif

        {{-- Attachments --}}
        @php
            $attachments = [];
            if ($ticket->attachment) $attachments[] = $ticket->attachment;
            foreach($ticket->replies as $r) {
                if ($r->attachment) $attachments[] = $r->attachment;
            }
        @endphp
        @if(count($attachments))
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-paperclip mr-1"></i> Attachments</h3></div>
            <div class="card-body p-2">
                @foreach($attachments as $att)
                <a href="{{ asset('storage/'.$att) }}" target="_blank" class="d-flex align-items-center gap-1 small p-2 border-bottom text-info">
                    <i class="fas fa-file mr-1"></i> {{ basename($att) }}
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@push('js')
<script>
const CSRF      = '{{ csrf_token() }}';
const CHAT_URL  = '{{ route("client-support.chat", $ticket->id) }}';
const MSG_URL   = '{{ route("client-support.chat.messages", $ticket->id) }}';
let lastId      = {{ $ticket->replies->last()->id ?? 0 }};
let polling;

// ── Auto scroll ───────────────────────────────────────────────────
function scrollBottom() {
    const box = document.getElementById('chatBox');
    if (box) box.scrollTop = box.scrollHeight;
}
scrollBottom();

// ── Attachment label ─────────────────────────────────────────────
$('#replyAttachment').change(function () {
    $('#attachName').text(this.files[0]?.name ?? '');
});

// ── Send Reply ───────────────────────────────────────────────────
$('#btnSendReply').click(function () {
    const msg = $('#replyMessage').val().trim();
    if (!msg) { toastr.warning('Please type a message.'); return; }

    const btn  = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    const form = new FormData();
    form.append('_token', CSRF);
    form.append('message', msg);
    const file = $('#replyAttachment')[0].files[0];
    if (file) form.append('attachment', file);

    $.ajax({
        url: CHAT_URL,
        method: 'POST',
        data: form,
        processData: false,
        contentType: false,
        success(res) {
            if (res.success) {
                $('#no-msg').remove();
                appendMsg(res.reply);
                lastId = res.reply.id;
                $('#replyMessage').val('');
                $('#replyAttachment').val('');
                $('#attachName').text('');
                scrollBottom();
            }
        },
        error() { toastr.error('Failed to send message.'); },
        complete() { btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Send'); }
    });
});

// Ctrl+Enter to send
$('#replyMessage').keydown(function (e) {
    if (e.ctrlKey && e.key === 'Enter') $('#btnSendReply').click();
});

// ── Append message ───────────────────────────────────────────────
function appendMsg(r) {
    const isAdmin = r.sender_type === 'admin';
    const initials = r.sender_name.substring(0, 2).toUpperCase();
    const avClass  = isAdmin ? 'av-admin' : 'av-customer';
    const msgClass = isAdmin ? 'admin' : 'customer';
    const badgeCls = isAdmin ? 'primary' : 'info';
    const badgeTxt = isAdmin ? 'Admin' : 'Client';
    const attach   = r.attachment
        ? `<div class="mt-1"><a href="${r.attachment}" target="_blank" class="text-info small"><i class="fas fa-paperclip"></i> Attachment</a></div>`
        : '';

    const html = `
    <div class="chat-msg ${msgClass}" id="msg-${r.id}">
        <div class="chat-avatar ${avClass}">${initials}</div>
        <div>
            <div class="chat-meta">
                <strong>${r.sender_name}</strong>
                <span class="badge badge-${badgeCls}" style="font-size:9px;">${badgeTxt}</span>
            </div>
            <div class="chat-bubble">${r.message}</div>
            ${attach}
            <div class="chat-meta">${r.ago}</div>
        </div>
    </div>`;

    $('#chatBox').append(html);
}

// ── Polling (every 5 seconds) ────────────────────────────────────
function pollMessages() {
    $.get(MSG_URL, { after: lastId }, function (res) {
        if (res.success && res.replies.length > 0) {
            res.replies.forEach(r => {
                if ($(`#msg-${r.id}`).length === 0) {
                    $('#no-msg').remove();
                    appendMsg(r);
                    lastId = r.id;
                }
            });
            scrollBottom();
        }
    });
}

polling = setInterval(pollMessages, 5000);

// Stop polling when page hidden
document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
        clearInterval(polling);
    } else {
        polling = setInterval(pollMessages, 5000);
    }
});
</script>
@endpush
