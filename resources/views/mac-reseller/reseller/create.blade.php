@extends('adminlte::page')

@section('title', 'Add MAC Reseller')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Add POPs <small class="text-muted">Adding POP</small></h1>
        <div>
            <a href="{{ route('mac-reseller.list.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-users-cog"></i> POP &rsaquo; Add POPs
            </a>
        </div>
    </div>
@stop

@section('content')
<form action="{{ route('mac-reseller.list.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Personal Information --}}
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-user-circle text-secondary mr-2"></i> Personal Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">CONTACT PERSON NAME <span class="text-danger">*</span></label>
                        <input type="text" name="contact_person" class="form-control form-control-sm @error('contact_person') is-invalid @enderror" value="{{ old('contact_person') }}" required>
                        @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">EMAIL ADDRESS <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">MOBILE NO. <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" class="form-control form-control-sm @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}" required>
                        @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">PHONE NO.</label>
                        <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">NATIONAL ID</label>
                        <input type="text" name="national_id" class="form-control form-control-sm" value="{{ old('national_id') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">DISTRICT <span class="text-danger">*</span></label>
                        <select name="district" class="form-control form-control-sm">
                            <option value="">Select</option>
                            @foreach(['Dhaka','Chittagong','Sylhet','Rajshahi','Khulna','Barisal','Rangpur','Mymensingh'] as $d)
                            <option value="{{ $d }}" {{ old('district') == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">UPAZILA <span class="text-danger">*</span></label>
                        <select name="upazila" class="form-control form-control-sm">
                            <option value="">Select</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">ZONE <span class="text-danger">*</span></label>
                        <select name="zone" class="form-control form-control-sm">
                            <option value="">Select</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">POP CODE <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control form-control-sm bg-light" value="{{ $nextCode }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">POP PREFIX <i class="fas fa-info-circle text-info"></i></label>
                        <input type="text" name="pop_prefix" class="form-control form-control-sm" placeholder="Ex: AB1" value="{{ old('pop_prefix') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">SET PREFIX IN MIKROTIK USERNAME? <i class="fas fa-info-circle text-info"></i></label>
                        <select name="use_prefix_in_mikrotik_username" class="form-control form-control-sm">
                            <option value="0">No, I Don't</option>
                            <option value="1">Yes, I Want</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">POP TYPE <i class="fas fa-info-circle text-info"></i></label>
                        <select name="pop_type" class="form-control form-control-sm">
                            <option value="prepaid">Prepaid</option>
                            <option value="postpaid">Postpaid</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">MINIMUM RECHARGEABLE AMOUNT <i class="fas fa-info-circle text-info"></i></label>
                        <input type="number" name="min_rechargeable_amount" class="form-control form-control-sm" value="{{ old('min_rechargeable_amount', 5) }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="small font-weight-bold">ADDRESS <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control form-control-sm" rows="2" required>{{ old('address') }}</textarea>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">POP LOGO</label>
                        <input type="file" name="logo" class="form-control-file form-control-sm" accept="image/*">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Business & Login Information --}}
    <div class="card mt-3">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-briefcase text-secondary mr-2"></i> Business & Login Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">POP / BUSINESS NAME <span class="text-danger">*</span></label>
                        <input type="text" name="business_name" class="form-control form-control-sm @error('business_name') is-invalid @enderror" value="{{ old('business_name') }}" required>
                        @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">TARIFF NAME <span class="text-danger">*</span></label>
                        <select name="tariff_id" class="form-control form-control-sm">
                            <option value="">Select</option>
                            @foreach($tariffs as $t)
                            <option value="{{ $t->id }}" {{ old('tariff_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">WANT TO DISABLE CLIENTS? <i class="fas fa-info-circle text-info"></i></label>
                        <select name="want_to_disable_clients" class="form-control form-control-sm">
                            <option value="1">Yes, I Want</option>
                            <option value="0">No, I Don't</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">MINIMUM BALANCE</label>
                        <input type="number" name="min_balance" class="form-control form-control-sm" value="{{ old('min_balance', 0) }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="small font-weight-bold">USER NAME <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control form-control-sm @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="small font-weight-bold">PASSWORD <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="small font-weight-bold">CONFIRM PASSWORD <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control form-control-sm" required>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- POP Menus --}}
    <div class="card mt-3">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-bars text-secondary mr-2"></i> POP Menus</h5>
        </div>
        <div class="card-body">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="selectAllMenus">
                <label class="form-check-label font-weight-bold" for="selectAllMenus">SELECT ALL MENUS</label>
            </div>
            <div class="row">
                @foreach($menus as $menu)
                <div class="col-md-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input menu-checkbox" type="checkbox"
                            name="allowed_menus[]" value="{{ $menu }}"
                            id="menu-{{ Str::slug($menu) }}"
                            {{ in_array($menu, old('allowed_menus', [])) ? 'checked' : '' }}>
                        <label class="form-check-label small text-uppercase" for="menu-{{ Str::slug($menu) }}">
                            {{ $menu }}
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="d-flex justify-content-between mt-3 mb-4">
        <a href="{{ route('mac-reseller.list.index') }}" class="btn btn-secondary btn-sm px-4">
            <i class="fas fa-list mr-1"></i> Go To List
        </a>
        <button type="submit" class="btn btn-primary btn-sm px-4">
            Save <i class="fas fa-chevron-right ml-1"></i>
        </button>
    </div>
</form>
@stop

@section('js')
<script>
$('#selectAllMenus').on('change', function() {
    $('.menu-checkbox').prop('checked', this.checked);
});
$('.menu-checkbox').on('change', function() {
    if (!this.checked) $('#selectAllMenus').prop('checked', false);
    if ($('.menu-checkbox:checked').length === $('.menu-checkbox').length) {
        $('#selectAllMenus').prop('checked', true);
    }
});
</script>
@stop
