@extends('reseller.layouts.app')

@section('title', 'Support History')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Support History</h4>
    <div>
        <a href="{{ route('reseller.client-support.history.pdf', request()->query()) }}" class="btn btn-danger btn-sm" target="_blank">
            <i class="fas fa-file-pdf mr-1"></i> PDF
        </a>
        <a href="{{ route('reseller.client-support.history.csv', request()->query()) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-csv mr-1"></i> CSV
        </a>
    </div>
</div>

<style>
.cust-stat-card {
    border-radius: 4px; color: #fff; padding: 14px 16px; margin-bottom: 16px;
    height: 80px; display: flex; align-items: center; justify-content: space-between; overflow: hidden;
}
.cust-stat-card .sc-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: rgba(255,255,255,.85); margin-bottom: 4px; }
.cust-stat-card .sc-value { font-size: 32px; font-weight: 700; line-height: 1; color: #fff; }
.cust-stat-card .sc-icon { font-size: 52px; color: rgba(255,255,255,.18); }
</style>

<div class="row mb-3">
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div><div class="sc-label"><i class="fas fa-ticket-alt mr-1"></i> Total Solved</div><div class="sc-value">{{ $totalTickets }}</div></div>
            <div class="sc-icon"><i class="fas fa-ticket-alt"></i></div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#f39c12;">
            <div><div class="sc-label"><i class="fas fa-cog mr-1"></i> Priority Breakdown</div><div class="sc-value" style="font-size:18px;">H:{{ $highCount }} M:{{ $mediumCount }} L:{{ $lowCount }}</div></div>
            <div class="sc-icon"><i class="fas fa-cog"></i></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body pb-1">
        <form method="GET">
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
                        <label class="small font-weight-bold">Category</label>
                        <select name="category_id" class="form-control form-control-sm">
                            <option value="">All</option>
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
                            <option value="">All</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-search mr-1"></i> Apply Filter</button>
                    <a href="{{ route('reseller.client-support.monthly') }}" class="btn btn-secondary btn-sm">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Sr.No.</th><th>Date</th><th>Ticket No</th><th>Client Code</th><th>Username</th>
                        <th>Mobile No</th><th>Zone</th><th>Category</th><th>Solve Time</th><th>Duration</th><th>Info</th>
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
                        <td><small>{{ $t->customer->resellerZone->name ?? '—' }}</small></td>
                        <td><small>{{ $t->category->name ?? '—' }}</small></td>
                        <td><small>{{ $t->solved_at?->format('d M Y H:i A') ?? '—' }}</small></td>
                        <td><small class="text-muted">{{ $t->duration }}</small></td>
                        <td>
                            <small>
                                Priority: <span class="badge badge-{{ $t->priority_badge }}">{{ ucfirst($t->priority) }}</span><br>
                                {{ $t->assignees->pluck('name')->implode(', ') ?: '—' }}
                            </small>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">No solved tickets in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer"><small class="text-muted">Showing {{ $tickets->count() }} entries</small></div>
</div>

@endsection