@extends('reseller.layouts.app')

@section('title', 'Payment History')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Payment History</h4>
    <a href="{{ route('reseller.payment.collect') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Collect Payment
    </a>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="card text-center py-3" style="border-left:4px solid #00a65a;">
            <div class="text-success font-weight-bold" style="font-size:24px;">৳{{ number_format($totalThisMonth) }}</div>
            <small class="text-muted">This Month</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-center py-3" style="border-left:4px solid #17a2b8;">
            <div class="text-info font-weight-bold" style="font-size:24px;">৳{{ number_format($totalAllTime) }}</div>
            <small class="text-muted">All Time</small>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Client Name / Phone" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="method" class="form-control form-control-sm">
                        <option value="">All Methods</option>
                        @foreach(['cash','bkash','nagad','rocket','card','bank'] as $m)
                            <option value="{{ $m }}" {{ request('method') == $m ? 'selected' : '' }}>{{ ucfirst($m) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="reseller_employee_id" class="form-control form-control-sm">
                        <option value="">All Staff</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('reseller_employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr><th>Date</th><th>Client</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Received By</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                @forelse($payments as $pay)
                <tr>
                    <td><small>{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') : '' }}</small></td>
                    <td>
                        <small>{{ $pay->customer->name ?? '—' }}</small>
                        <br><small class="text-muted">{{ $pay->customer->phone ?? '' }}</small>
                    </td>
                    <td><small><code>{{ $pay->invoice->invoice_no ?? '—' }}</code></small></td>
                    <td><small class="text-success font-weight-bold">৳{{ number_format($pay->amount) }}</small></td>
                    <td><span class="badge badge-secondary">{{ ucfirst($pay->method) }}</span></td>
                    <td><small>{{ $pay->receivedByReseller->name ?? '—' }}</small></td>
                    <td>
                        @if($pay->isVoid())
                            <span class="badge badge-danger">Voided</span>
                        @else
                            <span class="badge badge-success">Active</span>
                        @endif
                    </td>
                    <td>
                        @if(!$pay->isVoid())
                        <button type="button" class="btn btn-xs btn-outline-danger void-btn" data-id="{{ $pay->id }}" title="Void">
                            <i class="fas fa-undo"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $payments->links() }}</div>
</div>

{{-- Void Modal --}}
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="voidForm" method="POST">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title">Void Payment</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label class="small">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control form-control-sm" rows="2" required></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Void Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).on('click', '.void-btn', function () {
    var id = $(this).data('id');
    $('#voidForm').attr('action', "{{ url('reseller/payment') }}/" + id + "/void");
    $('#voidModal').modal('show');
});
</script>
@endsection