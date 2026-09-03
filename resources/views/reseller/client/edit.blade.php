@extends('reseller.layouts.app')

@section('title', 'Edit Client')

@section('content')
<form action="{{ route('reseller.client.update', $client) }}" method="POST" enctype="multipart/form-data" id="clientForm">
@csrf
@method('PUT')

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

{{-- ══════════════════════════════════════════════ --}}
{{-- SECTION 1: Personal Information               --}}
{{-- ══════════════════════════════════════════════ --}}
<div class="card mb-3">
    <div class="card-header bg-primary text-white py-2">
        <h3 class="card-title mb-0"><i class="fas fa-user mr-2"></i>Personal Information</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $client->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $client->phone) }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $client->email) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>NID / Birth Certificate No</label>
                            <input type="text" name="nid_number" class="form-control"
                                   value="{{ old('nid_number', $client->nid_number) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Occupation</label>
                            <input type="text" name="occupation" class="form-control"
                                   value="{{ old('occupation', $client->occupation) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="">-- Select --</option>
                                @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('gender', $client->gender) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $client->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Photos --}}
            <div class="col-md-4">
                <div class="form-group text-center">
                    <label class="d-block">Customer Photo</label>
                    @if($client->photo)
                        <img id="photoPreview" src="{{ asset('storage/' . $client->photo) }}"
                             class="img-thumbnail rounded-circle mx-auto mb-2"
                             style="width:110px;height:110px;object-fit:cover;">
                        <div id="photoPlaceholder" style="display:none;"></div>
                    @else
                        <div id="photoPlaceholder"
                             class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
                             style="width:110px;height:110px;border:2px dashed #dee2e6;">
                            <i class="fas fa-user fa-3x text-muted"></i>
                        </div>
                        <img id="photoPreview" src="" class="img-thumbnail rounded-circle mx-auto mb-2"
                             style="width:110px;height:110px;object-fit:cover;display:none;">
                    @endif
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="photoInput" name="photo"
                               accept="image/*" onchange="previewImg(this,'photoPreview','photoPlaceholder')">
                        <label class="custom-file-label text-left" for="photoInput">Change photo...</label>
                    </div>
                </div>
                <div class="form-group text-center mt-3">
                    <label class="d-block">NID / Birth Certificate Photo</label>
                    @if($client->nid_photo)
                        <img id="nidPreview" src="{{ asset('storage/' . $client->nid_photo) }}"
                             class="img-thumbnail mx-auto mb-2" style="max-width:100%;max-height:80px;">
                        <div id="nidPlaceholder" style="display:none;"></div>
                    @else
                        <div id="nidPlaceholder"
                             class="bg-light d-flex align-items-center justify-content-center mx-auto mb-2"
                             style="height:80px;border:2px dashed #dee2e6;border-radius:8px;">
                            <span class="text-muted"><i class="fas fa-id-card fa-2x"></i></span>
                        </div>
                        <img id="nidPreview" src="" class="img-thumbnail mx-auto mb-2"
                             style="max-width:100%;max-height:80px;display:none;">
                    @endif
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="nidInput" name="nid_photo"
                               accept="image/*" onchange="previewImg(this,'nidPreview','nidPlaceholder')">
                        <label class="custom-file-label text-left" for="nidInput">Change NID photo...</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════ --}}
{{-- SECTION 2: Service Information                --}}
{{-- ══════════════════════════════════════════════ --}}
<div class="card mb-3">
    <div class="card-header bg-success text-white py-2">
        <h3 class="card-title mb-0"><i class="fas fa-box mr-2"></i>Service Information</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Package <span class="text-danger">*</span></label>
                    <select name="mac_reseller_tariff_package_id" id="packageSelect"
                            class="form-control @error('mac_reseller_tariff_package_id') is-invalid @enderror" required>
                        <option value="">-- Select Package --</option>
                        @forelse($packages as $pkg)
                            <option value="{{ $pkg->id }}"
                                    data-server="{{ $pkg->server_name }}"
                                    data-protocol="{{ strtoupper($pkg->protocol) }}"
                                    data-rate="{{ $pkg->rate }}"
                                    {{ old('mac_reseller_tariff_package_id', $client->mac_reseller_tariff_package_id) == $pkg->id ? 'selected' : '' }}>
                                {{ $pkg->package->name ?? 'Package #' . $pkg->id }} — {{ $pkg->profile }}
                            </option>
                        @empty
                            <option value="" disabled>No packages assigned — contact your admin</option>
                        @endforelse
                    </select>
                    @error('mac_reseller_tariff_package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label>Server</label>
                    <input type="text" id="pkgServer" class="form-control bg-light" value="{{ $client->resellerTariffPackage->server_name ?? '' }}" readonly>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Protocol</label>
                    <input type="text" id="pkgProtocol" class="form-control bg-light" value="{{ strtoupper($client->resellerTariffPackage->protocol ?? '') }}" readonly>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Client Type <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="client_type_id" id="clientTypeSelect"
                                class="form-control @error('client_type_id') is-invalid @enderror" required>
                            <option value="">-- Select Client Type --</option>
                            @foreach($clientTypes as $ct)
                                <option value="{{ $ct->id }}" {{ old('client_type_id', $client->client_type_id) == $ct->id ? 'selected' : '' }}>
                                    {{ $ct->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-success"
                                    data-toggle="modal" data-target="#modalClientType" title="Add Client Type">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    @error('client_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Billing Status <span class="text-danger">*</span></label>
                    <select name="billing_status" class="form-control @error('billing_status') is-invalid @enderror" required>
                        @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'left' => 'Left', 'free' => 'Free'] as $val => $label)
                            <option value="{{ $val }}" {{ old('billing_status', $client->billing_status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('billing_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Zone</label>
                    <div class="input-group">
                        <select name="mac_reseller_zone_id" id="zoneSelect" class="form-control">
                            <option value="">-- Select Zone --</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ old('mac_reseller_zone_id', $client->mac_reseller_zone_id) == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-success"
                                    data-toggle="modal" data-target="#modalZone" title="Add Zone">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Sub Zone</label>
                    <select name="mac_reseller_sub_zone_id" id="subZoneSelect" class="form-control">
                        <option value="">-- Select Sub Zone --</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Monthly Bill Amount</label>
                    <div class="input-group">
                        <input type="number" name="monthly_bill_amount" id="billAmount"
                               class="form-control" value="{{ old('monthly_bill_amount', $client->monthly_bill_amount) }}"
                               step="0.01" min="0">
                        <div class="input-group-append"><span class="input-group-text">BDT</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Billing Date <span class="text-danger">*</span></label>
                    <input type="number" name="billing_date"
                           class="form-control @error('billing_date') is-invalid @enderror"
                           min="1" max="28" value="{{ old('billing_date', $client->billing_date) }}" required>
                    @error('billing_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Connection Date <span class="text-danger">*</span></label>
                    <input type="date" name="connection_date"
                           class="form-control @error('connection_date') is-invalid @enderror"
                           value="{{ old('connection_date', $client->connection_date ? \Carbon\Carbon::parse($client->connection_date)->format('Y-m-d') : '') }}" required>
                    @error('connection_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════ --}}
{{-- SECTION 3: Network / PPPoE                    --}}
{{-- ══════════════════════════════════════════════ --}}
<div class="card mb-3">
    <div class="card-header bg-info text-white py-2">
        <h3 class="card-title mb-0"><i class="fas fa-network-wired mr-2"></i>Network / PPPoE</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Connection Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                        @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'expired' => 'Expired'] as $val => $label)
                            <option value="{{ $val }}" {{ old('status', $client->status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>PPPoE Username</label>
                    <input type="text" name="pppoe_username" class="form-control"
                           value="{{ old('pppoe_username', $client->pppoe_username) }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>PPPoE Password</label>
                    <input type="text" name="pppoe_password" class="form-control" placeholder="Leave blank to keep current">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>IP Address</label>
                    <input type="text" name="ip_address" class="form-control"
                           value="{{ old('ip_address', $client->ip_address) }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>MAC Address</label>
                    <input type="text" name="mac_address" class="form-control"
                           value="{{ old('mac_address', $client->mac_address) }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('reseller.client.show', $client) }}" class="btn btn-secondary">
            <i class="fas fa-times mr-1"></i>Cancel
        </a>
        <button type="submit" class="btn btn-primary" id="btnSubmit">
            <i class="fas fa-save mr-1"></i>Update Client
        </button>
    </div>
</div>

</form>

{{-- Client Type Quick Add Modal --}}
<div class="modal fade" id="modalClientType" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title"><i class="fas fa-user-tag mr-1"></i> Add Client Type</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-2">
                    <label class="small">Name <span class="text-danger">*</span></label>
                    <input type="text" id="newClientTypeName" class="form-control form-control-sm">
                    <div class="text-danger small mt-1" id="clientTypeError"></div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="btnSaveClientType">
                    <i class="fas fa-save mr-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Zone Quick Add Modal --}}
<div class="modal fade" id="modalZone" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title"><i class="fas fa-map-marked-alt mr-1"></i> Add Zone</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-2">
                    <label class="small">Zone Name <span class="text-danger">*</span></label>
                    <input type="text" id="newZoneName" class="form-control form-control-sm">
                    <div class="text-danger small mt-1" id="zoneError"></div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveZone">
                    <i class="fas fa-save mr-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
var CSRF = '{{ csrf_token() }}';
var currentZoneId = {{ $client->mac_reseller_zone_id ?? 'null' }};
var currentSubZoneId = {{ $client->mac_reseller_sub_zone_id ?? 'null' }};

var allSubZones = {!! $subZones->groupBy('mac_reseller_zone_id')->toJson() !!};

function populateSubZoneSelect(zoneId, selectedId) {
    var $sub = $('#subZoneSelect');
    $sub.empty().append('<option value="">-- Select Sub Zone --</option>');
    var list = allSubZones[zoneId] || [];
    list.forEach(function (sz) {
        var selected = (selectedId && String(selectedId) === String(sz.id)) ? 'selected' : '';
        $sub.append('<option value="' + sz.id + '" ' + selected + '>' + sz.name + '</option>');
    });
}

// pre-fill sub-zone on page load based on the client's current zone
$(function () {
    if (currentZoneId) {
        populateSubZoneSelect(currentZoneId, currentSubZoneId);
    }
});

$('#zoneSelect').on('change', function () {
    populateSubZoneSelect($(this).val());
});

function previewImg(input, previewId, placeholderId) {
    var file = input.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        $('#' + previewId).attr('src', e.target.result).show();
        $('#' + placeholderId).hide();
        $(input).next('.custom-file-label').text(file.name);
    };
    reader.readAsDataURL(file);
}

$('#packageSelect').on('change', function () {
    var opt = $(this).find(':selected');
    $('#pkgServer').val(opt.data('server') || '');
    $('#pkgProtocol').val(opt.data('protocol') || '');
});

$('#clientForm').on('submit', function() {
    $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
});

function quickAdd(url, name, selectId, errorId, btnId, modalId) {
    $(errorId).text('');
    if (!name.trim()) { $(errorId).text('Name is required.'); return; }
    $(btnId).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
    $.ajax({
        url: url, method: 'POST',
        data: { _token: CSRF, name: name.trim() },
        success: function(res) {
            if (res.success) {
                $(selectId).append('<option value="' + res.id + '" selected>' + res.name + '</option>');
                $(modalId).modal('hide');
                $(modalId + ' input').val('');
            }
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors || {};
            $(errorId).text(errors.name ? errors.name[0] : 'Failed to save.');
        },
        complete: function() {
            $(btnId).prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save');
        }
    });
}

$('#btnSaveZone').on('click', function() {
    quickAdd('{{ route('reseller.client.quick-add.zone') }}', $('#newZoneName').val(),
             '#zoneSelect', '#zoneError', '#btnSaveZone', '#modalZone');
});
$('#btnSaveClientType').on('click', function() {
    quickAdd('{{ route('reseller.client.quick-add.client-type') }}', $('#newClientTypeName').val(),
             '#clientTypeSelect', '#clientTypeError', '#btnSaveClientType', '#modalClientType');
});
</script>
@endsection
