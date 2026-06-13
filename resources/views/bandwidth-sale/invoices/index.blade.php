{{-- resources/views/bandwidth-sale/invoices/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Bandwidth Sale — Invoices')

@section('page_actions')
    <button class="btn btn-primary btn-sm" id="btnAddInvoice">
        <i class="fas fa-plus mr-1"></i> Create Invoice
    </button>
@endsection

@section('page_content')

{{-- ══ STAT CARDS ═══════════════════════════════════════════════ --}}
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#0073b7,#005a8e);">
            <div><div class="bs-label">Total Invoices</div><div class="bs-val">{{ $stats['total'] ?? 0 }}</div></div>
            <i class="fas fa-file-invoice bs-icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#00a65a,#007a42);">
            <div><div class="bs-label">Paid</div><div class="bs-val">{{ $stats['paid'] ?? 0 }}</div></div>
            <i class="fas fa-check-circle bs-icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#f39c12,#c07d0a);">
            <div><div class="bs-label">Unpaid / Due</div><div class="bs-val">{{ $stats['unpaid'] ?? 0 }}</div></div>
            <i class="fas fa-clock bs-icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bws-stat" style="background:linear-gradient(135deg,#6f42c1,#4e2d8a);">
            <div><div class="bs-label">Total Received</div><div class="bs-val">৳ {{ number_format($stats['received'] ?? 0) }}</div></div>
            <i class="fas fa-money-bill-wave bs-icon"></i>
        </div>
    </div>
</div>

{{-- ══ FILTER ════════════════════════════════════════════════════ --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-header py-2" id="filterToggle" style="cursor:pointer;">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 font-weight-bold">
                <i class="fas fa-filter mr-1 text-info"></i> Filter & Search
            </h6>
            <i class="fas fa-chevron-up text-muted" id="filterChevron"></i>
        </div>
    </div>
    <div class="card-body pt-3 pb-2" id="filterBody">
        <form method="GET" action="{{ route('bandwidth-sale.invoices.index') }}">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">From Month</label>
                        <input type="month" name="from_month" class="form-control form-control-sm"
                               value="{{ request('from_month', now()->format('Y-m')) }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">To Month</label>
                        <input type="month" name="to_month" class="form-control form-control-sm"
                               value="{{ request('to_month', now()->format('Y-m')) }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Payment Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            @foreach(['paid'=>'Paid','unpaid'=>'Unpaid','partial'=>'Partial','overdue'=>'Overdue'] as $v=>$l)
                                <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Customer</label>
                        <select name="customer_id" class="form-control form-control-sm select2">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ request('customer_id')==$c->id?'selected':'' }}>
                                    {{ $c->customer_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Created By</label>
                        <select name="created_by" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($employees ?? [] as $emp)
                                <option value="{{ $emp->user_id }}"
                                    {{ request('created_by') == $emp->user_id ? 'selected':'' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-1">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('bandwidth-sale.invoices.index') }}" class="btn btn-sm btn-secondary ml-1">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ══ TABLE ═════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0 font-weight-bold">
            <i class="fas fa-list mr-1"></i> Invoice List
            <span class="badge badge-info ml-1">{{ $invoices->total() }}</span>
        </h6>
        <div class="d-flex align-items-center">
            <input type="text" id="tableSearch" class="form-control form-control-sm mr-2"
                   placeholder="Search..." style="width:180px;" autocomplete="off">
            <select id="perPage" class="form-control form-control-sm" style="width:70px;">
                @foreach([20,50,100] as $pp)
                    <option value="{{ $pp }}" {{ request('per_page',20)==$pp?'selected':'' }}>{{ $pp }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="invTable">
                <thead style="background:#2c3e50;color:#fff;">
                    <tr>
                        <th style="width:30px;"></th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Bill No</th>
                        <th>Month</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Received</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Due</th>
                        <th>Created By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="width:120px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    @php
                        $sc = match($inv->status) {
                            'paid'    => ['bg'=>'#00a65a','label'=>'Paid'],
                            'partial' => ['bg'=>'#17a2b8','label'=>'Partial'],
                            'overdue' => ['bg'=>'#dc3545','label'=>'Due'],
                            default   => ['bg'=>'#f39c12','label'=>'Unpaid'],
                        };
                    @endphp
                    <tr>
                        <td></td>
                        <td>
                            <a href="#" class="font-weight-bold text-primary btn-view-inv"
                               data-id="{{ $inv->id }}">
                                {{ $inv->bwsCustomer->customer_name ?? '—' }}
                            </a>
                        </td>
                        <td class="text-muted" style="font-size:12px;">
                            {{ $inv->bwsCustomer->contact_person ?? '—' }}
                        </td>
                        <td>
                            <a href="#" class="text-info btn-view-inv" data-id="{{ $inv->id }}">
                                <code>{{ $inv->invoice_no }}</code>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-light border" style="font-size:12px;">
                                {{ \Carbon\Carbon::parse($inv->billing_month.'-01')->format('M-y') }}
                            </span>
                        </td>
                        <td class="text-right font-weight-bold">{{ number_format($inv->total_amount,2) }}</td>
                        <td class="text-right text-success">{{ number_format($inv->received_amount,2) }}</td>
                        <td class="text-right text-warning">{{ number_format($inv->discount,2) }}</td>
                        <td class="text-right {{ $inv->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                            {{ number_format($inv->due_amount,2) }}
                        </td>
                        <td class="text-muted" style="font-size:12px;">{{ $inv->createdBy->name ?? '—' }}</td>
                        <td class="text-muted" style="font-size:12px;white-space:nowrap;">
                            {{ optional($inv->created_at)->format('d/m/Y') }}
                        </td>
                        <td>
                            @if($inv->due_amount > 0 && $inv->status !== 'paid')
                                <button class="btn btn-xs btn-outline-primary btn-pay"
                                        data-id="{{ $inv->id }}"
                                        style="font-size:11px;padding:2px 6px;">
                                    Pay
                                </button>
                            @endif
                            <span class="badge px-2 py-1"
                                  style="background:{{ $sc['bg'] }};color:#fff;border-radius:20px;font-size:11px;">
                                {{ $sc['label'] }}
                            </span>
                        </td>
                        <td class="text-center" style="white-space:nowrap;">
                            {{-- View --}}
                            <button class="btn btn-xs btn-light border btn-view-inv"
                                    data-id="{{ $inv->id }}" title="View">
                                <i class="fas fa-eye text-info"></i>
                            </button>
                            {{-- Edit --}}
                            @if($inv->status !== 'paid')
                            <button class="btn btn-xs btn-light border btn-edit-inv"
                                    data-id="{{ $inv->id }}" title="Edit">
                                <i class="fas fa-edit text-success"></i>
                            </button>
                            @endif
                            {{-- PDF --}}
                            <a href="{{ route('bandwidth-sale.invoices.pdf', $inv->id) }}"
                               class="btn btn-xs btn-light border" title="PDF" target="_blank">
                                <i class="fas fa-file-pdf text-danger"></i>
                            </a>
                            {{-- Delete --}}
                            @if($inv->status !== 'paid')
                            <button class="btn btn-xs btn-light border btn-del-inv"
                                    data-id="{{ $inv->id }}"
                                    data-no="{{ $inv->invoice_no }}"
                                    title="Delete">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center py-5 text-muted">
                            <i class="fas fa-file-invoice fa-3x d-block mb-3 opacity-50"></i>
                            No invoices found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($invoices->hasPages())
    <div class="card-footer py-2">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }}
            </small>
            {{ $invoices->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>


{{-- ══════════════════════════════════════════════════════════════
     VIEW MODAL
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background:#0073b7;color:#fff;">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Invoice — <span id="vm_no"></span>
                    <span id="vm_status_badge" class="ml-2"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="vm_body" style="max-height:80vh;overflow-y:auto;">
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button class="btn btn-warning btn-sm" id="vm_editBtn" style="display:none;">
                    <i class="fas fa-edit mr-1"></i> Edit
                </button>
                <button class="btn btn-primary btn-sm" id="vm_payBtn" style="display:none;">
                    <i class="fas fa-hand-holding-usd mr-1"></i> Receive Payment
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════
     ADD / EDIT INVOICE MODAL
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="invoiceModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" id="inv_modal_header" style="background:#0073b7;color:#fff;">
                <h5 class="modal-title" id="inv_modal_title">
                    <i class="fas fa-plus-circle mr-2"></i> Create Invoice
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pb-1" style="max-height:82vh;overflow-y:auto;">

                {{-- ── Invoice Details ─────────────────────────── --}}
                <div class="section-title">
                    <i class="fas fa-file-invoice mr-1 text-primary"></i> Invoice Details
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small font-weight-bold">Customer <span class="text-danger">*</span></label>
                            <select id="inv_customer" class="form-control form-control-sm select2-modal" required>
                                <option value="">— Select Customer —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                            data-contact="{{ $c->contact_person }}"
                                            data-mobile="{{ $c->mobile_number }}">
                                        {{ $c->customer_name }}
                                        @if($c->customer_code)({{ $c->customer_code }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <div id="inv_cust_info" class="mt-1 text-muted small d-none"></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="small font-weight-bold">Invoice No</label>
                            <input type="text" id="inv_no" class="form-control form-control-sm"
                                   readonly style="background:#f8f9fa;font-weight:bold;color:#0073b7;"
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="small font-weight-bold">Billing Month <span class="text-danger">*</span></label>
                            <input type="month" id="inv_month" class="form-control form-control-sm"
                                   value="{{ now()->format('Y-m') }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="small font-weight-bold">Payment Due Date</label>
                            <input type="date" id="inv_due" class="form-control form-control-sm"
                                   value="{{ now()->endOfMonth()->format('Y-m-d') }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="small font-weight-bold">Status</label>
                            <select id="inv_status" class="form-control form-control-sm">
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="partial">Partial</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input"
                                   id="inv_daily" value="1" checked>
                            <label class="custom-control-label small font-weight-bold" for="inv_daily">
                                Daily Basis Calculation
                            </label>
                        </div>
                    </div>
                </div>

                {{-- ── Invoice Items (Full Width) ───────────────── --}}
                <div class="section-title mt-1">
                    <i class="fas fa-list mr-1 text-secondary"></i> Invoice Items
                    <button type="button" class="btn btn-xs btn-outline-primary float-right"
                            id="btnAddItem">
                        <i class="fas fa-plus mr-1"></i> Add Row
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" id="itemsTable" style="width:100%;">
                        <thead style="background:#2c3e50;color:#fff;">
                            <tr>
                                <th style="min-width:160px;">Item / Service</th>
                                <th style="min-width:150px;">Description</th>
                                <th style="width:65px;min-width:60px;">Unit</th>
                                <th style="width:75px;min-width:65px;">Qty</th>
                                <th style="width:90px;min-width:75px;">Rate</th>
                                <th style="width:70px;min-width:60px;">VAT%</th>
                                <th class="daily-col" style="width:110px;">From</th>
                                <th class="daily-col" style="width:110px;">To</th>
                                <th style="width:100px;">Total</th>
                                <th style="width:36px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                        <tfoot>
                            <tr style="background:#f8f9fa;">
                                <td colspan="8" class="text-right font-weight-bold pr-3"
                                    id="totalLabel">Invoice Total</td>
                                <td class="text-right font-weight-bold text-primary pr-2"
                                    id="invTotal" style="font-size:14px;">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- ── Notes + Summary (side by side) ─────────────── --}}
                <div class="row mt-3">
                    <div class="col-md-7">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Remarks / Notes</label>
                            <textarea id="inv_notes" class="form-control form-control-sm" rows="3"
                                      placeholder="Optional..." autocomplete="off"></textarea>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card card-outline card-info mb-0">
                            <div class="card-header py-2">
                                <h6 class="mb-0 font-weight-bold small">
                                    <i class="fas fa-calculator mr-1 text-info"></i> Summary
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-borderless mb-0" style="font-size:13px;">
                                    <tr>
                                        <td class="text-muted pl-3">Sub Total</td>
                                        <td class="text-right pr-3 font-weight-bold" id="sumSub">৳ 0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-3">VAT</td>
                                        <td class="text-right pr-3" id="sumVat">৳ 0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-3">Discount</td>
                                        <td class="text-right pr-3">
                                            <div class="input-group input-group-sm justify-content-end">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text py-0">৳</span>
                                                </div>
                                                <input type="number" id="inv_discount"
                                                       class="form-control form-control-sm text-right"
                                                       style="max-width:100px;"
                                                       value="0" min="0" step="0.01"
                                                       oninput="recalcAll()" autocomplete="off">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr style="border-top:2px solid #dee2e6;">
                                        <td class="font-weight-bold pl-3" style="font-size:14px;">Grand Total</td>
                                        <td class="text-right pr-3 font-weight-bold text-primary"
                                            id="sumGrand" style="font-size:15px;">৳ 0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-3 small">Received</td>
                                        <td class="text-right pr-3">
                                            <div class="input-group input-group-sm justify-content-end">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text py-0">৳</span>
                                                </div>
                                                <input type="number" id="inv_received"
                                                       class="form-control form-control-sm text-right"
                                                       style="max-width:100px;"
                                                       value="0" min="0" step="0.01"
                                                       oninput="recalcAll()" autocomplete="off">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-3 small">Due</td>
                                        <td class="text-right pr-3 text-danger font-weight-bold"
                                            id="sumDue">৳ 0.00</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- end modal-body --}}

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveInvoice">
                    <i class="fas fa-save mr-1"></i> Save Invoice
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════
     RECEIVE PAYMENT MODAL
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="payModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#2c3e50;color:#fff;">
                <h5 class="modal-title">
                    <i class="fas fa-hand-holding-usd mr-2"></i> Receive Payment
                    <small class="ml-2 text-info" id="pay_inv_no"></small>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Received Date <span class="text-danger">*</span></label>
                        <input type="date" id="pay_date" class="form-control form-control-sm"
                               value="{{ now()->format('Y-m-d') }}" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Received From</label>
                        <input type="text" id="pay_from" class="form-control form-control-sm"
                               placeholder="Person name" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Received By</label>
                        <select id="pay_by" class="form-control form-control-sm">
                            <option value="">Select</option>
                            @foreach($employees ?? [] as $emp)
                                <option value="{{ $emp->user_id }}"
                                    {{ $emp->user_id == auth()->id() ? 'selected':'' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Payment Method <span class="text-danger">*</span></label>
                        <select id="pay_method" class="form-control form-control-sm">
                            <option value="cash">Cash</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                            <option value="bank">Bank</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                <table class="table table-sm table-bordered mt-2" style="font-size:13px;">
                    <thead style="background:#2c3e50;color:#fff;">
                        <tr><th>Details</th><th class="text-right">Amount</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Grand Total</td>
                            <td class="text-right font-weight-bold" id="pay_grand">0.00</td>
                        </tr>
                        <tr>
                            <td>Previously Paid</td>
                            <td class="text-right text-success" id="pay_prev">0.00</td>
                        </tr>
                        <tr class="table-warning">
                            <td class="font-weight-bold">Balance Due</td>
                            <td class="text-right font-weight-bold" id="pay_due">0.00</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Received Amount <span class="text-danger">*</span></td>
                            <td>
                                <input type="number" id="pay_amount"
                                       class="form-control form-control-sm text-right"
                                       value="0" min="0.01" step="0.01" autocomplete="off">
                            </td>
                        </tr>
                        <tr>
                            <td>Discount</td>
                            <td>
                                <input type="number" id="pay_discount"
                                       class="form-control form-control-sm text-right"
                                       value="0" min="0" step="0.01" autocomplete="off">
                            </td>
                        </tr>
                        <tr>
                            <td>Receipt / Transaction No.</td>
                            <td>
                                <input type="text" id="pay_txn" class="form-control form-control-sm"
                                       placeholder="Optional" autocomplete="off">
                            </td>
                        </tr>
                        <tr>
                            <td>Remarks</td>
                            <td>
                                <input type="text" id="pay_remarks" class="form-control form-control-sm"
                                       placeholder="Optional" autocomplete="off">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="alert alert-info py-2 mb-0 mt-2" style="font-size:12px;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Payment save হলে <strong>Accounting → Income</strong> এ automatically
                    <strong>"Bandwidth Sale"</strong> category তে record তৈরি হবে।
                </div>
            </div>
            <div class="modal-footer py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSubmitPay">
                    <i class="fas fa-save mr-1"></i> Submit Payment
                </button>
            </div>
        </div>
    </div>
</div>

@endsection


@section('extra_css')
<style>
.bws-stat { border-radius:8px; padding:14px 18px; color:#fff;
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:16px; box-shadow:0 3px 10px rgba(0,0,0,.15); }
.bs-label { font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.6px; color:rgba(255,255,255,.85); margin-bottom:4px; }
.bs-val   { font-size:26px; font-weight:700; line-height:1.1; }
.bws-stat .bs-icon { font-size:44px; color:rgba(255,255,255,.18); }

#invTable thead th { font-size:11px; font-weight:700; white-space:nowrap; padding:9px 8px; }
#invTable tbody td { font-size:13px; padding:8px; vertical-align:middle; }
#invTable tbody tr:hover { background:#f0f7ff; }

#itemsTable thead th { font-size:11px; font-weight:700; white-space:nowrap; padding:8px 6px; }
#itemsTable tbody td { padding:4px 5px; vertical-align:middle; }

.section-title { font-size:13px; font-weight:700; color:#2c3e50;
    padding:7px 12px; background:#f0f4f8; border-left:4px solid #0073b7;
    border-radius:4px; margin-bottom:10px; }
</style>
@endsection


@section('js')
<script>
const CSRF = '{{ csrf_token() }}';
let editInvId = null;
let payInvId  = null;
let rowIdx    = 0;

// Bandwidth services from DB
const BWS_SERVICES = @json($bwsServices ?? []);

function toastOk(msg)  { Swal.fire({toast:true,position:'top-end',icon:'success',title:msg,showConfirmButton:false,timer:2500}); }
function toastErr(msg) { Swal.fire({toast:true,position:'top-end',icon:'error',  title:msg,showConfirmButton:false,timer:3500}); }

// On service dropdown change — fill unit
function onServiceChange(i) {
    var opt = $(`#irow-${i} .item-service option:selected`);
    var unit = opt.data('unit') || '';
    $(`#irow-${i} .item-unit`).val(unit);
    recalcRow(i);
}

// ── init ──────────────────────────────────────────────────────
$(function () {
    fetchNextNo();
    addItemRow();
    toggleDailyCols();

    $('.select2').select2({ width:'100%' });

    $('#inv_daily').on('change', toggleDailyCols);
    $('#inv_customer').on('change', function () {
        var opt = $(this).find(':selected');
        if ($(this).val()) {
            $('#inv_cust_info').removeClass('d-none')
                .text((opt.data('contact')||'') + (opt.data('contact')?' — ':'') + (opt.data('mobile')||''));
        } else {
            $('#inv_cust_info').addClass('d-none');
        }
    });

    $('#filterToggle').on('click', function () {
        $('#filterBody').slideToggle(200);
        $('#filterChevron').toggleClass('fa-chevron-up fa-chevron-down');
    });

    $('#perPage').on('change', function () {
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', $(this).val());
        window.location.href = url.toString();
    });

    $('#tableSearch').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#invTable tbody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(val));
        });
    });
});

// ── fetch next invoice no ─────────────────────────────────────
function fetchNextNo() {
    $.get('/bandwidth-sale/invoices/next-no', function (r) {
        if (r.invoice_no) $('#inv_no').val(r.invoice_no);
    });
}

// ── toggle daily cols ─────────────────────────────────────────
function toggleDailyCols() {
    var on = $('#inv_daily').is(':checked');
    $('.daily-col').toggle(on);
    $('#totalLabel').attr('colspan', on ? 8 : 6);
}

// ── add item row ──────────────────────────────────────────────
function addItemRow(data) {
    data = data || {};
    var i = rowIdx++;

    // Build service options
    var serviceOpts = '<option value="">— Select Service —</option>';
    BWS_SERVICES.forEach(function(s) {
        var sel = (data.item_name && data.item_name == s.id) ? 'selected' : '';
        serviceOpts += `<option value="${s.id}" data-unit="${s.unit||''}" data-rate="${s.rate||0}" ${sel}>${s.name}</option>`;
    });
    // If existing item_name is text (not id), add it as a custom option
    if (data.item_name && isNaN(data.item_name)) {
        serviceOpts += `<option value="${data.item_name}" selected>${data.item_name}</option>`;
    }

    var row = `
    <tr id="irow-${i}">
        <td>
            <select class="form-control form-control-sm item-service"
                    onchange="onServiceChange(${i})" style="min-width:150px;">
                ${serviceOpts}
            </select>
        </td>
        <td><input type="text" class="form-control form-control-sm item-desc"
                   value="${data.description||''}" placeholder="Description" autocomplete="off"></td>
        <td><input type="text" class="form-control form-control-sm item-unit"
                   value="${data.unit||''}" placeholder="Unit" autocomplete="off"
                   style="min-width:55px;"></td>
        <td><input type="number" class="form-control form-control-sm item-qty text-right"
                   value="${data.quantity||1}" min="0" step="any"
                   oninput="recalcRow(${i})" autocomplete="off"
                   style="min-width:60px;"></td>
        <td><input type="number" class="form-control form-control-sm item-rate text-right"
                   value="${data.rate||0}" min="0" step="any"
                   oninput="recalcRow(${i})" autocomplete="off"
                   style="min-width:70px;"></td>
        <td><input type="number" class="form-control form-control-sm item-vat text-right"
                   value="${data.vat_percent||0}" min="0" max="100" step="any"
                   oninput="recalcRow(${i})" autocomplete="off"
                   style="min-width:55px;"></td>
        <td class="daily-col"><input type="date" class="form-control form-control-sm item-from"
                   value="${data.from_date||''}" oninput="recalcRow(${i})"></td>
        <td class="daily-col"><input type="date" class="form-control form-control-sm item-to"
                   value="${data.to_date||''}" oninput="recalcRow(${i})"></td>
        <td><input type="text" class="form-control form-control-sm item-total text-right font-weight-bold"
                   value="${parseFloat(data.total||0).toFixed(2)}" readonly
                   style="background:#f8f9fa;color:#0073b7;min-width:80px;"></td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-outline-danger"
                    onclick="removeRow(${i})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`;
    $('#itemsBody').append(row);
    var on = $('#inv_daily').is(':checked');
    $(`#irow-${i} .daily-col`).toggle(on);
    recalcRow(i);
}

function recalcRow(i) {
    var qty  = parseFloat($(`#irow-${i} .item-qty`).val())  || 0;
    var rate = parseFloat($(`#irow-${i} .item-rate`).val()) || 0;
    var vat  = parseFloat($(`#irow-${i} .item-vat`).val())  || 0;
    if ($('#inv_daily').is(':checked')) {
        var from = $(`#irow-${i} .item-from`).val();
        var to   = $(`#irow-${i} .item-to`).val();
        if (from && to) {
            qty = Math.max(0, (new Date(to)-new Date(from))/86400000+1);
            $(`#irow-${i} .item-qty`).val(qty);
        }
    }
    var sub   = qty * rate;
    var total = sub + sub*(vat/100);
    $(`#irow-${i} .item-total`).val(total.toFixed(2));
    recalcAll();
}

function recalcAll() {
    var sub=0, vat=0;
    $('#itemsBody tr').each(function() {
        var q = parseFloat($(this).find('.item-qty').val())  || 0;
        var r = parseFloat($(this).find('.item-rate').val()) || 0;
        var v = parseFloat($(this).find('.item-vat').val())  || 0;
        var s = q*r; sub+=s; vat+=s*(v/100);
    });
    var disc  = parseFloat($('#inv_discount').val()) || 0;
    var recv  = parseFloat($('#inv_received').val()) || 0;
    var grand = sub + vat - disc;
    var due   = Math.max(0, grand - recv);
    $('#invTotal').text(grand.toFixed(2));
    $('#sumSub').text('৳ '+sub.toFixed(2));
    $('#sumVat').text('৳ '+vat.toFixed(2));
    $('#sumGrand').text('৳ '+grand.toFixed(2));
    $('#sumDue').text('৳ '+due.toFixed(2));
}

function removeRow(i) {
    if ($('#itemsBody tr').length<=1) return;
    $(`#irow-${i}`).remove(); recalcAll();
}

$('#btnAddItem').on('click', function() { addItemRow(); toggleDailyCols(); });

// ── collect items JSON ────────────────────────────────────────
function collectItems() {
    var items=[];
    $('#itemsBody tr').each(function() {
        items.push({
            item_name:   $(this).find('.item-service').val(),
            description: $(this).find('.item-desc').val(),
            unit:        $(this).find('.item-unit').val(),
            quantity:    $(this).find('.item-qty').val(),
            rate:        $(this).find('.item-rate').val(),
            vat:         $(this).find('.item-vat').val(),
            from_date:   $(this).find('.item-from').val(),
            to_date:     $(this).find('.item-to').val(),
            total:       $(this).find('.item-total').val(),
        });
    });
    return JSON.stringify(items);
}

// ── reset modal ───────────────────────────────────────────────
function resetInvoiceModal() {
    editInvId = null;
    $('#inv_modal_header').css('background','#0073b7');
    $('#inv_modal_title').html('<i class="fas fa-plus-circle mr-2"></i> Create Invoice');
    $('#btnSaveInvoice').html('<i class="fas fa-save mr-1"></i> Save Invoice');
    $('#inv_customer').val('').trigger('change');
    $('#inv_cust_info').addClass('d-none');
    $('#inv_month').val('{{ now()->format("Y-m") }}');
    $('#inv_due').val('{{ now()->endOfMonth()->format("Y-m-d") }}');
    $('#inv_status').val('unpaid');
    $('#inv_daily').prop('checked',true);
    $('#inv_discount').val(0);
    $('#inv_received').val(0);
    $('#inv_notes').val('');
    $('#itemsBody').empty(); rowIdx=0;
    addItemRow(); toggleDailyCols();
    fetchNextNo(); recalcAll();
}

// ── OPEN ADD ──────────────────────────────────────────────────
$('#btnAddInvoice').on('click', function() {
    resetInvoiceModal();
    $('#invoiceModal').modal('show');
    setTimeout(() => $('#inv_customer').select2({
        dropdownParent: $('#invoiceModal'), width:'100%'
    }), 200);
});

// ── OPEN EDIT ─────────────────────────────────────────────────
$(document).on('click', '.btn-edit-inv', function() {
    var id = $(this).data('id');
    editInvId = id;

    $('#inv_modal_header').css('background','#f39c12');
    $('#inv_modal_title').html('<i class="fas fa-edit mr-2"></i> Edit Invoice');
    $('#btnSaveInvoice').html('<i class="fas fa-save mr-1"></i> Update Invoice');

    $.ajax({
        url: '/bandwidth-sale/invoices/'+id,
        method: 'GET',
        headers: {'X-Requested-With':'XMLHttpRequest'},
        success: function(res) {
            if (!res.success) { toastErr('Load failed.'); return; }
            var inv = res.invoice;

            $('#inv_no').val(inv.invoice_no);
            $('#inv_customer').val(inv.bws_customer_id).trigger('change');
            $('#inv_month').val(inv.billing_month);
            $('#inv_due').val(inv.payment_due || '');
            $('#inv_status').val(inv.status);
            $('#inv_daily').prop('checked', inv.daily_basis == 1);
            $('#inv_discount').val(inv.discount);
            $('#inv_received').val(inv.received_amount);
            $('#inv_notes').val(inv.notes || '');

            $('#itemsBody').empty(); rowIdx=0;
            (inv.items||[]).forEach(item => addItemRow(item));
            toggleDailyCols(); recalcAll();

            $('#invoiceModal').modal('show');
            setTimeout(() => $('#inv_customer').select2({
                dropdownParent: $('#invoiceModal'), width:'100%'
            }), 200);
        },
        error: () => toastErr('Failed to load invoice.')
    });
});

// ── SAVE ──────────────────────────────────────────────────────
$('#btnSaveInvoice').on('click', function() {
    if (!$('#inv_customer').val()) { toastErr('Customer is required.'); return; }
    if (!$('#inv_month').val())    { toastErr('Billing Month is required.'); return; }

    var grand = parseFloat($('#sumGrand').text().replace('৳ ','')) || 0;
    var due   = parseFloat($('#sumDue').text().replace('৳ ',''))   || 0;
    var sub   = parseFloat($('#sumSub').text().replace('৳ ',''))   || 0;
    var vat   = parseFloat($('#sumVat').text().replace('৳ ',''))   || 0;

    var payload = {
        _token:           CSRF,
        bws_customer_id:  $('#inv_customer').val(),
        billing_month:    $('#inv_month').val(),
        payment_due:      $('#inv_due').val(),
        status:           $('#inv_status').val(),
        daily_basis:      $('#inv_daily').is(':checked') ? 1 : 0,
        total_amount:     sub.toFixed(2),
        vat_amount:       vat.toFixed(2),
        discount:         $('#inv_discount').val(),
        grand_total:      grand.toFixed(2),
        received_amount:  $('#inv_received').val(),
        due_amount:       due.toFixed(2),
        notes:            $('#inv_notes').val(),
        items_json:       collectItems(),
    };

    if (editInvId) payload['_method'] = 'PUT';

    var url = editInvId
        ? '/bandwidth-sale/invoices/'+editInvId
        : '/bandwidth-sale/invoices';

    var $btn = $(this).prop('disabled',true)
                      .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    $.ajax({
        url: url, method: 'POST', data: payload,
        success: function(res) {
            if (res.success || res.invoice_no) {
                $('#invoiceModal').modal('hide');
                toastOk(res.message || 'Invoice saved.');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastErr(res.message || 'Failed.');
            }
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors;
            toastErr(errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message||'Error.'));
        },
        complete: () => $btn.prop('disabled',false)
                            .html(editInvId
                                ? '<i class="fas fa-save mr-1"></i> Update Invoice'
                                : '<i class="fas fa-save mr-1"></i> Save Invoice')
    });
});

// ── VIEW MODAL ────────────────────────────────────────────────
$(document).on('click', '.btn-view-inv', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    $('#vm_no').text('Loading...');
    $('#vm_body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>');
    $('#vm_editBtn, #vm_payBtn').hide();
    $('#viewModal').modal('show');

    $.ajax({
        url: '/bandwidth-sale/invoices/'+id,
        method: 'GET',
        headers: {'X-Requested-With':'XMLHttpRequest'},
        success: function(res) {
            if (!res.success) { toastErr('Load failed.'); return; }
            var inv = res.invoice;
            var sc  = {paid:'#00a65a',partial:'#17a2b8',overdue:'#dc3545',unpaid:'#f39c12'};
            var lbl = {paid:'Paid',partial:'Partial',overdue:'Overdue',unpaid:'Unpaid'};

            $('#vm_no').text(inv.invoice_no);
            $('#vm_status_badge').html(
                `<span class="badge px-2 py-1" style="background:${sc[inv.status]||'#999'};color:#fff;border-radius:20px;font-size:11px;">
                    ${lbl[inv.status]||inv.status}
                </span>`
            );

            var itemRows = (inv.items||[]).map((it,i) => `
                <tr>
                    <td>${i+1}</td>
                    <td>${it.item_name||'—'}</td>
                    <td>${it.description||'—'}</td>
                    <td class="text-center">${it.unit||'—'}</td>
                    <td class="text-right">${parseFloat(it.quantity).toFixed(2)}</td>
                    <td class="text-right">${parseFloat(it.rate).toFixed(2)}</td>
                    <td class="text-right">${it.vat_percent}%</td>
                    ${inv.daily_basis ? `<td>${it.from_date||'—'}</td><td>${it.to_date||'—'}</td>` : ''}
                    <td class="text-right font-weight-bold">${parseFloat(it.total).toFixed(2)}</td>
                </tr>`).join('') || '<tr><td colspan="10" class="text-center text-muted">No items</td></tr>';

            var payRows = (inv.payments||[]).map((p,i) => `
                <tr class="${p.status==='void'?'text-muted':''}">
                    <td>${i+1}</td>
                    <td><code>${p.payment_no}</code></td>
                    <td>${p.received_date||'—'}</td>
                    <td><span class="badge badge-secondary">${(p.payment_method||'').toUpperCase()}</span></td>
                    <td class="text-right text-success font-weight-bold">৳ ${parseFloat(p.received_amount).toFixed(2)}</td>
                    <td><span class="badge badge-${p.status==='void'?'secondary':'success'}">${p.status}</span></td>
                </tr>`).join('') || '<tr><td colspan="6" class="text-center text-muted py-2">No payments yet.</td></tr>';

            $('#vm_body').html(`
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless" style="font-size:13px;">
                            <tr><td class="text-muted" style="width:40%">Customer</td>
                                <td><strong>${inv.customer_name||'—'}</strong></td></tr>
                            <tr><td class="text-muted">Contact</td><td>${inv.contact_person||'—'}</td></tr>
                            <tr><td class="text-muted">Mobile</td><td>${inv.mobile||'—'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless" style="font-size:13px;">
                            <tr><td class="text-muted" style="width:45%">Billing Month</td>
                                <td class="font-weight-bold">${inv.billing_month||'—'}</td></tr>
                            <tr><td class="text-muted">Payment Due</td><td>${inv.payment_due||'—'}</td></tr>
                            <tr><td class="text-muted">Daily Basis</td>
                                <td><span class="badge badge-${inv.daily_basis?'info':'secondary'}">${inv.daily_basis?'Yes':'No'}</span></td></tr>
                        </table>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered" style="font-size:12px;">
                        <thead style="background:#2c3e50;color:#fff;">
                            <tr>
                                <th>#</th><th>Item</th><th>Desc</th><th>Unit</th>
                                <th class="text-right">Qty</th><th class="text-right">Rate</th>
                                <th class="text-right">VAT</th>
                                ${inv.daily_basis ? '<th>From</th><th>To</th>' : ''}
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>${itemRows}</tbody>
                        <tfoot style="background:#f8f9fa;">
                            <tr>
                                <td colspan="${inv.daily_basis?9:7}" class="text-right font-weight-bold">Grand Total</td>
                                <td class="text-right font-weight-bold text-primary">
                                    ৳ ${parseFloat(inv.grand_total).toFixed(2)}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <table class="table table-sm table-bordered" style="font-size:13px;">
                            <tr><td class="text-muted">Total Amount</td>
                                <td class="text-right">৳ ${parseFloat(inv.total_amount).toFixed(2)}</td></tr>
                            <tr><td class="text-muted">Discount</td>
                                <td class="text-right text-warning">(৳ ${parseFloat(inv.discount).toFixed(2)})</td></tr>
                            <tr><td class="text-muted">Grand Total</td>
                                <td class="text-right font-weight-bold">৳ ${parseFloat(inv.grand_total).toFixed(2)}</td></tr>
                            <tr><td class="text-muted">Received</td>
                                <td class="text-right text-success font-weight-bold">৳ ${parseFloat(inv.received_amount).toFixed(2)}</td></tr>
                            <tr style="background:#fff3cd;"><td class="font-weight-bold">Balance Due</td>
                                <td class="text-right font-weight-bold text-danger">৳ ${parseFloat(inv.due_amount).toFixed(2)}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-7">
                        <div class="section-title mb-2" style="font-size:12px;">
                            <i class="fas fa-money-bill-wave mr-1 text-success"></i> Payment History
                        </div>
                        <table class="table table-sm table-bordered" style="font-size:12px;">
                            <thead style="background:#2c3e50;color:#fff;">
                                <tr><th>#</th><th>No</th><th>Date</th><th>Method</th>
                                    <th class="text-right">Amount</th><th>Status</th></tr>
                            </thead>
                            <tbody>${payRows}</tbody>
                        </table>
                    </div>
                </div>
                ${inv.notes ? `<div class="callout-info p-2 mt-2 rounded" style="background:#e8f4fd;border-left:4px solid #17a2b8;font-size:12px;"><strong>Notes:</strong> ${inv.notes}</div>` : ''}
            `);

            // show action buttons
            if (inv.status !== 'paid') {
                $('#vm_editBtn').show().data('id', id);
                if (inv.due_amount > 0) $('#vm_payBtn').show().data('id', id);
            }
        },
        error: () => toastErr('Failed to load invoice.')
    });
});

$('#vm_editBtn').on('click', function() {
    $('#viewModal').modal('hide');
    setTimeout(() => $('.btn-edit-inv[data-id="'+$(this).data('id')+'"]').trigger('click'), 400);
});

$('#vm_payBtn').on('click', function() {
    $('#viewModal').modal('hide');
    setTimeout(() => openPayModal($(this).data('id')), 400);
});

// ── PAY MODAL ─────────────────────────────────────────────────
$(document).on('click', '.btn-pay', function() {
    openPayModal($(this).data('id'));
});

function openPayModal(id) {
    payInvId = id;
    $('#pay_inv_no').text('Loading...');
    $('#pay_grand, #pay_prev, #pay_due').text('0.00');
    $('#pay_amount').val(0);
    $('#pay_discount').val(0);
    $('#pay_txn, #pay_remarks').val('');

    $.ajax({
        url: '/bandwidth-sale/invoices/'+id+'/receive',
        method: 'GET',
        headers: {'X-Requested-With':'XMLHttpRequest'},
        success: function(res) {
            if (!res.success) return;
            $('#pay_inv_no').text(res.invoice_no);
            $('#pay_grand').text(parseFloat(res.payable_amount).toFixed(2));
            $('#pay_prev').text(parseFloat(res.previous_paid).toFixed(2));
            $('#pay_due').text(parseFloat(res.balance_due).toFixed(2));
            $('#pay_amount').val(parseFloat(res.balance_due).toFixed(2));
        }
    });
    $('#payModal').modal('show');
}

$('#btnSubmitPay').on('click', function() {
    var amount = parseFloat($('#pay_amount').val());
    if (!amount || amount<=0) { toastErr('Received amount required.'); return; }

    var $btn = $(this).prop('disabled',true)
                      .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    $.ajax({
        url:    '/bandwidth-sale/invoices/'+payInvId+'/receive',
        method: 'POST',
        data: {
            _token:                 CSRF,
            received_date:          $('#pay_date').val(),
            received_from:          $('#pay_from').val(),
            received_by:            $('#pay_by').val(),
            payment_method:         $('#pay_method').val(),
            received_amount:        amount,
            discount:               $('#pay_discount').val(),
            receipt_transaction_no: $('#pay_txn').val(),
            remarks:                $('#pay_remarks').val(),
        },
        success: function(res) {
            if (res.success) {
                $('#payModal').modal('hide');
                toastOk(res.message + (res.income_no ? ' | Income: '+res.income_no : ''));
                setTimeout(() => location.reload(), 1800);
            } else {
                toastErr(res.message);
            }
        },
        error: xhr => toastErr(xhr.responseJSON?.message || 'Error.'),
        complete: () => $btn.prop('disabled',false)
                            .html('<i class="fas fa-save mr-1"></i> Submit Payment')
    });
});

// ── DELETE ────────────────────────────────────────────────────
$(document).on('click', '.btn-del-inv', function() {
    var id = $(this).data('id');
    var no = $(this).data('no');
    Swal.fire({
        title: 'Delete Invoice?',
        html: `<code>${no}</code> permanently delete হবে।`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({
            url: '/bandwidth-sale/invoices/'+id,
            method: 'POST',
            data: { _token:CSRF, _method:'DELETE' },
            success: function(res) {
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
