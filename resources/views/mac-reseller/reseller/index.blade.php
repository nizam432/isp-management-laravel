@extends('adminlte::page')

@section('title', 'MAC Reseller List')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"> <small class="text-muted fs-6">All POPs</small></h1>
        </div>
    </div>
@stop

@section('content')

{{-- Summary Cards --}}
<style>
.cust-stat-card {
    border-radius: 4px;
    color: #fff;
    padding: 14px 16px;
    margin-bottom: 16px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
}
.cust-stat-card .sc-left .sc-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(255,255,255,.85);
    margin-bottom: 4px;
}
.cust-stat-card .sc-left .sc-value {
    font-size: 32px;
    font-weight: 700;
    line-height: 1;
    color: #fff;
}
.cust-stat-card .sc-icon {
    font-size: 52px;
    color: rgba(255,255,255,.18);
}
</style>
<div class="row mb-3">
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-tie mr-1"></i> Total POPs</div>
                <div class="sc-value">{{ $totalPops }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-tie"></i></div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-users mr-1"></i> Total POP Client</div>
                <div class="sc-value">{{ $totalPopClients }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="cust-stat-card" style="background:#6f42c1;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-check mr-1"></i> Online Clients</div>
                <div class="sc-value">{{ $onlineClients }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-check"></i></div>
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
                    {{--<th>Clients(Running)</th>
                    <th>Clients(Enabled)</th>
                    <th>Clients(Disabled)</th>
                    <th>Clients(Left)</th>--}}
                    <th>RemainingFund</th>
                    {{--<th>ClientEnabled</th>
                    <th>FundStart</th>
                    <th>IsLocked?</th>--}}
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
                    <td>
                        @php
                            $serverNames = $r->tariff?->packages->pluck('server_name')->filter()->unique();
                        @endphp
                        {{ $serverNames && $serverNames->isNotEmpty() ? $serverNames->implode(', ') : 'N/A' }}
                    </td>
                    <td>{{ $r->mobile }}</td>
                    <td>{{ $r->business_name }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $r->level)) }}</td>
                    <td>{{ $r->tariff?->name ?? 'N/A' }}</td>
                        {{--<td>0</td>
                    <td>0</td>
                    <td>0</td>
                        <td>0</td>--}}
                    <td>{{ number_format($r->remaining_fund, 2) }}</td>

                    {{--
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input toggle-client"
                                id="ce-{{ $r->id }}" data-id="{{ $r->id }}"
                                {{ $r->client_enabled ? 'checked' : '' }}>
                            <label class="custom-control-label" for="ce-{{ $r->id }}"></label>
                        </div>
                    </td>

                    
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input toggle-fund"
                                id="fs-{{ $r->id }}" data-id="{{ $r->id }}"
                                {{ $r->fund_start ? 'checked' : '' }}>
                            <label class="custom-control-label" for="fs-{{ $r->id }}"></label>
                        </div>
                    </td>

                  
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input toggle-locked"
                                id="lk-{{ $r->id }}" data-id="{{ $r->id }}"
                                {{ $r->is_locked ? 'checked' : '' }}>
                            <label class="custom-control-label" for="lk-{{ $r->id }}"></label>
                        </div>
                    </td>
                    --}}
                    {{-- Action --}}
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('mac-reseller.list.edit', $r->id) }}">
                                    <i class="fas fa-edit mr-2 text-success"></i> Edit
                                </a>
                                <a class="dropdown-item change-password-btn" href="#"
                                   data-id="{{ $r->id }}" data-name="{{ $r->business_name }}">
                                    <i class="fas fa-key mr-2 text-warning"></i> Change Password
                                </a>
                                <a class="dropdown-item" href="{{ route('mac-reseller.list.login-as', $r->id) }}"
                                   target="_blank">
                                    <i class="fas fa-sign-in-alt mr-2 text-primary"></i> Login
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

{{-- Change Password Modal --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="changePasswordForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Change Password &mdash; <span id="cp-name"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cp-id" name="reseller_id">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                    </div>
                    <div id="cp-error" class="text-danger small"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
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

// Open Change Password modal
$(document).on('click', '.change-password-btn', function(e) {
    e.preventDefault();
    $('#changePasswordForm')[0].reset();
    $('#cp-error').text('');
    $('#cp-id').val($(this).data('id'));
    $('#cp-name').text($(this).data('name'));
    $('#changePasswordModal').modal('show');
});

// Submit Change Password
$('#changePasswordForm').on('submit', function(e) {
    e.preventDefault();
    const id = $('#cp-id').val();
    $('#cp-error').text('');

    $.post(`/mac-reseller/list/${id}/change-password`, $(this).serialize())
        .done(function(res) {
            $('#changePasswordModal').modal('hide');
            if (window.toastr) {
                toastr.success(res.message || 'Password updated successfully.');
            } else {
                alert(res.message || 'Password updated successfully.');
            }
        })
        .fail(function(xhr) {
            const msg = xhr.responseJSON?.message
                || Object.values(xhr.responseJSON?.errors ?? {}).flat().join(' ')
                || 'Failed to update password.';
            $('#cp-error').text(msg);
        });
});
</script>
@stop