{{-- resources/views/bandwidth-sale/customers/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Bandwidth Sale — Customers')

@section('page_actions')
    <button class="btn btn-primary btn-sm" id="btnAddCustomer">
        <i class="fas fa-plus mr-1"></i> + Customer
    </button>
@endsection

@section('page_content')

{{-- ══ STAT CARDS ═══════════════════════════════════════════════ --}}
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#0073b7,#005a8e);">
            <div><div class="bs-label">Total Customers</div><div class="bs-val">{{ $customers->total() }}</div></div>
            <i class="fas fa-users bs-icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#00a65a,#007a42);">
            <div><div class="bs-label">Active</div><div class="bs-val">{{ $customers->getCollection()->where('activity_status','active')->count() }}</div></div>
            <i class="fas fa-check-circle bs-icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#dc3545,#a71d2a);">
            <div><div class="bs-label">Inactive</div><div class="bs-val">{{ $customers->getCollection()->where('activity_status','inactive')->count() }}</div></div>
            <i class="fas fa-times-circle bs-icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#f39c12,#c07d0a);">
            <div><div class="bs-label">Total Balance Due</div><div class="bs-val">৳ {{ number_format($customers->getCollection()->sum('balance_due')) }}</div></div>
            <i class="fas fa-balance-scale bs-icon"></i>
        </div>
    </div>
</div>

{{-- ══ TABLE CARD ════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0 font-weight-bold">
            <i class="fas fa-users mr-1"></i> Customer List
        </h6>
        <div class="d-flex align-items-center">
            <input type="text" id="tableSearch" class="form-control form-control-sm mr-2"
                   placeholder="Search..." style="width:200px;" autocomplete="off">
            <select id="perPage" class="form-control form-control-sm" style="width:70px;">
                @foreach([10,25,50,100] as $pp)
                    <option value="{{ $pp }}" {{ request('per_page',10)==$pp?'selected':'' }}>{{ $pp }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="custTable">
                <thead style="background:#2c3e50;color:#fff;">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Customer</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Mobile Number</th>
                        <th class="text-right">Balance Due</th>
                        <th style="width:110px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $i => $c)
                    <tr data-search="{{ strtolower($c->customer_name.' '.$c->mobile_number.' '.($c->contact_person??'')) }}">
                        <td>{{ $customers->firstItem() + $i }}</td>
                        <td>
                            <a href="{{ route('bandwidth-sale.customers.show', $c->id) }}"
                               class="font-weight-bold text-primary">
                                {{ $c->customer_name }}
                            </a>
                            <br><small class="text-muted">{{ $c->customer_code }}</small>
                        </td>
                        <td>{{ $c->contact_person ?? '—' }}</td>
                        <td>{{ $c->email ?? '—' }}</td>
                        <td>{{ $c->mobile_number }}</td>
                        <td class="text-right {{ $c->balance_due > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                            ৳ {{ number_format($c->balance_due, 2) }}
                        </td>
                        <td class="text-center" style="white-space:nowrap;">
                            <button class="btn btn-xs btn-light border btn-view"
                                    data-id="{{ $c->id }}" title="View">
                                <i class="fas fa-eye text-info"></i>
                            </button>
                            <button class="btn btn-xs btn-light border btn-edit"
                                    data-id="{{ $c->id }}" title="Edit">
                                <i class="fas fa-edit text-success"></i>
                            </button>
                            <button class="btn btn-xs btn-light border btn-delete"
                                    data-id="{{ $c->id }}"
                                    data-name="{{ $c->customer_name }}"
                                    title="Delete">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-3x d-block mb-3 opacity-50"></i>
                            No customers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($customers->hasPages())
    <div class="card-footer py-2">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }}
            </small>
            {{ $customers->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>


{{-- ══════════════════════════════════════════════════════════════
     ADD / EDIT MODAL  —  single page, no tabs, section titles
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="customerModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog" style="max-width:55%;">
        <div class="modal-content">

            <div class="modal-header" id="modalHeader" style="background:#0073b7;color:#fff;">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fas fa-plus-circle mr-2"></i> Add New Customer
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" style="max-height:75vh; overflow-y:auto;">

                {{-- ══ SECTION 1: Customer Information ══════════ --}}
                <div class="section-title">
                    <i class="fas fa-user mr-1 text-primary"></i> Customer Information
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small font-weight-bold">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" id="f_customer_name" class="form-control form-control-sm"
                                   placeholder="Company / Customer name" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small font-weight-bold">Customer Code</label>
                            <input type="text" id="f_customer_code" class="form-control form-control-sm"
                                   placeholder="Auto generated" readonly style="background:#f8f9fa;" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small font-weight-bold">Contact Person</label>
                            <input type="text" id="f_contact_person" class="form-control form-control-sm"
                                   placeholder="Contact person name" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small font-weight-bold">Email</label>
                            <input type="email" id="f_email" class="form-control form-control-sm"
                                   placeholder="email@example.com" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" id="f_mobile_number" class="form-control form-control-sm"
                                   placeholder="01XXXXXXXXX" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">Phone Number</label>
                            <input type="text" id="f_phone_number" class="form-control form-control-sm"
                                   placeholder="Optional" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">POP Status <span class="text-danger">*</span></label>
                            <select id="f_pop_status" class="form-control form-control-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">Reference By</label>
                            <input type="text" id="f_reference_by" class="form-control form-control-sm"
                                   placeholder="Referred by" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="small font-weight-bold">Address</label>
                            <input type="text" id="f_address" class="form-control form-control-sm"
                                   placeholder="Full address" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">Facebook URL</label>
                            <input type="text" id="f_facebook_url" class="form-control form-control-sm"
                                   placeholder="https://facebook.com/..." autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">Skype ID</label>
                            <input type="text" id="f_skype_id" class="form-control form-control-sm"
                                   placeholder="skype.id" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">Website</label>
                            <input type="text" id="f_website" class="form-control form-control-sm"
                                   placeholder="https://..." autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="small font-weight-bold">Remarks / Notes</label>
                            <textarea id="f_remarks" class="form-control form-control-sm" rows="2"
                                      placeholder="Optional notes..." autocomplete="off"></textarea>
                        </div>
                    </div>
                </div>

                {{-- ══ SECTION 2: Transmission Information ══════ --}}
                <div class="section-title mt-2">
                    <i class="fas fa-network-wired mr-1 text-info"></i> Transmission Information
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="small font-weight-bold">ATTN Info</label>
                            <input type="text" id="f_attn_info" class="form-control form-control-sm"
                                   placeholder="ATTN information" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">BZR DR / NAS ID</label>
                            <input type="text" id="f_bzr_dr_nas_id" class="form-control form-control-sm"
                                   placeholder="NAS ID" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">Activation Date</label>
                            <input type="date" id="f_activation_date" class="form-control form-control-sm" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">POP Info</label>
                            <input type="text" id="f_pop_info" class="form-control form-control-sm"
                                   placeholder="POP AGRI ART BU PI" autocomplete="off">
                        </div>
                    </div>

                    {{-- VLAN --}}
                    <div class="col-md-12">
                        <label class="small font-weight-bold">VLAN Info</label>
                        <div id="vlan_rows"></div>
                        <button type="button" class="btn btn-xs btn-outline-info mt-1" id="btnAddVlan">
                            <i class="fas fa-plus mr-1"></i> Add VLAN
                        </button>
                    </div>

                    {{-- IP --}}
                    <div class="col-md-12 mt-2">
                        <label class="small font-weight-bold">IP Addresses</label>
                        <div id="ip_rows"></div>
                        <button type="button" class="btn btn-xs btn-outline-success mt-1" id="btnAddIp">
                            <i class="fas fa-plus mr-1"></i> Add IP
                        </button>
                    </div>
                </div>

                {{-- ══ SECTION 3: Login Information ═════════════ --}}
                <div class="section-title mt-2">
                    <i class="fas fa-key mr-1 text-warning"></i> Login Information
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">Username</label>
                            <input type="text" id="f_username" class="form-control form-control-sm"
                                   placeholder="Login username" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">
                                Password <small class="text-muted" id="pwHint"></small>
                            </label>
                            <input type="password" id="f_password" class="form-control form-control-sm"
                                   placeholder="••••••••" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">Activity Status</label>
                            <select id="f_activity_status" class="form-control form-control-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>{{-- end modal-body --}}

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveCustomer">
                    <i class="fas fa-save mr-1"></i> Save Customer
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     SHOW MODAL
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="showModal" tabindex="-1">
    <div class="modal-dialog" style="max-width:55%;">
        <div class="modal-content">
            <div class="modal-header" style="background:#0073b7;color:#fff;">
                <h5 class="modal-title">
                    <i class="fas fa-user mr-2"></i>
                    <span id="showName">Customer Detail</span>
                    <code class="ml-2 small" id="showCode"></code>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="max-height:75vh;overflow-y:auto;" id="showBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button type="button" class="btn btn-warning btn-sm" id="showEditBtn">
                    <i class="fas fa-edit mr-1"></i> Edit
                </button>
            </div>
        </div>
    </div>
</div>
<style>
.bws-stat { border-radius:8px; padding:14px 18px; color:#fff;
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:16px; box-shadow:0 3px 10px rgba(0,0,0,.15); }
.bs-label { font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.6px; color:rgba(255,255,255,.85); margin-bottom:4px; }
.bs-val   { font-size:26px; font-weight:700; line-height:1.1; }
.bws-stat .bs-icon { font-size:44px; color:rgba(255,255,255,.18); }

#custTable thead th { font-size:12px; font-weight:700; white-space:nowrap; padding:10px; }
#custTable tbody td { font-size:13px; padding:9px 10px; vertical-align:middle; }
#custTable tbody tr:hover { background:#f0f7ff; }

.section-title {
    font-size: 13px;
    font-weight: 700;
    color: #2c3e50;
    padding: 7px 12px;
    background: #f0f4f8;
    border-left: 4px solid #0073b7;
    border-radius: 4px;
    margin-bottom: 12px;
}

.callout { border-left:4px solid #17a2b8; padding:10px 14px; background:#f8f9fa; border-radius:4px; }
    background: #f8f9fa;
    border-radius: 5px;
    padding: 6px 10px;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
}
</style>
@endsection


@section('js')
<script>
const CSRF = '{{ csrf_token() }}';
let editId = null;

function toastOk(msg)  { Swal.fire({toast:true,position:'top-end',icon:'success',title:msg,showConfirmButton:false,timer:2500}); }
function toastErr(msg) { Swal.fire({toast:true,position:'top-end',icon:'error',  title:msg,showConfirmButton:false,timer:3500}); }

// ── Per page ──────────────────────────────────────────────────
$('#perPage').on('change', function () {
    var url = new URL(window.location.href);
    url.searchParams.set('per_page', $(this).val());
    window.location.href = url.toString();
});

// ── Live search ───────────────────────────────────────────────
$('#tableSearch').on('keyup', function () {
    var val = $(this).val().toLowerCase();
    $('#custTable tbody tr').each(function () {
        $(this).toggle($(this).data('search').includes(val));
    });
});

// ── Reset modal to ADD mode ───────────────────────────────────
function resetModal() {
    editId = null;
    $('#modalHeader').css('background','#0073b7');
    $('#modalTitle').html('<i class="fas fa-plus-circle mr-2"></i> Add New Customer');
    $('#btnSaveCustomer').html('<i class="fas fa-save mr-1"></i> Save Customer');
    $('#pwHint').text('');

    ['customer_name','customer_code','contact_person','email',
     'mobile_number','phone_number','reference_by','address',
     'facebook_url','skype_id','website','remarks',
     'attn_info','bzr_dr_nas_id','activation_date','pop_info',
     'username','password'
    ].forEach(k => $('#f_'+k).val(''));

    $('#f_pop_status').val('active');
    $('#f_activity_status').val('active');

    $('#vlan_rows').empty(); addVlanRow('','');
    $('#ip_rows').empty();   addIpRow('');
}

// ── OPEN ADD ──────────────────────────────────────────────────
$('#btnAddCustomer').on('click', function () {
    resetModal();
    $('#customerModal').modal('show');
});

// ── OPEN SHOW MODAL ───────────────────────────────────────────
var showCustomerId = null;
$(document).on('click', '.btn-view', function () {
    showCustomerId = $(this).data('id');
    $('#showName').text('Loading...');
    $('#showCode').text('');
    $('#showBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>');
    $('#showModal').modal('show');

    $.ajax({
        url: '/bandwidth-sale/customers/' + showCustomerId,
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function (res) {
            if (!res.success) { toastErr('Load failed.'); return; }
            var c = res.customer;
            $('#showName').text(c.customer_name);
            $('#showCode').text(c.customer_code);

            var vlans = (c.vlan_info || []).map((v,i) =>
                `<tr><td>${i+1}</td><td>${v.vlan_name||'—'}</td><td><code>${v.vlan_id||'—'}</code></td></tr>`
            ).join('') || '<tr><td colspan="3" class="text-muted">—</td></tr>';

            var ips = (c.ip_addresses || []).map(ip =>
                `<span class="badge badge-dark mr-1 mb-1 p-2" style="font-size:12px;">${ip}</span>`
            ).join('') || '—';

            $('#showBody').html(`
                <div class="section-title"><i class="fas fa-user mr-1 text-primary"></i> Customer Information</div>
                <div class="row" style="font-size:13px;">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted" style="width:45%">Customer Name</td><td><strong>${c.customer_name}</strong></td></tr>
                            <tr><td class="text-muted">Contact Person</td><td>${c.contact_person||'—'}</td></tr>
                            <tr><td class="text-muted">Email</td><td>${c.email||'—'}</td></tr>
                            <tr><td class="text-muted">Mobile</td><td>${c.mobile_number}</td></tr>
                            <tr><td class="text-muted">Phone</td><td>${c.phone_number||'—'}</td></tr>
                            <tr><td class="text-muted">POP Status</td><td>
                                <span class="badge badge-${c.pop_status==='active'?'success':'danger'}">${c.pop_status}</span>
                            </td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted" style="width:45%">Reference By</td><td>${c.reference_by||'—'}</td></tr>
                            <tr><td class="text-muted">Address</td><td>${c.address||'—'}</td></tr>
                            <tr><td class="text-muted">Facebook</td><td>${c.facebook_url ? '<a href="'+c.facebook_url+'" target="_blank">Link</a>' : '—'}</td></tr>
                            <tr><td class="text-muted">Skype</td><td>${c.skype_id||'—'}</td></tr>
                            <tr><td class="text-muted">Website</td><td>${c.website ? '<a href="'+c.website+'" target="_blank">'+c.website+'</a>' : '—'}</td></tr>
                            <tr><td class="text-muted">Status</td><td>
                                <span class="badge badge-${c.activity_status==='active'?'success':'secondary'}">${c.activity_status}</span>
                            </td></tr>
                        </table>
                    </div>
                    ${c.remarks ? `<div class="col-12"><div class="callout callout-info" style="font-size:12px;"><strong>Remarks:</strong> ${c.remarks}</div></div>` : ''}
                </div>

                <div class="section-title mt-2"><i class="fas fa-network-wired mr-1 text-info"></i> Transmission Information</div>
                <div class="row" style="font-size:13px;">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted" style="width:45%">ATTN Info</td><td>${c.attn_info||'—'}</td></tr>
                            <tr><td class="text-muted">BZR DR / NAS ID</td><td>${c.bzr_dr_nas_id||'—'}</td></tr>
                            <tr><td class="text-muted">Activation Date</td><td>${c.activation_date||'—'}</td></tr>
                            <tr><td class="text-muted">POP Info</td><td>${c.pop_info||'—'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2"><strong class="small">VLAN Info</strong>
                            <table class="table table-sm table-bordered mt-1">
                                <thead class="thead-dark"><tr><th>#</th><th>VLAN Name</th><th>VLAN ID</th></tr></thead>
                                <tbody>${vlans}</tbody>
                            </table>
                        </div>
                        <div><strong class="small">IP Addresses</strong><div class="mt-1">${ips}</div></div>
                    </div>
                </div>

                <div class="section-title mt-2"><i class="fas fa-key mr-1 text-warning"></i> Login Information</div>
                <div class="row" style="font-size:13px;">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted" style="width:45%">Username</td><td><code>${c.username||'—'}</code></td></tr>
                            <tr><td class="text-muted">Activity Status</td><td>
                                <span class="badge badge-${c.activity_status==='active'?'success':'secondary'}">${c.activity_status}</span>
                            </td></tr>
                        </table>
                    </div>
                </div>
            `);
        },
        error: () => toastErr('Failed to load.')
    });
});

// Show modal → Edit button
$('#showEditBtn').on('click', function () {
    $('#showModal').modal('hide');
    setTimeout(() => {
        $('.btn-edit[data-id="' + showCustomerId + '"]').trigger('click');
    }, 400);
});

// ── OPEN EDIT ─────────────────────────────────────────────────
$(document).on('click', '.btn-edit', function () {
    editId = $(this).data('id');
    $('#modalHeader').css('background','#f39c12');
    $('#modalTitle').html('<i class="fas fa-edit mr-2"></i> Edit Customer');
    $('#btnSaveCustomer').html('<i class="fas fa-save mr-1"></i> Update Customer');
    $('#pwHint').text('(blank = no change)');

    $.ajax({
        url: '/bandwidth-sale/customers/' + editId,
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function (res) {
        if (!res.success) { toastErr('Load failed.'); return; }
        var c = res.customer;

        $('#f_customer_name').val(c.customer_name);
        $('#f_customer_code').val(c.customer_code);
        $('#f_contact_person').val(c.contact_person || '');
        $('#f_email').val(c.email || '');
        $('#f_mobile_number').val(c.mobile_number);
        $('#f_phone_number').val(c.phone_number || '');
        $('#f_pop_status').val(c.pop_status);
        $('#f_reference_by').val(c.reference_by || '');
        $('#f_address').val(c.address || '');
        $('#f_facebook_url').val(c.facebook_url || '');
        $('#f_skype_id').val(c.skype_id || '');
        $('#f_website').val(c.website || '');
        $('#f_remarks').val(c.remarks || '');
        $('#f_attn_info').val(c.attn_info || '');
        $('#f_bzr_dr_nas_id').val(c.bzr_dr_nas_id || '');
        $('#f_activation_date').val(c.activation_date || '');
        $('#f_pop_info').val(c.pop_info || '');
        $('#f_username').val(c.username || '');
        $('#f_password').val('');
        $('#f_activity_status').val(c.activity_status);

        $('#vlan_rows').empty();
        var vlans = c.vlan_info || [];
        if (!vlans.length) vlans = [{vlan_name:'',vlan_id:''}];
        vlans.forEach(v => addVlanRow(v.vlan_name, v.vlan_id));

        $('#ip_rows').empty();
        var ips = c.ip_addresses || [];
        if (!ips.length) ips = [''];
        ips.forEach(ip => addIpRow(ip));

        $('#customerModal').modal('show');
        },
        error: () => toastErr('Failed to load.')
    });
});

// ── VLAN helpers ──────────────────────────────────────────────
function addVlanRow(name, id) {
    $('#vlan_rows').append(`
        <div class="vlan-row">
            <input type="text" class="form-control form-control-sm mr-2 vlan-name"
                   placeholder="VLAN Name" value="${name||''}" style="max-width:180px;" autocomplete="off">
            <input type="text" class="form-control form-control-sm mr-2 vlan-id"
                   placeholder="VLAN ID" value="${id||''}" style="max-width:120px;" autocomplete="off">
            <button type="button" class="btn btn-xs btn-danger"
                    onclick="$(this).closest('.vlan-row').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>`);
}
$('#btnAddVlan').on('click', () => addVlanRow('',''));

// ── IP helpers ────────────────────────────────────────────────
function addIpRow(ip) {
    $('#ip_rows').append(`
        <div class="ip-row">
            <input type="text" class="form-control form-control-sm mr-2 ip-value"
                   placeholder="e.g. 192.168.1.1" value="${ip||''}" style="max-width:220px;" autocomplete="off">
            <button type="button" class="btn btn-xs btn-danger"
                    onclick="$(this).closest('.ip-row').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>`);
}
$('#btnAddIp').on('click', () => addIpRow(''));

// ── SAVE ──────────────────────────────────────────────────────
$('#btnSaveCustomer').on('click', function () {
    var name   = $('#f_customer_name').val().trim();
    var mobile = $('#f_mobile_number').val().trim();

    if (!name)   { toastErr('Customer Name is required.'); return; }
    if (!mobile) { toastErr('Mobile Number is required.'); return; }

    var vlans = [];
    $('#vlan_rows .vlan-row').each(function () {
        var n = $(this).find('.vlan-name').val().trim();
        var i = $(this).find('.vlan-id').val().trim();
        if (n || i) vlans.push({vlan_name:n, vlan_id:i});
    });
    var ips = [];
    $('#ip_rows .ip-row').each(function () {
        var v = $(this).find('.ip-value').val().trim();
        if (v) ips.push(v);
    });

    var payload = {
        _token:          CSRF,
        customer_name:   name,
        contact_person:  $('#f_contact_person').val().trim(),
        email:           $('#f_email').val().trim(),
        mobile_number:   mobile,
        phone_number:    $('#f_phone_number').val().trim(),
        pop_status:      $('#f_pop_status').val(),
        reference_by:    $('#f_reference_by').val().trim(),
        address:         $('#f_address').val().trim(),
        facebook_url:    $('#f_facebook_url').val().trim(),
        skype_id:        $('#f_skype_id').val().trim(),
        website:         $('#f_website').val().trim(),
        remarks:         $('#f_remarks').val().trim(),
        attn_info:       $('#f_attn_info').val().trim(),
        bzr_dr_nas_id:   $('#f_bzr_dr_nas_id').val().trim(),
        activation_date: $('#f_activation_date').val(),
        pop_info:        $('#f_pop_info').val().trim(),
        vlan_info:       JSON.stringify(vlans),
        ip_addresses:    JSON.stringify(ips),
        username:        $('#f_username').val().trim(),
        password:        $('#f_password').val(),
        activity_status: $('#f_activity_status').val(),
    };

    if (editId) payload['_method'] = 'PUT';

    var url = editId
        ? '/bandwidth-sale/customers/' + editId
        : '/bandwidth-sale/customers';

    var $btn = $(this).prop('disabled', true)
                      .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    $.ajax({
        url: url, method: 'POST', data: payload,
        success: function (res) {
            if (res.success) {
                $('#customerModal').modal('hide');
                toastOk(res.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                toastErr(res.message || 'Failed.');
            }
        },
        error: function (xhr) {
            var errors = xhr.responseJSON?.errors;
            toastErr(errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message || 'Error.'));
        },
        complete: function () {
            $btn.prop('disabled', false)
                .html(editId
                    ? '<i class="fas fa-save mr-1"></i> Update Customer'
                    : '<i class="fas fa-save mr-1"></i> Save Customer');
        }
    });
});

// ── DELETE ────────────────────────────────────────────────────
$(document).on('click', '.btn-delete', function () {
    var id   = $(this).data('id');
    var name = $(this).data('name');
    Swal.fire({
        title: 'Delete Customer?',
        html: `<strong>${name}</strong> delete হবে।<br>
               <small class="text-danger">Invoice থাকলে delete হবে না।</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor:  '#6c757d',
        confirmButtonText:  'Delete',
        cancelButtonText:   'Cancel',
        reverseButtons: true,
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({
            url:    '/bandwidth-sale/customers/' + id,
            method: 'POST',
            data:   { _token: CSRF, _method: 'DELETE' },
            success: function (res) {
                if (res.success) {
                    toastOk(res.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastErr(res.message);
                }
            },
            error: xhr => toastErr(xhr.responseJSON?.message || 'Delete failed.')
        });
    });
});
</script>
@endsection
