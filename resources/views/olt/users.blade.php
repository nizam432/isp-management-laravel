@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('title', 'OLT Users')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-users mr-2 text-primary"></i>OLT <small class="text-muted">Olt Users</small></h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('olt.index') }}">OLT</a></li>
            <li class="breadcrumb-item active">OLT Users</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

    {{-- ══ STAT CARDS ══ --}}
    <div class="row mb-3">
        <div class="col-6 col-md-3">
            <div class="info-box bg-gradient-teal mb-3">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Online</span>
                    <span class="info-box-number">{{ $stats['online'] }}</span>
                    <span class="progress-description">Online Clients</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="info-box bg-gradient-red mb-3">
                <span class="info-box-icon"><i class="fas fa-user-slash"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Offline</span>
                    <span class="info-box-number">{{ $stats['offline'] }}</span>
                    <span class="progress-description">Offline Clients</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="info-box bg-gradient-orange mb-3">
                <span class="info-box-icon"><i class="fas fa-signal"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Weak Signal</span>
                    <span class="info-box-number">{{ $stats['weak_signal'] }}</span>
                    <span class="progress-description">Signal &lt; -27 dBm</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="info-box bg-gradient-gray-dark mb-3">
                <span class="info-box-icon"><i class="fas fa-server"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total OLT</span>
                    <span class="info-box-number">{{ $stats['total_olt'] }}</span>
                    <span class="progress-description">No of OLT devices</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ FILTER ══ --}}
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter</h3>
            <button class="btn btn-sm btn-light" id="btnToggleFilter">
                Hide <i class="fas fa-chevron-up ml-1"></i>

        </div>
        <div class="card-body pb-1" id="filterBody">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="small font-weight-bold">FILTER BY STATUS:</label>
                    <select id="filterStatus" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold">FILTER BY DBM:</label>
                    <select id="filterDbm" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="excellent">Excellent (≥ -20 dBm)</option>
                        <option value="good">Good (-20 ~ -24 dBm)</option>
                        <option value="weak">Weak (-24 ~ -27 dBm)</option>
                        <option value="very_weak">Very Weak (&lt; -27 dBm)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold">FILTER BY OLT:</label>
                    <select id="filterOlt" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($oltList as $olt)
                            <option value="{{ $olt->id }}">{{ $olt->ip_address }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary btn-sm mr-1" id="btnApply">
                        <i class="fas fa-search mr-1"></i> Apply

                    <button class="btn btn-danger btn-sm" id="btnClear">
                        <i class="fas fa-times mr-1"></i> Clear

                </div>
            </div>
        </div>
    </div>

    {{-- ══ TABLE ══ --}}
    <div class="card">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> ONU / OLT User List</h3>
            <span class="badge badge-secondary" id="rowCount">0 records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="oltUsersTable" class="table table-bordered table-striped table-hover mb-0"
                    style="font-size: 12px; white-space: nowrap;">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>#</th>
                            <th>Client Code</th>
                            <th>UserName</th>
                            <th>Client Name</th>
                            <th>MAC Address</th>
                            <th>OLT Name</th>
                            <th>Optical Power</th>
                            <th>OLT Port</th>
                            <th>Status</th>
                            <th>Last Deregister Time</th>
                            <th>Distance (m)</th>
                            <th>Deregister Reason</th>
                            <th>Last Synced</th>

                        </tr>
                    </thead>
                    <tbody id="usersBody">
                        <tr>
                            <td colspan="13" class="text-center py-3 text-muted">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script>
const DATA_URL = '{{ route("olt.users.data") }}';

loadData();

$('#btnApply').on('click', loadData);
$('#btnClear').on('click', function () {
    $('#filterStatus, #filterDbm, #filterOlt').val('');
    loadData();
});

$('#btnToggleFilter').on('click', function () {
    $('#filterBody').toggle();
    const hidden = !$('#filterBody').is(':visible');
    $(this).html(hidden
        ? 'Show <i class="fas fa-chevron-down ml-1"></i>'
        : 'Hide <i class="fas fa-chevron-up ml-1"></i>');
});

function loadData() {
    $('#usersBody').html('<tr><td colspan="13" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');
    $('#rowCount').text('Loading...');

    $.get(DATA_URL, {
        status: $('#filterStatus').val(),
        dbm:    $('#filterDbm').val(),
        olt_id: $('#filterOlt').val(),
    }, function (res) {
        renderRows(res.data ?? []);
        $('#rowCount').text((res.count ?? 0) + ' records');
    }).fail(() => {
        $('#usersBody').html('<tr><td colspan="13" class="text-center text-danger">Data লোড হয়নি।</td></tr>');
    });
}

function renderRows(rows) {
    if (!rows.length) {
        $('#usersBody').html('<tr><td colspan="13" class="text-center text-muted py-3">No data available</td></tr>');
        return;
    }

    const html = rows.map((u, i) => {
        const statusBadge = {
            online:  '<span class="badge badge-success">Online</span>',
            offline: '<span class="badge badge-danger">Offline</span>',
        }[u.onu_status] ?? '<span class="badge badge-secondary">Unknown</span>';

        const power = u.optical_power !== null
            ? `${u.signal_badge} <small>${u.optical_power} dBm</small>`
            : '-';

        return `<tr>
            <td>${i + 1}</td>
            <td>${u.client_code}</td>
            <td>${u.username}</td>
            <td>${u.client_name}</td>
            <td><small>${u.mac_address}</small></td>
            <td><small>${u.olt_name}</small></td>
            <td>${power}</td>
            <td>${u.olt_port}</td>
            <td>${statusBadge}</td>
            <td><small>${u.last_deregister_time}</small></td>
            <td>${u.distance}</td>
            <td>${u.deregister_reason}</td>
            <td><small>${u.last_synced_at}</small></td>




            </td>
        </tr>`;
    }).join('');

    $('#usersBody').html(html);
}
</script>
@endsection
