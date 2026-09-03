@extends('reseller.layouts.app')

@section('title', 'Ticket Chat — ' . $ticket->ticket_no)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="m-0">Ticket {{ $ticket->ticket_no }}</h4>
        <small class="text-muted">
            {{ $ticket->customer->name ?? '—' }} | {{ $ticket->customer->phone ?? '' }} |
            <span class="badge badge-{{ $ticket->status_badge }}">{{ ucfirst($ticket->status) }}</span>
            <span class="badge badge-{{ $ticket->priority_badge }}">{{ ucfirst($ticket->priority) }}</span>
        </small>
    </div>
    <a href="{{ route('reseller.client-support.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body" id="chatBox" style="height:450px; overflow-y:auto; background:#f4f6f9;">
        @foreach($ticket->replies as $reply)
            @php
                $isReseller = $reply->sender_type === 'reseller';
                $isAdmin    = $reply->sender_type === 'admin';
            @endphp
            <div class="d-flex mb-3 {{ ($isReseller || $isAdmin) ? 'justify-content-end' : 'justify-content-start' }}">
                <div class="p-2 rounded" style="max-width:70%; background:{{ ($isReseller || $isAdmin) ? '#007bff' : '#fff' }}; color:{{ ($isReseller || $isAdmin) ? '#fff' : '#333' }}; border:1px solid #dee2e6;">
                    <small class="d-block font-weight-bold mb-1">
                        {{ $reply->sender_name }}
                        @if($isReseller)<span class="badge badge-light text-primary ml-1">You</span>@endif
                    </small>
                    <div>{{ $reply->message }}</div>
                    @if($reply->attachment)
                        <a href="{{ asset('storage/' . $reply->attachment) }}" target="_blank" class="d-block mt-1" style="color:inherit;text-decoration:underline;">
                            <i class="fas fa-paperclip mr-1"></i> Attachment
                        </a>
                    @endif
                    <small class="d-block mt-1" style="opacity:.8;">{{ $reply->created_at->format('d M Y h:i A') }}</small>
                </div>
            </div>
        @endforeach
    </div>
    <div class="card-footer">
        <form id="replyForm" enctype="multipart/form-data">
            <div class="input-group">
                <input type="text" id="messageInput" class="form-control" placeholder="Type a reply...">
                <div class="input-group-append">
                    <label class="btn btn-outline-secondary mb-0" title="Attach file">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" id="attachmentInput" hidden>
                    </label>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send</button>
                </div>
            </div>
            <small class="text-muted" id="attachedFileName"></small>
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
const CSRF = '{{ csrf_token() }}';
let lastId = {{ $ticket->replies->max('id') ?? 0 }};

function scrollToBottom() {
    const box = document.getElementById('chatBox');
    box.scrollTop = box.scrollHeight;
}
scrollToBottom();

$('#attachmentInput').on('change', function () {
    $('#attachedFileName').text(this.files[0]?.name ?? '');
});

function appendMessage(r) {
    const isReseller = r.sender_type === 'reseller' || r.sender_type === 'admin';
    const bg = isReseller ? '#007bff' : '#fff';
    const color = isReseller ? '#fff' : '#333';
    const align = isReseller ? 'justify-content-end' : 'justify-content-start';
    const youBadge = r.sender_type === 'reseller' ? '<span class="badge badge-light text-primary ml-1">You</span>' : '';
    const attach = r.attachment ? `<a href="${r.attachment}" target="_blank" class="d-block mt-1" style="color:inherit;text-decoration:underline;"><i class="fas fa-paperclip mr-1"></i> Attachment</a>` : '';

    $('#chatBox').append(`
        <div class="d-flex mb-3 ${align}">
            <div class="p-2 rounded" style="max-width:70%; background:${bg}; color:${color}; border:1px solid #dee2e6;">
                <small class="d-block font-weight-bold mb-1">${r.sender_name} ${youBadge}</small>
                <div>${r.message}</div>
                ${attach}
                <small class="d-block mt-1" style="opacity:.8;">${r.time}</small>
            </div>
        </div>
    `);
    scrollToBottom();
}

$('#replyForm').on('submit', function (e) {
    e.preventDefault();
    const message = $('#messageInput').val().trim();
    if (!message) return;

    const fd = new FormData();
    fd.append('_token', CSRF);
    fd.append('message', message);
    const file = $('#attachmentInput')[0].files[0];
    if (file) fd.append('attachment', file);

    $.ajax({
        url: '{{ route("reseller.client-support.chat.reply", $ticket) }}',
        method: 'POST', data: fd, contentType: false, processData: false,
        success(res) {
            if (res.success) {
                appendMessage(res.reply);
                lastId = res.reply.id;
                $('#messageInput').val('');
                $('#attachmentInput').val('');
                $('#attachedFileName').text('');
            }
        }
    });
});

// Poll for new messages every 5 seconds
setInterval(function () {
    $.get('{{ route("reseller.client-support.chat.messages", $ticket) }}', { after: lastId }, function (res) {
        if (res.success && res.replies.length) {
            res.replies.forEach(function (r) {
                appendMessage(r);
                lastId = r.id;
            });
        }
    });
}, 5000);
</script>
@endsection
