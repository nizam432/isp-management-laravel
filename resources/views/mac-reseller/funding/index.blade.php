@extends('adminlte::page')

@section('title', 'MAC Reseller Funding')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
        <div>
            <h1 class="m-0"><i class="fas fa-money-bill-wave mr-1 text-warning"></i> MAC Reseller Funding</h1>
            <small class="text-muted">Manage reseller wallet fund requests and payments</small>
        </div>
        <button class="btn btn-success" data-toggle="modal" data-target="#giveFundModal">
            <i class="fas fa-plus mr-1"></i> Give Fund
        </button>
    </div>
@stop

@section('content')

{{-- Toolbar --}}
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:8px">
            {{-- <button class="btn btn-outline-dark btn-sm" id="bulkToggleBtn">
                <i class="fas fa-ban mr-1"></i> Bulk Restrict (Block/Unblock)
            </button> --}}
            <div class="d-flex flex-wrap" style="gap:8px">
                <a href="{{ route('mac-reseller.funding.download-pdf') }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
                <a href="{{ route('mac-reseller.funding.download-excel') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card shadow-sm mb-3">
    <div class="card-header py-2 bg-light">
        <i class="fas fa-filter mr-1 text-muted"></i> <span class="font-weight-bold small text-uppercase text-muted">Filters</span>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('mac-reseller.funding.index') }}">
            <div class="row">
                <div class="col-6 col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">MAC RESELLER</label>
                    <select name="reseller_id" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($resellers as $r)
                        <option value="{{ $r->id }}" {{ request('reseller_id') == $r->id ? 'selected' : '' }}>{{ $r->business_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">STATUS</label>
                    <select name="transaction_status" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="paid" {{ request('transaction_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="due" {{ request('transaction_status') == 'due' ? 'selected' : '' }}>Due</option>
                        <option value="partial" {{ request('transaction_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">FROM DATE</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">TO DATE</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">PAYMENT BY</label>
                    <select name="payment_by" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($employees as $e)
                        <option value="{{ $e->id }}" {{ request('payment_by') == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">RECEIVED BY</label>
                    <select name="received_by" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($employees as $e)
                        <option value="{{ $e->id }}" {{ request('received_by') == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">RESTRICT STATUS</label>
                    <select name="restrict_status" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="1" {{ request('restrict_status') === '1' ? 'selected' : '' }}>Restricted</option>
                        <option value="0" {{ request('restrict_status') === '0' ? 'selected' : '' }}>Unrestricted</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 mb-2 d-flex align-items-end" style="gap:6px">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a href="{{ route('mac-reseller.funding.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm">
    <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
        <span class="font-weight-bold small text-uppercase text-muted">
            <i class="fas fa-list mr-1"></i> Fund Requests
        </span>
        <input type="text" id="searchInput" class="form-control form-control-sm" style="max-width:220px" placeholder="Search this table...">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-sm mb-0" id="fundTable" style="font-size:12.5px; white-space:nowrap">
            <thead class="bg-dark text-white">
                <tr>
                    {{-- <th style="width:32px"><input type="checkbox" id="checkAll"></th> --}}
                    <th>Reseller</th>
                    <th>Invoice No.</th>
                    <th class="text-right">Fund Amt</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Due</th>
                    <th>Funding Date</th>
                    <th>Given By</th>
                    <th>Last Received</th>
                    <th>Status</th>
                    {{-- <th>Restrict</th> --}}
                    <th style="width:140px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fundings as $f)
                <tr>
                    {{-- <td><input type="checkbox" class="row-check" value="{{ $f->id }}"></td> --}}
                    <td class="font-weight-bold">{{ $f->reseller?->business_name }}</td>
                    <td><span class="text-muted">{{ $f->invoice_number }}</span></td>
                    <td class="text-right">{{ number_format($f->fund_amount, 0) }}</td>
                    <td class="text-right text-success">{{ number_format($f->payment, 0) }}</td>
                    <td class="text-right {{ $f->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">{{ number_format($f->due_amount, 0) }}</td>
                    <td>{{ $f->funding_date?->format('d M, Y') }}</td>
                    <td>{{ $f->fundGivenBy?->name }}</td>
                    <td>
                        @if($f->received_date)
                            {{ $f->received_date->format('d M, Y') }}<br>
                            <small class="text-muted">{{ $f->receivedBy?->name }}</small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($f->transaction_status == 'paid')
                            <span class="badge badge-success">Paid</span>
                        @elseif($f->transaction_status == 'due')
                            <span class="badge badge-danger">Due</span>
                        @else
                            <span class="badge badge-warning">Partial</span>
                        @endif
                    </td>
                    {{-- <td>
                        <button class="btn btn-xs {{ $f->restrict_online ? 'btn-success' : 'btn-outline-secondary' }} toggle-restrict-btn" data-id="{{ $f->id }}">
                            <i class="fas {{ $f->restrict_online ? 'fa-check' : 'fa-ban' }}"></i>
                        </button>
                    </td> --}}
                    <td>
                        <div class="btn-group">
                            @if($f->transaction_status !== 'paid')
                            <button class="btn btn-xs btn-success pay-btn" data-id="{{ $f->id }}" data-due="{{ $f->due_amount }}" title="Add Payment">
                                <i class="fas fa-hand-holding-usd mr-1"></i> Pay
                            </button>
                            @endif
                            <a href="{{ route('mac-reseller.funding.history', ['reseller_id' => $f->reseller_id]) }}"
                               class="btn btn-xs btn-outline-info" title="Payment History">
                                <i class="fas fa-list"></i>
                            </a>
                            <button class="btn btn-xs btn-outline-primary" title="Invoice"><i class="fas fa-file-invoice"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-4 text-muted">No funding records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $fundings->links() }}
    </div>
</div>

{{-- Give Fund Modal --}}
<div class="modal fade" id="giveFundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-money-bill-wave mr-1"></i> Fund Transaction</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="giveFundForm">
                    @csrf
                    <div class="form-group">
                        <label class="small font-weight-bold">RESELLER NAME <span class="text-danger">*</span></label>
                        <select name="reseller_id" class="form-control" required>
                            <option value="">Select MAC Reseller</option>
                            @foreach($resellers as $r)
                            <option value="{{ $r->id }}">{{ $r->business_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">FUNDING AMOUNT <span class="text-danger">*</span></label>
                            <input type="number" name="fund_amount" id="fundAmount" class="form-control" min="1" step="0.01" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">RECEIVED AMOUNT</label>
                            <input type="number" name="payment" id="paymentAmt" class="form-control" min="0" step="0.01" placeholder="0">
                        </div>
                    </div>
                    <small class="text-muted d-block mb-3" style="margin-top:-10px">Leave blank/0 if the reseller hasn't paid anything yet — add a payment later.</small>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">PAYMENT METHOD</label>
                            <select name="payment_method" class="form-control">
                                <option value="cash">Cash</option>
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                                <option value="rocket">Rocket</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">RECEIVED BY</label>
                            <select name="received_by" class="form-control">
                                <option value="">Select Employee</option>
                                @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="apply_vat" id="applyVat" value="1" class="custom-control-input">
                            <label class="custom-control-label small" for="applyVat">Apply VAT?</label>
                        </div>
                        <input type="number" name="vat" id="vatAmt" class="form-control mt-1" min="0" step="0.01" value="0" disabled placeholder="VAT amount">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">DISCOUNT</label>
                            <input type="number" name="discount" id="discountAmt" class="form-control" min="0" step="0.01" value="0">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">NET AMOUNT</label>
                            <input type="text" id="netAmount" class="form-control bg-light" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">RECEIVED DATE</label>
                        <input type="date" name="received_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">REMARKS</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Add Payment Modal --}}
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="fas fa-hand-holding-usd mr-1"></i> Add Payment</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="addPaymentForm">
                    @csrf
                    <input type="hidden" name="funding_id" id="ap_funding_id">
                    <div class="form-group">
                        <label class="small font-weight-bold">AMOUNT <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="ap_amount" class="form-control" min="0.01" step="0.01" required>
                        <small class="text-muted">Due: ৳<span id="ap_due_display"></span></small>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">PAYMENT METHOD</label>
                        <select name="payment_method" class="form-control">
                            <option value="cash">Cash</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                            <option value="card">Card</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">REMARKS</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fas fa-save mr-1"></i> Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .btn-xs { padding: .2rem .4rem; font-size: .75rem; line-height: 1.2; }
    @media (max-width: 767px) {
        .card-header .font-weight-bold { display:block; margin-bottom:6px; }
    }
</style>
@stop

@section('js')
<script>
$('#applyVat').on('change', function() {
    $('#vatAmt').prop('disabled', !this.checked);
    if (!this.checked) { $('#vatAmt').val(0); calcNet(); }
});

function calcNet() {
    const fund     = parseFloat($('#fundAmount').val()) || 0;
    const vat      = parseFloat($('#vatAmt').val()) || 0;
    const discount = parseFloat($('#discountAmt').val()) || 0;
    $('#netAmount').val((fund + vat - discount).toFixed(2));
}
$('#fundAmount, #vatAmt, #discountAmt').on('input', calcNet);

$('#giveFundForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: "{{ route('mac-reseller.funding.store') }}",
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if (res.success) {
                $('#giveFundModal').modal('hide');
                toastr.success(res.message + ' | Invoice: ' + res.invoice);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(res.message || 'Save failed.');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) toastr.error(Object.values(errors).flat().join('\n'));
            else toastr.error(xhr.responseJSON?.message || 'Save failed.');
        }
    });
});

$(document).on('click', '.pay-btn', function() {
    const id  = $(this).data('id');
    const due = $(this).data('due');
    $('#ap_funding_id').val(id);
    $('#ap_amount').val(due);
    $('#ap_due_display').text(parseFloat(due).toFixed(2));
    $('#addPaymentModal').modal('show');
});

$('#addPaymentForm').on('submit', function(e) {
    e.preventDefault();
    const id = $('#ap_funding_id').val();
    $.ajax({
        url: `/mac-reseller/funding/${id}/paid`,
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if (res.success) {
                $('#addPaymentModal').modal('hide');
                toastr.success('Payment saved.');
                setTimeout(() => location.reload(), 800);
            } else {
                toastr.error(res.message || 'Payment failed.');
            }
        },
        error: function(xhr) { toastr.error(xhr.responseJSON?.message || 'Payment failed.'); }
    });
});

{{-- $(document).on('click', '.toggle-restrict-btn', function() {
    const id = $(this).data('id');
    $.post(`/mac-reseller/funding/${id}/toggle-restrict`, { _token: '{{ csrf_token() }}' }, () => location.reload());
}); --}}

{{-- $('#checkAll').on('change', function() { $('.row-check').prop('checked', this.checked); });

$('#bulkToggleBtn').on('click', function() {
    const ids = $('.row-check:checked').map((_, el) => el.value).get();
    if (!ids.length) { toastr.warning('Please select at least one record.'); return; }
    const action = confirm('Block selected? OK=Block, Cancel=Unblock') ? 'block' : 'unblock';
    $.post("{{ route('mac-reseller.funding.bulk-toggle-restrict') }}", { _token: '{{ csrf_token() }}', ids, action },
        (res) => { if (res.success) location.reload(); });
}); --}}

$('#searchInput').on('keyup', function() {
    const val = $(this).val().toLowerCase();
    $('#fundTable tbody tr').each(function() {
        $(this).toggle($(this).text().toLowerCase().includes(val));
    });
});
</script>
@stop