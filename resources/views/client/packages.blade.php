{{-- resources/views/client/packages.blade.php --}}
@extends('client.layout')
@section('title', 'Package List')

@section('extra_css')
<style>
    .pkg-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 20px;
    }
    @media (max-width: 600px) {
        .pkg-grid { grid-template-columns: 1fr; }
    }

    .pkg-card {
        background: #fff;
        border-radius: 12px;
        border: 2px solid #eef0f5;
        overflow: hidden;
        transition: transform .15s, box-shadow .15s;
        position: relative;
    }
    .pkg-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,.08);
    }
    .pkg-card.current {
        border-color: #00c897;
    }

    /* Current badge */
    .current-badge {
        position: absolute; top: 12px; right: 12px;
        background: #00c897; color: #fff;
        font-size: 10px; font-weight: 700;
        padding: 3px 10px; border-radius: 999px;
        text-transform: uppercase; letter-spacing: .5px;
    }

    .pkg-header {
        padding: 20px 20px 14px;
        border-bottom: 1px solid #f0f2f7;
    }
    .pkg-name {
        font-size: 16px; font-weight: 700; color: #1a1f36;
        margin-bottom: 4px;
    }
    .pkg-type {
        font-size: 11px; color: #aaa; text-transform: uppercase; letter-spacing: .5px;
    }

    .pkg-price {
        padding: 16px 20px;
        background: #f8f9fc;
        display: flex; align-items: baseline; gap: 4px;
    }
    .pkg-price .amount {
        font-size: 28px; font-weight: 700; color: #1a1f36;
    }
    .pkg-price .currency {
        font-size: 14px; font-weight: 600; color: #888;
    }
    .pkg-price .period {
        font-size: 12px; color: #aaa; margin-left: 2px;
    }

    .pkg-features {
        padding: 16px 20px;
    }
    .pkg-feature {
        display: flex; align-items: center; gap: 10px;
        font-size: 13px; color: #444; padding: 5px 0;
        border-bottom: 1px solid #f4f6f9;
    }
    .pkg-feature:last-child { border-bottom: none; }
    .pkg-feature i { width: 16px; text-align: center; color: #00c897; font-size: 13px; }
    .pkg-feature .val { font-weight: 600; color: #1a1f36; margin-left: auto; }

    .pkg-footer {
        padding: 14px 20px;
        border-top: 1px solid #f0f2f7;
    }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <div class="page-title" style="margin:0;">Package List</div>
    <div style="font-size:12px; color:#aaa;">
        {{ \App\Models\Setting::get('company_name', 'SmartISP') }} &rsaquo; Apps &rsaquo; Package List
    </div>
</div>

{{-- Current package info --}}
@if($customer->package)
<div style="background:#f0faf7; border:1px solid #b0e8d0; border-radius:10px; padding:14px 18px; margin-bottom:20px; font-size:13px; color:#1a7a50; display:flex; align-items:center; gap:10px;">
    <i class="fas fa-check-circle" style="font-size:18px;"></i>
    <div>
        <strong>Your Current Package:</strong> {{ $customer->package->name }}
        &nbsp;&mdash;&nbsp;
        {{ $customer->package->speed_download }}Mbps / {{ $customer->package->speed_upload }}Mbps
        &nbsp;&mdash;&nbsp;
        Tk{{ number_format($customer->monthly_bill_amount ?: $customer->package->price, 0) }}/month
    </div>
</div>
@endif

{{-- Package Grid --}}
<div class="pkg-grid">
    @forelse($packages as $pkg)
    @php $isCurrent = $customer->package_id === $pkg->id; @endphp
    <div class="pkg-card {{ $isCurrent ? 'current' : '' }}">

        @if($isCurrent)
            <div class="current-badge"><i class="fas fa-check" style="margin-right:3px;"></i> Current</div>
        @endif

        <div class="pkg-header">
            <div class="pkg-name">{{ $pkg->name }}</div>
            <div class="pkg-type">{{ ucfirst($pkg->clientType->name ?? 'All') }} Package</div>
        </div>

        <div class="pkg-price">
            <span class="currency">Tk</span>
            <span class="amount">{{ number_format($pkg->price, 0) }}</span>
            <span class="period">/month</span>
        </div>

        <div class="pkg-features">
            <div class="pkg-feature">
                <i class="fas fa-arrow-down"></i>
                <span>Download Speed</span>
                <span class="val">{{ $pkg->speed_download }} Mbps</span>
            </div>
            <div class="pkg-feature">
                <i class="fas fa-arrow-up"></i>
                <span>Upload Speed</span>
                <span class="val">{{ $pkg->speed_upload }} Mbps</span>
            </div>
            <div class="pkg-feature">
                <i class="fas fa-database"></i>
                <span>Data Limit</span>
                <span class="val">{{ $pkg->data_limit_label }}</span>
            </div>
            @if($pkg->connection_fee > 0)
            <div class="pkg-feature">
                <i class="fas fa-plug"></i>
                <span>Connection Fee</span>
                <span class="val">Tk{{ number_format($pkg->connection_fee, 0) }}</span>
            </div>
            @endif
            @if($pkg->description)
            <div class="pkg-feature" style="color:#888; font-size:12px;">
                <i class="fas fa-info-circle"></i>
                <span>{{ $pkg->description }}</span>
            </div>
            @endif
        </div>

        <div class="pkg-footer">
            @if($isCurrent)
                <div style="text-align:center; color:#00c897; font-size:13px; font-weight:600;">
                    <i class="fas fa-check-circle"></i> Active Package
                </div>
            @else
                <a href="{{ route('client.tickets') }}?pkg={{ urlencode($pkg->name) }}" class="btn btn-primary" style="width:100%; justify-content:center;">
                    <i class="fas fa-exchange-alt"></i> Request Package Change
                </a>
            @endif
        </div>

    </div>
    @empty
    <div style="grid-column:1/-1; text-align:center; color:#aaa; padding:3rem;">
        <i class="fas fa-box-open" style="font-size:3rem; display:block; margin-bottom:1rem;"></i>
        No packages available.
    </div>
    @endforelse
</div>

@endsection
