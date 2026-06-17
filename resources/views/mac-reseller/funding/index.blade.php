@extends('adminlte::page')

@section('title', 'MAC Reseller Funding')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-money-bill-wave mr-1 text-warning"></i> MAC Reseller Funding
                <small class="text-muted">MAC Reseller Fund</small>
            </h1>
        </div>
        <span class="text-muted small"><i class="fas fa-users-cog"></i> Mac Reseller &rsaquo; Reseller Funding <i class="fas fa-sync-alt"></i></span>
    </div>
@stop

@section('content')

{{-- Tab buttons --}}
<div class="mb-3 d-flex align-items-center flex-wrap" style="gap:8px">
    <a href="{{ route('mac-reseller.funding.index') }}" class="btn btn-dark btn-sm">
        <i class="fas fa-list mr-1"></i> MACReseller Fund
    </a>
    <a href="{{ route('mac-reseller.funding.history') }}" class="btn btn-outline-dark btn-sm">
        <i class="fas fa-history mr-1"></i> MACReseller Fund History
    </a>
    <div class="ml-auto d-flex" style="gap:8px">
        <a href="{{ route('mac-reseller.funding.download-pdf') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-file-pdf mr-1"></i> Download PDF
        </a>
        <a href="{{ route('mac-reseller.funding.download-excel') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-file-excel mr-1"></i> Download Excel
        </a>
        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#giveFundModal">
            <i class="fas fa-plus mr-1"></i> Give Fund
        </button>
    </div>
</div>

<button class="btn btn-dark btn-sm mb-3" id="bulkToggleBtn">
    <i class="fas fa-ban mr-1"></i> BulkOnlineFundRechargeRestriction(Block/Unblock)
</button>

<div class="card">
    <div class="card-body">
        {{-- Filters --}}
        <form method="GET" action="{{ route('mac-reseller.funding.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">MAC RESELLERS</label>
                        <select name="reseller_id" class="form-control form-control-sm">
                            <option value="">Select MACReseller</option>
                            @foreach($resellers as $r)
                            <option value="{{ $r->id }}" {{ request('reseller_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->business_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">TRANSACTION STATUS</label>
                        <select name="transaction_status" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="paid" {{ request('transaction_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="due" {{ request('transaction_status') == 'due' ? 'selected' : '' }}>Due</option>
                            <option value="partial" {{ request('transaction_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">FROM DATE</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">TO DATE</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">PAYMENT BY</label>
                        <select name="payment_by" class="form-control form-control-sm">
                            <option value="">Select</option>
                            @foreach($employees as $e)
                            <option value="{{ $e->id }}" {{ request('payment_by') == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">RECEIVED BY</label>
                        <select name="received_by" class="form-control form-control-sm">
                            <option value="">Select</option>
                            @foreach($employees as $e)
                            <option value="{{ $e->id }}" {{ request('received_by') == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">RESTRICT STATUS</label>
                        <select name="restrict_status" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option value="1">Restricted</option>
                            <option value="0">Unrestricted</option>
                        </select>
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
                    <option>25</option><option selected>100</option>
                </select>
                <span class="small">ENTRIES</span>
            </div>
            <div class="col-sm-4 offset-sm-6 text-right d-flex align-items-center justify-content-end" style="gap:8px">
                <label class="mb-0 small">SEARCH:</label>
                <input type="text" id="searchInput" class="form-control form-control-sm" style="width:200px">
            </div>
        </div>

        <div class="table-responsive">
        <table class="table table-bordered table-sm" id="fundTable" style="font-size:12px">
            <thead class="bg-dark text-white">
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>ResellerName</th>
                    <th>InvoiceNumber</th>
                    <th>FundAmount</th>
                    <th>Payment</th>
                    <th>P.Processing Fee</th>
                    <th>Vat</th>
                    <th>Discount</th>
                    <th>DueAmount</th>
                    <th>FundingDate</th>
                    <th>FundGivenBy</th>
                    <th>ReceivedDate(Last)</th>
                    <th>ReceivedBy(Last)</th>
                    <th>Remarks</th>
                    <th>Trans.Status</th>
                    <th>RestrictOnlinePayment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fundings as $f)
                <tr>
                    <td><input type="checkbox" class="row-check" value="{{ $f->id }}"></td>
                    <td>{{ $f->reseller?->business_name }}</td>
                    <td>{{ $f->invoice_number }}</td>
                    <td>{{ number_format($f->fund_amount, 0) }}</td>
                    <td>{{ number_format($f->payment, 0) }}</td>
                    <td>{{ number_format($f->processing_fee, 0) }}</td>
                    <td>{{ number_format($f->vat, 0) }}</td>
                    <td>{{ number_format($f->discount, 0) }}</td>
                    <td>{{ number_format($f->due_amount, 0) }}</td>
                    <td>{{ $f->funding_date?->format('d/m/Y') }}</td>
                    <td>{{ $f->fundGivenBy?->name }}</td>
                    <td>{{ $f->received_date?->format('d/m/Y') }}</td>
                    <td>{{ $f->receivedBy?->name }}</td>
                    <td>{{ $f->remarks }}</td>
                    <td>
                        @if($f->transaction_status == 'paid')
                            <span class="badge badge-success">Paid</span>
                        @elseif($f->transaction_status == 'due')
                            <button class="btn btn-sm btn-success pay-btn" data-id="{{ $f->id }}">Pay</button>
                            <span class="badge badge-danger">Due</span>
                            <button class="btn btn-sm btn-info refund-btn" data-id="{{ $f->id }}">Refund</button>
                        @else
                            <button class="btn btn-sm btn-success pay-btn" data-id="{{ $f->id }}">Pay</button>
                            <span class="badge badge-warning">Partial</span>
                            <button class="btn btn-sm btn-info refund-btn" data-id="{{ $f->id }}">Refund</button>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm {{ $f->restrict_online ? 'btn-success' : 'btn-outline-secondary' }} toggle-restrict-btn"
                            data-id="{{ $f->id }}">
                            @if($f->restrict_online)
                                <i class="fas fa-check mr-1"></i> Unblocked
                            @else
                                <i class="fas fa-ban mr-1"></i> Block
                            @endif
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" title="Invoice"><i class="fas fa-file-invoice"></i></button>
                        <button class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="17" class="text-center">No funding records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        {{ $fundings->links() }}
    </div>
</div>

{{-- Give Fund Modal --}}
<div class="modal fade" id="giveFundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fund Transaction</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="giveFundForm">
                    @csrf
                    <div class="form-group">
                        <label class="small font-weight-bold">RESELLER NAME <span class="text-danger">*</span></label>
                        <select name="reseller_id" class="form-control" required>
                            <option value="">Select MACReseller</option>
                            @foreach($resellers as $r)
                            <option value="{{ $r->id }}">{{ $r->business_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">FUNDING AMOUNT <span class="text-danger">*</span></label>
                        <input type="number" name="fund_amount" id="fundAmount" class="form-control" min="1" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">RECEIVED AMOUNT <span class="text-danger">*</span></label>
                        <input type="number" name="payment" id="paymentAmt" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">VAT AMOUNT</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" name="apply_vat" id="applyVat" value="1">
                                    <span class="ml-1 small">Do you want to apply VAT?</span>
                                </div>
                            </div>
                        </div>
                        <input type="number" name="vat" id="vatAmt" class="form-control mt-1" min="0" step="0.01" value="0" disabled>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">NET AMOUNT</label>
                        <input type="text" id="netAmount" class="form-control bg-light" readonly>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">DISCOUNT</label>
                        <input type="number" name="discount" id="discountAmt" class="form-control" min="0" step="0.01" value="0">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">RECEIVED BY <span class="text-danger">*</span></label>
                        <select name="received_by" class="form-control" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">RECEIVED DATE</label>
                        <input type="date" name="received_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">REMARKS</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-danger px-4" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-secondary px-4">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
// VAT toggle
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

// Give Fund submit
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
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) toastr.error(Object.values(errors).flat().join('\n'));
        }
    });
});

// Pay
$(document).on('click', '.pay-btn', function() {
    const id = $(this).data('id');
    if (!confirm('Mark as Paid?')) return;
    $.post(`/mac-reseller/funding/${id}/paid`, { _token: '{{ csrf_token() }}' },
        (res) => { if (res.success) location.reload(); }
    );
});

// Toggle Restrict
$(document).on('click', '.toggle-restrict-btn', function() {
    const id = $(this).data('id');
    $.post(`/mac-reseller/funding/${id}/toggle-restrict`, { _token: '{{ csrf_token() }}' },
        () => location.reload()
    );
});

// Check all
$('#checkAll').on('change', function() {
    $('.row-check').prop('checked', this.checked);
});

// Bulk toggle
$('#bulkToggleBtn').on('click', function() {
    const ids = $('.row-check:checked').map((_, el) => el.value).get();
    if (!ids.length) { toastr.warning('Please select at least one record.'); return; }
    const action = confirm('Block selected? OK=Block, Cancel=Unblock') ? 'block' : 'unblock';
    $.post("{{ route('mac-reseller.funding.bulk-toggle-restrict') }}", {
        _token: '{{ csrf_token() }}', ids, action
    }, (res) => { if (res.success) location.reload(); });
});

// Search
$('#searchInput').on('keyup', function() {
    const val = $(this).val().toLowerCase();
    $('#fundTable tbody tr').each(function() {
        $(this).toggle($(this).text().toLowerCase().includes(val));
    });
});
</script>
@stop
