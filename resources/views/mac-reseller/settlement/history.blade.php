@extends('adminlte::page')

@section('title', 'Settlement History')

@section('content_header')
    <div>
        <h1 class="m-0"><i class="fas fa-hand-holding-usd mr-1 text-info"></i> Settlement History</h1>
        <small class="text-muted">Record of payouts made to MAC Resellers for their PGW collections</small>
    </div>
@stop

@section('content')

{{-- Navigation --}}
<div class="mb-3 d-flex flex-wrap" style="gap:8px">
    <a href="{{ route('mac-reseller.settlement.index') }}" class="btn btn-outline-dark btn-sm">
        <i class="fas fa-list mr-1"></i> Settlement
    </a>
    <a href="{{ route('mac-reseller.settlement.pgw-transactions') }}" class="btn btn-outline-dark btn-sm">
        <i class="fas fa-exchange-alt mr-1"></i> PGW Transactions
    </a>
    <a href="{{ route('mac-reseller.settlement.history') }}" class="btn btn-dark btn-sm">
        <i class="fas fa-history mr-1"></i> Settlement History
    </a>
</div>

{{-- Summary — Customer-page style: label+value left, faded icon right --}}
<div class="row mb-3">
    <div class="col-6 col-md-4 mb-2">
        <div class="inc-stat-card" style="background:#0dcaf0">
            <div class="inc-stat-content">
                <span class="inc-stat-label">Total Received</span>
                <span class="inc-stat-value">৳{{ number_format($settlements->sum('total_received'), 2) }}</span>
            </div>
            <i class="fas fa-inbox inc-stat-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-2">
        <div class="inc-stat-card" style="background:#28a745">
            <div class="inc-stat-content">
                <span class="inc-stat-label">Total Settled</span>
                <span class="inc-stat-value">৳{{ number_format($settlements->sum('settled_amount'), 2) }}</span>
            </div>
            <i class="fas fa-check-circle inc-stat-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-2">
        <div class="inc-stat-card" style="background:#dc3545">
            <div class="inc-stat-content">
                <span class="inc-stat-label">Remaining</span>
                <span class="inc-stat-value">৳{{ number_format($settlements->sum('remaining_amount'), 2) }}</span>
            </div>
            <i class="fas fa-hourglass-half inc-stat-icon"></i>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header py-2 bg-light">
        <span class="font-weight-bold small text-uppercase text-muted"><i class="fas fa-list mr-1"></i> Settlement Records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-sm mb-0" style="font-size:12.5px">
            <thead class="bg-dark text-white">
                <tr>
                    <th>#</th>
                    <th>Reseller</th>
                    <th class="text-right">Total Received</th>
                    <th class="text-right">Settled Amount</th>
                    <th class="text-right">Remaining</th>
                    <th>Status</th>
                    <th>Settlement Date</th>
                    <th>Settled By</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($settlements as $i => $s)
                <tr>
                    <td>{{ $loop->iteration + ($settlements->currentPage() - 1) * $settlements->perPage() }}</td>
                    <td class="font-weight-bold">{{ $s->reseller?->business_name }}</td>
                    <td class="text-right">{{ number_format($s->total_received, 2) }}</td>
                    <td class="text-right text-success">{{ number_format($s->settled_amount, 2) }}</td>
                    <td class="text-right {{ $s->remaining_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">{{ number_format($s->remaining_amount, 2) }}</td>
                    <td>
                        @if($s->payment_status == 'settled')
                            <span class="badge badge-success">Settled</span>
                        @else
                            <span class="badge badge-warning">{{ ucfirst($s->payment_status) }}</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($s->settlement_date)->format('d M, Y') }}</td>
                    <td>{{ $s->settledBy?->name }}</td>
                    <td>{{ $s->remarks }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">No settlement records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $settlements->links() }}
    </div>
</div>

@stop

@section('css')
<style>
    .inc-stat-card {
        position: relative; border-radius: 10px; padding: 16px 18px; color: #fff;
        overflow: hidden; min-height: 80px; display: flex; align-items: center;
    }
    .inc-stat-content { position: relative; z-index: 2; display: flex; flex-direction: column; }
    .inc-stat-label { font-size: 12px; text-transform: uppercase; opacity: .85; letter-spacing: .3px; }
    .inc-stat-value { font-size: 22px; font-weight: 700; margin-top: 2px; }
    .inc-stat-icon {
        position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
        font-size: 46px; opacity: .25; z-index: 1;
    }
</style>
@stop
