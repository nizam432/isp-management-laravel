@extends('adminlte::page')

@section('title', 'MAC Reseller List')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">POPs <small class="text-muted fs-6">All POPs</small></h1>
        </div>
        <span class="text-muted"><i class="fas fa-users-cog"></i> POP &rsaquo; POP List <i class="fas fa-sync-alt"></i></span>
    </div>
@stop

@section('content')

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="info-box" style="background:linear-gradient(135deg,#17a2b8,#138496);color:#fff">
            <span class="info-box-icon"><i class="fas fa-user-tie"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total POPs</span>
                <span class="info-box-number">{{ $totalPops }}</span>
                <span>Total POPs of admin</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box" style="background:linear-gradient(135deg,#28a745,#218838);color:#fff">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total POP Client</span>
                <span class="info-box-number">{{ $totalPopClients }}</span>
                <span>Total running client of POPs</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box" style="background:linear-gradient(135deg,#6f42c1,#5a32a3);color:#fff">
            <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Online Clients</span>
                <span class="info-box-number">{{ $onlineClients }}</span>
                <span>Total exported online client of POPs</span>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('mac-reseller.list.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">FUND START</label>
                        <select name="fund_start" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="1" {{ request('fund_start') == '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ request('fund_start') == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">POP TYPE</label>
                        <select name="pop_type" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="prepaid" {{ request('pop_type') == 'prepaid' ? 'selected' : '' }}>Prepaid</option>
                            <option value="postpaid" {{ request('pop_type') == 'postpaid' ? 'selected' : '' }}>Postpaid</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">LOGIN STATUS</label>
                        <select name="login_status" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="locked">Locked</option>
                            <option value="active">Active</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">CLIENT ENABLED</label>
                        <select name="client_enabled" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">POP STATUS</label>
                        <select name="pop_status" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">CREATION FROM</label>
                        <input type="date" name="creation_from" class="form-control form-control-sm" value="{{ request('creation_from') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">CREATION TO</label>
                        <input type="date" name="creation_to" class="form-control form-control-sm" value="{{ request('creation_to') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100 mb-3">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="row mb-2">
            <div class="col-sm-2 d-flex align-items-center" style="gap:8px">
                <label class="mb-0 small">SHOW</label>
                <select class="form-control form-control-sm" style="width:70px">
                    <option selected>100</option>
                </select>
                <span class="small">ENTRIES</span>
            </div>
            <div class="col-sm-4 offset-sm-6 text-right d-flex align-items-center justify-content-end" style="gap:8px">
                <label class="mb-0 small">SEARCH:</label>
                <input type="text" id="searchInput" class="form-control form-control-sm" style="width:200px">
            </div>
        </div>

        <div class="table-responsive">
        <table class="table table-bordered table-sm" id="resellerTable" style="font-size:12px">
            <thead class="bg-dark text-white">
                <tr>
                    <th>Code</th>
                    <th>POP Name</th>
                    <th>POP Type</th>
                    <th>ContactPerson</th>
                    <th>Server Name</th>
                    <th>Mobile</th>
                    <th>Company Name</th>
                    <th>Level</th>
                    <th>TarifName</th>
                    <th>Clients(Running)</th>
                    <th>Clients(Enabled)</th>
                    <th>Clients(Disabled)</th>
                    <th>Clients(Left)</th>
                    <th>RemainingFund</th>
                    <th>ClientEnabled</th>
                    <th>FundStart</th>
                    <th>IsLocked?</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($resellers as $r)
                <tr>
                    <td>{{ $r->code }}</td>
                    <td>{{ $r->business_name }}</td>
                    <td>
                        <span class="badge badge-{{ $r->pop_type == 'prepaid' ? 'info' : 'warning' }}">
                            {{ ucfirst($r->pop_type) }}
                        </span>
                    </td>
                    <td>{{ $r->contact_person }}</td>
                    <td>{{ $r->tariff?->packages->first()?->server_name ?? 'N/A' }}</td>
                    <td>{{ $r->mobile }}</td>
                    <td>{{ $r->business_name }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $r->level)) }}</td>
                    <td>{{ $r->tariff?->name ?? 'N/A' }}</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>{{ number_format($r->remaining_fund, 2) }}</td>

                    {{-- Client Enabled Toggle --}}
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input toggle-client"
                                id="ce-{{ $r->id }}" data-id="{{ $r->id }}"
                                {{ $r->client_enabled ? 'checked' : '' }}>
                            <label class="custom-control-label" for="ce-{{ $r->id }}"></label>
                        </div>
                    </td>

                    {{-- Fund Start Toggle --}}
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input toggle-fund"
                                id="fs-{{ $r->id }}" data-id="{{ $r->id }}"
                                {{ $r->fund_start ? 'checked' : '' }}>
                            <label class="custom-control-label" for="fs-{{ $r->id }}"></label>
                        </div>
                    </td>

                    {{-- IsLocked Toggle --}}
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input toggle-locked"
                                id="lk-{{ $r->id }}" data-id="{{ $r->id }}"
                                {{ $r->is_locked ? 'checked' : '' }}>
                            <label class="custom-control-label" for="lk-{{ $r->id }}"></label>
                        </div>
                    </td>

                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('mac-reseller.list.edit', $r->id) }}">
                                    <i class="fas fa-edit mr-2 text-success"></i> Edit
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="18" class="text-center">No resellers found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{ $resellers->links() }}
    </div>
</div>
@stop

@section('js')
<script>
// Toggle Client Enabled
$(document).on('change', '.toggle-client', function() {
    const id = $(this).data('id');
    $.post(`/mac-reseller/list/${id}/client-enabled`, { _token: '{{ csrf_token() }}' });
});

// Toggle Fund Start
$(document).on('change', '.toggle-fund', function() {
    const id = $(this).data('id');
    $.post(`/mac-reseller/list/${id}/fund-start`, { _token: '{{ csrf_token() }}' });
});

// Toggle Locked
$(document).on('change', '.toggle-locked', function() {
    const id = $(this).data('id');
    $.post(`/mac-reseller/list/${id}/locked`, { _token: '{{ csrf_token() }}' });
});

// Search
$('#searchInput').on('keyup', function() {
    const val = $(this).val().toLowerCase();
    $('#resellerTable tbody tr').each(function() {
        $(this).toggle($(this).text().toLowerCase().includes(val));
    });
});
</script>
@stop
