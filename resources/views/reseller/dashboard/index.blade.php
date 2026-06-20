@extends('reseller.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Remaining Fund</p>
                        <h4 class="font-weight-bold mb-0">{{ number_format($stats['remaining_fund'], 2) }}</h4>
                    </div>
                    <div style="width:48px;height:48px;border-radius:12px;background:#f0fdf4;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-wallet text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Total Clients</p>
                        <h4 class="font-weight-bold mb-0">{{ $stats['total_clients'] }}</h4>
                    </div>
                    <div style="width:48px;height:48px;border-radius:12px;background:#eff6ff;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Active Clients</p>
                        <h4 class="font-weight-bold mb-0">{{ $stats['active_clients'] }}</h4>
                    </div>
                    <div style="width:48px;height:48px;border-radius:12px;background:#f0fdf4;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-user-check text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Disabled Clients</p>
                        <h4 class="font-weight-bold mb-0">{{ $stats['disabled_clients'] }}</h4>
                    </div>
                    <div style="width:48px;height:48px;border-radius:12px;background:#fef2f2;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-user-times text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-2" style="border-radius:12px">
    <div class="card-body">
        <h6 class="font-weight-bold mb-3"><i class="fas fa-info-circle text-info mr-1"></i> Account Information</h6>
        <table class="table table-sm table-borderless mb-0" style="font-size:.875rem">
            <tr><td class="text-muted" style="width:200px">POP Code</td><td class="font-weight-bold">{{ $reseller->code }}</td></tr>
            <tr><td class="text-muted">Business Name</td><td class="font-weight-bold">{{ $reseller->business_name }}</td></tr>
            <tr><td class="text-muted">Contact Person</td><td>{{ $reseller->contact_person }}</td></tr>
            <tr><td class="text-muted">Mobile</td><td>{{ $reseller->mobile }}</td></tr>
            <tr><td class="text-muted">POP Type</td><td><span class="badge badge-info">{{ ucfirst($reseller->pop_type) }}</span></td></tr>
            <tr><td class="text-muted">Tariff</td><td>{{ $reseller->tariff?->name ?? 'N/A' }}</td></tr>
        </table>
    </div>
</div>
@stop
