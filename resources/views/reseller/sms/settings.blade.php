@extends('reseller.layouts.app')

@section('title', 'SMS Gateway Settings')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-times-circle mr-1"></i> {{ session('error') }}
    </div>
@endif

<h4 class="mb-3">SMS Gateway Settings</h4>

@if($gateways->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        Super Admin কোনো SMS Gateway এখনো Enable করেননি। পরে আবার চেষ্টা করুন।
    </div>
@else

@php
    $settingMap = $settings->keyBy('gateway_slug');
@endphp

<div class="row">
    @foreach($gateways as $gw)
    @php
        $setting  = $settingMap->get($gw->slug);
        $isActive = $setting?->is_active ?? false;
        $config   = $setting?->config ?? $gw->config ?? [];
    @endphp
    <div class="col-md-6">
        <div class="card {{ $isActive ? 'border-success' : '' }}">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-server mr-1"></i> {{ $gw->name }}
                    @if($isActive)
                        <span class="badge badge-success ml-2"><i class="fas fa-check mr-1"></i> Active</span>
                    @else
                        <span class="badge badge-secondary ml-2">Inactive</span>
                    @endif
                </h3>
                <small class="text-muted"><code>{{ $gw->slug }}</code></small>
            </div>

            <form action="{{ route('reseller.sms-service.settings.save', $gw->slug) }}" method="POST">
                @csrf
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-info-circle mr-1"></i> {{ $gw->description ?? '' }}
                    </p>

                    @foreach($config as $key => $value)
                    <div class="form-group">
                        <label class="font-weight-bold small text-uppercase">{{ str_replace('_', ' ', $key) }}</label>
                        <input
                            type="{{ in_array($key, ['api_key', 'auth_token', 'password']) ? 'password' : 'text' }}"
                            name="config[{{ $key }}]"
                            class="form-control form-control-sm"
                            value="{{ $value }}"
                            placeholder="{{ strtoupper($key) }}">
                    </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save mr-1"></i> Save & Activate
                    </button>
                </div>
            </form>

            @if($setting)
            <div class="card-footer p-2">
                <form action="{{ route('reseller.sms-service.settings.toggle', $gw->slug) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-{{ $isActive ? 'danger' : 'success' }} btn-block">
                        <i class="fas fa-{{ $isActive ? 'ban' : 'check' }} mr-1"></i>
                        {{ $isActive ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

@endif

@endsection
