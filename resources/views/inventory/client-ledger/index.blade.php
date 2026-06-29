@extends('adminlte::page')
@section('title', 'Client Ledger')

@section('content_header')
    <div>
        <h4 class="mb-0 font-weight-bold text-dark">
            <i class="fas fa-users mr-2 text-primary"></i>Client Ledger
        </h4>
        <small class="text-muted">Product sale due by customer</small>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4">
                <label class="small font-weight-bold">Search Client</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search client name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-3 mr-2">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('inventory.client-ledger.index') }}" class="btn btn-secondary btn-sm px-3">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Client Due List
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Client</th>
                        <th style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Phone</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Total Sale</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Total Paid</th>
                        <th class="text-right" style="font-size:12px;font-weight:700;text-transform:uppercase;color:#555;padding:10px 12px;">Due</th>
                        <th class="text-center" style="width:70px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                    @php $due = ($client->total_credit ?? 0) - ($client->total_debit ?? 0); @endphp
                    <tr>
                        <td style="padding:10px 12px;">
                            <span class="font-weight-bold">{{ $client->name }}</span>
                            <br><small class="text-muted">{{ $client->customer_code }}</small>
                        </td>
                        <td style="padding:10px 12px;" class="text-muted">{{ $client->phone }}</td>
                        <td style="padding:10px 12px;" class="text-right">৳{{ number_format($client->total_credit ?? 0, 2) }}</td>
                        <td style="padding:10px 12px;" class="text-right text-success">৳{{ number_format($client->total_debit ?? 0, 2) }}</td>
                        <td style="padding:10px 12px;" class="text-right {{ $due > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                            ৳{{ number_format($due, 2) }}
                        </td>
                        <td style="padding:10px 12px;" class="text-center">
                            <a href="{{ route('inventory.client-ledger.show', $client) }}"
                               class="btn btn-sm btn-info px-2" title="View Ledger">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No client ledger found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($clients->hasPages())
    <div class="card-footer bg-light py-2">{{ $clients->links() }}</div>
    @endif
</div>

@endsection

@section('css')
<style>
    .table tbody td { vertical-align: middle; }
    .table tbody tr:hover { background:#f0f4ff !important; }
</style>
@stop
