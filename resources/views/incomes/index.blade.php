{{-- resources/views/incomes/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Income')
@section('page_actions')
    <button type="button" class="btn btn-success btn-sm" id="btnAddIncome">
        <i class="fas fa-plus mr-1"></i> Add Income
    </button>
    <a href="{{ route('expenses.profit-loss') }}" class="btn btn-info btn-sm ml-1">
        <i class="fas fa-chart-pie mr-1"></i> P&L Report
    </a>
@endsection
@section('page_content')

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">This Month (Manual)</span>
                <span class="info-box-number">BDT {{ number_format($totalThis) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-primary">
            <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Monthly Bill (Payments)</span>
                <span class="info-box-number">BDT {{ number_format($monthlyBillThis) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-calendar-minus"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Last Month (Manual)</span>
                <span class="info-box-number">BDT {{ number_format($totalLast) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-calendar-day"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Today</span>
                <span class="info-box-number">BDT {{ number_format($todayTotal) }}</span>
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
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Description / Payer / INC-No"
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Category</label>
                        <select name="category_id" class="form-control form-control-sm">
                            <option value="">All Categories</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}"
                                        {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="void"   {{ request('status') == 'void'   ? 'selected' : '' }}>Void</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Method</label>
                        <select name="payment_method" class="form-control form-control-sm">
                            <option value="">All Methods</option>
                            @foreach(['cash','bkash','nagad','rocket','bank','cheque','card'] as $m)
                                <option value="{{ $m }}"
                                        {{ request('payment_method') == $m ? 'selected' : '' }}>
                                    {{ strtoupper($m) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                               value="{{ request('date_from') }}">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                               value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end pb-2">
                    <button type="submit" class="btn btn-primary btn-sm mr-1">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('incomes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i> Income List
            <span class="badge badge-secondary ml-1">{{ $incomes->total() }}</span>
        </h3>
        <div class="ml-auto d-flex align-items-center">
            <label class="mr-2 mb-0 text-muted small">Show</label>
            <select class="form-control form-control-sm" style="width:80px;"
                    onchange="window.location.href='{{ request()->fullUrlWithQuery([]) }}&per_page='+this.value">
                @foreach([10,20,50,100] as $n)
                    <option value="{{ $n }}" {{ request('per_page',20)==$n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:40px">#</th>
                    <th>Income No</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Payer</th>
                    <th>Method</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th style="width:105px">Action</th>
                </tr>
            </thead>
            <tbody id="incomeTableBody">
                @forelse($incomes as $i => $inc)
                <tr id="income-row-{{ $inc->id }}" class="{{ $inc->isVoid() ? 'text-muted' : '' }}">
                    <td>{{ $incomes->firstItem() + $i }}</td>
                    <td>
                        <a href="{{ route('incomes.show', $inc) }}">
                            <code>{{ $inc->income_no }}</code>
                        </a>
                    </td>
                    <td>{{ $inc->income_date->format('d M Y') }}</td>
                    <td>
                        @if($inc->category)
                            <span class="badge" style="{{ $inc->category->badgeStyle }}">
                                {{ $inc->category->name }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ Str::limit($inc->description, 35) ?? '—' }}</td>
                    <td>{{ $inc->payer ?? ($inc->customer?->name ?? '—') }}</td>
                    <td>
                        <span class="badge badge-light border">
                            {{ strtoupper($inc->payment_method) }}
                        </span>
                    </td>
                    <td class="text-right font-weight-bold {{ $inc->isVoid() ? '' : 'text-success' }}">
                        BDT {{ number_format($inc->amount, 2) }}
                    </td>
                    <td>{!! $inc->statusBadge !!}</td>
                    <td>
                        <a href="{{ route('incomes.show', $inc) }}"
                           class="btn btn-xs btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if(!$inc->isVoid())
                        <button class="btn btn-xs btn-warning btn-edit-income"
                                data-id="{{ $inc->id }}"
                                data-url="{{ route('incomes.edit-data', $inc) }}"
                                data-update-url="{{ route('incomes.update', $inc) }}"
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-xs btn-danger btn-void-income"
                                data-id="{{ $inc->id }}"
                                data-no="{{ $inc->income_no }}"
                                data-url="{{ route('incomes.void', $inc) }}"
                                title="Void">
                            <i class="fas fa-ban"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr id="incomeEmptyRow">
                    <td colspan="10" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No income records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($incomes->count() > 0)
            <tfoot>
                <tr class="font-weight-bold bg-light">
                    <td colspan="7" class="text-right">Page Total:</td>
                    <td class="text-right text-success">
                        BDT {{ number_format($incomes->where('status', 'active')->sum('amount'), 2) }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="card-footer">
        {{ $incomes->withQueryString()->links() }}
    </div>
</div>

{{-- ═══════════════ ADD MODAL ═══════════════ --}}
<div class="modal fade" id="addIncomeModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-1"></i> Add New Income
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pb-1">
                @include('incomes._form', ['formId' => 'addIncomeForm'])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-success btn-sm" id="btnSaveIncome">
                    <i class="fas fa-save mr-1"></i> Save Income
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════ EDIT MODAL ═══════════════ --}}
<div class="modal fade" id="editIncomeModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-1"></i> Edit Income —
                    <code id="editIncomeNo"></code>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pb-1">
                <div id="editIncomeLoader" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2">Loading...</p>
                </div>
                <div id="editIncomeContent" style="display:none;">
                    @include('incomes._form', ['formId' => 'editIncomeForm'])
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-warning btn-sm" id="btnUpdateIncome">
                    <i class="fas fa-save mr-1"></i> Update Income
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════ VOID MODAL ═══════════════ --}}
<div class="modal fade" id="voidIncomeModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-ban mr-1"></i> Void Income
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-2 text-muted small">
                    <code id="voidIncomeNo"></code> will be voided and excluded from reports.
                </p>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">
                        Reason <span class="text-danger">*</span>
                    </label>
                    <textarea id="voidIncomeReason" rows="3"
                              class="form-control form-control-sm"
                              placeholder="Enter reason for voiding..."></textarea>
                    <div class="invalid-feedback" id="voidIncomeReasonError"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btnConfirmVoidIncome">
                    <i class="fas fa-ban mr-1"></i> Confirm Void
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
const CSRF = '{{ csrf_token() }}';

// ── Helpers ───────────────────────────────────────────────────
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
$('#btnAddIncome').on('click', function () {
    clearErrors('addIncomeForm');
    document.getElementById('addIncomeForm').reset();
    $('#addIncomeForm [name="income_date"]').val('{{ date("Y-m-d") }}');
    $('#addIncomeModal').modal('show');
});

$('#btnSaveIncome').on('click', function () {
    clearErrors('addIncomeForm');
    var btn = $(this).prop('disabled', true)
                     .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    var fd = new FormData(document.getElementById('addIncomeForm'));
    fd.append('_token', CSRF);

    $.ajax({
        url:         '{{ route("incomes.store") }}',
        method:      'POST',
        data:        fd,
        processData: false,
        contentType: false,
        success: function (res) {
            $('#addIncomeModal').modal('hide');
            toastr.success(res.message);
            prependRow(res.income);
            $('#incomeEmptyRow').remove();
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                showErrors('addIncomeForm', xhr.responseJSON.errors || {});
                toastr.error(xhr.responseJSON.message || 'Please fill in all fields correctly.');
            } else {
                toastr.error('Something went wrong. Please try again.');
            }
        },
        complete: function () {
            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Income');
        }
    });
});

// ── EDIT ──────────────────────────────────────────────────────
$(document).on('click', '.btn-edit-income', function () {
    var editDataUrl = $(this).data('url');
    var updateUrl   = $(this).data('update-url');

    $('#editIncomeNo').text('...');
    $('#editIncomeLoader').show();
    $('#editIncomeContent').hide();
    $('#editIncomeModal').data('update-url', updateUrl).modal('show');
    clearErrors('editIncomeForm');

    $.get(editDataUrl, function (res) {
        if (!res.success) { toastr.error(res.message); return; }
        var e = res.income;
        var f = '#editIncomeForm ';

        $('#editIncomeNo').text(e.income_no);
        $(f + '[name="income_date"]').val(e.income_date);
        $(f + '[name="category_id"]').val(e.category_id);
        $(f + '[name="amount"]').val(e.amount);
        $(f + '[name="payment_method"]').val(e.payment_method);
        $(f + '[name="transaction_id"]').val(e.transaction_id || '');
        $(f + '[name="reference_no"]').val(e.reference_no || '');
        $(f + '[name="customer_id"]').val(e.customer_id || '');
        $(f + '[name="payer"]').val(e.payer || '');
        $(f + '[name="description"]').val(e.description || '');

        if (e.receipt_url) {
            $('#editIncomeCurrentReceipt').attr('href', e.receipt_url);
            $('#editIncomeReceiptWrap').show();
        } else {
            $('#editIncomeReceiptWrap').hide();
        }

        $('#editIncomeLoader').hide();
        $('#editIncomeContent').show();
    }).fail(function () {
        $('#editIncomeModal').modal('hide');
        toastr.error('Could not load income data.');
    });
});

$('#btnUpdateIncome').on('click', function () {
    clearErrors('editIncomeForm');
    var btn       = $(this).prop('disabled', true)
                           .html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
    var updateUrl = $('#editIncomeModal').data('update-url');

    var fd = new FormData(document.getElementById('editIncomeForm'));
    fd.append('_token', CSRF);
    fd.append('_method', 'PUT');

    $.ajax({
        url:         updateUrl,
        method:      'POST',
        data:        fd,
        processData: false,
        contentType: false,
        success: function (res) {
            $('#editIncomeModal').modal('hide');
            toastr.success(res.message);
            updateRow(res.income);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                showErrors('editIncomeForm', xhr.responseJSON.errors || {});
                toastr.error(xhr.responseJSON.message || 'Please fill in all fields correctly.');
            } else {
                toastr.error('Update failed. Please try again.');
            }
        },
        complete: function () {
            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Income');
        }
    });
});

// ── VOID ──────────────────────────────────────────────────────
var voidUrl = null, voidId = null;

$(document).on('click', '.btn-void-income', function () {
    voidUrl = $(this).data('url');
    voidId  = $(this).data('id');
    $('#voidIncomeNo').text($(this).data('no'));
    $('#voidIncomeReason').val('').removeClass('is-invalid');
    $('#voidIncomeReasonError').text('').hide();
    $('#voidIncomeModal').modal('show');
});

$('#btnConfirmVoidIncome').on('click', function () {
    var reason = $('#voidIncomeReason').val().trim();
    if (!reason) {
        $('#voidIncomeReason').addClass('is-invalid');
        $('#voidIncomeReasonError').text('Please enter a reason.').show();
        return;
    }

    var btn = $(this).prop('disabled', true)
                     .html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

    $.post(voidUrl, { _token: CSRF, reason: reason }, function (res) {
        $('#voidIncomeModal').modal('hide');
        toastr.warning(res.message);
        var row = $('#income-row-' + voidId);
        row.addClass('text-muted');
        row.find('.btn-warning.btn-edit-income, .btn-danger.btn-void-income').remove();
        row.find('td:nth-child(9)').html('<span class="badge badge-secondary">Void</span>');
    }).fail(function (xhr) {
        toastr.error(xhr.responseJSON?.message || 'Could not void this income.');
    }).always(function () {
        btn.prop('disabled', false).html('<i class="fas fa-ban mr-1"></i> Confirm Void');
    });
});

// ── DOM Helpers ───────────────────────────────────────────────
function prependRow(e) {
    $('#incomeTableBody').prepend(`
        <tr id="income-row-${e.id}">
            <td>—</td>
            <td><a href="${e.show_url}"><code>${e.income_no}</code></a></td>
            <td>${e.income_date}</td>
            <td><span class="badge" style="${e.category_style}">${e.category_name}</span></td>
            <td>${e.description}</td>
            <td>${e.payer}</td>
            <td><span class="badge badge-light border">${e.payment_method}</span></td>
            <td class="text-right font-weight-bold text-success">BDT ${e.amount}</td>
            <td>${e.status_badge}</td>
            <td>
                <a href="${e.show_url}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                <button class="btn btn-xs btn-warning btn-edit-income"
                        data-id="${e.id}" data-url="${e.edit_data_url}"
                        data-update-url="${e.update_url}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-xs btn-danger btn-void-income"
                        data-id="${e.id}" data-no="${e.income_no}"
                        data-url="${e.void_url}">
                    <i class="fas fa-ban"></i>
                </button>
            </td>
        </tr>`);
}

function updateRow(e) {
    var row = $('#income-row-' + e.id);
    if (!row.length) return;
    row.find('td:eq(1)').html('<a href="' + e.show_url + '"><code>' + e.income_no + '</code></a>');
    row.find('td:eq(2)').text(e.income_date);
    row.find('td:eq(3)').html('<span class="badge" style="' + e.category_style + '">' + e.category_name + '</span>');
    row.find('td:eq(4)').text(e.description);
    row.find('td:eq(5)').text(e.payer);
    row.find('td:eq(6)').html('<span class="badge badge-light border">' + e.payment_method + '</span>');
    row.find('td:eq(7)').html('BDT ' + e.amount);
    row.find('td:eq(8)').html(e.status_badge);
}
</script>
@endpush

@endsection
