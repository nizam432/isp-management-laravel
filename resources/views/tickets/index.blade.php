{{-- resources/views/tickets/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Support Tickets')
@section('page_actions')
    <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Ticket</a>
@endsection
@section('page_content')
<div class="row mb-3">
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $openCount }}</h3><p>Open Tickets</p></div>
            <div class="icon"><i class="fas fa-ticket-alt"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ $urgentCount }}</h3><p>Urgent Tickets</p></div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline flex-wrap gap-2">
            <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Subject / customer" value="{{ request('search') }}">
            <select name="status" class="form-control form-control-sm mr-2">
                <option value="">All Status</option>
                @foreach(['open','assigned','in_progress','resolved','closed'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <select name="priority" class="form-control form-control-sm mr-2">
                <option value="">All Priority</option>
                @foreach(['low','medium','high','urgent'] as $p)
                    <option value="{{ $p }}" {{ request('priority') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-default mr-1"><i class="fas fa-search"></i></button>
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-secondary">Reset</a>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead class="thead-light">
                <tr><th>Ticket No</th><th>Customer</th><th>Subject</th><th>Category</th><th>Priority</th><th>Status</th><th>Assigned To</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td><code>{{ $ticket->ticket_no }}</code></td>
                    <td>{{ $ticket->customer->name }}</td>
                    <td>{{ Str::limit($ticket->subject, 35) }}</td>
                    <td>{{ ucfirst($ticket->category) }}</td>
                    <td>
                        <span class="badge badge-{{ $ticket->priority === 'urgent' ? 'danger' : ($ticket->priority === 'high' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td><span class="badge badge-info">{{ ucwords(str_replace('_',' ',$ticket->status)) }}</span></td>
                    <td>{{ $ticket->assignedTo->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                        <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No tickets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $tickets->withQueryString()->links() }}</div>
</div>
@endsection
