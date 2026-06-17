@extends('adminlte::page')

@section('title', 'Package Configuration')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Package Configuration</h1>
            <small class="text-muted">Configure Packages</small>
        </div>
        <div class="text-right">
            <span class="text-muted">
                <i class="fas fa-users-cog"></i> POP &rsaquo; Package
            </span>
        </div>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="text-right mb-3">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addPackageModal">
                <i class="fas fa-plus"></i> Package
            </button>
        </div>

        <div class="row mb-2">
            <div class="col-sm-2">
                <label>SHOW</label>
                <select id="perPage" class="form-control form-control-sm">
                    <option>25</option><option selected>100</option><option>200</option>
                </select>
            </div>
            <div class="col-sm-2 d-flex align-items-end"><span>ENTRIES</span></div>
            <div class="col-sm-4 offset-sm-4 text-right d-flex align-items-end justify-content-end">
                <label class="mr-2 mb-0">SEARCH:</label>
                <input type="text" id="searchInput" class="form-control form-control-sm" style="width:200px">
            </div>
        </div>

        <table class="table table-bordered table-striped" id="packageTable">
            <thead class="bg-dark text-white">
                <tr>
                    <th>Serial No.</th>
                    <th>Package Name</th>
                    <th>Bandwidth_Allocation MB</th>
                    <th>Package Details</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $i => $pkg)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $pkg->name }}</td>
                    <td>{{ $pkg->bandwidth_mb }}</td>
                    <td>{{ $pkg->details }}</td>
                    <td>
                        <button class="btn btn-sm btn-success edit-btn"
                            data-id="{{ $pkg->id }}"
                            data-name="{{ $pkg->name }}"
                            data-bandwidth="{{ $pkg->bandwidth_mb }}"
                            data-details="{{ $pkg->details }}"
                            data-toggle="modal" data-target="#editPackageModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $pkg->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">No packages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Package Modal --}}
<div class="modal fade" id="addPackageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Add Package</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="addPackageForm">
                    @csrf
                    <div class="form-group">
                        <label class="text-uppercase font-weight-bold small">
                            PACKAGE NAME <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="text-uppercase font-weight-bold small">
                            BANDWITH ALLOCATION MB (ONLY FOR BTRC REPORT) <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="bandwidth_mb" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="text-uppercase font-weight-bold small">DETAILS(OPTIONAL)</label>
                        <textarea name="details" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="d-flex justify-content-between flex-wrap" style="gap:8px">
                        <button type="button" class="btn btn-danger btn-sm px-4"
                            onclick="document.getElementById('addPackageForm').reset()">Clear</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Save</button>
                    </div>
                    <div class="text-right mt-2">
                        <button type="button" class="btn btn-danger btn-sm px-4" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Package Modal --}}
<div class="modal fade" id="editPackageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Edit Package</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editPackageForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editPackageId">
                    <div class="form-group">
                        <label class="text-uppercase font-weight-bold small">
                            PACKAGE NAME <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="editName" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="text-uppercase font-weight-bold small">
                            BANDWITH ALLOCATION MB (ONLY FOR BTRC REPORT) <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="editBandwidth" name="bandwidth_mb" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="text-uppercase font-weight-bold small">DETAILS(OPTIONAL)</label>
                        <textarea id="editDetails" name="details" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="d-flex justify-content-between flex-wrap" style="gap:8px">
                        <button type="button" class="btn btn-danger btn-sm px-4"
                            onclick="document.getElementById('editPackageForm').reset()">Clear</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Update</button>
                    </div>
                    <div class="text-right mt-2">
                        <button type="button" class="btn btn-danger btn-sm px-4" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
const ROUTES = {
    store:   "{{ route('mac-reseller.package.store') }}",
    update:  (id) => `/mac-reseller/package/${id}`,
    destroy: (id) => `/mac-reseller/package/${id}`,
};

// Add Package
$('#addPackageForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: ROUTES.store,
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if (res.success) {
                $('#addPackageModal').modal('hide');
                toastr.success(res.message);
                setTimeout(() => location.reload(), 800);
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) toastr.error(Object.values(errors).flat().join('\n'));
        }
    });
});

// Edit button → fill modal
$(document).on('click', '.edit-btn', function() {
    $('#editPackageId').val($(this).data('id'));
    $('#editName').val($(this).data('name'));
    $('#editBandwidth').val($(this).data('bandwidth'));
    $('#editDetails').val($(this).data('details'));
});

// Update Package
$('#editPackageForm').on('submit', function(e) {
    e.preventDefault();
    const id = $('#editPackageId').val();
    $.ajax({
        url: ROUTES.update(id),
        method: 'POST',
        data: $(this).serialize() + '&_method=PUT',
        success: function(res) {
            if (res.success) {
                $('#editPackageModal').modal('hide');
                toastr.success(res.message);
                setTimeout(() => location.reload(), 800);
            }
        }
    });
});

// Delete
$(document).on('click', '.delete-btn', function() {
    const id = $(this).data('id');
    if (!confirm('Are you sure?')) return;
    $.ajax({
        url: ROUTES.destroy(id),
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
        success: function(res) {
            if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 800); }
        }
    });
});

// Search
$('#searchInput').on('keyup', function() {
    const val = $(this).val().toLowerCase();
    $('#packageTable tbody tr').each(function() {
        $(this).toggle($(this).text().toLowerCase().includes(val));
    });
});
</script>
@stop
