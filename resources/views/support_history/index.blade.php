{{-- resources/views/support_history/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Monthly Support')
@section('page_actions')
    <a href="{{ route('support-history.pdf', request()->query()) }}" class="btn btn-danger btn-sm mr-1" target="_blank">
        <i class="fas fa-file-pdf mr-1"></i> Generate PDF
    </a>
    <a href="{{ route('support-history.csv', request()->query()) }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-csv mr-1"></i> Generate CSV
    </a>
@endsection

@section('page_content')

{{-- Summary Cards --}}
<style>
.cust-stat-card {
    border-radius: 4px; color: #fff; padding: 14px 16px;
    margin-bottom: 16px; height: 80px;
    display: flex; align-items: center; justify-content: space-between; overflow: hidden;
}
.cust-stat-card .sc-left .sc-label {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: rgba(255,255,255,.85); margin-bottom: 4px;
}
.cust-stat-card .sc-left .sc-value { font-size: 32px; font-weight: 700; line-height: 1; color: #fff; }
.cust-stat-card .sc-icon { font-size: 52px; color: rgba(255,255,255,.18); }
</style>

<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-ticket-alt mr-1"></i> Total Tickets</div>
                <div class="sc-value">{{ $totalTickets }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-ticket-alt"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user mr-1"></i> From Client Portal</div>
                <div class="sc-value">{{ $fromClient }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#e74c3c;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-shield mr-1"></i> From Admin Portal</div>
                <div class="sc-value">{{ $fromAdmin }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-shield"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#f39c12;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-cog mr-1"></i> Ticket's Priority</div>
                <div class="sc-value" style="font-size:18px;">H:{{ $highCount }} M:{{ $mediumCount }} L:{{ $lowCount }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-cog"></i></div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card">
    <div class="card-body pb-1">
        <form method="GET" action="{{ route('support-history.index') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="form-group">
                        <label class="small font-weight-bold">From Date</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="form-group">
                        <label class="small font-weight-bold">To Date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date', now()->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="form-group">
                        <label class="small font-weight-bold">Solved By</label>
                        <select name="solved_by" class="form-control form-control-sm">
                            <option value="">Select</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('solved_by') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="form-group">
                        <label class="small font-weight-bold">Problem Category</label>
                        <select name="category_id" class="form-control form-control-sm">
                            <option value="">Select</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="form-group">
                        <label class="small font-weight-bold">Zone</label>
                        <select name="zone_id" class="form-control form-control-sm">
                            <option value="">Select</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-6 d-flex align-items-end mb-3">
                    <button type="button" class="btn btn-danger btn-sm mr-2" onclick="$('#filterForm')[0].reset(); window.location='{{ route('support-history.index') }}'">
                        <i class="fas fa-times mr-1"></i> Clear Filter
                    </button>
                    <button type="submit" class="btn btn-info btn-sm">
                        <i class="fas fa-search mr-1"></i> Apply Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="d-flex align-items-center justify-content-between px-3 pt-2 pb-1">
            <div>
                <label class="small mb-0 mr-1">SHOW</label>
                <select class="form-control form-control-sm d-inline-block" style="width:70px;">
                    <option>10</option><option>25</option><option selected>100</option>
                </select>
                <label class="small mb-0 ml-1">ENTRIES</label>
            </div>
            <div>
                <label class="small mb-0 mr-1">SEARCH:</label>
                <input type="text" id="historySearch" class="form-control form-control-sm d-inline-block" style="width:180px;">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0" id="historyTable">
                <thead class="thead-dark">
                    <tr>
                        <th>Sr.No.</th>
                        <th>Date</th>
                        <th>TicketNo.</th>
                        <th>ClientCode</th>
                        <th>Username</th>
                        <th>MobileNo.</th>
                        <th>Zone</th>
                        <th>Category</th>
                        <th>Solve Time</th>
                        <th>Solved By</th>
                        <th>Duration</th>
                        <th>Ticketing Info</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $i => $t)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><small>{{ $t->created_at->format('d M Y') }}</small></td>
                        <td><small><code>{{ $t->ticket_no }}</code></small></td>
                        <td><small>{{ $t->customer->customer_code ?? '—' }}</small></td>
                        <td><small>{{ $t->customer->pppoe_username ?? '—' }}</small></td>
                        <td><small>{{ $t->customer->phone ?? '—' }}</small></td>
                        <td><small>{{ $t->customer->zone->name ?? '—' }}</small></td>
                        <td><small>{{ $t->category->name ?? '—' }}</small></td>
                        <td><small>{{ $t->solved_at?->format('d M Y H:i A') ?? '—' }}</small></td>
                        <td><small>{{ $t->solvedBy->name ?? '—' }}</small></td>
                        <td><small class="text-muted">{{ $t->duration }}</small></td>
                        <td>
                            <small>
                                Priority: <span class="badge badge-{{ $t->priority_badge }}">{{ ucfirst($t->priority) }}</span><br>
                                {{ $t->assignees->pluck('name')->implode(', ') ?: '—' }}
                            </small>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="text-center text-muted py-4">No data available in table</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <small class="text-muted">Showing {{ $tickets->count() }} entries</small>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
$('#historySearch').on('keyup', function () {
    const val = $(this).val().toLowerCase();
    $('#historyTable tbody tr').each(function () {
        $(this).toggle($(this).text().toLowerCase().includes(val));
    });
});
</script>
@endpush
