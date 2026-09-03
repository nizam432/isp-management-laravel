@extends('reseller.layouts.app')

@section('title', 'Sub Zone')

@section('content')



<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-plus mr-1"></i> Add Sub Zone</div>
            <div class="card-body">
                @if($zones->isEmpty())
                    <p class="text-muted mb-0">
                        You don't have any Zone yet.
                        <a href="{{ route('reseller.configuration.zone.index') }}">Add a Zone first</a>.
                    </p>
                @else
                <form method="POST" action="{{ route('reseller.configuration.subzone.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Zone</label>
                        <select name="mac_reseller_zone_id" class="form-control @error('mac_reseller_zone_id') is-invalid @enderror" required>
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
                        <label>Sub Zone Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Details (optional)</label>
                        <textarea name="details" class="form-control" rows="2">{{ old('details') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-plus mr-1"></i> Add Sub Zone
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-map-marker-alt mr-1"></i> Your Sub Zones</div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Zone</th>
                            <th>Details</th>
                            <th>Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subZones as $subZone)
                        <tr>
                            <td>{{ $subZone->name }}</td>
                            <td>{{ $subZone->zone->name ?? '—' }}</td>
                            <td>{{ $subZone->details ?? '—' }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input toggle-subzone"
                                        id="sz-{{ $subZone->id }}" data-id="{{ $subZone->id }}"
                                        {{ $subZone->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="sz-{{ $subZone->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-subzone-btn"
                                    data-id="{{ $subZone->id }}"
                                    data-name="{{ $subZone->name }}"
                                    data-details="{{ $subZone->details }}"
                                    data-toggle="modal" data-target="#editSubZoneModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('reseller.configuration.subzone.destroy', $subZone->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this sub zone?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No sub zones added yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit Sub Zone Modal --}}
<div class="modal fade" id="editSubZoneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editSubZoneForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sub Zone</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Sub Zone Name</label>
                        <input type="text" name="name" id="edit-subzone-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Details</label>
                        <textarea name="details" id="edit-subzone-details" class="form-control" rows="2"></textarea>
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
$(document).on('click', '.edit-subzone-btn', function () {
    var id = $(this).data('id');
    $('#edit-subzone-name').val($(this).data('name'));
    $('#edit-subzone-details').val($(this).data('details'));
    $('#editSubZoneForm').attr('action', '/reseller/configuration/subzone/' + id);
});

$(document).on('change', '.toggle-subzone', function () {
    var id = $(this).data('id');
    $.post('/reseller/configuration/subzone/' + id + '/toggle', { _token: '{{ csrf_token() }}' });
});
</script>
@endsection