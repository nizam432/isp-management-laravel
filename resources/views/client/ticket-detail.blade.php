{{-- resources/views/client/ticket-detail.blade.php --}}
@extends('client.layout')
@section('title', 'Ticket #' . $ticket->ticket_no)

@section('extra_css')
<style>
    .ticket-detail-grid { display: grid; grid-template-columns: 1fr 280px; gap: 20px; }
    @media (max-width: 768px) { .ticket-detail-grid { grid-template-columns: 1fr; } }

    /* Chat */
    .chat-box { display: flex; flex-direction: column; gap: 14px; padding: 16px; max-height: 480px; overflow-y: auto; }
    .chat-msg { display: flex; gap: 10px; align-items: flex-start; max-width: 85%; }
    .chat-msg.admin { flex-direction: row; }
    .chat-msg.customer { flex-direction: row-reverse; margin-left: auto; }
    .chat-avatar {
        width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; color: #fff;
    }
    .chat-avatar.customer-av { background: #1a1f36; }
    .chat-avatar.admin-av    { background: #00c897; }
    .chat-bubble {
        padding: 10px 14px; border-radius: 12px; font-size: 13px; line-height: 1.5;
        max-width: 100%;
    }
    .chat-msg.customer .chat-bubble { background: #1a1f36; color: #fff; border-bottom-right-radius: 3px; }
    .chat-msg.admin    .chat-bubble { background: #f0f2f7; color: #333; border-bottom-left-radius: 3px; }
    .chat-meta { font-size: 11px; color: #aaa; margin-top: 4px; }
    .chat-msg.customer .chat-meta { text-align: right; }

    /* Reply form */
    .reply-box { border-top: 1px solid #eef0f5; padding: 14px 16px; }
    .reply-area {
        width: 100%; border: 1.5px solid #e0e4ef; border-radius: 8px;
        padding: 10px 12px; font-size: 13px; resize: vertical; outline: none;
        font-family: inherit; min-height: 80px;
    }
    .reply-area:focus { border-color: #00c897; }
    .reply-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; }
    .attach-label { font-size: 12px; color: #888; cursor: pointer; display: flex; align-items: center; gap: 5px; }
    .attach-label:hover { color: #00c897; }
    .attach-label input { display: none; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
    <a href="{{ route('client.tickets') }}" style="color:#888; font-size:13px; text-decoration:none;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
    <span style="color:#ccc;">|</span>
    <div class="page-title" style="margin:0;">Ticket #{{ $ticket->ticket_no }}</div>
</div>

<div class="ticket-detail-grid">

    {{-- LEFT: Ticket info + Discussion --}}
    <div>

        {{-- Ticket Info Card --}}
        <div class="card">
            <div class="card-body">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
                    <h2 style="font-size:18px; font-weight:600; color:#1a1f36;">
                        {{ $ticket->category->name ?? 'Support Ticket' }} — #{{ $ticket->ticket_no }}
                    </h2>
                    <a href="{{ route('client.tickets') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; font-size:13px;">
                    <div>
                        <div style="color:#888; margin-bottom:3px;">Reported By :</div>
                        <div>[{{ $customer->customer_code }}] {{ $customer->name }}</div>
                    </div>
                    <div>
                        <div style="color:#888; margin-bottom:3px;">Assigned To :</div>
                        <div>
                            @if($ticket->assignees->count() > 0)
                                {{ $ticket->assignees->pluck('name')->join(', ') }}
                            @else
                                <span style="color:#aaa;">Not assigned yet</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div style="color:#888; margin-bottom:3px;">Created On :</div>
                        <div>{{ $ticket->created_at->diffForHumans() }}</div>
                    </div>
                    <div>
                        <div style="color:#888; margin-bottom:3px;">Updated On :</div>
                        <div>{{ $ticket->updated_at->format('d M Y h:i A') }}</div>
                    </div>
                    <div>
                        <div style="color:#888; margin-bottom:3px;">Status :</div>
                        @php
                            $statusMap = ['pending'=>['badge-danger','Pending'],'processing'=>['badge-warning','Processing'],'solved'=>['badge-success','Solved'],'closed'=>['badge-secondary','Closed']];
                            [$sBadge,$sText] = $statusMap[$ticket->status] ?? ['badge-secondary', ucfirst($ticket->status)];
                        @endphp
                        <span class="badge {{ $sBadge }}">{{ $sText }}</span>
                    </div>
                    <div>
                        <div style="color:#888; margin-bottom:3px;">Priority :</div>
                        @php
                            $priorityMap = ['urgent'=>['badge-danger','Urgent'],'high'=>['badge-warning','High'],'medium'=>['badge-warning','Medium'],'low'=>['badge-secondary','Low']];
                            [$pBadge,$pText] = $priorityMap[$ticket->priority] ?? ['badge-secondary', ucfirst($ticket->priority)];
                        @endphp
                        <span class="badge {{ $pBadge }}">{{ $pText }}</span>
                    </div>
                </div>

                @if($ticket->remarks)
                <div style="margin-top:16px; padding-top:16px; border-top:1px solid #f0f2f7;">
                    <div style="color:#888; font-size:12px; margin-bottom:5px;">Overview :</div>
                    <div style="font-size:13px; color:#444; line-height:1.6;">{{ $ticket->remarks }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Discussion --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-comments"></i>
                Discussion ({{ $ticket->replies->count() }})
            </div>

            {{-- Chat messages --}}
            <div class="chat-box" id="chatBox">
                @forelse($ticket->replies as $reply)
                    @php $isCustomer = $reply->sender_type === 'customer'; @endphp
                    <div class="chat-msg {{ $isCustomer ? 'customer' : 'admin' }}">
                        <div class="chat-avatar {{ $isCustomer ? 'customer-av' : 'admin-av' }}">
                            {{ strtoupper(substr($isCustomer ? $customer->name : ($reply->user->name ?? 'A'), 0, 2)) }}
                        </div>
                        <div>
                            <div style="font-size:11px; color:#888; margin-bottom:3px; {{ $isCustomer ? 'text-align:right;' : '' }}">
                                {{ $isCustomer ? $customer->name : ($reply->user->name ?? 'Admin') }}
                                <span class="badge {{ $isCustomer ? 'badge-info' : 'badge-success' }}" style="font-size:9px; padding:1px 6px;">{{ $isCustomer ? 'User' : 'Admin' }}</span>
                            </div>
                            <div class="chat-bubble">{{ $reply->message }}</div>
                            @if($reply->attachment)
                            <div style="margin-top:5px; {{ $isCustomer ? 'text-align:right;' : '' }}">
                                <a href="{{ asset('storage/'.$reply->attachment) }}" target="_blank" style="font-size:11px; color:#00c897;">
                                    <i class="fas fa-paperclip"></i> Attachment
                                </a>
                            </div>
                            @endif
                            <div class="chat-meta">{{ $reply->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; color:#aaa; padding:2rem;">
                        <i class="fas fa-comments" style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
                        No messages yet.
                    </div>
                @endforelse
            </div>

            {{-- Reply form --}}
            @if(!in_array($ticket->status, ['solved', 'closed']))
            <div class="reply-box">
                <form method="POST" action="{{ route('client.tickets.reply', $ticket) }}" enctype="multipart/form-data">
                    @csrf
                    @error('message')
                        <div style="color:#e74c3c; font-size:12px; margin-bottom:6px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                    @error('attachment')
                        <div style="color:#e74c3c; font-size:12px; margin-bottom:6px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                    <textarea name="message" class="reply-area" placeholder="Your message..." required></textarea>
                    <div class="reply-footer">
                        <label class="attach-label">
                            <i class="fas fa-paperclip"></i> Attachment
                            <input type="file" name="attachment" accept=".png,.jpg,.jpeg,.pdf">
                        </label>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div style="padding:14px 16px; background:#f9fafb; font-size:13px; color:#888; text-align:center;">
                <i class="fas fa-lock"></i> This ticket is {{ $ticket->status }}. You cannot reply.
            </div>
            @endif
        </div>

    </div>

    {{-- RIGHT: Attachments --}}
    <div>
        <div class="card">
            <div class="card-header"><i class="fas fa-paperclip"></i> Attachments</div>
            <div class="card-body">
                @php
                    $attachments = [];
                    if ($ticket->attachment) $attachments[] = ['file' => $ticket->attachment, 'label' => 'Ticket Attachment'];
                    foreach($ticket->replies as $r) {
                        if ($r->attachment) $attachments[] = ['file' => $r->attachment, 'label' => 'Reply Attachment'];
                    }
                @endphp

                @if(count($attachments) > 0)
                    @foreach($attachments as $att)
                    <a href="{{ asset('storage/'.$att['file']) }}" target="_blank"
                        style="display:flex; align-items:center; gap:8px; font-size:13px; color:#3a7bd5; text-decoration:none; padding:8px 0; border-bottom:1px solid #f0f2f7;">
                        <i class="fas fa-file" style="color:#aaa;"></i>
                        {{ $att['label'] }}
                    </a>
                    @endforeach
                @else
                    <div style="color:#aaa; font-size:13px; text-align:center; padding:1rem;">
                        No attachments
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection

@section('extra_js')
<script>
    // Auto scroll chat to bottom
    const chatBox = document.getElementById('chatBox');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>
@endsection
