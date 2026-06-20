@extends('reseller.layouts.app')
@section('title', 'Ticket Details')
@section('content')

<div class="mb-3">
    <a href="{{ route('reseller.client-support.index') }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card border-0 shadow-sm mb-3" style="border-radius:12px">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="font-weight-bold mb-0">{{ $ticket->subject ?? 'No subject' }}</h5>
            <span class="badge badge-{{ $ticket->status == 'open' ? 'warning' : 'success' }} px-3 py-2">{{ ucfirst($ticket->status) }}</span>
        </div>
        <p class="text-muted small mb-0">Client: {{ $ticket->customer?->name }} | Created: {{ $ticket->created_at?->format('d M Y h:i A') }}</p>
        <hr>
        <p>{{ $ticket->description ?? $ticket->message ?? '—' }}</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3" style="border-radius:12px">
    <div class="card-body">
        <h6 class="font-weight-bold mb-3"><i class="fas fa-comments text-info mr-1"></i> Conversation</h6>
        @forelse($ticket->replies as $r)
        <div class="mb-3 p-2" style="background:#f8fafc;border-radius:8px">
            <div class="d-flex justify-content-between">
                <strong class="small">{{ ucfirst($r->replied_by ?? 'User') }}</strong>
                <span class="text-muted small">{{ $r->created_at?->format('d M Y h:i A') }}</span>
            </div>
            <p class="mb-0 mt-1" style="font-size:.875rem">{{ $r->message }}</p>
        </div>
        @empty
        <p class="text-muted small">No replies yet.</p>
        @endforelse
    </div>
</div>

@if($ticket->status === 'open')
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body">
        <form id="replyForm">
            @csrf
            <div class="form-group">
                <label class="small font-weight-bold">Reply</label>
                <textarea name="message" class="form-control form-control-sm" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-sm btn-success px-4">
                <i class="fas fa-paper-plane mr-1"></i> Send Reply
            </button>
            <button type="button" id="closeTicketBtn" class="btn btn-sm btn-outline-danger px-4">
                <i class="fas fa-check mr-1"></i> Close Ticket
            </button>
        </form>
    </div>
</div>
@endif

@stop

@section('js')
<script>
$('#replyForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: "{{ route('reseller.client-support.reply', $ticket->id) }}",
        method: 'POST',
        data: $(this).serialize(),
        success: (res) => { if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 800); } }
    });
});

$('#closeTicketBtn').on('click', function() {
    if (!confirm('Close this ticket?')) return;
    $.post("{{ route('reseller.client-support.close', $ticket->id) }}", { _token: '{{ csrf_token() }}' },
        (res) => { if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 800); } }
    );
});
</script>
@stop
