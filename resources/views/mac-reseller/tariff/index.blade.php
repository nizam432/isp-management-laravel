@extends('adminlte::page')

@section('title', 'POP Tariffs')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-cube mr-1"></i> POP Tariffs</h1>
            <small class="text-muted">POP Tariff Config</small>
        </div>
        <span class="text-muted"><i class="fas fa-users-cog"></i> POP &rsaquo; Tariff Config <i class="fas fa-sync-alt"></i></span>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="text-right mb-3">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addTariffModal">
                <i class="fas fa-plus"></i> Add Tariff
            </button>
        </div>

        <div class="row mb-2">
            <div class="col-sm-2 d-flex align-items-center" style="gap:8px">
                <label class="mb-0">SHOW</label>
                <select class="form-control form-control-sm" style="width:80px">
                    <option>25</option><option selected>100</option>
                </select>
                <span>ENTRIES</span>
            </div>
            <div class="col-sm-4 offset-sm-6 text-right d-flex align-items-center justify-content-end" style="gap:8px">
                <label class="mb-0">SEARCH:</label>
                <input type="text" id="searchInput" class="form-control form-control-sm" style="width:200px">
            </div>
        </div>

        <table class="table table-bordered table-striped" id="tariffTable">
            <thead class="bg-dark text-white">
                <tr>
                    <th>S/N</th>
                    <th>Tariff Name</th>
                    <th>Assigned POPs</th>
                    <th>Packages</th>
                    <th>Servers</th>
                    <th>Profiles</th>
                    <th>CreatedOn</th>
                    <th>CreatedBy</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tariffs as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $t->name }}</td>
                    <td>{{ $t->resellers->pluck('business_name')->join(', ') }}</td>
                    <td>{{ $t->packages->map(fn($p) => $p->package?->name)->filter()->join(', ') }}</td>
                    <td>{{ $t->packages->pluck('server_name')->filter()->join(', ') }}</td>
                    <td>{{ $t->packages->pluck('profile')->filter()->join(', ') }}</td>
                    <td>{{ $t->created_at->format('d M Y') }}</td>
                    <td>{{ $t->createdBy?->name }}</td>
                    <td>
                        <button class="btn btn-sm btn-info sync-btn" data-id="{{ $t->id }}" title="Sync Mikrotik">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-warning toggle-btn" data-id="{{ $t->id }}" title="Toggle">
                            <i class="fas fa-circle" style="color:{{ $t->is_active ? 'green' : 'red' }}"></i>
                        </button>
                        <button class="btn btn-sm btn-success edit-tariff-btn"
                            data-id="{{ $t->id }}"
                            data-toggle="modal" data-target="#editTariffModal" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-primary view-btn" data-id="{{ $t->id }}" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-tariff-btn" data-id="{{ $t->id }}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center">No tariffs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Tariff Modal --}}
<div class="modal fade" id="addTariffModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Tariff</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="addTariffForm">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold small">TARIFF TYPE <span class="text-danger">*</span>
                            <i class="fas fa-info-circle text-info ml-1"></i>
                        </label>
                        <div>
                            <div class="custom-control custom-radio d-inline-block mr-4">
                                <input type="radio" id="typeCustom" name="tariff_type" value="custom" class="custom-control-input" checked>
                                <label class="custom-control-label" for="typeCustom">
                                    <i class="fas fa-arrow-circle-right text-warning"></i> Custom
                                </label>
                            </div>
                            <div class="custom-control custom-radio d-inline-block">
                                <input type="radio" id="typeDate" name="tariff_type" value="date_to_date" class="custom-control-input">
                                <label class="custom-control-label" for="typeDate">
                                    <i class="fas fa-arrow-circle-right text-warning"></i> Date To Date
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">TARIFF NAME</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold">Add Package Lines</h6>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small">PACKAGE NAME</label>
                                <select id="linePackage" class="form-control form-control-sm">
                                    <option value="">Select</option>
                                    @foreach($packages as $pkg)
                                    <option value="{{ $pkg->id }}" data-name="{{ $pkg->name }}">{{ $pkg->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="small">SERVER NAME</label>
                                <input type="text" id="lineServer" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="small">PACKAGE RATE</label>
                                <input type="number" id="lineRate" class="form-control form-control-sm" min="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="small">VALIDITY DAYS</label>
                                <input type="number" id="lineValidity" class="form-control form-control-sm" value="30" min="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="small">MIN ACTIVATION DAYS</label>
                                <input type="number" id="lineMinActivation" class="form-control form-control-sm" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" id="addLineBtn" class="btn btn-dark btn-sm mb-3 w-100">Add Package</button>
                        </div>
                    </div>

                    <table class="table table-sm table-bordered" id="linesTable">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>Sr. No.</th>
                                <th>Package</th>
                                <th>Server</th>
                                <th>Protocol</th>
                                <th>Profile</th>
                                <th>Rate</th>
                                <th>Validity Days</th>
                                <th>Min Activation Days</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="linesTbody">
                            <tr id="noLinesRow"><td colspan="9" class="text-center text-muted">No packages added yet.</td></tr>
                        </tbody>
                    </table>
                    <div id="linesData"></div>

                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-danger btn-sm px-4" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-secondary btn-sm px-4">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
let lineCount = 0;

$('#addLineBtn').on('click', function() {
    const pkgEl  = $('#linePackage');
    const pkgId  = pkgEl.val();
    const pkgName = pkgEl.find('option:selected').data('name');
    const server  = $('#lineServer').val();
    const rate    = $('#lineRate').val() || 0;
    const validity = $('#lineValidity').val() || 30;
    const minAct  = $('#lineMinActivation').val() || 1;

    if (!pkgId) { toastr.warning('Please select a package.'); return; }

    $('#noLinesRow').hide();
    lineCount++;
    const row = `<tr id="line-${lineCount}">
        <td>${lineCount}</td>
        <td>${pkgName}</td>
        <td>${server}</td>
        <td>-</td>
        <td>-</td>
        <td>${rate}</td>
        <td>${validity}</td>
        <td>${minAct}</td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="$('#line-${lineCount}').remove(); removeLine(${lineCount})"><i class="fas fa-times"></i></button></td>
    </tr>`;
    $('#linesTbody').append(row);

    $('#linesData').append(`
        <input type="hidden" name="lines[${lineCount}][package_id]" value="${pkgId}">
        <input type="hidden" name="lines[${lineCount}][server_name]" value="${server}">
        <input type="hidden" name="lines[${lineCount}][rate]" value="${rate}">
        <input type="hidden" name="lines[${lineCount}][validity_days]" value="${validity}">
        <input type="hidden" name="lines[${lineCount}][min_activation_days]" value="${minAct}">
    `);
});

function removeLine(idx) {
    $(`input[name^="lines[${idx}]"]`).remove();
}

$('#addTariffForm').on('submit', function(e) {
    e.preventDefault();
    if (lineCount === 0) { toastr.warning('Please add at least one package.'); return; }
    $.ajax({
        url: "{{ route('mac-reseller.tariff.store') }}",
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if (res.success) {
                $('#addTariffModal').modal('hide');
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

// Delete
$(document).on('click', '.delete-tariff-btn', function() {
    const id = $(this).data('id');
    if (!confirm('Delete this tariff?')) return;
    $.ajax({
        url: `/mac-reseller/tariff/${id}`,
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
        success: (res) => { if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 800); } }
    });
});

// Toggle
$(document).on('click', '.toggle-btn', function() {
    const id = $(this).data('id');
    $.post(`/mac-reseller/tariff/${id}/toggle`, { _token: '{{ csrf_token() }}' }, () => location.reload());
});

// Sync
$(document).on('click', '.sync-btn', function() {
    const id = $(this).data('id');
    $.post(`/mac-reseller/tariff/${id}/sync-mikrotik`, { _token: '{{ csrf_token() }}' },
        (res) => toastr.success(res.message)
    );
});

// Search
$('#searchInput').on('keyup', function() {
    const val = $(this).val().toLowerCase();
    $('#tariffTable tbody tr').each(function() {
        $(this).toggle($(this).text().toLowerCase().includes(val));
    });
});
</script>
@stop
