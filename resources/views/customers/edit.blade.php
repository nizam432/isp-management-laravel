{{-- resources/views/customers/edit.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Edit Customer: ' . $customer->name)

@section('page_actions')
    <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@section('page_content')
<form action="{{ route('customers.update', $customer) }}" method="POST" enctype="multipart/form-data" id="editForm">
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
        <h3 class="card-title mb-0">
            <i class="fas fa-user mr-2"></i>Personal Information
        </h3>
        <div class="card-tools">
            <span class="badge badge-light text-dark font-weight-bold">{{ $customer->customer_code }}</span>
        </div>
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
                                   value="{{ old('name', $customer->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $customer->phone) }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $customer->email) }}" placeholder="email@example.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>NID / Birth Certificate No</label>
                            <input type="text" name="nid_number" class="form-control"
                                   value="{{ old('nid_number', $customer->nid_number) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Occupation</label>
                            <input type="text" name="occupation" class="form-control"
                                   value="{{ old('occupation', $customer->occupation) }}"
                                   placeholder="e.g. Business, Service">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="male"   {{ old('gender', $customer->gender) == 'male'   ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $customer->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other"  {{ old('gender', $customer->gender) == 'other'  ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Photos --}}
            <div class="col-md-4">
                {{-- Customer Photo --}}
                <div class="form-group text-center">
                    <label class="d-block">Customer Photo</label>
                    @if($customer->photo)
                        <img id="photoPreview" src="{{ asset('storage/'.$customer->photo) }}"
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

                {{-- NID Photo --}}
                <div class="form-group text-center mt-3">
                    <label class="d-block">NID / Birth Certificate Photo</label>
                    @if($customer->nid_photo)
                        <img id="nidPreview" src="{{ asset('storage/'.$customer->nid_photo) }}"
                             class="img-thumbnail mx-auto mb-2"
                             style="max-width:100%;max-height:80px;">
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
{{-- SECTION 2: Service Information               --}}
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
                    <select name="package_id" id="packageSelect"
                            class="form-control @error('package_id') is-invalid @enderror" required>
                        <option value="">-- Select Package --</option>
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}" data-price="{{ $pkg->price }}"
                                    {{ old('package_id', $customer->package_id) == $pkg->id ? 'selected' : '' }}>
                                {{ $pkg->name }} — {{ number_format($pkg->price) }} BDT
                            </option>
                        @endforeach
                    </select>
                    @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Client Type + Quick Add --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label>Client Type <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="client_type_id" id="clientTypeSelect"
                                class="form-control @error('client_type_id') is-invalid @enderror" required>
                            <option value="">-- Select Client Type --</option>
                            @foreach($clientTypes as $ct)
                                <option value="{{ $ct->id }}"
                                        {{ old('client_type_id', $customer->client_type_id) == $ct->id ? 'selected' : '' }}>
                                    {{ $ct->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-success"
                                    data-toggle="modal" data-target="#modalClientType">
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
                    <select name="billing_status"
                            class="form-control @error('billing_status') is-invalid @enderror" required>
                        @foreach(['active','inactive','left','free'] as $bs)
                            <option value="{{ $bs }}"
                                    {{ old('billing_status', $customer->billing_status) == $bs ? 'selected' : '' }}>
                                {{ ucfirst($bs) }}
                            </option>
                        @endforeach
                    </select>
                    @error('billing_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Zone + Quick Add --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label>Zone</label>
                    <div class="input-group">
                        <select name="zone_id" id="zoneSelect" class="form-control">
                            <option value="">-- Select Zone --</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}"
                                        {{ old('zone_id', $customer->zone_id) == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-success"
                                    data-toggle="modal" data-target="#modalZone">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Sub Zone</label>
                    <select name="sub_zone_id" id="subZoneSelect" class="form-control">
                        <option value="">-- Select --</option>
                        @foreach($subZones as $sz)
                            <option value="{{ $sz->id }}"
                                    {{ old('sub_zone_id', $customer->sub_zone_id) == $sz->id ? 'selected' : '' }}>
                                {{ $sz->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Connection Type + Quick Add --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label>Connection Type</label>
                    <div class="input-group">
                        <select name="connection_type_id" id="connectionTypeSelect" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($connectionTypes as $ct)
                                <option value="{{ $ct->id }}"
                                        {{ old('connection_type_id', $customer->connection_type_id) == $ct->id ? 'selected' : '' }}>
                                    {{ $ct->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-success"
                                    data-toggle="modal" data-target="#modalConnectionType">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>Monthly Bill Amount</label>
                    <div class="input-group">
                        <input type="number" name="monthly_bill_amount" id="billAmount"
                               class="form-control" step="0.01" min="0"
                               value="{{ old('monthly_bill_amount', $customer->monthly_bill_amount) }}">
                        <div class="input-group-append">
                            <span class="input-group-text">BDT</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Billing Date <span class="text-danger">*</span></label>
                    <input type="number" name="billing_date"
                           class="form-control @error('billing_date') is-invalid @enderror"
                           min="1" max="28"
                           value="{{ old('billing_date', $customer->billing_date) }}" required>
                    <small class="text-muted">Day of month (1–28)</small>
                    @error('billing_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Connection Date <span class="text-danger">*</span></label>
                    <input type="date" name="connection_date"
                           class="form-control @error('connection_date') is-invalid @enderror"
                           value="{{ old('connection_date', $customer->connection_date?->format('Y-m-d')) }}" required>
                    @error('connection_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Agent</label>
                    <select name="agent_id" class="form-control">
                        <option value="">-- No Agent --</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}"
                                    {{ old('agent_id', $customer->agent_id) == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }} ({{ $agent->area }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════ --}}
{{-- SECTION 3: Network / PPPoE                   --}}
{{-- ══════════════════════════════════════════════ --}}
<div class="card mb-3">
    <div class="card-header bg-info text-white py-2">
        <h3 class="card-title mb-0"><i class="fas fa-network-wired mr-2"></i>Network / PPPoE</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>MikroTik Router (Server)</label>
                    <select name="router_id" class="form-control">
                        <option value="">-- Select Router --</option>
                        @foreach($routers as $router)
                            <option value="{{ $router->id }}"
                                    {{ old('router_id', $customer->router_id) == $router->id ? 'selected' : '' }}>
                                {{ $router->name }} ({{ $router->ip_address }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Protocol Type + Quick Add --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label>Protocol Type</label>
                    <div class="input-group">
                        <select name="protocol_type_id" id="protocolTypeSelect" class="form-control">
                            <option value="">-- Select Protocol --</option>
                            @foreach($protocolTypes as $pt)
                                <option value="{{ $pt->id }}"
                                        {{ old('protocol_type_id', $customer->protocol_type_id) == $pt->id ? 'selected' : '' }}>
                                    {{ $pt->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-success"
                                    data-toggle="modal" data-target="#modalProtocolType">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Connection Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                        @foreach(['active','inactive','suspended','expired'] as $s)
                            <option value="{{ $s }}"
                                    {{ old('status', $customer->status) == $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>PPPoE Username</label>
                    <input type="text" name="pppoe_username"
                           class="form-control @error('pppoe_username') is-invalid @enderror"
                           value="{{ old('pppoe_username', $customer->pppoe_username) }}">
                    @error('pppoe_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>PPPoE Password</label>
                    <input type="text" name="pppoe_password" class="form-control"
                           value="{{ old('pppoe_password', $customer->pppoe_password) }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>IP Address</label>
                    <input type="text" name="ip_address" class="form-control"
                           value="{{ old('ip_address', $customer->ip_address) }}"
                           placeholder="192.168.1.x">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>MAC Address</label>
                    <input type="text" name="mac_address" class="form-control"
                           value="{{ old('mac_address', $customer->mac_address) }}"
                           placeholder="AA:BB:CC:DD:EE:FF">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Submit --}}
<div class="card">
    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">
            <i class="fas fa-times mr-1"></i>Cancel
        </a>
        <button type="submit" class="btn btn-warning" id="btnSubmit">
            <i class="fas fa-save mr-1"></i>Update Customer
        </button>
    </div>
</div>

</form>

{{-- ════════════════════════════════════════════════════ --}}
{{-- QUICK ADD MODALS                                    --}}
{{-- ════════════════════════════════════════════════════ --}}

{{-- Zone Modal --}}
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
                    <input type="text" id="newZoneName" class="form-control form-control-sm" placeholder="Zone name">
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

{{-- Connection Type Modal --}}
<div class="modal fade" id="modalConnectionType" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title"><i class="fas fa-plug mr-1"></i> Add Connection Type</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-2">
                    <label class="small">Name <span class="text-danger">*</span></label>
                    <input type="text" id="newConnectionTypeName" class="form-control form-control-sm" placeholder="e.g. Fiber, Wireless">
                    <div class="text-danger small mt-1" id="connectionTypeError"></div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveConnectionType">
                    <i class="fas fa-save mr-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Client Type Modal --}}
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
                    <input type="text" id="newClientTypeName" class="form-control form-control-sm" placeholder="e.g. Home User, Corporate">
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

{{-- Protocol Type Modal --}}
<div class="modal fade" id="modalProtocolType" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-info text-white py-2">
                <h6 class="modal-title"><i class="fas fa-network-wired mr-1"></i> Add Protocol Type</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-2">
                    <label class="small">Name <span class="text-danger">*</span></label>
                    <input type="text" id="newProtocolTypeName" class="form-control form-control-sm" placeholder="e.g. PPPoE, DHCP">
                    <div class="text-danger small mt-1" id="protocolTypeError"></div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info btn-sm" id="btnSaveProtocolType">
                    <i class="fas fa-save mr-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra_js')
<script>
var CSRF = '{{ csrf_token() }}';

// ── Photo Preview ────────────────────────────────────────
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

// ── Zone → SubZone AJAX ──────────────────────────────────
var currentSubZone = '{{ $customer->sub_zone_id }}';

$('#zoneSelect').on('change', function() {
    var zoneId = $(this).val();
    var $sub   = $('#subZoneSelect');
    if (!zoneId) { $sub.html('<option value="">-- Select Zone first --</option>'); return; }
    $sub.html('<option value="">Loading...</option>');
    $.get('/customers/sub-zones', { zone_id: zoneId }, function(data) {
        var opts = '<option value="">-- Select Sub Zone --</option>';
        data.forEach(function(sz) {
            var sel = (sz.id == currentSubZone) ? 'selected' : '';
            opts += '<option value="' + sz.id + '" ' + sel + '>' + sz.name + '</option>';
        });
        $sub.html(opts);
    }).fail(function() { $sub.html('<option value="">-- Select Sub Zone --</option>'); });
});

// ── Submit loading ───────────────────────────────────────
$('#editForm').on('submit', function() {
    $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Updating...');
});

// ════════════════════════════════════════════════════════
// Quick Add Helper
// ════════════════════════════════════════════════════════
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
                $(modalId + ' input[type=text]').val('');
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
    quickAdd('/customers/quick-add/zone', $('#newZoneName').val(),
             '#zoneSelect', '#zoneError', '#btnSaveZone', '#modalZone');
});
$('#modalZone').on('shown.bs.modal', function() { $('#newZoneName').focus(); });
$('#newZoneName').on('keypress', function(e) { if (e.which === 13) $('#btnSaveZone').click(); });

$('#btnSaveConnectionType').on('click', function() {
    quickAdd('/customers/quick-add/connection-type', $('#newConnectionTypeName').val(),
             '#connectionTypeSelect', '#connectionTypeError', '#btnSaveConnectionType', '#modalConnectionType');
});
$('#modalConnectionType').on('shown.bs.modal', function() { $('#newConnectionTypeName').focus(); });
$('#newConnectionTypeName').on('keypress', function(e) { if (e.which === 13) $('#btnSaveConnectionType').click(); });

$('#btnSaveClientType').on('click', function() {
    quickAdd('/customers/quick-add/client-type', $('#newClientTypeName').val(),
             '#clientTypeSelect', '#clientTypeError', '#btnSaveClientType', '#modalClientType');
});
$('#modalClientType').on('shown.bs.modal', function() { $('#newClientTypeName').focus(); });
$('#newClientTypeName').on('keypress', function(e) { if (e.which === 13) $('#btnSaveClientType').click(); });

$('#btnSaveProtocolType').on('click', function() {
    quickAdd('/customers/quick-add/protocol-type', $('#newProtocolTypeName').val(),
             '#protocolTypeSelect', '#protocolTypeError', '#btnSaveProtocolType', '#modalProtocolType');
});
$('#modalProtocolType').on('shown.bs.modal', function() { $('#newProtocolTypeName').focus(); });
$('#newProtocolTypeName').on('keypress', function(e) { if (e.which === 13) $('#btnSaveProtocolType').click(); });
</script>
@endsection