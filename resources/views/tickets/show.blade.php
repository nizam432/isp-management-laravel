{{-- resources/views/tickets/show.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Ticket: ' . $ticket->ticket_no)
@section('page_actions')
    <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
@endsection
@section('page_content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Ticket Details</h3></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Ticket No</th><td><code>{{ $ticket->ticket_no }}</code></td></tr>
                    <tr><th>Customer</th><td>{{ $ticket->customer->name }}</td></tr>
                    <tr><th>Phone</th><td>{{ $ticket->customer->phone }}</td></tr>
                    <tr><th>Category</th><td>{{ ucfirst($ticket->category) }}</td></tr>
                    <tr><th>Priority</th><td>
                        <span class="badge badge-{{ $ticket->priority === 'urgent' ? 'danger' : 'secondary' }}">{{ ucfirst($ticket->priority) }}</span>
                    </td></tr>
                    <tr><th>Status</th><td>
                        <span class="badge badge-info">{{ ucwords(str_replace('_',' ',$ticket->status)) }}</span>
                    </td></tr>
                    <tr><th>Assigned To</th><td>{{ $ticket->assignedTo->name ?? 'Unassigned' }}</td></tr>
                    <tr><th>Created</th><td>{{ $ticket->created_at->format('d M Y') }}</td></tr>
                    @if($ticket->resolved_at)
                    <tr><th>Resolved</th><td>{{ $ticket->resolved_at->format('d M Y') }}</td></tr>
                    @endif
                </table>
                <hr>
                <strong>Description:</strong>
                <p class="mt-1">{{ $ticket->description ?? 'No description.' }}</p>
            </div>
        </div>

        {{-- Update Ticket --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Update Ticket</h3></div>
            <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            @foreach(['open','assigned','in_progress','resolved','closed'] as $s)
                                <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_',' ',$s)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" class="form-control">
                            @foreach(['low','medium','high','urgent'] as $p)
                                <option value="{{ $p }}" {{ $ticket->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign To</label>
                        <select name="assigned_to" class="form-control">
                            <option value="">-- Unassigned --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ $ticket->assigned_to == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary btn-block">Update Ticket</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        {{-- Replies --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Conversation</h3></div>
            <div class="card-body">
                @forelse($ticket->replies as $reply)
                <div class="direct-chat-msg {{ $reply->user_id === auth()->id() ? 'right' : '' }} mb-3">
                    <div class="direct-chat-infos clearfix">
                        <span class="direct-chat-name {{ $reply->user_id === auth()->id() ? 'float-right' : 'float-left' }}">
                            {{ $reply->user->name ?? 'System' }}
                        </span>
                        <span class="direct-chat-timestamp {{ $reply->user_id === auth()->id() ? 'float-left' : 'float-right' }}">
                            {{ $reply->created_at->format('d M Y h:i A') }}
                        </span>
                    </div>
                    <div class="direct-chat-text {{ $reply->user_id === auth()->id() ? 'bg-primary text-white' : '' }}">
                        {{ $reply->message }}
                    </div>
                </div>
                @empty
                <p class="text-center text-muted">No replies yet. Be the first to reply.</p>
                @endforelse
            </div>
        </div>

        {{-- Reply Form --}}
        @if(!in_array($ticket->status, ['closed']))
        <div class="card">
            <div class="card-header"><h3 class="card-title">Add Reply</h3></div>
            <form action="{{ route('tickets.reply', $ticket) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <textarea name="message" class="form-control" rows="3" placeholder="Type your reply here..." required></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary"><i class="fas fa-reply"></i> Send Reply</button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
