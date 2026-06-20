{{-- resources/views/packages/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Packages')
@section('page_actions')
    <a href="{{ route('packages.sync.preview') }}" class="btn btn-success btn-sm mr-1">
        <i class="fas fa-sync mr-1"></i> Sync from MikroTik
    </a>
    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#packageModal"
            onclick="resetPackageForm()">
        <i class="fas fa-plus mr-1"></i> Add Package
    </button>
@endsection
@section('page_content')

{{-- Stats --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ $packages->total() }}</h3><p>Total Packages</p></div>
            <div class="icon"><i class="fas fa-box"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $packages->getCollection()->where('is_active', true)->count() }}</h3><p>Active</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $packages->getCollection()->sum('customers_count') }}</h3><p>Total Customers</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $packages->getCollection()->where('mikrotik_profile', null)->count() }}</h3>
                <p>No MikroTik Profile</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

{{-- Package Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-box mr-1"></i> Package List</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Speed</th>
                    <th>Data Limit</th>
                    <th>Validity</th>
                    <th>Price</th>
                    <th>Connection Fee</th>
                    <th>Client Type</th>
                    <th>Protocol</th>
                    <th>MikroTik Profile</th>
                    <th class="text-center">Customers</th>
                    <th>Status</th>
                    <th style="width:120px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $i => $pkg)
                <tr>
                    <td class="text-muted small">{{ $packages->firstItem() + $i }}</td>
                    <td>
                        <strong>{{ $pkg->name }}</strong>
                        @if($pkg->description)
                            <br><small class="text-muted">{{ Str::limit($pkg->description, 40) }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-info">
                            <i class="fas fa-arrow-down mr-1"></i>{{ $pkg->speed_download }} Mbps
                        </span>
                        <br>
                        <span class="badge badge-secondary mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>{{ $pkg->speed_upload }} Mbps
                        </span>
                    </td>
                    <td>
                        @if($pkg->data_limit == 0)
                            <span class="badge badge-success">Unlimited</span>
                        @else
                            <span class="badge badge-warning">{{ $pkg->data_limit }} GB</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-light border">{{ $pkg->validity_days }} days</span>
                    </td>
                    <td>
                        <strong>৳ {{ number_format($pkg->price) }}</strong>
                        <br><small class="text-muted">/month</small>
                    </td>
                    <td>
                        @if($pkg->connection_fee > 0)
                            <span class="text-muted">৳ {{ number_format($pkg->connection_fee) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-primary">
                            {{ $pkg->client_type_id == 0 ? 'All' : ($pkg->clientType->name ?? '—') }}
                        </span>
                    </td>
                    <td>
                        @if($pkg->protocolType)
                            <span class="badge badge-dark">{{ $pkg->protocolType->name }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($pkg->mikrotik_profile)
                            <code class="small">{{ $pkg->mikrotik_profile }}</code>
                        @else
                            <span class="badge badge-danger">Not Set</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-{{ $pkg->customers_count > 0 ? 'info' : 'secondary' }}">
                            {{ $pkg->customers_count }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $pkg->is_active ? 'success' : 'secondary' }}">
                            {{ $pkg->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        {{-- Edit --}}
                        <button class="btn btn-xs btn-warning"
                                onclick="editPackage({{ $pkg->toJson() }})"
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        {{-- Toggle --}}
                        <form action="{{ route('packages.toggle', $pkg) }}" method="POST" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="btn btn-xs btn-{{ $pkg->is_active ? 'secondary' : 'success' }}"
                                    title="{{ $pkg->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas fa-{{ $pkg->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>
                        {{-- Delete --}}
                        @if($pkg->customers_count === 0)
                        <form action="{{ route('packages.destroy', $pkg) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button"
                                    class="btn btn-xs btn-danger swal-delete"
                                    data-message="Package '{{ $pkg->name }}' will be permanently deleted."
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @else
                        <button class="btn btn-xs btn-danger" disabled
                                title="{{ $pkg->customers_count }} customers assigned">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center text-muted py-4">
                        <i class="fas fa-box fa-2x d-block mb-2"></i>
                        No packages found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Total {{ $packages->total() }} packages — page {{ $packages->currentPage() }}/{{ $packages->lastPage() }}
        </small>
        {{ $packages->links('pagination::bootstrap-4') }}
    </div>
</div>

{{-- ── Add / Edit Package Modal ──────────────────────── --}}
<div class="modal fade" id="packageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="packageModalTitle">
                    <i class="fas fa-plus mr-1"></i> Add Package
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="packageForm" action="{{ route('packages.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="packageMethod" value="POST">
                <div class="modal-body">
                    <div class="row">
                        {{-- Name --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Package Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="pkgName"
                                       class="form-control" placeholder="e.g. Home 10 Mbps" required>
                            </div>
                        </div>
                        {{-- Type --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Client Type <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="client_type_id" id="pkgType" class="form-control" required>
                                        <option value="0" selected>All Client</option>
                                        @foreach($clientTypes as $ct)
                                            <option value="{{ $ct->id }}">{{ ucfirst($ct->name) }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary"
                                                data-toggle="modal" data-target="#addTypeModal"
                                                title="Add new type">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Speed Download --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Download Speed <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="speed_download" id="pkgDownload"
                                           class="form-control" placeholder="10" min="1" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">Mbps</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Speed Upload --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Upload Speed <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="speed_upload" id="pkgUpload"
                                           class="form-control" placeholder="10" min="1" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">Mbps</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Data Limit --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Data Limit</label>
                                <div class="input-group">
                                    <input type="number" name="data_limit" id="pkgDataLimit"
                                           class="form-control" value="0" placeholder="0 = Unlimited" min="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text">GB</span> 
                                    </div>
                                </div>
                                <small class="text-muted">0 = Unlimited</small>
                            </div>
                        </div> 
                        {{-- Connection Fee --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Connection Fee</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">৳</span>
                                    </div>
                                    <input type="number" name="connection_fee" id="pkgConnectionFee"
                                           class="form-control" placeholder="0" min="0" step="0.01">
                                </div>
                            </div>
                        </div>                        
                        {{-- Validity --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Validity <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="validity_days" id="pkgValidityDays"
                                           class="form-control" value="30" min="1" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">Days</span>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                        {{-- Price --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">৳</span>
                                    </div>
                                    <input type="number" name="price" id="pkgPrice"
                                           class="form-control" placeholder="500" min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>BTRC Bandwidth <small class="text-muted">(optional)</small></label>
                                <input type="text" name="btrc_bandwidth" id="pkgBtrcBandwidth"
                                       class="form-control" placeholder="e.g. 10Mbps">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>BTRC Price <small class="text-muted">(optional)</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">৳</span>
                                    </div>
                                    <input type="number" name="btrc_price" id="pkgBtrcPrice"
                                           class="form-control" placeholder="0" min="0" step="0.01">
                                </div>
                            </div>
                        </div> 

                        {{-- Protocol Type --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Protocol Type</label>
                                <select name="protocol_type_id" id="pkgProtocolType" class="form-control"
                                        onchange="loadMikrotikProfiles(this.value)">
                                    <option value="">-- Select Protocol --</option>
                                    @foreach($protocolTypes as $pt)
                                        <option value="{{ $pt->id }}" data-slug="{{ \Illuminate\Support\Str::slug($pt->name) }}">
                                            {{ $pt->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Selecting a protocol loads its profiles below.</small>
                            </div>
                        </div>                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">MikroTik Profile</label>

                                {{-- Toggle --}}
                                <div class="mb-2">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary active" id="btnSelectProfile"
                                                onclick="toggleProfile('select')">Select Existing</button>
                                        <button type="button" class="btn btn-outline-success" id="btnNewProfile"
                                                onclick="toggleProfile('new')">+ Create New</button>
                                    </div>
                                </div>

                               
                            </div>
                        </div>
                        
                             <div class="col-md-4">
                            <div class="form-group">
 {{-- Select Existing --}}
                                <div id="selectProfileDiv">
                                    <select name="mikrotik_profile" id="pkgMikrotikProfile" class="form-control">
                                        <option value="">-- Select Protocol Type first --</option>
                                        @foreach($mikrotikProfiles as $profile)
                                            <option value="{{ $profile }}">{{ $profile }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" id="profileLoadingHint" style="display:none;">
                                        <i class="fas fa-spinner fa-spin mr-1"></i> Loading profiles...
                                    </small>
                                </div>

                                {{-- Create New --}}
                                <div id="newProfileDiv" style="display:none;">
                                    <input type="text" name="new_mikrotik_profile" id="newProfileName"
                                           class="form-control" placeholder="Profile name e.g. 10MB">
                                    <small class="text-muted">
                                        Speed will be taken from package Download/Upload — auto created on all routers.
                                    </small>
                                </div>
                                </div>
                                </div>
                       
                        {{-- Description --}}
                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Description</label>
                                <textarea name="description" id="pkgDescription"
                                          class="form-control" rows="2"
                                          placeholder="Optional..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="pkgSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Save Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Add Type Modal ─────────────────────────────────── --}}
<div class="modal fade" id="addTypeModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-plus mr-1"></i> Add New Type</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label class="font-weight-bold">Type Name</label>
                    <input type="text" id="newTypeName" class="form-control"
                           placeholder="e.g. Corporate">
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-primary btn-sm" onclick="addNewType()">
                    <i class="fas fa-save mr-1"></i> Add
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
// ── Reset form for Add ────────────────────────────────
function resetPackageForm() {
    document.getElementById('packageModalTitle').innerHTML = '<i class="fas fa-plus mr-1"></i> Add Package';
    document.getElementById('packageMethod').value  = 'POST';
    document.getElementById('packageForm').action   = '{{ route("packages.store") }}';
    document.getElementById('packageForm').reset();
    document.getElementById('pkgValidityDays').value = 30;
    document.getElementById('pkgSubmitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Save Package';
    toggleProfile('select');
    document.getElementById('pkgMikrotikProfile').innerHTML = '<option value="">-- Select Protocol Type first --</option>';
}

// ── Edit Package ──────────────────────────────────────
function editPackage(pkg) {
    document.getElementById('packageModalTitle').innerHTML = '<i class="fas fa-edit mr-1"></i> Edit Package';
    document.getElementById('packageMethod').value  = 'PUT';
    document.getElementById('packageForm').action   = '/packages/' + pkg.id;
    document.getElementById('pkgName').value             = pkg.name;
    document.getElementById('pkgType').value             = pkg.client_type_id;
    document.getElementById('pkgProtocolType').value     = pkg.protocol_type_id || '';
    document.getElementById('pkgDownload').value         = pkg.speed_download;
    document.getElementById('pkgUpload').value           = pkg.speed_upload;
    document.getElementById('pkgPrice').value            = pkg.price;
    document.getElementById('pkgConnectionFee').value    = pkg.connection_fee || 0;
    document.getElementById('pkgDataLimit').value        = pkg.data_limit || 0;
    document.getElementById('pkgValidityDays').value     = pkg.validity_days || 30;
    document.getElementById('pkgBtrcBandwidth').value    = pkg.btrc_bandwidth || '';
    document.getElementById('pkgBtrcPrice').value        = pkg.btrc_price || '';
    document.getElementById('pkgDescription').value      = pkg.description || '';
    document.getElementById('pkgSubmitBtn').innerHTML    = '<i class="fas fa-save mr-1"></i> Update Package';

    toggleProfile('select');

    // Load profiles for this package's protocol, then pre-select its current profile
    if (pkg.protocol_type_id) {
        loadMikrotikProfiles(pkg.protocol_type_id, pkg.mikrotik_profile);
    } else {
        document.getElementById('pkgMikrotikProfile').innerHTML =
            '<option value="">-- Select Protocol Type first --</option>';
    }

    $('#packageModal').modal('show');
}

// ── Load MikroTik profiles for the selected protocol (AJAX) ──
function loadMikrotikProfiles(protocolTypeId, preselect) {
    var select = document.getElementById('pkgMikrotikProfile');
    var hint   = document.getElementById('profileLoadingHint');

    if (!protocolTypeId) {
        select.innerHTML = '<option value="">-- Select Protocol Type first --</option>';
        return;
    }

    var protocolOption = document.querySelector('#pkgProtocolType option[value="' + protocolTypeId + '"]');
    var slug = protocolOption ? protocolOption.getAttribute('data-slug') : '';

    // Static connections have no MikroTik profile concept — let user type manually
    if (slug === 'static' || slug === 'svpn') {
        select.innerHTML = '<option value="">-- No profile list for this protocol --</option>';
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

// ── Add New Type ──────────────────────────────────────
function addNewType() {
    var name = document.getElementById('newTypeName').value.trim().toLowerCase();
    if (!name) return;

    // AJAX save to DB
    fetch('/package-types', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ name: name })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Add to the dropdown
            var select = document.getElementById('pkgType');
            var option = new Option(name.charAt(0).toUpperCase() + name.slice(1), name, true, true);
            select.add(option);
            document.getElementById('newTypeName').value = '';
            $('#addTypeModal').modal('hide');
            $('#packageModal').modal('show');
        }
    });
}
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
        document.getElementById('pkgMikrotikProfile').value = '';
    }
}
</script>
@endpush