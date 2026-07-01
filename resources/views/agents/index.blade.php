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
        @can('agent.create')
        <a href="{{ route('agents.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> Add Agent
        </a>
        @endcan
    </div>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible shadow-sm">
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible shadow-sm">
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
</div>
@endif

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="stat-card" style="background:linear-gradient(135deg,#1a237e,#283593);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-tie mr-1"></i> Total Agents</div>
                <div class="sc-value">{{ $totalAgents }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-tie"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-check-circle mr-1"></i> Active</div>
                <div class="sc-value">{{ $activeAgents }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card" style="background:linear-gradient(135deg,#5a6268,#6c757d);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-ban mr-1"></i> Inactive</div>
                <div class="sc-value">{{ $inactiveAgents }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-ban"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card" style="background:linear-gradient(135deg,#e65100,#f57c00);">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-wallet mr-1"></i> Pending Commission</div>
                <div class="sc-value">৳{{ number_format($pendingCommissionTotal) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-wallet"></i></div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4">
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
                    <option value="">All</option>
                    <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
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
        <input type="text" id="quickSearch" class="form-control form-control-sm"
               placeholder="Quick filter..." style="width:200px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="agentTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="th">Agent</th>
                        <th class="th">Login Email</th>
                        <th class="th">Phone</th>
                        <th class="th">Area</th>
                        <th class="th text-center">Customers</th>
                        <th class="th text-center">Commission %</th>
                        <th class="th text-right">Total Earned</th>
                        <th class="th text-right">Pending</th>
                        <th class="th text-center">Status</th>
                        <th class="th text-center" style="width:130px;">Action</th>
                    </tr>
                </thead>
                <tbody id="agentTableBody">
                    @forelse($agents as $agent)
                    <tr>
                        <td class="td">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle mr-2">
                                    {{ strtoupper(substr($agent->name, 0, 1)) }}
                                </div>
                                <a href="{{ route('agents.show', $agent) }}" class="font-weight-bold text-dark">
                                    {{ $agent->name }}
                                </a>
                            </div>
                        </td>
                        <td class="td text-muted small">{{ $agent->user?->email ?? '—' }}</td>
                        <td class="td">{{ $agent->phone ?? '—' }}</td>
                        <td class="td">
                            @if($agent->area)
                            <span class="badge badge-light border">{{ $agent->area }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="td text-center">
                            <span class="badge badge-info">{{ $agent->customers_count }}</span>
                        </td>
                        <td class="td text-center font-weight-bold">
                            {{ number_format($agent->commission_rate, 2) }}%
                        </td>
                        <td class="td text-right font-weight-bold text-success">
                            ৳{{ number_format($agent->commissions_sum_amount ?? 0, 2) }}
                        </td>
                        <td class="td text-right">
                            @if(($agent->pending_commission_sum ?? 0) > 0)
                            <span class="font-weight-bold text-warning">৳{{ number_format($agent->pending_commission_sum, 2) }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="td text-center">
                            @if($agent->is_active)
                            <span class="badge badge-success">Active</span>
                            @else
                            <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="td text-center">
                            <a href="{{ route('agents.show', $agent) }}"
                               class="btn btn-sm btn-info px-2 mb-1" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('agent.edit')
                            <a href="{{ route('agents.edit', $agent) }}"
                               class="btn btn-sm btn-warning px-2 mb-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('agents.toggle', $agent) }}" class="d-inline">
                                @csrf
                                <button type="submit" title="{{ $agent->is_active ? 'Deactivate' : 'Activate' }}"
                                        class="btn btn-sm mb-1 {{ $agent->is_active ? 'btn-secondary' : 'btn-success' }}"
                                        onclick="return confirm('{{ $agent->is_active ? 'Deactivate' : 'Activate' }} {{ $agent->name }}?')">
                                    <i class="fas fa-{{ $agent->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            @endcan
                            @can('agent.delete')
                            @if($agent->customers_count == 0)
                            <form method="POST" action="{{ route('agents.destroy', $agent) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger px-2 mb-1" title="Delete"
                                        onclick="return confirm('Delete agent {{ $agent->name }}? This cannot be undone.')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-user-tie fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No agents found.
                            @can('agent.create')
                            <a href="{{ route('agents.create') }}" class="d-block mt-2">+ Add your first agent</a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($agents->hasPages())
    <div class="card-footer bg-light py-2">
        {{ $agents->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection

@section('css')
<style>
    .stat-card {
        border-radius:8px; color:#fff; padding:14px 16px; margin-bottom:16px;
        height:80px; display:flex; align-items:center; justify-content:space-between;
        box-shadow:0 2px 8px rgba(0,0,0,0.12);
    }
    .stat-card .sc-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.85); margin-bottom:4px; }
    .stat-card .sc-value { font-size:26px; font-weight:700; line-height:1; color:#fff; }
    .stat-card .sc-icon  { font-size:48px; color:rgba(255,255,255,.18); }
    .th { font-size:12px; font-weight:700; text-transform:uppercase; color:#555; padding:10px 12px; }
    .td { padding:10px 12px; vertical-align:middle; }
    #agentTable tbody tr:hover { background:#f0f4ff !important; }
    .avatar-circle {
        width:34px; height:34px; border-radius:50%; background:#1a237e; color:#fff;
        font-weight:bold; font-size:14px; display:flex; align-items:center; justify-content:center;
        flex-shrink:0;
    }
</style>
@stop

@section('js')
@parent
<script>
$(function () {
    $('#quickSearch').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#agentTableBody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });
});
</script>
@stop
