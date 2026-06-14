{{-- resources/views/client/tickets.blade.php --}}
@extends('client.layout')
@section('title', 'Ticket List')

@section('content')

<div class="page-title">Ticket List</div>

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-ticket-alt"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Tickets</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-spinner"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['processing'] }}</div>
            <div class="stat-label">Processing</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['solved'] }}</div>
            <div class="stat-label">Solved</div>
        </div>
    </div>
</div>

{{-- New Ticket --}}
<div class="card">
    <div class="card-header"><i class="fas fa-plus-circle"></i> Create New Ticket</div>
    <div class="card-body">
        <form method="POST" action="{{ route('client.tickets.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label>Category <span style="color:#e74c3c;">*</span></label>
                    <select name="support_category_id" class="form-control {{ $errors->has('support_category_id') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select Category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('support_category_id') == $cat->id ? 'selected':'' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('support_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Priority <span style="color:#e74c3c;">*</span></label>
                    <select name="priority" class="form-control {{ $errors->has('priority') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select Priority —</option>
                        <option value="low"    {{ old('priority')=='low'    ? 'selected':'' }}>Low</option>
                        <option value="medium" {{ old('priority')=='medium' ? 'selected':'' }}>Medium</option>
                        <option value="high"   {{ old('priority')=='high'   ? 'selected':'' }}>High</option>
                        <option value="urgent" {{ old('priority')=='urgent' ? 'selected':'' }}>Urgent</option>
                    </select>
                    @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-group">
                <label>Description <span style="color:#e74c3c;">*</span></label>
                <textarea name="remarks" class="form-control {{ $errors->has('remarks') ? 'is-invalid' : '' }}"
                    rows="3" placeholder="Describe your issue..." required>{{ old('remarks') }}</textarea>
                @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Submit Ticket
            </button>
        </form>
    </div>
</div>

{{-- Ticket List --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between;">
        <span><i class="fas fa-list"></i> My Tickets</span>
        <form method="GET" style="display:flex; gap:8px;">
            <select name="status" class="form-control" style="width:auto; padding:5px 10px; font-size:12px;">
                <option value="">All</option>
                <option value="pending"    {{ request('status')=='pending'    ? 'selected':'' }}>Pending</option>
                <option value="processing" {{ request('status')=='processing' ? 'selected':'' }}>Processing</option>
                <option value="solved"     {{ request('status')=='solved'     ? 'selected':'' }}>Solved</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        </form>
    </div>

    @if($tickets->isEmpty())
        <div class="card-body" style="text-align:center; color:#aaa; padding:2.5rem;">
            <i class="fas fa-ticket-alt" style="font-size:2.5rem; display:block; margin-bottom:.75rem;"></i>
            No tickets found.
        </div>
    @else
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ticket No</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $i => $ticket)
                @php
                    $priorityMap = ['urgent'=>['badge-danger','Urgent'],'high'=>['badge-warning','High'],'medium'=>['badge-info','Medium'],'low'=>['badge-secondary','Low']];
                    $statusMap2  = ['pending'=>['badge-danger','Pending'],'processing'=>['badge-warning','Processing'],'solved'=>['badge-success','Solved'],'closed'=>['badge-secondary','Closed']];
                    [$pBadge,$pText] = $priorityMap[$ticket->priority] ?? ['badge-secondary', $ticket->priority];
                    [$sBadge,$sText] = $statusMap2[$ticket->status]   ?? ['badge-secondary', $ticket->status];
                @endphp
                <tr>
                    <td>{{ $tickets->firstItem() + $i }}</td>
                    <td><small style="font-family:monospace; font-weight:600;">{{ $ticket->ticket_no }}</small></td>
                    <td><small>{{ $ticket->category->name ?? '—' }}</small></td>
                    <td><span class="badge {{ $pBadge }}">{{ $pText }}</span></td>
                    <td><span class="badge {{ $sBadge }}">{{ $sText }}</span></td>
                    <td><small>{{ $ticket->created_at->format('d M Y') }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $tickets->links() }}</div>
    @endif
</div>

@endsection
