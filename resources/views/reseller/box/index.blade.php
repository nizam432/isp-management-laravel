@extends('reseller.layouts.app')

@section('title', 'Box')

@section('content')

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-plus mr-1"></i> Add Box</div>
            <div class="card-body">
                @if($zones->isEmpty())
                    <p class="text-muted mb-0">
                        You don't have any Zone yet.
                        <a href="{{ route('reseller.configuration.zone.index') }}">Add a Zone first</a>.
                    </p>
                @else
                <form method="POST" action="{{ route('reseller.configuration.box.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Zone</label>
                        <select name="mac_reseller_zone_id" id="box-zone-select" class="form-control @error('mac_reseller_zone_id') is-invalid @enderror" required>
                            <option value="">Select Zone</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ old('mac_reseller_zone_id') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('mac_reseller_zone_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Sub Zone</label>
                        <select name="mac_reseller_sub_zone_id" id="box-subzone-select" class="form-control @error('mac_reseller_sub_zone_id') is-invalid @enderror" required>
                            <option value="">Select Zone first</option>
                        </select>
                        @error('mac_reseller_sub_zone_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Box Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Details (optional)</label>
                        <textarea name="details" class="form-control" rows="2">{{ old('details') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-plus mr-1"></i> Add Box
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-box mr-1"></i> Your Boxes</div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Zone</th>
                            <th>Sub Zone</th>
                            <th>Details</th>
                            <th>Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boxes as $box)
                        <tr>
                            <td>{{ $box->name }}</td>
                            <td>{{ $box->zone->name ?? '—' }}</td>
                            <td>{{ $box->subZone->name ?? '—' }}</td>
                            <td>{{ $box->details ?? '—' }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input toggle-box"
                                        id="bx-{{ $box->id }}" data-id="{{ $box->id }}"
                                        {{ $box->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="bx-{{ $box->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-box-btn"
                                    data-id="{{ $box->id }}"
                                    data-name="{{ $box->name }}"
                                    data-details="{{ $box->details }}"
                                    data-zone-id="{{ $box->mac_reseller_zone_id }}"
                                    data-subzone-id="{{ $box->mac_reseller_sub_zone_id }}"
                                    data-toggle="modal" data-target="#editBoxModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('reseller.configuration.box.destroy', $box->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this box?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No boxes added yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit Box Modal --}}
<div class="modal fade" id="editBoxModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editBoxForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Box</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Zone</label>
                        <select name="mac_reseller_zone_id" id="edit-box-zone" class="form-control" required>
                            <option value="">Select Zone</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sub Zone</label>
                        <select name="mac_reseller_sub_zone_id" id="edit-box-subzone" class="form-control" required>
                            <option value="">Select Zone first</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Box Name</label>
                        <input type="text" name="name" id="edit-box-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Details</label>
                        <textarea name="details" id="edit-box-details" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
// all sub-zones for this reseller, grouped by zone id — powers the dependent dropdowns
var allSubZones = {!! $subZones->groupBy('mac_reseller_zone_id')->toJson() !!};

function populateSubZoneSelect($select, zoneId, selectedSubZoneId) {
    $select.empty().append('<option value="">Select Sub Zone</option>');
    var list = allSubZones[zoneId] || [];
    list.forEach(function (sz) {
        var selected = (selectedSubZoneId && String(selectedSubZoneId) === String(sz.id)) ? 'selected' : '';
        $select.append('<option value="' + sz.id + '" ' + selected + '>' + sz.name + '</option>');
    });
}

// Add form
$('#box-zone-select').on('change', function () {
    populateSubZoneSelect($('#box-subzone-select'), $(this).val());
});

// Edit modal
$(document).on('click', '.edit-box-btn', function () {
    var id = $(this).data('id');
    var zoneId = $(this).data('zone-id');
    var subZoneId = $(this).data('subzone-id');

    $('#edit-box-name').val($(this).data('name'));
    $('#edit-box-details').val($(this).data('details'));
    $('#edit-box-zone').val(zoneId);
    populateSubZoneSelect($('#edit-box-subzone'), zoneId, subZoneId);

    $('#editBoxForm').attr('action', "{{ route('reseller.configuration.box.update', ['__ID__']) }}".replace('__ID__', id));
});

$('#edit-box-zone').on('change', function () {
    populateSubZoneSelect($('#edit-box-subzone'), $(this).val());
});

$(document).on('change', '.toggle-box', function () {
    var id = $(this).data('id');
    $.post("{{ route('reseller.configuration.box.toggle', ['__ID__']) }}".replace('__ID__', id), { _token: '{{ csrf_token() }}' });
});
</script>
@endsection