@extends('adminlte::page')

@section('title', 'Fund Received History')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-history mr-1 text-info"></i> Fund Received History
            </h1>
        </div>
    </div>
@stop

@section('content')



<div class="card">
    <div class="card-body">
        {{-- Filters --}}
        <form method="GET" action="{{ route('mac-reseller.funding.history') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small">MAC RESELLER</label>
                        <select name="reseller_id" class="form-control form-control-sm">
                            <option value="">All</option>
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
                        <label class="small">STATUS</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="void" {{ request('status') == 'void' ? 'selected' : '' }}>Void</option>
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
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a href="{{ route('mac-reseller.funding.history') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-redo mr-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Summary --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Active Received (this page)</span>
                        <span class="info-box-number">৳{{ number_format($payments->where('status', 'active')->sum('amount'), 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary"><i class="fas fa-ban"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Voided (this page)</span>
                        <span class="info-box-number">৳{{ number_format($payments->where('status', 'void')->sum('amount'), 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Entries (this page)</span>
                        <span class="info-box-number">{{ $payments->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
        <table class="table table-bordered table-sm" style="font-size:12px">
            <thead class="bg-dark text-white">
                <tr>
                    <th>#</th>
                    <th>Reseller</th>
                    <th>Invoice No.</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Received By</th>
                    <th>Received Date</th>
                    <th>Remarks</th>
                    <th>Status</th>
                    <th>Void Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr class="{{ $p->isVoid() ? 'table-secondary text-muted' : '' }}">
                    <td>{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                    <td>{{ $p->funding->reseller?->business_name }}</td>
                    <td>{{ $p->funding->invoice_number }}</td>
                    <td>{{ number_format($p->amount, 2) }}</td>
                    <td><span class="badge badge-light text-uppercase">{{ $p->payment_method }}</span></td>
                    <td>{{ $p->receivedBy?->name }}</td>
                    <td>{{ $p->received_date?->format('d/m/Y') }}</td>
                    <td>{{ $p->remarks }}</td>
                    <td>
                        @if($p->isVoid())
                            <span class="badge badge-secondary">Void</span>
                        @else
                            <span class="badge badge-success">Active</span>
                        @endif
                    </td>
                    <td>
                        @if($p->isVoid())
                            <small class="text-danger">{{ $p->void_reason }}</small>
                            <br><small class="text-muted">by {{ $p->voidBy?->name }} on {{ $p->void_date?->format('d/m/Y H:i') }}</small>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if(!$p->isVoid())
                            <button class="btn btn-sm btn-danger void-btn" data-id="{{ $p->id }}">
                                <i class="fas fa-ban mr-1"></i> Void
                            </button>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center">No payment history found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        {{ $payments->links() }}
    </div>
</div>

{{-- Void Payment Modal --}}
<div class="modal fade" id="voidPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="fas fa-ban mr-1"></i> Void Payment</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="voidPaymentForm">
                    @csrf
                    <input type="hidden" name="payment_id" id="vp_payment_id">
                    <p class="text-muted small">
                        Voiding this payment will reverse its linked Income entry and reduce the
                        reseller's fund balance accordingly. This cannot be undone.
                    </p>
                    <div class="form-group">
                        <label class="small font-weight-bold">VOID REASON <span class="text-danger">*</span></label>
                        <textarea name="void_reason" class="form-control" rows="3" required
                                  placeholder="e.g. Entered by mistake, duplicate entry, customer refund..."></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fas fa-ban mr-1"></i> Confirm Void
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
$(document).on('click', '.void-btn', function() {
    $('#vp_payment_id').val($(this).data('id'));
    $('#voidPaymentModal').modal('show');
});

$('#voidPaymentForm').on('submit', function(e) {
    e.preventDefault();
    const id = $('#vp_payment_id').val();
    $.ajax({
        url: `/mac-reseller/funding/payment/${id}/void`,
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if (res.success) {
                $('#voidPaymentModal').modal('hide');
                toastr.success('Payment voided.');
                setTimeout(() => location.reload(), 800);
            } else {
                toastr.error(res.message || 'Void failed.');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) toastr.error(Object.values(errors).flat().join('\n'));
            else toastr.error(xhr.responseJSON?.message || 'Void failed.');
        }
    });
});
</script>
@stop
