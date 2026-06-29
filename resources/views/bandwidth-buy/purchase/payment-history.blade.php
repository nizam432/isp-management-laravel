@extends('adminlte::page')
@section('title', 'Payment History')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-history mr-2 text-primary"></i>Bandwidth Purchase — Payment History
            </h4>
            <small class="text-muted">All bandwidth purchase payments</small>
        </div>
        <div>
            <button class="btn btn-success btn-sm px-3" id="btnXlsx">
                <i class="fas fa-file-excel mr-1"></i> XLSX
            </button>
            <button class="btn btn-danger btn-sm px-3 ml-1" id="btnPdf">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </button>
        </div>
    </div>
@endsection

@section('content')

{{-- ── Filter ──────────────────────────────────────────────────────── --}}
<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" id="filterForm" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold">Provider</label>
                <select name="provider_id" class="form-control form-control-sm">
                    <option value="">All Providers</option>
                    @foreach($providers as $prov)
                        <option value="{{ $prov->id }}" {{ request('provider_id') == $prov->id ? 'selected' : '' }}>
                            {{ $prov->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Transaction Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="void"   {{ request('status') == 'void'   ? 'selected' : '' }}>Void</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm"
                       value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm"
                       value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-3 mr-2">
                    <i class="fas fa-search mr-1"></i>Search
                </button>
                <a href="{{ route('bandwidth-buy.purchase.all-payment-history') }}"
                   class="btn btn-secondary btn-sm px-3">
                    <i class="fas fa-redo mr-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Payment List ─────────────────────────────────────────────────── --}}
<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Payment List
            <span class="badge badge-light ml-2">{{ $payments->total() }}</span>
        </h6>
        <div class="d-flex align-items-center">
            <span class="text-white mr-3" style="font-size:13px;">
                Total: <strong>৳ {{ number_format($totalAmount, 2) }}</strong>
            </span>
            <select id="perPage" class="form-control form-control-sm" style="width:70px;">
                @foreach([20, 50, 100] as $n)
                    <option value="{{ $n }}" {{ request('per_page', 20) == $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:13px;">
                <thead style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                    <tr>
                        <th class="text-center" style="width:50px;">#</th>
                        <th>Payment Date</th>
                        <th>Invoice No</th>
                        <th>Provider</th>
                        <th class="text-right">Amount (৳)</th>
                        <th>Method</th>
                        <th>Tx No</th>
                        <th>Remarks</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th style="width:140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $i => $pay)
                    <tr id="pay-row-{{ $pay->id }}" class="{{ $pay->status === 'void' ? 'text-muted' : '' }}">
                        <td class="text-center text-muted small">{{ $payments->firstItem() + $i }}</td>
                        <td>{{ optional($pay->payment_date)->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('bandwidth-buy.purchase.index') }}"
                               class="font-weight-bold text-primary">
                                {{ $pay->purchase->invoice_no ?? '—' }}
                            </a>
                        </td>
                        <td>{{ $pay->purchase->provider->company_name ?? '—' }}</td>
                        <td class="text-right font-weight-bold {{ $pay->status === 'void' ? 'text-muted' : 'text-success' }}">
                            ৳ {{ number_format($pay->amount, 2) }}
                        </td>
                        <td>
                            <span class="badge badge-light border">
                                {{ strtoupper($pay->payment_method) }}
                            </span>
                        </td>
                        <td>{{ $pay->transaction_no ?? '—' }}</td>
                        <td>{{ Str::limit($pay->remarks, 30) ?? '—' }}</td>
                        <td>{{ $pay->createdBy->name ?? '—' }}</td>
                        <td>
                            @if($pay->status === 'void')
                                <span class="badge badge-secondary">Void</span>
                            @else
                                <span class="badge badge-success">Active</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-xs btn-info btn-view-payment"
                                    data-id="{{ $pay->id }}"
                                    title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($pay->status !== 'void')
                            <button class="btn btn-xs btn-danger btn-void-payment ml-1"
                                    data-id="{{ $pay->id }}"
                                    data-no="{{ $pay->id }}"
                                    style="font-size:11px; padding:2px 8px;">
                                <i class="fas fa-ban mr-1"></i>Void
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                            No payments found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="card-footer">
        {{ $payments->links() }}
    </div>
    @endif
</div>

{{-- ── View Modal ───────────────────────────────────────────────────── --}}
<div class="modal fade" id="viewPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-lg" style="border-radius:10px; overflow:hidden;">
            <div class="modal-header border-0 py-3"
                 style="background:linear-gradient(135deg,#1a237e,#283593);">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-eye mr-2"></i>Payment Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body px-4 py-3" id="viewPaymentBody">
                <div class="text-center py-3 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light px-4">
                <button type="button" class="btn btn-light border px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
@parent
<script>
var CSRF = '{{ csrf_token() }}';

// ── Per page ──────────────────────────────────────────────────
$('#perPage').on('change', function () {
    var url = new URL(window.location.href);
    url.searchParams.set('per_page', $(this).val());
    window.location.href = url.toString();
});

// ── Export XLSX ───────────────────────────────────────────────
$('#btnXlsx').on('click', function () {
    var params = $('#filterForm').serialize();
    window.location.href = '{{ route("bandwidth-buy.purchase.all-payment-history.xlsx") }}?' + params;
});

// ── Export PDF ────────────────────────────────────────────────
$('#btnPdf').on('click', function () {
    var params = $('#filterForm').serialize();
    window.open('{{ route("bandwidth-buy.purchase.all-payment-history.pdf") }}?' + params, '_blank');
});

// ── View Payment ──────────────────────────────────────────────
$(document).on('click', '.btn-view-payment', function () {
    var id = $(this).data('id');
    $('#viewPaymentBody').html('<div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#viewPaymentModal').modal('show');

    $.get('{{ url("bandwidth-buy/purchase/payment") }}/' + id + '/detail', function (res) {
        var p = res.payment;
        var voidSection = '';
        if (p.is_void) {
            voidSection = `
                <hr>
                <div class="row mt-2">
                    <div class="col-6"><strong class="text-danger">Void Date</strong></div>
                    <div class="col-6 text-danger">${p.void_date}</div>
                </div>
                <div class="row mt-1">
                    <div class="col-6"><strong class="text-danger">Void By</strong></div>
                    <div class="col-6 text-danger">${p.void_by}</div>
                </div>
                <div class="row mt-1">
                    <div class="col-6"><strong class="text-danger">Void Reason</strong></div>
                    <div class="col-6 text-danger">${p.void_reason}</div>
                </div>`;
        }
        $('#viewPaymentBody').html(`
            <div class="row mb-2">
                <div class="col-6"><strong>Invoice No</strong></div>
                <div class="col-6 text-primary font-weight-bold">${p.invoice_no}</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Provider</strong></div>
                <div class="col-6">${p.provider}</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Payment Date</strong></div>
                <div class="col-6">${p.payment_date}</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Amount</strong></div>
                <div class="col-6 font-weight-bold text-success">৳ ${p.amount}</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Method</strong></div>
                <div class="col-6"><span class="badge badge-light border">${p.method}</span></div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Transaction No</strong></div>
                <div class="col-6">${p.transaction_no}</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Remarks</strong></div>
                <div class="col-6">${p.remarks}</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Created By</strong></div>
                <div class="col-6">${p.created_by}</div>
            </div>
            ${voidSection}
        `);
    }).fail(function () {
        $('#viewPaymentBody').html('<div class="text-center text-danger py-3">Failed to load details.</div>');
    });
});

// ── Void Payment ──────────────────────────────────────────────
$(document).on('click', '.btn-void-payment', function () {
    var payId = $(this).data('id');
    var payNo = $(this).data('no');

    Swal.fire({
        title: 'Void Payment?',
        html: `Payment <code>#${payNo}</code> void হবে।<br>
               <strong class="text-success">Accounting Expense ও void হবে।</strong>`,
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Void reason (required)',
        inputAttributes: { autocomplete: 'off' },
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Void',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        preConfirm: function (val) {
            if (!val || !val.trim()) Swal.showValidationMessage('Reason required.');
            return val;
        }
    }).then(function (r) {
        if (!r.isConfirmed) return;
        $.ajax({
            url:    '{{ url("bandwidth-buy/purchase/payment") }}/' + payId + '/void',
            method: 'POST',
            data:   { _token: CSRF, reason: r.value },
            success: function (res) {
                toastr.success(res.message);
                $(`#pay-row-${payId}`).fadeOut();
                setTimeout(() => location.reload(), 1500);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Void failed.');
            }
        });
    });
});
</script>
@endsection
