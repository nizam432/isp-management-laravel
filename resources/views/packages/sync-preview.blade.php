{{-- resources/views/packages/sync-preview.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Sync Packages from MikroTik')
@section('page_actions')
    <a href="{{ route('packages.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

{{-- Router & Protocol Select --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline">
            <label class="mr-2 font-weight-bold">
                <i class="fas fa-server mr-1"></i> Router:
            </label>
            <select name="router_id" class="form-control form-control-sm mr-2"
                    onchange="this.form.submit()">
                @foreach($routers as $r)
                    <option value="{{ $r->id }}"
                            {{ $selectedRouter->id == $r->id ? 'selected' : '' }}>
                        {{ $r->name }} ({{ $r->ip_address }})
                    </option>
                @endforeach
            </select>

            <label class="mr-2 ml-2 font-weight-bold">
                <i class="fas fa-route mr-1"></i> Protocol:
            </label>
            <select name="protocol" class="form-control form-control-sm mr-2"
                    onchange="this.form.submit()">
                <option value="pppoe"   {{ $protocol == 'pppoe'   ? 'selected' : '' }}>PPPoE</option>
                <option value="hotspot" {{ $protocol == 'hotspot' ? 'selected' : '' }}>Hotspot</option>
            </select>

            <span class="badge badge-info ml-2">
                {{ count($profiles) }} profiles found
            </span>
            @if(count($profiles) > 0)
                <span class="badge badge-success ml-1">
                    {{ count(array_filter($profiles, fn($p) => !in_array($p['name'], $existingNames))) }} new
                </span>
                <span class="badge badge-secondary ml-1">
                    {{ count(array_filter($profiles, fn($p) => in_array($p['name'], $existingNames))) }} already added
                </span>
            @endif
        </form>
    </div>
</div>

@if(empty($profiles))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        No profiles found from MikroTik. Check router connection.
    </div>
@else

<form action="{{ route('packages.sync.store') }}" method="POST">
    @csrf
    <input type="hidden" name="router_id" value="{{ $selectedRouter->id }}">

    @php
        $autoProtocolType = $protocolTypes->first(fn($pt) => \Illuminate\Support\Str::slug($pt->name) === $protocol);
    @endphp

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                <i class="fas fa-sync mr-1"></i> MikroTik Profiles —
                <strong>{{ $selectedRouter->name }}</strong>
                <span class="badge badge-dark">{{ strtoupper($protocol) }}</span>
            </h3>
            <div>
                <button type="button" class="btn btn-xs btn-outline-primary mr-1" id="selectAll">
                    Select All New
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary" id="deselectAll">
                    Deselect All
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:40px">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th>Profile Name</th>
                        <th>Rate Limit</th>
                        <th>Price (BDT)</th>
                        <th>Connection Fee</th>
                        <th>Validity (Days)</th>
                        <th>Client Type</th>
                        <th>Protocol</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($profiles as $i => $profile)
                    @php
                        $alreadyExists = in_array($profile['name'], $existingNames);
                        $rateLimit     = $profile['rate-limit'] ?? $profile['rate_limit'] ?? '';
                    @endphp
                    <tr class="{{ $alreadyExists ? 'table-secondary text-muted' : '' }}">
                        <td>
                            <input type="checkbox"
                                   name="profiles[{{ $i }}][selected]"
                                   value="1"
                                   class="profile-check"
                                   {{ $alreadyExists ? 'disabled' : 'checked' }}>
                        </td>
                        <td>
                            <strong>{{ $profile['name'] }}</strong>
                            <input type="hidden" name="profiles[{{ $i }}][name]"             value="{{ $profile['name'] }}">
                            <input type="hidden" name="profiles[{{ $i }}][rate_limit]"        value="{{ $rateLimit }}">
                            <input type="hidden" name="profiles[{{ $i }}][protocol_type_id]"  value="{{ $autoProtocolType->id ?? '' }}">
                        </td>
                        <td>
                            <code>{{ $rateLimit ?: '—' }}</code>
                        </td>
                        <td>
                            @if(!$alreadyExists)
                            <input type="number"
                                   name="profiles[{{ $i }}][price]"
                                   class="form-control form-control-sm"
                                   style="width:100px"
                                   value="0" min="0">
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if(!$alreadyExists)
                            <input type="number"
                                   name="profiles[{{ $i }}][connection_fee]"
                                   class="form-control form-control-sm"
                                   style="width:100px"
                                   value="0" min="0">
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if(!$alreadyExists)
                            <input type="number"
                                   name="profiles[{{ $i }}][validity_days]"
                                   class="form-control form-control-sm"
                                   style="width:90px"
                                   value="30" min="1">
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if(!$alreadyExists)
                            <select name="profiles[{{ $i }}][client_type_id]"
                                    class="form-control form-control-sm"
                                    style="width:110px"
                                    {{ $alreadyExists ? 'disabled' : '' }}>
                                <option value="0">All Client</option>
                                @foreach($clientTypes as $ct)
                                    <option value="{{ $ct->id }}">{{ ucfirst($ct->name) }}</option>
                                @endforeach
                            </select>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-dark">{{ $autoProtocolType->name ?? strtoupper($protocol) }}</span>
                        </td>
                        <td>
                            @if($alreadyExists)
                                <span class="badge badge-success">
                                    <i class="fas fa-check mr-1"></i> Already Added
                                </span>
                            @else
                                <span class="badge badge-warning">
                                    <i class="fas fa-plus mr-1"></i> New
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="fas fa-info-circle mr-1"></i>
                Already added profiles will be skipped automatically.
            </small>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Save Selected Packages
            </button>
        </div>
    </div>

</form>

@endif

@endsection

@push('js')
<script>
document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('.profile-check:not(:disabled)')
        .forEach(cb => cb.checked = this.checked);
});
document.getElementById('selectAll').addEventListener('click', function() {
    document.querySelectorAll('.profile-check:not(:disabled)')
        .forEach(cb => cb.checked = true);
});
document.getElementById('deselectAll').addEventListener('click', function() {
    document.querySelectorAll('.profile-check:not(:disabled)')
        .forEach(cb => cb.checked = false);
});
</script>
@endpush