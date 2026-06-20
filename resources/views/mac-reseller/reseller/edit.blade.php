@extends('adminlte::page')

@section('title', 'Edit MAC Reseller')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-user-edit mr-2 text-success"></i> Edit MAC Reseller
            </h1>
            <small class="text-muted ml-1">Update reseller (POP) account — {{ $macReseller->code }}</small>
        </div>
        <a href="{{ route('mac-reseller.list.index') }}" class="btn btn-sm btn-light">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
@stop

@section('css')
<style>
    .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .card-section-header {
        background: #f8fafc; border-bottom: 1px solid #eef0f3;
        padding: 14px 22px; border-radius: 12px 12px 0 0;
        display: flex; align-items: center; gap: 10px;
    }
    .card-section-header .icon-box {
        width: 34px; height: 34px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem; flex-shrink: 0;
    }
    .icon-personal { background:#eff6ff; color:#2563eb; }
    .icon-business { background:#f0fdf4; color:#16a34a; }
    .icon-menus    { background:#fef3c7; color:#b45309; }
    .icon-security { background:#fee2e2; color:#dc2626; }
    .card-section-header h5 { margin:0; font-size:.95rem; font-weight:700; color:#1e293b; }

    .form-label-sm {
        font-size: .75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .4px; color: #475569; margin-bottom: 5px; display:block;
    }
    .form-control-sm-custom {
        border-radius: 8px; border: 1.5px solid #e2e8f0;
        padding: 8px 12px; font-size: .875rem; transition: border .2s; width:100%;
    }
    .form-control-sm-custom:focus { border-color:#28a745; box-shadow:0 0 0 3px rgba(40,167,69,.1); outline:none; }

    /* ── Logo Upload (smart drag-drop preview) ─────────── */
    .logo-upload-wrap { display:flex; flex-direction:column; align-items:center; gap:10px; }
    .logo-dropzone {
        width: 130px; height: 130px; border-radius: 16px;
        border: 2px dashed #cbd5e1; background:#f8fafc;
        display:flex; flex-direction:column; align-items:center; justify-content:center;
        cursor:pointer; transition: all .2s; position:relative; overflow:hidden;
    }
    .logo-dropzone:hover { border-color:#28a745; background:#f0fdf4; }
    .logo-dropzone.dragover { border-color:#28a745; background:#f0fdf4; transform:scale(1.02); }
    .logo-dropzone i.upload-icon { font-size:1.6rem; color:#94a3b8; margin-bottom:6px; }
    .logo-dropzone:hover i.upload-icon { color:#28a745; }
    .logo-dropzone .upload-text { font-size:.7rem; color:#94a3b8; text-align:center; padding:0 8px; }
    .logo-dropzone img.logo-preview {
        position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; display:none;
    }
    .logo-remove-btn {
        display:none; font-size:.72rem; color:#dc2626; background:none; border:none;
        font-weight:600; cursor:pointer; padding:2px 8px;
    }
    .logo-remove-btn:hover { text-decoration:underline; }
    .logo-filename { font-size:.72rem; color:#64748b; text-align:center; max-width:160px; word-break:break-all; }

    /* ── Menu checkboxes ────────────────────────────────── */
    .menu-pill {
        display:flex; align-items:center; gap:8px; padding:9px 12px;
        border:1.5px solid #e2e8f0; border-radius:9px; cursor:pointer;
        transition: all .15s; margin-bottom:8px; background:#fff;
    }
    .menu-pill:hover { border-color:#28a745; background:#f8fdfa; }
    .menu-pill input { accent-color:#28a745; width:15px; height:15px; cursor:pointer; }
    .menu-pill label { margin:0; font-size:.8rem; font-weight:600; color:#374151; cursor:pointer; flex:1; }
    .menu-pill input:checked ~ label { color:#16a34a; }

    .select-all-bar {
        display:flex; align-items:center; justify-content:space-between;
        background:#fef3c7; border-radius:9px; padding:10px 14px; margin-bottom:14px;
    }
    .select-all-bar label { margin:0; font-weight:700; font-size:.8rem; color:#92400e; }

    .btn-submit-pop {
        background: linear-gradient(135deg, #28a745, #20c997);
        border:none; color:#fff; font-weight:600; padding:10px 28px;
        border-radius:9px; font-size:.9rem; transition: all .2s;
        box-shadow:0 3px 10px rgba(40,167,69,.3);
    }
    .btn-submit-pop:hover { transform:translateY(-1px); box-shadow:0 5px 15px rgba(40,167,69,.4); color:#fff; }

    .badge-required { color:#dc2626; }

    .password-hint {
        background:#fff7ed; border:1px solid #fed7aa; border-radius:8px;
        padding:8px 12px; font-size:.78rem; color:#9a3412; margin-bottom:14px;
        display:flex; align-items:center; gap:8px;
    }
</style>
@stop

@section('content')
<form action="{{ route('mac-reseller.list.update', $macReseller->id) }}" method="POST" enctype="multipart/form-data" id="resellerForm">
    @csrf
    @method('PUT')

    {{-- ══════════════════════════════════════════
         SECTION 1: Personal Information
    ══════════════════════════════════════════ --}}
    <div class="card mb-3">
        <div class="card-section-header">
            <div class="icon-box icon-personal"><i class="fas fa-user-circle"></i></div>
            <h5>Personal Information</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">

                {{-- Left: form fields --}}
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm">Contact Person Name <span class="badge-required">*</span></label>
                            <input type="text" name="contact_person" class="form-control-sm-custom @error('contact_person') is-invalid @enderror" value="{{ old('contact_person', $macReseller->contact_person) }}" required>
                            @error('contact_person')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm">Email Address</label>
                            <input type="email" name="email" class="form-control-sm-custom" value="{{ old('email', $macReseller->email) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm">Mobile No. <span class="badge-required">*</span></label>
                            <input type="text" name="mobile" class="form-control-sm-custom @error('mobile') is-invalid @enderror" value="{{ old('mobile', $macReseller->mobile) }}" required>
                            @error('mobile')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm">Phone No.</label>
                            <input type="text" name="phone" class="form-control-sm-custom" value="{{ old('phone', $macReseller->phone) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm">National ID</label>
                            <input type="text" name="national_id" class="form-control-sm-custom" value="{{ old('national_id', $macReseller->national_id) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm">POP Code</label>
                            <input type="text" class="form-control-sm-custom bg-light" value="{{ $macReseller->code }}" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm">District</label>
                            <select name="district" id="districtSelect" class="form-control-sm-custom">
                                <option value="">Select</option>
                                @foreach($districts ?? [] as $d)
                                <option value="{{ $d->name }}" data-id="{{ $d->id }}" {{ old('district', $macReseller->district) == $d->name ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm d-flex justify-content-between align-items-center">
                                <span>Upazila</span>
                                <button type="button" class="btn btn-link btn-sm p-0" id="addUpazilaBtn" style="font-size:.7rem;text-decoration:none;{{ $macReseller->district ? '' : 'display:none' }}" data-toggle="modal" data-target="#quickAddUpazilaModal">
                                    <i class="fas fa-plus-circle"></i> Add New
                                </button>
                            </label>
                            <select name="upazila" id="upazilaSelect" class="form-control-sm-custom">
                                <option value="">{{ $macReseller->district ? 'Select Upazila' : '— Select District First —' }}</option>
                                @foreach($upazilas ?? [] as $u)
                                <option value="{{ $u->name }}" {{ old('upazila', $macReseller->upazila) == $u->name ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                                @if($macReseller->upazila && !($upazilas ?? collect())->pluck('name')->contains($macReseller->upazila))
                                <option value="{{ $macReseller->upazila }}" selected>{{ $macReseller->upazila }} (legacy)</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm d-flex justify-content-between align-items-center">
                                <span>Zone</span>
                                <button type="button" class="btn btn-link btn-sm p-0" style="font-size:.7rem;text-decoration:none" data-toggle="modal" data-target="#quickAddZoneModal">
                                    <i class="fas fa-plus-circle"></i> Add New
                                </button>
                            </label>
                            <select name="zone" id="zoneSelect" class="form-control-sm-custom">
                                <option value="">Select</option>
                                @foreach($zones ?? [] as $z)
                                <option value="{{ $z->name }}" {{ old('zone', $macReseller->zone) == $z->name ? 'selected' : '' }}>{{ $z->name }}</option>
                                @endforeach
                                @if($macReseller->zone && !($zones ?? collect())->pluck('name')->contains($macReseller->zone))
                                <option value="{{ $macReseller->zone }}" selected>{{ $macReseller->zone }} (legacy)</option>
                                @endif
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm">POP Prefix <i class="fas fa-info-circle text-info" data-toggle="tooltip" title="Prefix used in Mikrotik username, e.g. AB1"></i></label>
                            <input type="text" name="pop_prefix" class="form-control-sm-custom" placeholder="Ex: AB1" value="{{ old('pop_prefix', $macReseller->pop_prefix) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm">Set Prefix in Mikrotik Username?</label>
                            <select name="use_prefix_in_mikrotik_username" class="form-control-sm-custom">
                                <option value="0" {{ !$macReseller->use_prefix_in_mikrotik_username ? 'selected' : '' }}>No, I Don't</option>
                                <option value="1" {{ $macReseller->use_prefix_in_mikrotik_username ? 'selected' : '' }}>Yes, I Want</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm">POP Type</label>
                            <select name="pop_type" class="form-control-sm-custom">
                                <option value="prepaid" {{ $macReseller->pop_type == 'prepaid' ? 'selected' : '' }}>Prepaid</option>
                                <option value="postpaid" {{ $macReseller->pop_type == 'postpaid' ? 'selected' : '' }}>Postpaid</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label-sm">Minimum Rechargeable Amount</label>
                            <input type="number" name="min_rechargeable_amount" class="form-control-sm-custom" value="{{ old('min_rechargeable_amount', $macReseller->min_rechargeable_amount) }}" min="0" step="0.01">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label-sm">Address <span class="badge-required">*</span></label>
                            <textarea name="address" class="form-control-sm-custom" rows="1" required>{{ old('address', $macReseller->address) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Right: Smart Logo Upload --}}
                <div class="col-md-3">
                    <label class="form-label-sm text-center d-block">POP Logo</label>
                    <div class="logo-upload-wrap">
                        <div class="logo-dropzone" id="logoDropzone">
                            <img id="logoPreview" class="logo-preview"
                                src="{{ $macReseller->logo ? asset('storage/' . $macReseller->logo) : '' }}"
                                style="{{ $macReseller->logo ? 'display:block' : 'display:none' }}"
                                alt="Logo preview">
                            <i class="fas fa-cloud-upload-alt upload-icon" id="logoIcon" style="{{ $macReseller->logo ? 'display:none' : 'display:block' }}"></i>
                            <span class="upload-text" id="logoText" style="{{ $macReseller->logo ? 'display:none' : 'display:block' }}">Click or drag<br>image here</span>
                        </div>
                        <input type="file" name="logo" id="logoInput" accept="image/*" style="display:none">
                        <span class="logo-filename" id="logoFilename"></span>
                        <button type="button" class="logo-remove-btn" id="logoRemoveBtn" style="{{ $macReseller->logo ? 'display:inline-block' : 'display:none' }}">
                            <i class="fas fa-times-circle"></i> Remove
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         SECTION 2: Business & Login Information
    ══════════════════════════════════════════ --}}
    <div class="card mb-3">
        <div class="card-section-header">
            <div class="icon-box icon-business"><i class="fas fa-briefcase"></i></div>
            <h5>Business & Login Information</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label-sm">POP / Business Name <span class="badge-required">*</span></label>
                    <input type="text" name="business_name" class="form-control-sm-custom @error('business_name') is-invalid @enderror" value="{{ old('business_name', $macReseller->business_name) }}" required>
                    @error('business_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label-sm">Tariff Name</label>
                    <select name="tariff_id" class="form-control-sm-custom">
                        <option value="">Select</option>
                        @foreach($tariffs ?? [] as $t)
                        <option value="{{ $t->id }}" {{ old('tariff_id', $macReseller->tariff_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label-sm">Want to Disable Clients?</label>
                    <select name="want_to_disable_clients" class="form-control-sm-custom">
                        <option value="1" {{ $macReseller->want_to_disable_clients ? 'selected' : '' }}>Yes, I Want</option>
                        <option value="0" {{ !$macReseller->want_to_disable_clients ? 'selected' : '' }}>No, I Don't</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label-sm">Minimum Balance</label>
                    <input type="number" name="min_balance" class="form-control-sm-custom" value="{{ old('min_balance', $macReseller->min_balance) }}" min="0" step="0.01">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label-sm">Username <span class="badge-required">*</span></label>
                    <input type="text" name="username" class="form-control-sm-custom @error('username') is-invalid @enderror" value="{{ old('username', $macReseller->username) }}" required>
                    @error('username')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         SECTION 3: Security — Change Password (optional)
    ══════════════════════════════════════════ --}}
    <div class="card mb-3">
        <div class="card-section-header">
            <div class="icon-box icon-security"><i class="fas fa-lock"></i></div>
            <h5>Change Password</h5>
        </div>
        <div class="card-body p-4">
            <div class="password-hint">
                <i class="fas fa-info-circle"></i>
                Leave both fields blank to keep the current password unchanged.
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label-sm">New Password</label>
                    <input type="password" name="password" class="form-control-sm-custom @error('password') is-invalid @enderror">
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label-sm">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control-sm-custom">
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         SECTION 4: POP Menus
    ══════════════════════════════════════════ --}}
    <div class="card mb-3">
        <div class="card-section-header">
            <div class="icon-box icon-menus"><i class="fas fa-bars"></i></div>
            <h5>POP Menus — Reseller Portal Access</h5>
        </div>
        <div class="card-body p-4">
            <div class="select-all-bar">
                <label>
                    <input type="checkbox" id="selectAllMenus" style="accent-color:#b45309;width:15px;height:15px;margin-right:6px">
                    SELECT ALL MENUS
                </label>
                <span class="text-muted small" id="menuCountText">0 of {{ count($menus ?? []) }} selected</span>
            </div>
            <div class="row">
                @php $currentMenus = old('allowed_menus', $macReseller->allowed_menus ?? []); @endphp
                @foreach($menus ?? [] as $menu)
                <div class="col-md-3 col-sm-6">
                    <div class="menu-pill">
                        <input type="checkbox" class="menu-checkbox" name="allowed_menus[]" value="{{ $menu }}"
                            id="menu-{{ Str::slug($menu) }}"
                            {{ in_array($menu, $currentMenus) ? 'checked' : '' }}>
                        <label for="menu-{{ Str::slug($menu) }}">{{ ucwords(strtolower($menu)) }}</label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="d-flex justify-content-between mb-4">
        <a href="{{ route('mac-reseller.list.index') }}" class="btn btn-light btn-sm px-4">
            <i class="fas fa-list mr-1"></i> Go To List
        </a>
        <button type="submit" class="btn-submit-pop">
            <i class="fas fa-save mr-1"></i> Update Reseller
        </button>
    </div>
</form>

{{-- Quick Add Zone Modal --}}
<div class="modal fade" id="quickAddZoneModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:14px;overflow:hidden">
            <div class="modal-header" style="background:#f8fafc;border-bottom:1px solid #eef0f3">
                <h6 class="modal-title font-weight-bold mb-0"><i class="fas fa-map-marker-alt text-success mr-1"></i> Add New Zone</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <label class="form-label-sm">Zone Name <span class="badge-required">*</span></label>
                <input type="text" id="newZoneName" class="form-control-sm-custom" placeholder="e.g. North Zone">
                <div class="text-danger small mt-1" id="zoneError"></div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #eef0f3">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn-submit-pop py-1 px-3" id="saveZoneBtn" style="font-size:.8rem">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Quick Add Upazila Modal --}}
<div class="modal fade" id="quickAddUpazilaModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:14px;overflow:hidden">
            <div class="modal-header" style="background:#f8fafc;border-bottom:1px solid #eef0f3">
                <h6 class="modal-title font-weight-bold mb-0"><i class="fas fa-map-pin text-success mr-1"></i> Add New Upazila</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">District: <strong id="upazilaModalDistrictName">—</strong></p>
                <label class="form-label-sm">Upazila Name <span class="badge-required">*</span></label>
                <input type="text" id="newUpazilaName" class="form-control-sm-custom" placeholder="e.g. Savar">
                <div class="text-danger small mt-1" id="upazilaError"></div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #eef0f3">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn-submit-pop py-1 px-3" id="saveUpazilaBtn" style="font-size:.8rem">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
$(function () {
    $('[data-toggle="tooltip"]').tooltip();

    // ── Smart Logo Upload (click + drag-drop + preview) ──
    const dropzone = document.getElementById('logoDropzone');
    const input    = document.getElementById('logoInput');
    const preview  = document.getElementById('logoPreview');
    const icon     = document.getElementById('logoIcon');
    const text     = document.getElementById('logoText');
    const filename = document.getElementById('logoFilename');
    const removeBtn= document.getElementById('logoRemoveBtn');

    dropzone.addEventListener('click', () => input.click());

    input.addEventListener('change', function () {
        if (this.files && this.files[0]) showPreview(this.files[0]);
    });

    ['dragenter', 'dragover'].forEach(evt => {
        dropzone.addEventListener(evt, function (e) {
            e.preventDefault(); e.stopPropagation();
            dropzone.classList.add('dragover');
        });
    });
    ['dragleave', 'drop'].forEach(evt => {
        dropzone.addEventListener(evt, function (e) {
            e.preventDefault(); e.stopPropagation();
            dropzone.classList.remove('dragover');
        });
    });
    dropzone.addEventListener('drop', function (e) {
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            input.files = e.dataTransfer.files;
            showPreview(file);
        }
    });

    function showPreview(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            icon.style.display = 'none';
            text.style.display = 'none';
            filename.textContent = file.name;
            removeBtn.style.display = 'inline-block';
        };
        reader.readAsDataURL(file);
    }

    removeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        input.value = '';
        preview.src = '';
        preview.style.display = 'none';
        icon.style.display = 'block';
        text.style.display = 'block';
        filename.textContent = '';
        removeBtn.style.display = 'none';
    });

    // ── Select All Menus + live count ─────────────────
    function updateMenuCount() {
        const total   = $('.menu-checkbox').length;
        const checked = $('.menu-checkbox:checked').length;
        $('#menuCountText').text(`${checked} of ${total} selected`);
        $('#selectAllMenus').prop('checked', checked === total && total > 0);
    }

    $('#selectAllMenus').on('change', function () {
        $('.menu-checkbox').prop('checked', this.checked);
        updateMenuCount();
    });
    $(document).on('change', '.menu-checkbox', updateMenuCount);
    updateMenuCount();


    // ── Quick Add Zone (AJAX, no page reload) ─────────
    $('#saveZoneBtn').on('click', function () {
        const name = $('#newZoneName').val().trim();
        $('#zoneError').text('');
        if (!name) { $('#zoneError').text('Zone name is required.'); return; }

        const $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: "{{ route('mac-reseller.list.quick-add-zone') }}",
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', name: name },
            success: function (res) {
                if (res.success) {
                    $('#zoneSelect').append(`<option value="${res.name}" selected>${res.name}</option>`);
                    $('#quickAddZoneModal').modal('hide');
                    $('#newZoneName').val('');
                    toastr.success('Zone added successfully.');
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                $('#zoneError').text(errors?.name ? errors.name[0] : 'Failed to add zone.');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save');
            }
        });
    });

    $('#quickAddZoneModal').on('shown.bs.modal', function () { $('#newZoneName').focus(); });
    $('#newZoneName').on('keypress', function (e) { if (e.which === 13) { e.preventDefault(); $('#saveZoneBtn').click(); } });

    // ── District → Upazila cascade (AJAX) ─────────────
    $('#districtSelect').on('change', function () {
        const districtId = $(this).find('option:selected').data('id');
        const $upazila    = $('#upazilaSelect');

        if (!districtId) {
            $upazila.html('<option value="">— Select District First —</option>');
            $('#addUpazilaBtn').hide();
            return;
        }

        $upazila.html('<option value="">Loading...</option>');
        $('#addUpazilaBtn').hide();

        $.get("{{ route('mac-reseller.list.get-upazilas') }}", { district_id: districtId })
            .done(function (data) {
                let opts = '<option value="">Select Upazila</option>';
                (data || []).forEach(u => opts += `<option value="${u.name}">${u.name}</option>`);
                $upazila.html(opts);
                $('#addUpazilaBtn').show();
            })
            .fail(function () {
                $upazila.html('<option value="">Failed to load</option>');
            });
    });

    // ── Quick Add Upazila (AJAX, no page reload) ──────
    $('#addUpazilaBtn').on('click', function () {
        const districtName = $('#districtSelect option:selected').text();
        $('#upazilaModalDistrictName').text(districtName || '—');
    });

    $('#saveUpazilaBtn').on('click', function () {
        const districtId = $('#districtSelect option:selected').data('id');
        const name = $('#newUpazilaName').val().trim();
        $('#upazilaError').text('');

        if (!districtId) { $('#upazilaError').text('Please select a District first.'); return; }
        if (!name) { $('#upazilaError').text('Upazila name is required.'); return; }

        const $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: "{{ route('mac-reseller.list.quick-add-upazila') }}",
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', district_id: districtId, name: name },
            success: function (res) {
                if (res.success) {
                    $('#upazilaSelect').append(`<option value="${res.name}" selected>${res.name}</option>`);
                    $('#quickAddUpazilaModal').modal('hide');
                    $('#newUpazilaName').val('');
                    toastr.success('Upazila added successfully.');
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                $('#upazilaError').text(errors?.name ? errors.name[0] : 'Failed to add upazila.');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save');
            }
        });
    });

    $('#quickAddUpazilaModal').on('shown.bs.modal', function () { $('#newUpazilaName').focus(); });
    $('#newUpazilaName').on('keypress', function (e) { if (e.which === 13) { e.preventDefault(); $('#saveUpazilaBtn').click(); } });
});
</script>
@stop
