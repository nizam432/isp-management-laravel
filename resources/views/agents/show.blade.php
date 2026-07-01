@extends('adminlte::page')
@section('title', 'Agent — ' . $agent->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-user-tie mr-2 text-primary"></i>{{ $agent->name }}
            </h4>
            <small class="text-muted">Agent Detail</small>
        </div>
        <div>
            @can('agent.edit')
            <a href="{{ route('agents.edit', $agent) }}" class="btn btn-warning btn-sm mr-1">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('agents.toggle', $agent) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm {{ $agent->is_active ? 'btn-secondary' : 'btn-success' }} mr-1"
                        onclick="return confirm('{{ $agent->is_active ? 'Deactivate' : 'Activate' }} this agent?')">
                    <i class="fas fa-{{ $agent->is_active ? 'ban' : 'check-circle' }} mr-1"></i>
                    {{ $agent->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            @endcan
            <a href="{{ route('agents.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible shadow-sm">
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible shadow-sm">
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
</div>
@endif

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Customers</span>
                <span class="info-box-number">{{ $agent->customers->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Commission</span>
                <span class="info-box-number" style="font-size:16px;">৳{{ number_format($totalCommission, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pending</span>
                <span class="info-box-number" style="font-size:16px;">৳{{ number_format($pendingCommission, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-info"><i class="fas fa-check-double"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Paid Out</span>
                <span class="info-box-number" style="font-size:16px;">৳{{ number_format($paidCommission, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Agent Info --}}
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold"><i class="fas fa-id-card mr-1"></i> Agent Info</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="pl-3 text-muted small font-weight-bold" style="width:40%;">Name</td>
                            <td class="font-weight-bold">{{ $agent->name }}</td>
                        </tr>
                        <tr>
                            <td class="pl-3 text-muted small font-weight-bold">Phone</td>
                            <td>{{ $agent->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="pl-3 text-muted small font-weight-bold">Area</td>
                            <td>
                                @if($agent->area)
                                <span class="badge badge-light border">{{ $agent->area }}</span>
                                @else —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="pl-3 text-muted small font-weight-bold">Commission</td>
                            <td class="font-weight-bold text-primary">{{ number_format($agent->commission_rate, 2) }}%</td>
                        </tr>
                        <tr>
                            <td class="pl-3 text-muted small font-weight-bold">Status</td>
                            <td>
                                @if($agent->is_active)
                                <span class="badge badge-success">Active</span>
                                @else
                                <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="pl-3 text-muted small font-weight-bold">Joined</td>
                            <td class="small text-muted">{{ $agent->created_at->format('d M Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Login Account --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2 bg-light">
                <h6 class="m-0 font-weight-bold small"><i class="fas fa-user-shield mr-1 text-muted"></i> Login Account</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="pl-3 text-muted small font-weight-bold" style="width:40%;">Email</td>
                            <td class="small">{{ $agent->user?->email ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="pl-3 text-muted small font-weight-bold">Last Login</td>
                            <td class="small text-muted">{{ $agent->user?->last_login_at?->format('d M Y H:i') ?? 'Never' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pay Commission --}}
        @can('agent.edit')
        @if($pendingCommission > 0)
        <div class="card shadow-sm border-warning">
            <div class="card-header py-2" style="background:#fff3cd; border-bottom:1px solid #ffeaa7;">
                <h6 class="m-0 font-weight-bold text-warning small"><i class="fas fa-wallet mr-1"></i> Pay Commission</h6>
            </div>
            <div class="card-body py-3">
                <p class="small text-muted mb-2">
                    Pending: <strong class="text-warning">৳{{ number_format($pendingCommission, 2) }}</strong>
                </p>
                <form method="POST" action="{{ route('agents.pay-commission', $agent) }}">
                    @csrf
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">৳</span>
                        </div>
                        <input type="number" name="amount" class="form-control"
                               value="{{ $pendingCommission }}" min="1" max="{{ $pendingCommission }}" step="0.01" required>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-warning font-weight-bold"
                                    onclick="return confirm('Pay commission?')">
                                Pay All
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif
        @endcan
    </div>

    {{-- Customers + Commission --}}
    <div class="col-md-8">
        {{-- Customers Tab --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center"
                 style="background:linear-gradient(135deg,#1b5e20 0%,#2e7d32 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-users mr-1"></i> Customers
                    <span class="badge badge-light ml-1">{{ $agent->customers->count() }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:300px; overflow-y:auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead style="position:sticky; top:0; background:#f8f9fa; z-index:1;">
                            <tr style="border-bottom:2px solid #dee2e6;">
                                <th class="th2">Customer</th>
                                <th class="th2">Phone</th>
                                <th class="th2">Zone</th>
                                <th class="th2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agent->customers as $customer)
                            <tr>
                                <td class="td2">
                                    <a href="{{ route('customers.show', $customer) }}" class="font-weight-bold text-dark">
                                        {{ $customer->name }}
                                    </a>
                                </td>
                                <td class="td2 small text-muted">{{ $customer->phone ?? '—' }}</td>
                                <td class="td2 small">{{ $customer->zone?->name ?? '—' }}</td>
                                <td class="td2 text-center">
                                    <span class="badge badge-{{ $customer->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($customer->status ?? 'active') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted small">No customers assigned.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Commission History --}}
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#e65100 0%,#f57c00 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-history mr-1"></i> Commission History
                    <span class="badge badge-light ml-1">{{ $agent->commissions->count() }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:320px; overflow-y:auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead style="position:sticky; top:0; background:#f8f9fa; z-index:1;">
                            <tr style="border-bottom:2px solid #dee2e6;">
                                <th class="th2">Date</th>
                                <th class="th2 text-right">Amount</th>
                                <th class="th2 text-center">Status</th>
                                <th class="th2 text-muted">Paid On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agent->commissions as $commission)
                            <tr>
                                <td class="td2 small text-muted">{{ $commission->created_at->format('d M Y') }}</td>
                                <td class="td2 text-right font-weight-bold">৳{{ number_format($commission->amount, 2) }}</td>
                                <td class="td2 text-center">
                                    @if($commission->status === 'paid')
                                    <span class="badge badge-success">Paid</span>
                                    @else
                                    <span class="badge badge-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td class="td2 small text-muted">
                                    {{ $commission->paid_at?->format('d M Y') ?? '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted small">No commission records yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    .th2 { font-size:11px; font-weight:700; text-transform:uppercase; color:#555; padding:8px 10px; }
    .td2 { padding:8px 10px; vertical-align:middle; }
    .table tbody tr:hover { background:#f0f4ff !important; }
</style>
@stop
