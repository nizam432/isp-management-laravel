@extends('reseller.layouts.app')

@section('title', 'Client Support')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Client Support Tickets</h4>
    <button class="btn btn-primary btn-sm" id="btnAddTicket">
        <i class="fas fa-plus mr-1"></i> Add Ticket
    </button>
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
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div><div class="sc-label"><i class="fas fa-ticket-alt mr-1"></i> This Month</div><div class="sc-value">{{ $totalTickets }}</div></div>
            <div class="sc-icon"><i class="fas fa-ticket-alt"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#f39c12;">
            <div><div class="sc-label"><i class="fas fa-clock mr-1"></i> Pending</div><div class="sc-value">{{ $pendingTickets }}</div></div>
            <div class="sc-icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#6f42c1;">
            <div><div class="sc-label"><i class="fas fa-cogs mr-1"></i> Processing</div><div class="sc-value">{{ $processingTickets }}</div></div>
            <div class="sc-icon"><i class="fas fa-cogs"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div><div class="sc-label"><i class="fas fa-check-circle mr-1"></i> Solved</div><div class="sc-value">{{ $solvedTickets }}</div></div>
            <div class="sc-icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card">
    <div class="card-body pb-1">
        <form method="GET" id="filterForm">
            <div class="row">
                <div class="col-md-2 col-6">
                    <div class="form-group">
                        <label class="small font-weight-bold">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="solved" {{ request('status') == 'solved' ? 'selected' : '' }}>Solved</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="form-group">
                        <label class="small font-weight-bold">Priority</label>
                        <select name="priority" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-6">
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
                <div class="col-md-2 col-6">
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
                <div class="col-md-2 col-6">
                    <div class="form-group">
                        <label class="small font-weight-bold">From Date</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="form-group">
                        <label class="small font-weight-bold">To Date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-search mr-1"></i> Apply Filter</button>
            <a href="{{ route('reseller.client-support.index') }}" class="btn btn-secondary btn-sm">Clear</a>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="d-flex align-items-center justify-content-between px-3 pt-2 pb-1">
            <span class="badge badge-info">{{ $tickets->count() }} tickets</span>
            <input type="text" id="ticketSearch" class="form-control form-control-sm" style="width:200px" placeholder="Search...">
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0" id="ticketTable">
                <thead class="thead-dark">
                    <tr>
                        <th>Ticket No</th>
                        <th>Client</th>
                        <th>Category</th>
                        <th>Complained No</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assignees</th>
                        <th>Created</th>
                        <th style="width:150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                    <tr id="ticket-row-{{ $t->id }}">
                        <td><code>{{ $t->ticket_no }}</code></td>
                        <td>
                            <strong>{{ $t->customer->name ?? '—' }}</strong>
                            <br><small class="text-muted">{{ $t->customer->customer_code ?? '' }} | {{ $t->customer->phone ?? '' }}</small>
                        </td>
                        <td>{{ $t->category->name ?? '—' }}</td>
                        <td>{{ $t->complained_no }}</td>
                        <td><span class="badge badge-{{ $t->priority_badge }}">{{ ucfirst($t->priority) }}</span></td>
                        <td><span class="badge badge-{{ $t->status_badge }}">{{ ucfirst($t->status) }}</span></td>
                        <td><small>{{ $t->assignees->pluck('name')->implode(', ') ?: '—' }}</small></td>
                        <td><small>{{ $t->created_at->format('d M Y h:i A') }}</small></td>
                        <td>
                            <a href="{{ route('reseller.client-support.chat', $t) }}" class="btn btn-xs btn-primary" title="Chat"><i class="fas fa-comments"></i></a>
                            <button class="btn btn-xs btn-warning btn-edit" data-id="{{ $t->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                            @if($t->status !== 'solved')
                            <button class="btn btn-xs btn-success btn-solve" data-id="{{ $t->id }}" data-mac="{{ $t->customer->mac_address ?? '' }}" data-ip="{{ $t->customer->ip_address ?? '' }}" title="Mark Solved"><i class="fas fa-check"></i></button>
                            @endif
                            <button class="btn btn-xs btn-info btn-assign" data-id="{{ $t->id }}" title="Assign"><i class="fas fa-user-plus"></i></button>
                            <button class="btn btn-xs btn-danger btn-delete" data-id="{{ $t->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No tickets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Ticket Modal --}}
<div class="modal fade" id="addTicketModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-plus mr-1"></i> Add Support Ticket</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">Client PPPoE Username / Code <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="lookupUsername" class="form-control" placeholder="Type username and press lookup">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" id="btnLookup"><i class="fas fa-search"></i> Lookup</button>
                        </div>
                    </div>
                </div>
                <div id="customerInfoBox" class="alert alert-light border" style="display:none;">
                    <div class="row small">
                        <div class="col-6"><strong>Name:</strong> <span id="ci_name"></span></div>
                        <div class="col-6"><strong>Phone:</strong> <span id="ci_phone"></span></div>
                        <div class="col-6"><strong>Zone:</strong> <span id="ci_zone"></span></div>
                        <div class="col-6"><strong>Status:</strong> <span id="ci_status"></span></div>
                    </div>
                    <input type="hidden" id="add_customer_id">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Category <span class="text-danger">*</span></label>
                            <select id="add_support_category_id" class="form-control">
                                <option value="">Select</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Priority <span class="text-danger">*</span></label>
                            <select id="add_priority" class="form-control">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Complained No <span class="text-danger">*</span></label>
                            <input type="text" id="add_complained_no" class="form-control" placeholder="Caller's phone number">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="font-weight-bold">Remarks <span class="text-danger">*</span></label>
                            <textarea id="add_remarks" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Attachment</label>
                            <input type="file" id="add_attachment" class="form-control-file">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="custom-control custom-checkbox mt-3">
                            <input type="checkbox" class="custom-control-input" id="add_send_sms" checked>
                            <label class="custom-control-label" for="add_send_sms">Send SMS to client</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnSaveTicket"><i class="fas fa-save mr-1"></i> Save Ticket</button>
            </div>
        </div>
    </div>
</div>

{{-- Solve Modal --}}
<div class="modal fade" id="solveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Press Yes if solved</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="solve_ticket_id">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold">Connectivity Status</label>
                            <input type="text" id="solve_connectivity" class="form-control form-control-sm" value="Checking..." readonly>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end mb-3">
                        <button type="button" class="btn btn-secondary btn-block" id="solve_online_badge" disabled>Checking...</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold">Uptime</label>
                            <input type="text" id="solve_uptime" class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold">Last Logout Time</label>
                            <input type="text" id="solve_logout_time" class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold">MAC Address</label>
                            <input type="text" id="solve_mac" class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold">IP Address</label>
                            <input type="text" id="solve_ip" class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger px-4" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark px-4" id="btnConfirmSolve">Yes, Solved</button>
            </div>
        </div>
    </div>
</div>

{{-- Assign Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Assign Employees</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assign_ticket_id">
                <div class="form-group">
                    <label class="small font-weight-bold">Department</label>
                    <select id="assign_department_id" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Employees</label>
                    <select id="assign_employee_ids" class="form-control form-control-sm" multiple size="6">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="assign_sms" checked>
                    <label class="custom-control-label" for="assign_sms">Notify via SMS</label>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnConfirmAssign">Assign</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
const CSRF = '{{ csrf_token() }}';

$('#ticketSearch').on('keyup', function () {
    const val = $(this).val().toLowerCase();
    $('#ticketTable tbody tr').each(function () {
        $(this).toggle($(this).text().toLowerCase().includes(val));
    });
});

$('#btnAddTicket').click(() => {
    $('#addTicketModal').find('input, textarea, select').val('');
    $('#customerInfoBox').hide();
    $('#add_priority').val('medium');
    $('#add_send_sms').prop('checked', true);
    $('#addTicketModal').modal('show');
});

$('#btnLookup').click(function () {
    const username = $('#lookupUsername').val();
    if (!username) return;
    $.get('{{ route("reseller.client-support.customer-info") }}', { username }, function (res) {
        if (res.success) {
            const c = res.customer;
            $('#add_customer_id').val(c.id);
            $('#ci_name').text(c.name);
            $('#ci_phone').text(c.phone);
            $('#ci_zone').text(c.zone);
            $('#ci_status').text(c.billing_status);
            $('#customerInfoBox').show();
        } else {
            alert(res.message);
            $('#customerInfoBox').hide();
        }
    });
});

$('#btnSaveTicket').click(function () {
    if (!$('#add_customer_id').val()) { alert('Please lookup a client first.'); return; }

    const fd = new FormData();
    fd.append('_token', CSRF);
    fd.append('customer_id', $('#add_customer_id').val());
    fd.append('support_category_id', $('#add_support_category_id').val());
    fd.append('priority', $('#add_priority').val());
    fd.append('complained_no', $('#add_complained_no').val());
    fd.append('remarks', $('#add_remarks').val());
    fd.append('send_sms', $('#add_send_sms').is(':checked') ? 1 : 0);
    const file = $('#add_attachment')[0].files[0];
    if (file) fd.append('attachment', file);

    $.ajax({
        url: '{{ route("reseller.client-support.store") }}',
        method: 'POST', data: fd, contentType: false, processData: false,
        success(res) {
            if (res.success) { location.reload(); }
        },
        error(xhr) {
            alert(Object.values(xhr.responseJSON?.errors ?? { e: ['Failed to save.'] }).flat().join('\n'));
        }
    });
});

$(document).on('click', '.btn-solve', function () {
    const id  = $(this).data('id');
    const mac = $(this).data('mac') ?? '';
    const ip  = $(this).data('ip') ?? '';

    $('#solve_ticket_id').val(id);
    $('#solve_mac').val(mac);
    $('#solve_ip').val(ip);
    $('#solve_uptime').val('Checking...');
    $('#solve_logout_time').val('Checking...');
    $('#solve_connectivity').val('Checking...');
    $('#solve_online_badge').text('Checking...').removeClass('btn-success btn-danger').addClass('btn-secondary');

    $('#solveModal').modal('show');

    $.get(`/reseller/client-support/${id}/mikrotik-status`, function (res) {
        if (res.online) {
            $('#solve_connectivity').val('Connected');
            $('#solve_online_badge').text('Online').removeClass('btn-secondary btn-danger').addClass('btn-success');
        } else {
            $('#solve_connectivity').val('Disconnected');
            $('#solve_online_badge').text('Offline').removeClass('btn-secondary btn-success').addClass('btn-danger');
        }
        $('#solve_uptime').val(res.uptime ?? '—');
        $('#solve_logout_time').val(res.last_logout ?? '—');
    }).fail(function () {
        $('#solve_connectivity').val('Disconnected');
        $('#solve_online_badge').text('Offline').removeClass('btn-secondary btn-success').addClass('btn-danger');
        $('#solve_uptime').val('N/A');
        $('#solve_logout_time').val('N/A');
    });
});

$('#btnConfirmSolve').click(function () {
    const isOnline = $('#solve_online_badge').hasClass('btn-success');
    if (!isOnline) {
        alert('Please make sure the connection is Online before marking as solved!');
        return;
    }

    const id  = $('#solve_ticket_id').val();
    const btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    $.post(`/reseller/client-support/${id}/solve`, { _token: CSRF }, function (res) {
        if (res.success) {
            $('#solveModal').modal('hide');
            location.reload();
        }
    }).always(function () {
        btn.prop('disabled', false).html('Yes, Solved');
    });
});

$(document).on('click', '.btn-delete', function () {
    const id = $(this).data('id');
    if (!confirm('Delete this ticket?')) return;
    $.ajax({
        url: `/reseller/client-support/${id}`, method: 'POST',
        data: { _token: CSRF, _method: 'DELETE' },
        success(res) { if (res.success) $(`#ticket-row-${id}`).remove(); }
    });
});

$(document).on('click', '.btn-assign', function () {
    $('#assign_ticket_id').val($(this).data('id'));
    $('#assignModal').modal('show');
});

$('#assign_department_id').on('change', function () {
    const deptId = $(this).val();
    if (!deptId) return;
    $.get(`/reseller/client-support/departments/${deptId}/employees`, function (res) {
        if (res.success) {
            const $sel = $('#assign_employee_ids').empty();
            res.employees.forEach(e => $sel.append(`<option value="${e.id}">${e.name}</option>`));
        }
    });
});

$('#btnConfirmAssign').click(function () {
    const id = $('#assign_ticket_id').val();
    const ids = $('#assign_employee_ids').val();
    if (!ids || !ids.length) { alert('Select at least one employee.'); return; }

    $.post(`/reseller/client-support/${id}/reassign`, {
        _token: CSRF, employee_ids: ids, sms: $('#assign_sms').is(':checked') ? 1 : 0,
    }, function (res) {
        if (res.success) { $('#assignModal').modal('hide'); location.reload(); }
    });
});
</script>
@endsection