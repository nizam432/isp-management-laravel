@extends('adminlte::page')
@section('title', 'Agents')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-user-tie mr-2 text-primary"></i>Agents
            </h4>
            <small class="text-muted">Manage all sales agents</small>
        </div>
        <a href="{{ route('agents.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> Add Agent
        </a>
    </div>
@endsection

@section('content')

{{-- spacing fix — card top margin so it doesn't touch navbar/header --}}
<div style="margin-top:4px;"></div>

{{-- Summary Cards --}}
<style>
.agent-stat-card {
    border-radius: 8px;
    color: #fff;
    padding: 14px 16px;
    margin-bottom: 16px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.agent-stat-card .sc-left .sc-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(255,255,255,.85);
    margin-bottom: 4px;
}
.agent-stat-card .sc-left .sc-value {
    font-size: 26px;
    font-weight: 700;
    line-height: 1;
    color: #fff;
}
.agent-stat-card .sc-icon {
    font-size: 48px;
    color: rgba(255,255,255,.18);
}
</style>
<div class="row mb-3" style="margin-top:8px;">
    <div class="col-md-3 col-6">
        <div class="agent-stat-card" style="background:linear-gradient(135deg,#1a237e,#283593);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-tie mr-1"></i> Total Agents</div>
                <div class="sc-value">{{ $totalAgents ?? $agents->total() }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-tie"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="agent-stat-card" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-check-circle mr-1"></i> Active</div>
                <div class="sc-value">{{ $activeAgents ?? 0 }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="agent-stat-card" style="background:linear-gradient(135deg,#5a6268,#6c757d);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-times-circle mr-1"></i> Inactive</div>
                <div class="sc-value">{{ $inactiveAgents ?? 0 }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="agent-stat-card" style="background:linear-gradient(135deg,#e65100,#f57c00);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-wallet mr-1"></i> Pending Commission</div>
                <div class="sc-value">৳{{ number_format($pendingCommissionTotal ?? 0) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-wallet"></i></div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Name / Phone / Email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Area</label>
                <select name="area" class="form-control form-control-sm">
                    <option value="">All Areas</option>
                    @foreach(\App\Models\Zone::all() as $zone)
                    <option value="{{ $zone->name }}" {{ request('area') == $zone->name ? 'selected' : '' }}>
                        {{ $zone->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-5 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-3 mr-2">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('agents.index') }}" class="btn btn-secondary btn-sm px-3">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Agent List --}}
<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Agent List
            <span class="badge badge-light ml-2">{{ $agents->total() }}</span>
        </h6>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Quick search..." style="width:220px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="agentTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th style="padding:10px 12px;font-size:12px;color:#555;">Agent</th>
                        <th style="padding:10px 12px;font-size:12px;color:#555;">Login Email</th>
                        <th style="padding:10px 12px;font-size:12px;color:#555;">Phone</th>
                        <th style="padding:10px 12px;font-size:12px;color:#555;">Area</th>
                        <th class="text-center" style="padding:10px 12px;font-size:12px;color:#555;">Customers</th>
                        <th class="text-center" style="padding:10px 12px;font-size:12px;color:#555;">Commission %</th>
                        <th class="text-right" style="padding:10px 12px;font-size:12px;color:#555;">Total Earned</th>
                        <th class="text-center" style="padding:10px 12px;font-size:12px;color:#555;">Status</th>
                        <th class="text-center" style="width:120px;">Action</th>
                    </tr>
                </thead>
                <tbody id="agentTableBody">
                    @forelse($agents as $agent)
                    <tr>
                        <td style="padding:10px 12px;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-2"
                                     style="width:34px; height:34px; background:#1a237e; color:#fff; font-weight:bold; font-size:14px;">
                                    {{ strtoupper(substr($agent->name, 0, 1)) }}
                                </div>
                                <span class="font-weight-bold">{{ $agent->name }}</span>
                            </div>
                        </td>
                        <td style="padding:10px 12px;" class="text-muted small">{{ $agent->user->email ?? '—' }}</td>
                        <td style="padding:10px 12px;">{{ $agent->phone ?? '—' }}</td>
                        <td style="padding:10px 12px;">
                            @if($agent->area)
                                <span class="badge badge-light border">{{ $agent->area }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 12px;" class="text-center">
                            <span class="badge badge-info">{{ $agent->customers_count ?? 0 }}</span>
                        </td>
                        <td style="padding:10px 12px;" class="text-center font-weight-bold">{{ number_format($agent->commission_rate, 2) }}%</td>
                        <td style="padding:10px 12px;" class="text-right font-weight-bold text-success">
                            ৳{{ number_format($agent->commissions_sum_amount ?? 0, 2) }}
                        </td>
                        <td style="padding:10px 12px;" class="text-center">
                            @if($agent->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td style="padding:10px 12px;" class="text-center">
                            <a href="{{ route('agents.show', $agent) }}" class="btn btn-sm btn-info px-2" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('agents.edit', $agent) }}" class="btn btn-sm btn-warning px-2" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-user-tie fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No agents found. Click <strong>+ Add Agent</strong> to add one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($agents->hasPages())
    <div class="card-footer bg-light py-2">{{ $agents->withQueryString()->links() }}</div>
    @endif
</div>

@endsection

@section('css')
<style>
    #agentTable tbody td { vertical-align: middle; }
    #agentTable tbody tr:hover { background:#f0f4ff !important; }
    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color:#1a237e; }
</style>
@stop

@section('js')
@parent
<script>
$(function () {
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#agentTableBody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });
});
</script>
@stop
