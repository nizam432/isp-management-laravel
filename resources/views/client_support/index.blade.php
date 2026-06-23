{{-- resources/views/client_support/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Client Support')
@section('page_actions')
    <button class="btn btn-primary btn-sm" id="btnNewTicket">
        <i class="fas fa-plus mr-1"></i> Open Support Ticket
    </button>
@endsection

@section('page_content')

{{-- ── Summary Cards ─────────────────────────────────────── --}}
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
.ticket-action-btn { font-size: 11px; padding: 2px 6px; }
</style>

<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-ticket-alt mr-1"></i> Total Tickets</div>
                <div class="sc-value" id="card_total">{{ $totalTickets }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-ticket-alt"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#e74c3c;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-hourglass-half mr-1"></i> Pending Tickets</div>
                <div class="sc-value" id="card_pending">{{ $pendingTickets }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-hourglass-half"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#f39c12;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-cog mr-1"></i> Processing Tickets</div>
                <div class="sc-value" id="card_processing">{{ $processingTickets }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-cog"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-check-circle mr-1"></i> Solved Tickets</div>
                <div class="sc-value" id="card_solved">{{ $solvedTickets }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
</div>

{{-- ── Filters ──────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header py-2">
        <h3 class="card-title mb-0"><i class="fas fa-filter mr-1 text-primary"></i> Search & Filter</h3>
    </div>
    <div class="card-body pb-1">
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="form-group">
                    <label class="small font-weight-bold">Support Category</label>
                    <select id="f_category" class="form-control form-control-sm">
                        <option value="">Select</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="form-group">
                    <label class="small font-weight-bold">Zone</label>
                    <select id="f_zone" class="form-control form-control-sm">
                        <option value="">Select</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="form-group">
                    <label class="small font-weight-bold">Solved By / Assign To</label>
                    <select id="f_solved_by" class="form-control form-control-sm">
                        <option value="">Select</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="form-group">
                    <label class="small font-weight-bold">Created By</label>
                    <select id="f_created_by" class="form-control form-control-sm">
                        <option value="">Select</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="form-group">
                    <label class="small font-weight-bold">Status</label>
                    <select id="f_status" class="form-control form-control-sm">
                        <option value="">Select</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="solved">Solved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="form-group">
                    <label class="small font-weight-bold">Priority</label>
                    <select id="f_priority" class="form-control form-control-sm">
                        <option value="">Select</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="form-group">
                    <label class="small font-weight-bold">From Date</label>
                    <input type="date" id="f_from" class="form-control form-control-sm">
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="form-group">
                    <label class="small font-weight-bold">To Date</label>
                    <input type="date" id="f_to" class="form-control form-control-sm">
                </div>
            </div>
        </div>
        <div class="form-group d-flex align-items-end">
            <div class="mr-3">
                <label class="small font-weight-bold">Complained No</label>
                <input type="text" id="f_complained" class="form-control form-control-sm" style="width:250px;" placeholder="Type text/number to filter (Complained No)">
            </div>
            <div class="mb-0">
                <button type="button" class="btn btn-secondary btn-sm mr-1" id="btnResetFilter">
                    <i class="fas fa-redo mr-1"></i> Reset
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnApplyFilter">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Ticket Table ─────────────────────────────────────────── --}}
<div class="card">
    <div class="card-body p-0">
        <div class="d-flex align-items-center justify-content-between px-3 pt-2 pb-1">
            <div>
                <label class="small mb-0 mr-1">SHOW</label>
                <select id="perPage" class="form-control form-control-sm d-inline-block" style="width:70px;">
                    <option>10</option><option>25</option><option selected>100</option>
                </select>
                <label class="small mb-0 ml-1">ENTRIES</label>
            </div>
            <div>
                <label class="small mb-0 mr-1">SEARCH:</label>
                <input type="text" id="tableSearch" class="form-control form-control-sm d-inline-block" style="width:180px;">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0" id="ticketTable">
                <thead class="thead-dark">
                    <tr>
                        <th>TicketNo.</th>
                        <th>ClientCode</th>
                        <th>ID/IP</th>
                        <th>CustomerName</th>
                        <th>Mobile (Existing)</th>
                        <th>ComplainNo.</th>
                        <th>Zone</th>
                        <th>Subzone</th>
                        <th>Problem</th>
                        <th>Priority</th>
                        <th>Complain Time</th>
                        <th>CreatedBy</th>
                        <th>Status</th>
                        <th>Assign To</th>
                        <th>Solved Time</th>
                    </tr>
                </thead>
                <tbody id="ticketBody">
                    @forelse($tickets as $t)
                    @include('client_support._row', ['t' => $t])
                    @empty
                    <tr id="empty-row"><td colspan="15" class="text-center text-muted py-4">No tickets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── New/Edit Ticket Modal ───────────────────────────────── --}}
<div class="modal fade" id="ticketModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white" id="ticketModalTitle">New Support Ticket</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ticket_id">

                {{-- Customer Search — Select2 --}}
                <div class="form-group">
                    <label class="small font-weight-bold text-uppercase">User Name (ID)</label>
                    <select id="customerSelect2" class="form-control" style="width:100%;">
                        <option value="">— Type name or phone to search —</option>
                        @foreach(\App\Models\Customer::with('package')->get() as $c)
                            <option value="{{ $c->id }}"
                                data-name="{{ $c->name }}"
                                data-phone="{{ $c->phone }}"
                                data-address="{{ $c->address }}"
                                data-zone="{{ $c->zone->name ?? '' }}"
                                data-billing_status="{{ $c->billing_status ?? 'active' }}"
                                data-package="{{ $c->package->name ?? '—' }}"
                                data-package_price="{{ $c->package->price ?? 0 }}"
                                data-ip="{{ $c->ip_address }}"
                                data-mikrotik="{{ $c->mikrotik_status ?? '' }}"
                                data-mac="{{ $c->mac_address }}">
                                {{ $c->name }} — {{ $c->phone }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" id="ticket_customer_id_err"></div>
                </div>
                <input type="hidden" id="ticket_customer_id">

                {{-- Customer Info --}}
                <div id="customerInfo" class="d-none">
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="small text-uppercase">Customer Name</label>
                                <input type="text" id="ci_name" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="small text-uppercase">Mobile Number (Existing)</label>
                                <input type="text" id="ci_phone" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="small text-uppercase">Client Address</label>
                                <input type="text" id="ci_address" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="small text-uppercase">Zone</label>
                                <input type="text" id="ci_zone" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="small text-uppercase">Billing Status</label>
                                <input type="text" id="ci_billing_status" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="small text-uppercase">Package Name</label>
                                <input type="text" id="ci_package" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="small text-uppercase">Package Price</label>
                                <input type="text" id="ci_package_price" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="small text-uppercase">MikroTik Status</label>
                                <input type="text" id="ci_mikrotik" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="small text-uppercase">MAC Address/Caller ID</label>
                                <input type="text" id="ci_mac" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Ticket Fields --}}
                <div class="row">
                    <div class="col-md-4 col-6">
                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold">Problem Category <span class="text-danger">*</span></label>
                            <select id="ticket_category" class="form-control form-control-sm">
                                <option value="">Select</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="ticket_category_err"></div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold">Problem Priority <span class="text-danger">*</span></label>
                            <select id="ticket_priority" class="form-control form-control-sm">
                                <option value="">Select</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            <div class="invalid-feedback" id="ticket_priority_err"></div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold">Complained Number <span class="text-danger">*</span></label>
                            <input type="text" id="ticket_complained_no" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="ticket_complained_err"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold">Attachments</label>
                            <input type="file" id="ticket_attachment" class="form-control-file form-control-sm" multiple>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="custom-control custom-checkbox mt-3">
                            <input type="checkbox" class="custom-control-input" id="ticket_send_sms">
                            <label class="custom-control-label font-weight-bold" for="ticket_send_sms">Send SMS to Client?</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="small text-uppercase font-weight-bold">Remarks / Note / Comments <span class="text-danger">*</span></label>
                    <textarea id="ticket_remarks" class="form-control" rows="4"></textarea>
                    <div class="invalid-feedback" id="ticket_remarks_err"></div>
                </div>
                <div id="ticketMessage" class="alert alert-danger d-none" role="alert"></div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <div>
                    <button type="button" class="btn btn-danger" id="btnTicketClear">Clear</button>
                    <button type="button" class="btn btn-dark" id="btnTicketSave">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Quick Solve Modal ────────────────────────────────────── --}}
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
                            <input type="text" id="solve_connectivity" class="form-control form-control-sm" value="Disconnected" readonly>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end mb-3">
                        <button type="button" class="btn btn-danger btn-block" id="solve_online_badge">Offline</button>
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
                            <label class="small text-uppercase font-weight-bold">MAC Address/Caller ID</label>
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

{{-- ── Reassign Modal ───────────────────────────────────────── --}}
<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reassign or Update Solvers</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="reassign_ticket_id">
                <div class="form-group">
                    <label class="small text-uppercase font-weight-bold">Department</label>
                    <select id="reassign_dept" class="form-control">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="small text-uppercase font-weight-bold">Employee</label>
                    <select id="reassign_employees" class="form-control" multiple>
                        <option value="" disabled>Select department first</option>
                    </select>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="reassign_sms">
                    <label class="custom-control-label" for="reassign_sms">SMS</label>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-success" id="btnConfirmReassign">Yes</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const CSRF = '{{ csrf_token() }}';
let editMode = false;

// ── Select2 Customer Search ───────────────────────────────────────
$(function () {
    $('#customerSelect2').select2({
        width: '100%',
        placeholder: '— Type name or phone to search —',
        allowClear: true,
        dropdownParent: $('#ticketModal'),
    });

    $('#customerSelect2').on('change', function () {
        const opt = $(this).find(':selected');
        const id  = $(this).val();

        if (!id) {
            $('#customerInfo').addClass('d-none');
            $('#ticket_customer_id').val('');
            $('#ticket_complained_no').val('');
            return;
        }

        $('#ticket_customer_id').val(id);
        $('#ci_name').val(opt.data('name'));
        $('#ci_phone').val(opt.data('phone'));
        $('#ci_address').val(opt.data('address'));
        $('#ci_zone').val(opt.data('zone'));
        $('#ci_billing_status').val(opt.data('billing_status'));
        $('#ci_package').val(opt.data('package'));
        $('#ci_package_price').val(opt.data('package_price') + ' BDT');
        $('#ci_ip').val(opt.data('ip'));
        $('#ci_mikrotik').val(opt.data('mikrotik'));
        $('#ci_mac').val(opt.data('mac'));

        // Auto-fill complained number with phone
        $('#ticket_complained_no').val(opt.data('phone'));

        $('#customerInfo').removeClass('d-none');
    });
});

// ── New Ticket ───────────────────────────────────────────────────
$('#btnNewTicket').click(() => {
    editMode = false;
    $('#ticket_id').val('');
    $('#ticketModalTitle').text('New Support Ticket');
    clearTicketForm();
    $('#ticketModal').modal('show');
});

function showTicketMessage(message, type = 'danger') {
    $('#ticketMessage')
        .removeClass('d-none alert-danger alert-success alert-warning')
        .addClass(`alert-${type}`)
        .text(message);
}

function clearTicketMessage() {
    $('#ticketMessage').addClass('d-none').text('');
}

function clearTicketForm() {
    $('#customerSelect2').val('').trigger('change').select2('close').removeClass('is-invalid');
    $('#ticket_customer_id').val('');
    $('#customerInfo').addClass('d-none');
    $('#ticket_category').val('').removeClass('is-invalid');
    $('#ticket_priority').val('').removeClass('is-invalid');
    $('#ticket_complained_no').val('').removeClass('is-invalid');
    $('#ticket_remarks').val('').removeClass('is-invalid');
    $('#ticket_send_sms').prop('checked', false);
    $('#ticket_customer_id_err').text('');
    clearTicketMessage();
}

$('#btnTicketClear').click(clearTicketForm);

$('#ticketModal').on('hidden.bs.modal', function () {
    $('#customerSelect2').select2('close');
    clearTicketMessage();
});

$('#btnTicketSave').click(function () {
    const id   = $('#ticket_id').val();
    const url  = id ? `/client-support/${id}` : '{{ route("client-support.store") }}';
    const btn  = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...');

    const formData = new FormData();
    formData.append('_token', CSRF);
    if (id) formData.append('_method', 'PUT');
    formData.append('customer_id', $('#ticket_customer_id').val());
    formData.append('support_category_id', $('#ticket_category').val());
    formData.append('priority', $('#ticket_priority').val());
    formData.append('complained_no', $('#ticket_complained_no').val());
    formData.append('remarks', $('#ticket_remarks').val());
    formData.append('send_sms', $('#ticket_send_sms').is(':checked') ? 1 : 0);
    const file = $('#ticket_attachment')[0].files[0];
    if (file) formData.append('attachment', file);

    $.ajax({
        url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success(res) {
            if (res.success) {
                if (id) {
                    $(`#ticket-row-${id}`).replaceWith(buildRow(res.ticket));
                } else {
                    $('#empty-row').remove();
                    $('#ticketBody').prepend(buildRow(res.ticket));
                }
                $('#ticketModal').modal('hide');
                toastr.success(res.message || 'Ticket submitted successfully.');
                clearTicketForm();
                return;
            }

            toastr.error(res.message || 'Unable to save the ticket.');
        },
        error(xhr) {
            const body   = xhr.responseJSON ?? {};
            const errors = body.errors || {};

            clearTicketMessage();

            // Clear previous feedback
            ['category', 'priority', 'complained_no', 'remarks', 'customer_id'].forEach(f => {
                const inputId = f === 'category' ? '#ticket_category' : (f === 'customer_id' ? '#customerSelect2' : `#ticket_${f}`);
                const errId   = f === 'category' ? '#ticket_category_err' : (f === 'customer_id' ? '#ticket_customer_id_err' : `#ticket_${f}_err`);
                $(inputId).toggleClass('is-invalid', !!errors[f]);
                $(errId).text(errors[f]?.[0] ?? '');
            });

            if (Object.keys(errors).length > 0) {
                const firstError = Object.values(errors)[0][0] ?? 'Please fix the errors and try again.';
                showTicketMessage(firstError, 'warning');
                return;
            }

            if (body.message) {
                showTicketMessage(body.message, 'danger');
                return;
            }

            if (xhr.status === 419) {
                showTicketMessage('Session expired. Please refresh the page and try again.', 'danger');
                return;
            }

            showTicketMessage('An unexpected error occurred while submitting the ticket.', 'danger');
        },
        complete() {
            btn.prop('disabled', false).html('Submit');
        }
    });
});

// ── Edit ─────────────────────────────────────────────────────────
$(document).on('click', '.btn-ticket-edit', function () {
    const id = $(this).data('id');
    $.get(`/client-support/${id}/edit`, function (res) {
        if (res.success) {
            const t = res.ticket;
            editMode = true;
            $('#ticket_id').val(t.id);
            $('#ticketModalTitle').text('Edit Support Ticket');

            // Set Select2
            $('#customerSelect2').val(t.customer_id).trigger('change');
            $('#ticket_customer_id').val(t.customer_id);

            // Override fields from DB (more accurate than data attributes)
            $('#ci_name').val(t.customer.name);
            $('#ci_phone').val(t.customer.phone);
            $('#ci_address').val(t.customer.address ?? '');
            $('#ci_zone').val(t.customer.zone?.name ?? '');
            $('#ci_billing_status').val(t.customer.billing_status ?? '');
            $('#ci_package').val(t.customer.package?.name ?? '—');
            $('#ci_package_price').val((t.customer.package?.price ?? 0) + ' BDT');
            $('#ci_ip').val(t.customer.ip_address ?? '');
            $('#ci_mikrotik').val(t.customer.mikrotik_status ?? '');
            $('#ci_mac').val(t.customer.mac_address ?? '');
            $('#customerInfo').removeClass('d-none');

            $('#ticket_category').val(t.support_category_id);
            $('#ticket_priority').val(t.priority);
            $('#ticket_complained_no').val(t.complained_no);
            $('#ticket_remarks').val(t.remarks);
            $('#ticket_send_sms').prop('checked', t.send_sms);
            $('#ticketModal').modal('show');
        }
    });
});

// ── Delete ───────────────────────────────────────────────────────
$(document).on('click', '.btn-ticket-delete', function () {
    const id = $(this).data('id');
    Swal.fire({ title: 'Delete Ticket?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, Delete' })
        .then(r => {
            if (r.isConfirmed) {
                $.ajax({
                    url: `/client-support/${id}`, method: 'POST',
                    data: { _token: CSRF, _method: 'DELETE' },
                    success(res) {
                        if (res.success) { $(`#ticket-row-${id}`).remove(); toastr.success(res.message); }
                    }
                });
            }
        });
});

// ── Reset Filter ─────────────────────────────────────────────────
$('#btnResetFilter').click(function () {
    $('#f_category, #f_zone, #f_solved_by, #f_created_by, #f_status, #f_priority').val('');
    $('#f_from, #f_to, #f_complained').val('');
    loadTickets();
});

// ── Apply Filter ─────────────────────────────────────────────────
$('#btnApplyFilter').click(function () {
    loadTickets();
});

function loadTickets() {
    const params = {
        category_id:  $('#f_category').val(),
        zone_id:      $('#f_zone').val(),
        solved_by:    $('#f_solved_by').val(),
        created_by:   $('#f_created_by').val(),
        status:       $('#f_status').val(),
        priority:     $('#f_priority').val(),
        from_date:    $('#f_from').val(),
        to_date:      $('#f_to').val(),
        complained_no:$('#f_complained').val(),
    };

    $('#ticketBody').html('<tr><td colspan="15" class="text-center py-3"><i class="fas fa-spinner fa-spin mr-1"></i> Loading...</td></tr>');

    $.get('{{ route("client-support.index") }}', params, function (html) {
        const rows = $(html).find('#ticketBody').html();
        $('#ticketBody').html(rows ?? '<tr><td colspan="15" class="text-center text-muted py-4">No tickets found.</td></tr>');

        // Update summary cards
        $('#card_total').text($(html).find('#card_total').text());
        $('#card_pending').text($(html).find('#card_pending').text());
        $('#card_processing').text($(html).find('#card_processing').text());
        $('#card_solved').text($(html).find('#card_solved').text());
    }).fail(function () {
        toastr.error('Failed to load tickets.');
        $('#ticketBody').html('<tr><td colspan="15" class="text-center text-muted py-4">Failed to load.</td></tr>');
    });
}

// ── Quick Solve ──────────────────────────────────────────────────
$(document).on('click', '.btn-ticket-solve', function () {
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

    // Check mikrotik status via AJAX
    $.get(`/client-support/${id}/mikrotik-status`, function (res) {
        if (res.online) {
            $('#solve_connectivity').val('Connected');
            $('#solve_online_badge').text('Online').removeClass('btn-secondary btn-danger').addClass('btn-success');
            $('#solve_uptime').val(res.uptime ?? '—');
            $('#solve_logout_time').val(res.last_logout ?? '—');
        } else {
            $('#solve_connectivity').val('Disconnected');
            $('#solve_online_badge').text('Offline').removeClass('btn-secondary btn-success').addClass('btn-danger');
            $('#solve_uptime').val(res.uptime ?? '—');
            $('#solve_logout_time').val(res.last_logout ?? '—');
        }
    }).fail(function () {
        $('#solve_connectivity').val('Disconnected');
        $('#solve_online_badge').text('Offline').removeClass('btn-secondary btn-success').addClass('btn-danger');
        $('#solve_uptime').val('N/A');
        $('#solve_logout_time').val('N/A');
    });
});

$('#btnConfirmSolve').click(function () {
    // Check connection status before solving
    const isOnline = $('#solve_online_badge').hasClass('btn-success');
    if (!isOnline) {
        toastr.warning('Please, make sure the connection is Online before marking as solved!', 'Connection Offline', {
            timeOut: 4000,
            positionClass: 'toast-top-center',
        });
        return;
    }

    const id  = $('#solve_ticket_id').val();
    const btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    $.ajax({
        url: `/client-support/${id}/solve`, method: 'POST',
        data: { _token: CSRF },
        success(res) {
            if (res.success) {
                const row = $(`#ticket-row-${id}`);
                row.find('.status-badge').html('<span class="badge badge-success">Solved</span>');
                row.find('.duration-cell').html(`<small class="text-muted">Duration<br>${res.duration}</small>`);
                $('#solveModal').modal('hide');
                toastr.success(res.message);
            }
        },
        complete() { btn.prop('disabled', false).html('Yes, Solved'); }
    });
});

// ── Reassign ─────────────────────────────────────────────────────
$(document).on('click', '.btn-ticket-reassign', function () {
    $('#reassign_ticket_id').val($(this).data('id'));
    $('#reassign_dept').val('');
    $('#reassign_employees').html('<option value="" disabled>Select department first</option>');
    $('#reassignModal').modal('show');
});

$('#reassign_dept').change(function () {
    const deptId = $(this).val();
    if (!deptId) return;
    $.get(`/client-support/departments/${deptId}/employees`, function (res) {
        if (res.success) {
            let opts = '';
            res.employees.forEach(e => { opts += `<option value="${e.id}">${e.name}</option>`; });
            $('#reassign_employees').html(opts);
        }
    });
});

$('#btnConfirmReassign').click(function () {
    const id        = $('#reassign_ticket_id').val();
    const employees = $('#reassign_employees').val();
    if (!employees || employees.length === 0) { toastr.error('Please select at least one employee.'); return; }
    const btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    $.ajax({
        url: `/client-support/${id}/reassign`, method: 'POST',
        data: { _token: CSRF, employee_ids: employees },
        success(res) {
            if (res.success) {
                $(`#ticket-row-${id}`).find('.assign-cell').html(`<small>${res.names}</small>`);
                $(`#ticket-row-${id}`).find('.status-badge').html('<span class="badge badge-warning">Processing</span>');
                $('#reassignModal').modal('hide');
                toastr.success(res.message);
            }
        },
        complete() { btn.prop('disabled', false).html('Yes'); }
    });
});

// ── Build Row ─────────────────────────────────────────────────────
function buildRow(t) {
    const priorityColors = { urgent: 'danger', high: 'warning', medium: 'info', low: 'secondary' };
    const statusColors   = { pending: 'danger', processing: 'warning', solved: 'success', closed: 'secondary' };
    const pColor = priorityColors[t.priority] || 'secondary';
    const sColor = statusColors[t.status]     || 'secondary';
    const priorityLabel = t.priority ? t.priority.charAt(0).toUpperCase() + t.priority.slice(1) : '—';
    const statusLabel   = t.status ? t.status.charAt(0).toUpperCase() + t.status.slice(1) : '—';

    return `<tr id="ticket-row-${t.id}">
        <td><small>${t.ticket_no}</small></td>
        <td><small>${t.client_code}</small></td>
        <td><small>${t.pppoe_username}</small></td>
        <td><small>${t.customer_name}</small></td>
        <td><small>${t.mobile}</small></td>
        <td><small>${t.complained_no}</small></td>
        <td><small>${t.zone}</small></td>
        <td><small>${t.sub_zone}</small></td>
        <td><small>${t.category}</small></td>
        <td><span class="badge badge-${pColor}">${priorityLabel}</span></td>
        <td><small>${t.created_at}</small></td>
        <td><small>${t.created_by}</small></td>
        <td class="status-badge">
            ${t.status === 'processing'
                ? `<span class="badge badge-warning btn-ticket-solve" data-id="${t.id}" data-mac="${t.mac_address??''}" data-ip="${t.ip_address??''}" style="cursor:pointer;">Processing</span>`
                : `<span class="badge badge-${sColor}">${statusLabel}</span>`
            }
        </td>
        <td class="assign-cell"><small>${t.assignees.map(a=>a.name).join(', ') || '—'}</small></td>
        <td class="duration-cell">
            ${t.duration ? `<small class="text-muted">Duration<br>${t.duration}</small>` : ''}
            <div class="mt-1">
                <button class="btn btn-xs btn-warning ticket-action-btn btn-ticket-reassign" data-id="${t.id}">Re Assign</button><br>
                <div class="mt-1">
                    <button class="btn btn-xs btn-success btn-ticket-solve" data-id="${t.id}" data-mac="${t.mac_address??''}" data-ip="${t.ip_address??''}" title="Solve"><i class="fas fa-check"></i></button>
                    <button class="btn btn-xs btn-info btn-ticket-edit" data-id="${t.id}" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-xs btn-danger btn-ticket-delete" data-id="${t.id}" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </td>
    </tr>`;
}
</script>
@endpush
