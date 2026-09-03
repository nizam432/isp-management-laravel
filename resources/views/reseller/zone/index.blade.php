@extends('reseller.layouts.app')

@section('title', 'Zone')

@section('content')



<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-plus mr-1"></i> Add Zone</div>
            <div class="card-body">
                <form method="POST" action="{{ route('reseller.configuration.zone.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Zone Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Details (optional)</label>
                        <textarea name="details" class="form-control" rows="2">{{ old('details') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-plus mr-1"></i> Add Zone
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-map-marker-alt mr-1"></i> Your Zones</div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Details</th>
                            <th>Sub Zones</th>
                            <th>Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zones as $zone)
                        <tr>
                            <td>{{ $zone->name }}</td>
                            <td>{{ $zone->details ?? '—' }}</td>
                            <td>{{ $zone->sub_zones_count }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input toggle-zone"
                                        id="z-{{ $zone->id }}" data-id="{{ $zone->id }}"
                                        {{ $zone->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="z-{{ $zone->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-zone-btn"
                                    data-id="{{ $zone->id }}"
                                    data-name="{{ $zone->name }}"
                                    data-details="{{ $zone->details }}"
                                    data-toggle="modal" data-target="#editZoneModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('reseller.configuration.zone.destroy', $zone->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this zone?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No zones added yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit Zone Modal --}}
<div class="modal fade" id="editZoneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editZoneForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Zone</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Zone Name</label>
                        <input type="text" name="name" id="edit-zone-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Details</label>
                        <textarea name="details" id="edit-zone-details" class="form-control" rows="2"></textarea>
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
$(document).on('click', '.edit-zone-btn', function () {
    var id = $(this).data('id');
    $('#edit-zone-name').val($(this).data('name'));
    $('#edit-zone-details').val($(this).data('details'));
    $('#editZoneForm').attr('action', '/reseller/configuration/zone/' + id);
});

$(document).on('change', '.toggle-zone', function () {
    var id = $(this).data('id');
    $.post('/reseller/configuration/zone/' + id + '/toggle', { _token: '{{ csrf_token() }}' });
});
</script>
@endsection