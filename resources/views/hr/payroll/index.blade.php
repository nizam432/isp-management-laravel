{{-- resources/views/hr/payroll/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Payroll')
@section('page_actions')
    <button class="btn btn-success btn-sm mr-1" id="btnXlsx">
        <i class="fas fa-file-excel mr-1"></i> XLSX
    </button>
    <button class="btn btn-danger btn-sm mr-1" id="btnPdf">
        <i class="fas fa-file-pdf mr-1"></i> PDF
    </button>
    <button class="btn btn-secondary btn-sm mr-1" id="btnBulkDelete" disabled>
        <i class="fas fa-trash mr-1"></i> Delete Selected
    </button>
    <a href="{{ route('payroll.generate') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Generate Payroll
    </a>
@endsection
@section('page_content')

{{-- Month Filter --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline">
            <label class="mr-2 font-weight-bold">Month:</label>
            <input type="month" name="month" class="form-control form-control-sm mr-2"
                   value="{{ $month }}">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-money-bill-wave mr-1"></i>
            Payroll — {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
        </h3>
        <div>
            <span class="badge badge-success mr-1">Paid: {{ $payrolls->where('status', 'paid')->count() }}</span>
            <span class="badge badge-warning mr-1">Partial: {{ $payrolls->where('status', 'partial')->count() }}</span>
            <span class="badge badge-secondary mr-1">Pending: {{ $payrolls->where('status', 'pending')->count() }}</span>
            <span class="badge badge-danger">Void: {{ $payrolls->where('status', 'void')->count() }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="selectAll">
                    </th>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Basic</th>
                    <th>Gross</th>
                    <th>Deduction</th>
                    <th>Net Salary</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th style="width:130px;">Action</th>
                </tr>
            </thead>
            <tbody id="payrollTableBody">
                @forelse($payrolls as $i => $payroll)
                <tr id="payroll-row-{{ $payroll->id }}" class="{{ $payroll->isVoid() ? 'text-muted' : '' }}">
                    <td>
                        @if($payroll->isPending() && $payroll->payments->count() === 0)
                        <input type="checkbox" class="pay-check" value="{{ $payroll->id }}">
                        @endif
                    </td>
                    <td>{{ $payrolls->firstItem() + $i }}</td>
                    <td>
                        <a href="{{ route('employees.show', $payroll->employee) }}" class="font-weight-bold">
                            {{ $payroll->employee->name }}
                        </a>
                        <br><small class="text-muted"><code>{{ $payroll->employee->employee_code }}</code></small>
                    </td>
                    <td>৳ {{ number_format($payroll->basic_salary) }}</td>
                    <td>৳ {{ number_format($payroll->gross_salary) }}</td>
                    <td class="text-danger">৳ {{ number_format($payroll->total_deduction) }}</td>
                    <td><strong>৳ {{ number_format($payroll->net_salary) }}</strong></td>
                    <td class="text-success">৳ {{ number_format($payroll->paid_amount) }}</td>
                    <td class="{{ $payroll->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                        ৳ {{ number_format($payroll->due_amount) }}
                    </td>
                    <td>{!! $payroll->statusBadge !!}</td>
                    <td>
                        <a href="{{ route('payroll.payslip', $payroll) }}"
                           class="btn btn-xs btn-info" target="_blank" title="Payslip">
                            <i class="fas fa-print"></i>
                        </a>
                        <a href="{{ route('payroll.payslip-pdf', $payroll) }}"
                           class="btn btn-xs btn-danger" title="Download PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <button class="btn btn-xs btn-primary btn-pay-history"
                                data-id="{{ $payroll->id }}"
                                data-name="{{ $payroll->employee->name }}"
                                title="Payment History">
                            <i class="fas fa-history"></i>
                        </button>
                        @if(!$payroll->isVoid() && !$payroll->isPaid())
                        <button class="btn btn-xs btn-success btn-pay-payroll"
                                data-id="{{ $payroll->id }}"
                                data-name="{{ $payroll->employee->name }}"
                                data-due="{{ $payroll->due_amount }}"
                                title="Pay">
                            <i class="fas fa-money-bill-wave"></i>
                        </button>
                        @endif
                        @if($payroll->isPending() && $payroll->payments->count() === 0)
                        <a href="{{ route('payroll.edit', $payroll) }}"
                           class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-xs btn-secondary btn-delete-payroll"
                                data-id="{{ $payroll->id }}"
                                title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                        No payroll records for this month.
                        <a href="{{ route('payroll.generate') }}" class="d-block mt-2">Generate Payroll</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Total {{ $payrolls->total() }}</small>
        {{ $payrolls->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
</div>

{{-- ══ PAY MODAL ══ --}}
<div class="modal fade" id="payModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-lg" style="border-radius:10px; overflow:hidden;">
            <div class="modal-header border-0 py-3" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Pay — <span id="payEmployeeName" class="text-warning"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="alert border-0 mb-3 py-2 px-3"
                     style="background:#e8f5e9; border-left:4px solid #2e7d32 !important; border-radius:6px;">
                    <i class="fas fa-info-circle text-success mr-1"></i>
                    Balance Due: <strong class="text-danger" id="payDueLabel"></strong>
                    <br><small class="text-muted">Payment save হলে Accounting → Expense এ automatically record হবে।</small>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Amount <span class="text-danger">*</span></label>
                    <input type="number" id="payAmount" class="form-control" min="0.01" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" id="payDate" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Payment Method <span class="text-danger">*</span></label>
                    <select id="payMethod" class="form-control">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                        <option value="rocket">Rocket</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">Transaction No</label>
                    <input type="text" id="payTxNo" class="form-control" placeholder="Optional">
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Note</label>
                    <input type="text" id="payNote" class="form-control" placeholder="Optional">
                </div>
            </div>
            <div class="modal-footer border-0 bg-light px-4 py-3">
                <button type="button" class="btn btn-light border px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success px-4" id="btnConfirmPay">
                    <i class="fas fa-check mr-1"></i>Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ PAYMENT HISTORY MODAL ══ --}}
<div class="modal fade" id="payHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:10px; overflow:hidden;">
            <div class="modal-header border-0 py-3"
                 style="background:linear-gradient(135deg,#1a237e,#283593);">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-history mr-2"></i>Payment History — <span id="histEmpName" class="text-warning"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:13px;">
                        <thead style="background:#f8f9fa;">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th class="text-right">Amount (৳)</th>
                                <th>Method</th>
                                <th>Tx No</th>
                                <th>Note</th>
                                <th>Created By</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="histPayTableBody">
                            <tr><td colspan="9" class="text-center py-4 text-muted">Loading...</td></tr>
                        </tbody>
                        <tfoot id="histPayFoot" style="display:none; background:#e8eaf6;">
                            <tr>
                                <td colspan="2" class="text-right font-weight-bold">Total Paid</td>
                                <td class="font-weight-bold text-success" id="histPayTotal"></td>
                                <td colspan="6"></td>
                            </tr>
                        </tfoot>
                    </table>
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

@push('js')
<script>
var CSRF = '{{ csrf_token() }}';

// ── Export XLSX ───────────────────────────────────────────────
$('#btnXlsx').on('click', function () {
    var month = $('input[name="month"]').val();
    window.location.href = '{{ route("payroll.export-xlsx") }}?month=' + month;
});

// ── Export PDF ────────────────────────────────────────────────
$('#btnPdf').on('click', function () {
    var month = $('input[name="month"]').val();
    window.open('{{ route("payroll.export-pdf") }}?month=' + month, '_blank');
});

// ── Payment History ───────────────────────────────────────────
var currentHistPayrollId = null;
$(document).on('click', '.btn-pay-history', function () {
    currentHistPayrollId = $(this).data('id');
    $('#histEmpName').text($(this).data('name'));
    $('#histPayTableBody').html('<tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></td></tr>');
    $('#histPayFoot').hide();
    $('#payHistoryModal').modal('show');
    loadPayHistory();
});

function loadPayHistory() {
    $.get('/payroll/' + currentHistPayrollId + '/payment-history', function (res) {
        if (!res.payments || !res.payments.length) {
            $('#histPayTableBody').html('<tr><td colspan="9" class="text-center py-4 text-muted">No payments yet.</td></tr>');
            return;
        }
        var html = '';
        res.payments.forEach(function (p, i) {
            html += `<tr id="hist-p-row-${p.id}" class="${p.is_void ? 'text-muted' : ''}">
                <td>${i+1}</td>
                <td>${p.payment_date}</td>
                <td class="text-right font-weight-bold ${p.is_void ? 'text-muted' : 'text-success'}">৳ ${p.amount}</td>
                <td><span class="badge badge-light border">${p.method}</span></td>
                <td>${p.transaction_no}</td>
                <td>${p.note}</td>
                <td>${p.created_by}</td>
                <td>${p.is_void
                    ? '<span class="badge badge-secondary">Void</span>'
                    : '<span class="badge badge-success">Active</span>'}</td>
                <td>${!p.is_void ? `
                    <button class="btn btn-xs btn-danger btn-void-payment"
                            data-id="${p.id}"
                            style="font-size:11px; padding:2px 8px;">
                        <i class="fas fa-ban mr-1"></i>Void
                    </button>` : '—'}</td>
            </tr>`;
        });
        $('#histPayTableBody').html(html);
        $('#histPayTotal').text('৳ ' + res.total);
        $('#histPayFoot').show();
    });
}

// ── Void Payment ──────────────────────────────────────────────
$(document).on('click', '.btn-void-payment', function () {
    var payId = $(this).data('id');

    $('#payHistoryModal').modal('hide');
    setTimeout(function () {
        Swal.fire({
            title: 'Void Payment?',
            html: `Payment void হবে।<br><strong class="text-success">Accounting Expense ও void হবে।</strong>`,
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
                url:    '/payroll/payment/' + payId + '/void',
                method: 'POST',
                data:   { _token: CSRF, reason: r.value },
                success: function (res) {
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 1500);
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Void failed.');
                }
            });
        });
    }, 400);
});

// ── Select All ────────────────────────────────────────────────
$('#selectAll').on('change', function () {
    $('.pay-check').prop('checked', this.checked);
    toggleBulkBtn();
});
$(document).on('change', '.pay-check', toggleBulkBtn);
function toggleBulkBtn() {
    $('#btnBulkDelete').prop('disabled', $('.pay-check:checked').length === 0);
}

// ── Bulk Delete ───────────────────────────────────────────────
$('#btnBulkDelete').on('click', function () {
    var ids = $('.pay-check:checked').map(function () { return $(this).val(); }).get();
    if (!ids.length) return;

    Swal.fire({
        title: 'Delete Selected?',
        html: `<strong>${ids.length}</strong> টি payroll delete হবে। Advance return হবে।`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
    }).then(function (r) {
        if (!r.isConfirmed) return;
        $.ajax({
            url:    '{{ route("payroll.bulk-delete") }}',
            method: 'POST',
            data:   { _token: CSRF, ids: ids },
            success: function (res) {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1500);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed.');
            }
        });
    });
});

// ── Pay ───────────────────────────────────────────────────────
var currentPayId  = null;
var currentPayDue = 0;

$(document).on('click', '.btn-pay-payroll', function () {
    currentPayId  = $(this).data('id');
    currentPayDue = parseFloat($(this).data('due'));
    $('#payEmployeeName').text($(this).data('name'));
    $('#payDueLabel').text('৳ ' + currentPayDue.toLocaleString('en-US', {minimumFractionDigits:2}));
    $('#payAmount').val('').attr('max', currentPayDue);
    $('#payTxNo, #payNote').val('');
    $('#payDate').val('{{ date("Y-m-d") }}');
    $('#payModal').modal('show');
});

$('#btnConfirmPay').off('click').on('click', function () {
    var amount = parseFloat($('#payAmount').val()) || 0;
    if (amount <= 0 || amount > currentPayDue) {
        toastr.error('Invalid amount.');
        return;
    }
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Processing...');

    $.ajax({
        url:    '/payroll/' + currentPayId + '/pay',
        method: 'POST',
        data: {
            _token:         CSRF,
            amount:         amount,
            payment_date:   $('#payDate').val(),
            payment_method: $('#payMethod').val(),
            transaction_no: $('#payTxNo').val(),
            note:           $('#payNote').val(),
        },
        success: function (res) {
            toastr.success(res.message);
            $('#payModal').modal('hide');
            setTimeout(() => location.reload(), 1500);
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Payment failed.');
            $('#btnConfirmPay').prop('disabled', false).html('<i class="fas fa-check mr-1"></i>Confirm Payment');
        }
    });
});

// ── Delete ────────────────────────────────────────────────────
$(document).on('click', '.btn-delete-payroll', function () {
    var id = $(this).data('id');
    Swal.fire({
        title: 'Delete Payroll?',
        text: 'Advance return হবে।',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete',
        reverseButtons: true,
    }).then(function (r) {
        if (!r.isConfirmed) return;
        $.ajax({
            url:    '/payroll/' + id,
            method: 'POST',
            data:   { _token: CSRF, _method: 'DELETE' },
            success: function (res) {
                toastr.success(res.message);
                $('#payroll-row-' + id).fadeOut();
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed.');
            }
        });
    });
});

</script>
@endpush
