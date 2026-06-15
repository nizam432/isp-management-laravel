{{-- resources/views/client/tickets.blade.php --}}
@extends('client.layout')
@section('title', 'Tickets')

@section('extra_css')
<style>
    .stat-card-ticket {
        background: #fff;
        border-radius: 10px;
        border: 1px solid #eef0f5;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .stat-icon-circle {
        width: 56px; height: 56px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; flex-shrink: 0;
    }
    .stat-icon-circle.blue   { background: #4e73df; color: #fff; }
    .stat-icon-circle.yellow { background: #f6c23e; color: #fff; }
    .stat-icon-circle.green  { background: #1cc88a; color: #fff; }
    .stat-icon-circle.red    { background: #e74a3b; color: #fff; }
    .stat-num  { font-size: 26px; font-weight: 700; color: #1a1f36; }
    .stat-lbl  { font-size: 13px; color: #888; margin-top: 2px; }

    .section-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 14px; flex-wrap: wrap; gap: 10px;
    }
    .section-title { font-size: 16px; font-weight: 600; color: #1a1f36; }

    .dt-controls {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px; margin-bottom: 12px;
    }
    .dt-show { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #555; }
    .dt-show select { padding: 4px 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; }
    .dt-search { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #555; }
    .dt-search input {
        padding: 5px 10px; border: 1px solid #ddd; border-radius: 6px;
        font-size: 13px; outline: none; width: 180px;
    }
    .dt-search input:focus { border-color: #00c897; }

    table.ticket-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    table.ticket-table th {
        background: #f8f9fc; padding: 10px 14px;
        text-align: left; font-weight: 600; color: #555;
        border-bottom: 2px solid #eef0f5; white-space: nowrap;
    }
    table.ticket-table th .sort-icon { color: #ccc; margin-left: 4px; font-size: 10px; }
    table.ticket-table td { padding: 11px 14px; border-bottom: 1px solid #f4f6f9; color: #444; vertical-align: middle; }
    table.ticket-table tr:last-child td { border-bottom: none; }
    table.ticket-table tr:hover td { background: #fafbfd; }
    table.ticket-table td a.subject-link { color: #4e73df; font-weight: 500; text-decoration: none; }
    table.ticket-table td a.subject-link:hover { text-decoration: underline; }

    .dt-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 14px; flex-wrap: wrap; gap: 10px; }
    .dt-info { font-size: 13px; color: #888; }
    .dt-pages { display: flex; gap: 4px; }
    .dt-pages a, .dt-pages span {
        padding: 5px 11px; border-radius: 6px; font-size: 12px;
        border: 1px solid #e0e4ef; color: #555; text-decoration: none; cursor: pointer;
    }
    .dt-pages .active { background: #4e73df; color: #fff; border-color: #4e73df; }
    .dt-pages a:hover { background: #f0f2f7; }

    /* Modal */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,.5); z-index: 999;
        align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: #fff; border-radius: 12px;
        width: 100%; max-width: 500px; margin: 1rem;
        box-shadow: 0 20px 60px rgba(0,0,0,.2); overflow: hidden;
    }
    .modal-head {
        background: #1a1f36; padding: 14px 20px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .modal-head-title { color: #fff; font-size: 15px; font-weight: 600; }
    .modal-close { background: none; border: none; color: #aaa; font-size: 22px; cursor: pointer; line-height: 1; }
    .modal-close:hover { color: #fff; }
    .modal-body-inner { padding: 20px; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <div class="page-title" style="margin:0;">Tickets</div>
    <div style="font-size:12px; color:#aaa;">
        SmartISP &rsaquo; Apps &rsaquo; Tickets
    </div>
</div>

{{-- Stat Cards --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:24px;">
    <div class="stat-card-ticket">
        <div class="stat-icon-circle blue"><i class="fas fa-tag"></i></div>
        <div><div class="stat-num">{{ $stats['total'] }}</div><div class="stat-lbl">Total Tickets</div></div>
    </div>
    <div class="stat-card-ticket">
        <div class="stat-icon-circle yellow"><i class="fas fa-clock"></i></div>
        <div><div class="stat-num">{{ $stats['pending'] }}</div><div class="stat-lbl">Pending Tickets</div></div>
    </div>
    <div class="stat-card-ticket">
        <div class="stat-icon-circle green"><i class="fas fa-check-circle"></i></div>
        <div><div class="stat-num">{{ $stats['solved'] }}</div><div class="stat-lbl">Closed Tickets</div></div>
    </div>
    <div class="stat-card-ticket">
        <div class="stat-icon-circle red"><i class="fas fa-trash"></i></div>
        <div><div class="stat-num">0</div><div class="stat-lbl">Deleted Tickets</div></div>
    </div>
</div>

{{-- Manage Tickets --}}
<div class="card">
    <div class="card-body" style="padding:20px;">

        <div class="section-header">
            <div class="section-title">Manage Tickets</div>
            <button class="btn btn-primary" id="btnAddTicket">
                <i class="fas fa-plus-circle"></i> Add Ticket
            </button>
        </div>

        {{-- DataTable Controls --}}
        <div class="dt-controls">
            <div class="dt-show">
                Show
                <select id="perPage" onchange="filterTickets()">
                    <option value="10" {{ request('per_page',10)==10?'selected':'' }}>10</option>
                    <option value="25" {{ request('per_page',10)==25?'selected':'' }}>25</option>
                    <option value="50" {{ request('per_page',10)==50?'selected':'' }}>50</option>
                </select>
                entries
            </div>
            <div class="dt-search">
                Search:
                <input type="text" id="searchInput" placeholder="Search..." value="{{ request('search') }}" oninput="debounceSearch()">
            </div>
        </div>

        {{-- Table --}}
        <div style="overflow-x:auto;">
            <table class="ticket-table">
                <thead>
                    <tr>
                        <th>ID <span class="sort-icon">&#8597;</span></th>
                        <th>Subject <span class="sort-icon">&#8597;</span></th>
                        <th>Requested By <span class="sort-icon">&#8597;</span></th>
                        <th>Assigned To <span class="sort-icon">&#8597;</span></th>
                        <th>Priority <span class="sort-icon">&#8597;</span></th>
                        <th>Status <span class="sort-icon">&#8597;</span></th>
                        <th>Created Date <span class="sort-icon">&#8597;</span></th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $i => $ticket)
                    @php
                        $priorityMap = [
                            'urgent' => ['#e74c3c','Urgent'],
                            'high'   => ['#e74c3c','High'],
                            'medium' => ['#f6c23e','Medium'],
                            'low'    => ['#95a5a6','Low'],
                        ];
                        $statusMap = [
                            'pending'    => ['#f6c23e','Pending'],
                            'processing' => ['#f39c12','Customer Reply'],
                            'solved'     => ['#1cc88a','Closed'],
                            'closed'     => ['#95a5a6','Closed'],
                        ];
                        [$pColor,$pText] = $priorityMap[$ticket->priority]  ?? ['#95a5a6', ucfirst($ticket->priority)];
                        [$sColor,$sText] = $statusMap[$ticket->status]      ?? ['#95a5a6', ucfirst($ticket->status)];
                    @endphp
                    <tr>
                        <td>{{ $tickets->firstItem() + $i }}</td>
                        <td>
                            <a href="{{ route('client.tickets.show', $ticket) }}" class="subject-link">
                                {{ $ticket->category->name ?? Str::limit($ticket->remarks, 30) }}
                            </a>
                        </td>
                        <td>{{ $customer->name }}</td>
                        <td>
                            @if($ticket->assignees && $ticket->assignees->count() > 0)
                                {{ $ticket->assignees->first()->name ?? '—' }}
                            @else
                                <span style="color:#aaa;">—</span>
                            @endif
                        </td>
                        <td>
                            <span style="background:{{ $pColor }}; color:#fff; padding:3px 10px; border-radius:4px; font-size:11px; font-weight:600;">
                                {{ $pText }}
                            </span>
                        </td>
                        <td>
                            <span style="background:{{ $sColor }}; color:#fff; padding:3px 10px; border-radius:4px; font-size:11px; font-weight:600;">
                                {{ $sText }}
                            </span>
                        </td>
                        <td><small>{{ $ticket->created_at->format('Y/m/d h:iA') }}</small></td>
                        <td>
                            <a href="{{ route('client.tickets.show', $ticket) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center; color:#aaa; padding:2.5rem;">
                            <i class="fas fa-ticket-alt" style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
                            No tickets found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="dt-footer">
            <div class="dt-info">
                @if($tickets->total() > 0)
                    Showing {{ $tickets->firstItem() }} to {{ $tickets->lastItem() }} of {{ $tickets->total() }} entries
                @else
                    Showing 0 entries
                @endif
            </div>
            <div class="dt-pages">
                @if($tickets->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $tickets->previousPageUrl() }}">Previous</a>
                @endif

                @foreach($tickets->getUrlRange(1, $tickets->lastPage()) as $page => $url)
                    @if($page == $tickets->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($tickets->hasMorePages())
                    <a href="{{ $tickets->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Add Ticket Modal --}}
<div class="modal-overlay" id="ticketModal">
    <div class="modal-box">
        <div class="modal-head">
            <div class="modal-head-title"><i class="fas fa-plus-circle" style="color:#00c897; margin-right:6px;"></i> Create New Ticket</div>
            <button class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="modal-body-inner">

            @if($errors->any())
            <div style="background:#fff0f0; border:1px solid #ffd0d0; border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:12px; color:#c0392b;">
                @foreach($errors->all() as $error)
                    <div><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('client.tickets.store') }}" enctype="multipart/form-data">
                @csrf

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

                <div class="form-group">
                    <label>Description <span style="color:#e74c3c;">*</span></label>
                    <textarea name="remarks" class="form-control {{ $errors->has('remarks') ? 'is-invalid' : '' }}"
                        rows="4" placeholder="Describe your issue..." required>{{ old('remarks') }}</textarea>
                    @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Attachment <small style="color:#888;">(png, jpg, jpeg, pdf)</small></label>
                    <input type="file" name="attachment" class="form-control" accept=".png,.jpg,.jpeg,.pdf">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:6px;">
                    <button type="button" class="btn btn-outline" id="modalCancel">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('extra_js')
<script>
// Modal open/close
const modal   = document.getElementById('ticketModal');
const btnAdd  = document.getElementById('btnAddTicket');
const btnClose= document.getElementById('modalClose');
const btnCancel= document.getElementById('modalCancel');

btnAdd.addEventListener('click',   () => modal.classList.add('open'));
btnClose.addEventListener('click', () => modal.classList.remove('open'));
btnCancel.addEventListener('click',() => modal.classList.remove('open'));
modal.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});

// Auto open on validation error
@if($errors->any())
    modal.classList.add('open');
@endif

// Search debounce
let searchTimer;
function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(filterTickets, 400);
}

function filterTickets() {
    const search   = document.getElementById('searchInput').value;
    const perPage  = document.getElementById('perPage').value;
    const url      = new URL(window.location.href);
    url.searchParams.set('search', search);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}
</script>
@endsection
