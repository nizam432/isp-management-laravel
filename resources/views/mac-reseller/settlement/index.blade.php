{{-- resources/views/mac-reseller/settlement/index.blade.php --}}
@extends('adminlte::page')
@section('title', 'PGW Transaction Settlement')
@section('content_header')
    <h1 class="m-0">POPs <small class="text-muted">All POPs</small></h1>
@stop
@section('content')
<div class="mb-3 d-flex" style="gap:8px">
    <a href="{{ route('mac-reseller.settlement.pgw-transactions') }}" class="btn btn-dark btn-sm">
        <i class="fas fa-list"></i> POP PGW Transactions
    </a>
    <a href="{{ route('mac-reseller.settlement.history') }}" class="btn btn-outline-dark btn-sm">
        <i class="fas fa-history"></i> Transaction Settlement History
    </a>
    <a href="{{ route('mac-reseller.settlement.index') }}" class="btn btn-outline-dark btn-sm">
        <i class="fas fa-exchange-alt"></i> POP Transactions
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label class="small">POP STATUS</label>
                    <select name="pop_status" class="form-control form-control-sm">
                        <option value="">Select</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small">POP TYPE</label>
                    <select name="pop_type" class="form-control form-control-sm">
                        <option value="">Select</option>
                        <option value="prepaid">Prepaid</option>
                        <option value="postpaid">Postpaid</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100 mb-0">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
        <table class="table table-bordered table-sm" style="font-size:12px">
            <thead class="bg-dark text-white">
                <tr>
                    <th>Code</th>
                    <th>POP Name</th>
                    <th>POP Type</th>
                    <th>Mobile</th>
                    <th>Total Received Amount</th>
                    <th>Settled Amount</th>
                    <th>Remaining Amount</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($resellers as $r)
                @php
                    $totalRec   = $r->total_received ?? 0;
                    $settled    = $r->pgwSettlements->sum('settled_amount');
                    $remaining  = $totalRec - $settled;
                    $status     = $totalRec > 0 ? ($remaining <= 0 ? 'settled' : 'pending') : 'no_transaction';
                @endphp
                <tr>
                    <td>{{ $r->code }}</td>
                    <td>{{ $r->business_name }}</td>
                    <td>
                        <span class="badge badge-{{ $r->pop_type == 'prepaid' ? 'info' : 'warning' }}">
                            {{ ucfirst($r->pop_type) }}
                        </span>
                    </td>
                    <td>{{ $r->mobile }}</td>
                    <td>{{ number_format($totalRec, 2) }}</td>
                    <td>{{ number_format($settled, 2) }}</td>
                    <td>{{ number_format($remaining, 2) }}</td>
                    <td>
                        @if($status == 'settled')
                            <span class="badge badge-success px-3 py-2">Settled</span>
                        @elseif($status == 'pending')
                            <button class="btn btn-sm btn-warning settle-btn" data-id="{{ $r->id }}">Settle</button>
                        @else
                            <span class="badge badge-secondary px-2 py-1">No transaction Available</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        {{ $resellers->links() }}
    </div>
</div>
@stop
@section('js')
<script>
$(document).on('click', '.settle-btn', function() {
    const id = $(this).data('id');
    const amount = prompt('Enter settlement amount:');
    if (!amount) return;
    $.post(`/mac-reseller/settlement/${id}/settle`, {
        _token: '{{ csrf_token() }}', amount
    }, (res) => { if (res.success) { toastr.success(res.message); location.reload(); } });
});
</script>
@stop
