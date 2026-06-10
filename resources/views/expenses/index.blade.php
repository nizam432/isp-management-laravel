{{-- resources/views/expenses/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Expenses')
@section('page_actions')
    <button type="button" class="btn btn-primary btn-sm" id="btnAddExpense">
        <i class="fas fa-plus mr-1"></i> Add Expense
    </button>
    <a href="{{ route('expenses.profit-loss') }}" class="btn btn-info btn-sm ml-1">
        <i class="fas fa-chart-pie mr-1"></i> P&L Report
    </a>
@endsection
@section('page_content')

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">This Month Expense</span>
                <span class="info-box-number">৳{{ number_format($totalThis) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-calendar-minus"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Last Month Expense</span>
                <span class="info-box-number">৳{{ number_format($totalLast) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-calendar-day"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Today's Expense</span>
                <span class="info-box-number">৳{{ number_format($todayTotal) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box {{ $pendingCount > 0 ? 'bg-orange' : 'bg-secondary' }}">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pending Approval</span>
                <span class="info-box-number">{{ $pendingCount }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Search</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Description / Payee / EXP-No"
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Category</label>
                        <select name="category_id" class="form-control form-control-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>Pending</option>
                            <option value="void"     {{ request('status') == 'void'     ? 'selected' : '' }}>Void</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Method</label>
                        <select name="payment_method" class="form-control form-control-sm">
                            <option value="">All Methods</option>
                            @foreach(['cash','bkash','nagad','rocket','bank','cheque','card'] as $m)
                                <option value="{{ $m }}" {{ request('payment_method') == $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end pb-2">
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-search"></i></button>
                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-redo"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i> Expense List
            <span class="badge badge-secondary ml-1">{{ $expenses->total() }}</span>
        </h3>
        <div class="ml-auto d-flex align-items-center">
            <label class="mr-2 mb-0 text-muted small">Show</label>
            <select class="form-control form-control-sm" style="width:80px;"
                    onchange="window.location.href='{{ request()->fullUrlWithQuery([]) }}&per_page='+this.value">
                @foreach([10,20,50,100] as $n)
                    <option value="{{ $n }}" {{ request('per_page',20)==$n?'selected':'' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:40px">#</th>
                    <th>Expense No</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Payee</th>
                    <th>Method</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th style="width:105px">Action</th>
                </tr>
            </thead>
            <tbody id="expenseTableBody">
                @forelse($expenses as $i => $exp)
                <tr id="expense-row-{{ $exp->id }}" class="{{ $exp->isVoid() ? 'text-muted' : '' }}">
                    <td>{{ $expenses->firstItem() + $i }}</td>
                    <td><a href="{{ route('expenses.show', $exp) }}"><code>{{ $exp->expense_no }}</code></a></td>
                    <td>{{ $exp->expense_date->format('d M Y') }}</td>
                    <td>
                        @if($exp->category)
                            <span class="badge" style="{{ $exp->category->badgeStyle }}">{{ $exp->category->name }}</span>
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td>{{ Str::limit($exp->description, 35) ?? '—' }}</td>
                    <td>{{ $exp->payee ?? '—' }}</td>
                    <td><span class="badge badge-light border">{{ strtoupper($exp->payment_method) }}</span></td>
                    <td class="text-right font-weight-bold {{ $exp->isVoid() ? '' : 'text-danger' }}">
                        ৳{{ number_format($exp->amount, 2) }}
                    </td>
                    <td>{!! $exp->statusBadge !!}</td>
                    <td>
                        <a href="{{ route('expenses.show', $exp) }}" class="btn btn-xs btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if(!$exp->isVoid())
                        <button class="btn btn-xs btn-warning btn-edit-expense"
                                data-id="{{ $exp->id }}"
                                data-url="{{ route('expenses.edit-data', $exp) }}"
                                data-update-url="{{ route('expenses.update', $exp) }}"
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-xs btn-danger btn-void-expense"
                                data-id="{{ $exp->id }}"
                                data-no="{{ $exp->expense_no }}"
                                data-url="{{ route('expenses.void', $exp) }}"
                                title="Void">
                            <i class="fas fa-ban"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr id="emptyRow">
                    <td colspan="10" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No expenses found.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($expenses->count() > 0)
            <tfoot>
                <tr class="font-weight-bold bg-light">
                    <td colspan="7" class="text-right">Page Total:</td>
                    <td class="text-right text-danger">৳{{ number_format($expenses->where('status','!=','void')->sum('amount'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="card-footer">{{ $expenses->withQueryString()->links() }}</div>
</div>

{{-- ════════════════════ ADD MODAL ════════════════════ --}}
<div class="modal fade" id="addExpenseModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-1"></i> Add New Expense</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pb-1">
                @include('expenses._form', ['formId' => 'addExpenseForm'])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveExpense">
                    <i class="fas fa-save mr-1"></i> Save Expense
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════ EDIT MODAL ════════════════════ --}}
<div class="modal fade" id="editExpenseModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-1"></i> Expense Edit —
                    <code id="editExpenseNo"></code>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pb-1">
                <div id="editModalLoader" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2">Loading...</p>
                </div>
                <div id="editModalContent" style="display:none;">
                    @include('expenses._form', ['formId' => 'editExpenseForm'])
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-warning btn-sm" id="btnUpdateExpense">
                    <i class="fas fa-save mr-1"></i> Update Expense
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════ VOID MODAL ════════════════════ --}}
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-ban mr-1"></i> Void Expense</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-2 text-muted small"><code id="voidExpenseNo"></code> will be voided and excluded from reports.</p>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Reason <span class="text-danger">*</span></label>
                    <textarea id="voidReason" rows="3" class="form-control form-control-sm"
                              placeholder="Enter reason for voiding..."></textarea>
                    <div class="invalid-feedback" id="voidReasonError"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" id="btnConfirmVoid">
                    <i class="fas fa-ban mr-1"></i> Confirm Void
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
const CSRF = '{{ csrf_token() }}';

// ── helpers ───────────────────────────────────────────────────
function clearErrors(formId) {
    $('#' + formId + ' .is-invalid').removeClass('is-invalid');
    $('#' + formId + ' .invalid-feedback').text('');
}

function showErrors(formId, errors) {
    $.each(errors, function (field, msgs) {
        var el = $('#' + formId + ' [name="' + field + '"]');
        el.addClass('is-invalid');
        el.closest('.form-group').find('.invalid-feedback').text(msgs[0]);
    });
}

// ── ADD ───────────────────────────────────────────────────────
$('#btnAddExpense').on('click', function () {
    clearErrors('addExpenseForm');
    document.getElementById('addExpenseForm').reset();
    $('#addExpenseForm [name="expense_date"]').val('{{ date("Y-m-d") }}');
    $('#addExpenseModal').modal('show');
});

$('#btnSaveExpense').on('click', function () {
    clearErrors('addExpenseForm');
    var btn = $(this).prop('disabled', true)
                     .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    var fd = new FormData(document.getElementById('addExpenseForm'));
    fd.append('_token', CSRF);

    $.ajax({
        url: '{{ route("expenses.store") }}',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            $('#addExpenseModal').modal('hide');
            toastr.success(res.message);
            prependRow(res.expense);
            $('#emptyRow').remove();
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                showErrors('addExpenseForm', xhr.responseJSON.errors || {});
                toastr.error('Please fill in all fields correctly.');
            } else {
                toastr.error('Something went wrong. Please try again.');
            }
        },
        complete: function () {
            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Expense');
        }
    });
});

// ── EDIT ──────────────────────────────────────────────────────
$(document).on('click', '.btn-edit-expense', function () {
    var editDataUrl = $(this).data('url');
    var updateUrl   = $(this).data('update-url');

    $('#editExpenseNo').text('...');
    $('#editModalLoader').show();
    $('#editModalContent').hide();
    $('#editExpenseModal').data('update-url', updateUrl).modal('show');
    clearErrors('editExpenseForm');

    $.get(editDataUrl, function (res) {
        if (!res.success) { toastr.error(res.message); return; }
        var e = res.expense;
        var f = '#editExpenseForm ';
        $('#editExpenseNo').text(e.expense_no);
        $(f + '[name="expense_date"]').val(e.expense_date);
        $(f + '[name="category_id"]').val(e.category_id);
        $(f + '[name="amount"]').val(e.amount);
        $(f + '[name="payment_method"]').val(e.payment_method);
        $(f + '[name="transaction_id"]').val(e.transaction_id || '');
        $(f + '[name="reference_no"]').val(e.reference_no || '');
        $(f + '[name="payee"]').val(e.payee || '');
        $(f + '[name="description"]').val(e.description || '');

        if (e.receipt_url) {
            $('#editCurrentReceipt').attr('href', e.receipt_url);
            $('#editCurrentReceiptWrap').show();
        } else {
            $('#editCurrentReceiptWrap').hide();
        }

        $('#editModalLoader').hide();
        $('#editModalContent').show();
    }).fail(function () {
        $('#editExpenseModal').modal('hide');
        toastr.error('Could not load expense data.');
    });
});

$('#btnUpdateExpense').on('click', function () {
    clearErrors('editExpenseForm');
    var btn       = $(this).prop('disabled', true)
                           .html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
    var updateUrl = $('#editExpenseModal').data('update-url');

    var fd = new FormData(document.getElementById('editExpenseForm'));
    fd.append('_token', CSRF);
    fd.append('_method', 'PUT');

    $.ajax({
        url: updateUrl,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            $('#editExpenseModal').modal('hide');
            toastr.success(res.message);
            updateRow(res.expense);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                showErrors('editExpenseForm', xhr.responseJSON.errors || {});
                toastr.error('Please fill in all fields correctly.');
            } else {
                toastr.error('Update failed. Please try again.');
            }
        },
        complete: function () {
            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Expense');
        }
    });
});

// ── VOID ──────────────────────────────────────────────────────
var voidUrl = null, voidId = null;

$(document).on('click', '.btn-void-expense', function () {
    voidUrl = $(this).data('url');
    voidId  = $(this).data('id');
    $('#voidExpenseNo').text($(this).data('no'));
    $('#voidReason').val('').removeClass('is-invalid');
    $('#voidReasonError').text('').hide();
    $('#voidModal').modal('show');
});

$('#btnConfirmVoid').on('click', function () {
    var reason = $('#voidReason').val().trim();
    if (!reason) {
        $('#voidReason').addClass('is-invalid');
        $('#voidReasonError').text('Please enter a reason.').show();
        return;
    }
    var btn = $(this).prop('disabled', true)
                     .html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

    $.post(voidUrl, { _token: CSRF, reason: reason }, function (res) {
        $('#voidModal').modal('hide');
        toastr.warning(res.message);
        var row = $('#expense-row-' + voidId);
        row.addClass('text-muted');
        row.find('.btn-warning.btn-edit-expense, .btn-danger.btn-void-expense').remove();
        row.find('td:nth-child(9)').html('<span class="badge badge-secondary">Void</span>');
    }).fail(function (xhr) {
        toastr.error(xhr.responseJSON?.message || 'Could not void this expense.');
    }).always(function () {
        btn.prop('disabled', false).html('<i class="fas fa-ban mr-1"></i> Confirm Void');
    });
});

// ── DOM helpers ───────────────────────────────────────────────
function prependRow(e) {
    $('#expenseTableBody').prepend(`
        <tr id="expense-row-${e.id}">
            <td>—</td>
            <td><a href="${e.show_url}"><code>${e.expense_no}</code></a></td>
            <td>${e.expense_date}</td>
            <td><span class="badge" style="${e.category_style}">${e.category_name}</span></td>
            <td>${e.description}</td>
            <td>${e.payee}</td>
            <td><span class="badge badge-light border">${e.payment_method}</span></td>
            <td class="text-right font-weight-bold text-danger">৳${e.amount}</td>
            <td>${e.status_badge}</td>
            <td>
                <a href="${e.show_url}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
            </td>
        </tr>`);
}

function updateRow(e) {
    var row = $('#expense-row-' + e.id);
    if (!row.length) return;
    row.find('td:eq(1)').html('<a href="' + e.show_url + '"><code>' + e.expense_no + '</code></a>');
    row.find('td:eq(2)').text(e.expense_date);
    row.find('td:eq(3)').html('<span class="badge" style="' + e.category_style + '">' + e.category_name + '</span>');
    row.find('td:eq(4)').text(e.description);
    row.find('td:eq(5)').text(e.payee);
    row.find('td:eq(6)').html('<span class="badge badge-light border">' + e.payment_method + '</span>');
    row.find('td:eq(7)').html('৳' + e.amount);
    row.find('td:eq(8)').html(e.status_badge);
}
</script>
@endpush

@endsection
