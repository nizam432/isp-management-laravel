{{-- resources/views/packages/edit.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Edit Package')
@section('page_actions')
    <a href="{{ route('packages.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection
@section('page_content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Package — {{ $package->name }}</h3>
    </div>
    <form action="{{ route('packages.update', $package) }}" method="POST" id="packageForm">
        @csrf @method('PUT')
        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Package Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $package->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Client Type</label>
                        <select name="client_type_id" class="form-control">
                            <option value="0" {{ old('client_type_id', $package->client_type_id) == 0 ? 'selected' : '' }}>All Client</option>
                            @foreach($clientTypes as $ct)
                                <option value="{{ $ct->id }}"
                                        {{ old('client_type_id', $package->client_type_id) == $ct->id ? 'selected' : '' }}>
                                    {{ ucfirst($ct->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Protocol Type</label>
                        <select name="protocol_type_id" id="protocolType" class="form-control"
                                onchange="loadMikrotikProfiles(this.value)">
                            <option value="">-- Select Protocol --</option>
                            @foreach($protocolTypes as $pt)
                                <option value="{{ $pt->id }}" data-slug="{{ \Illuminate\Support\Str::slug($pt->name) }}"
                                        {{ old('protocol_type_id', $package->protocol_type_id) == $pt->id ? 'selected' : '' }}>
                                    {{ $pt->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Selecting a protocol loads its profiles below.</small>
                    </div>
                    <div class="form-group">
                        <label>Monthly Price (BDT) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control"
                               value="{{ old('price', $package->price) }}" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Connection Fee (BDT)</label>
                        <input type="number" name="connection_fee" class="form-control"
                               value="{{ old('connection_fee', $package->connection_fee) }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Download Speed (Mbps) <span class="text-danger">*</span></label>
                        <input type="number" name="speed_download" class="form-control"
                               value="{{ old('speed_download', $package->speed_download) }}" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Upload Speed (Mbps) <span class="text-danger">*</span></label>
                        <input type="number" name="speed_upload" class="form-control"
                               value="{{ old('speed_upload', $package->speed_upload) }}" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Data Limit (GB)</label>
                        <input type="number" name="data_limit" class="form-control"
                               value="{{ old('data_limit', $package->data_limit) }}" min="0">
                        <small class="text-muted">0 = Unlimited</small>
                    </div>
                    <div class="form-group">
                        <label>Validity (Days) <span class="text-danger">*</span></label>
                        <input type="number" name="validity_days" class="form-control"
                               value="{{ old('validity_days', $package->validity_days) }}" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>MikroTik Profile</label>
                        <div class="mb-2">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary active" id="btnSelectProfile"
                                        onclick="toggleProfile('select')">Select Existing</button>
                                <button type="button" class="btn btn-outline-success" id="btnNewProfile"
                                        onclick="toggleProfile('new')">+ Create New</button>
                            </div>
                        </div>
                        <div id="selectProfileDiv">
                            <select name="mikrotik_profile" id="mikrotikProfileSelect" class="form-control">
                                <option value="">-- Select Protocol Type first --</option>
                                @foreach($mikrotikProfiles as $profile)
                                    <option value="{{ $profile }}"
                                            {{ old('mikrotik_profile', $package->mikrotik_profile) == $profile ? 'selected' : '' }}>
                                        {{ $profile }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="profileLoadingHint" style="display:none;">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Loading profiles...
                            </small>
                        </div>
                        <div id="newProfileDiv" style="display:none;">
                            <input type="text" name="new_mikrotik_profile" id="newProfileName"
                                   class="form-control" value="{{ old('new_mikrotik_profile') }}"
                                   placeholder="Profile name e.g. 10MB">
                            <small class="text-muted">
                                Speed will be taken from package Download/Upload — auto created on all routers.
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>BTRC Bandwidth <small class="text-muted">(optional)</small></label>
                        <input type="text" name="btrc_bandwidth" class="form-control"
                               value="{{ old('btrc_bandwidth', $package->btrc_bandwidth) }}" placeholder="e.g. 10Mbps">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>BTRC Price <small class="text-muted">(optional)</small></label>
                        <input type="number" name="btrc_price" class="form-control"
                               value="{{ old('btrc_price', $package->btrc_price) }}" min="0" step="0.01">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $package->description) }}</textarea>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save mr-1"></i> Update Package
            </button>
            <a href="{{ route('packages.index') }}" class="btn btn-secondary ml-1">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
function toggleProfile(type) {
    if (type === 'select') {
        document.getElementById('selectProfileDiv').style.display = 'block';
        document.getElementById('newProfileDiv').style.display   = 'none';
        document.getElementById('btnSelectProfile').classList.add('active');
        document.getElementById('btnNewProfile').classList.remove('active');
        document.getElementById('newProfileName').value = '';
    } else {
        document.getElementById('selectProfileDiv').style.display = 'none';
        document.getElementById('newProfileDiv').style.display   = 'block';
        document.getElementById('btnNewProfile').classList.add('active');
        document.getElementById('btnSelectProfile').classList.remove('active');
        document.getElementById('mikrotikProfileSelect').value = '';
    }
}

function loadMikrotikProfiles(protocolTypeId, preselect) {
    var select = document.getElementById('mikrotikProfileSelect');
    var hint   = document.getElementById('profileLoadingHint');

    if (!protocolTypeId) {
        select.innerHTML = '<option value="">-- Select Protocol Type first --</option>';
        return;
    }

    var protocolOption = document.querySelector('#protocolType option[value="' + protocolTypeId + '"]');
    var slug = protocolOption ? protocolOption.getAttribute('data-slug') : '';

    if (slug === 'static') {
        select.innerHTML = '<option value="">-- No profiles for Static --</option>';
        toggleProfile('new');
        return;
    }

    select.innerHTML = '<option value="">-- Loading... --</option>';
    hint.style.display = 'inline-block';

    fetch('{{ route("packages.mikrotik-profiles") }}?protocol=' + encodeURIComponent(slug))
        .then(res => res.json())
        .then(data => {
            hint.style.display = 'none';
            select.innerHTML = '<option value="">-- Select Profile --</option>';

            if (data.success && data.data.length > 0) {
                data.data.forEach(function (name) {
                    var option = new Option(name, name);
                    if (preselect && name === preselect) {
                        option.selected = true;
                    }
                    select.add(option);
                });
            } else {
                select.innerHTML = '<option value="">-- No profiles found --</option>';
            }
        })
        .catch(() => {
            hint.style.display = 'none';
            select.innerHTML = '<option value="">-- Failed to load profiles --</option>';
        });
}

// Pre-load profiles for this package's existing protocol type on page load,
// keeping its currently-saved MikroTik profile selected.
document.addEventListener('DOMContentLoaded', function () {
    var protocolSelect = document.getElementById('protocolType');
    if (protocolSelect.value) {
        loadMikrotikProfiles(protocolSelect.value, '{{ old('mikrotik_profile', $package->mikrotik_profile) }}');
    }
});
</script>
@endpush
