@extends('reseller.layouts.app')


@section('title', $entityLabel)

@section('content')



<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-plus mr-1"></i> Add {{ $entityLabel }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route($routeBase . '.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>{{ $entityLabel }} Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Details (optional)</label>
                        <textarea name="details" class="form-control" rows="2">{{ old('details') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-plus mr-1"></i> Add {{ $entityLabel }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Your {{ $entityPlural }}</div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Details</th>
                            <th>Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->details ?? '—' }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input toggle-lookup"
                                        id="lk-{{ $item->id }}" data-id="{{ $item->id }}"
                                        {{ $item->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="lk-{{ $item->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-lookup-btn"
                                    data-id="{{ $item->id }}"
                                    data-name="{{ $item->name }}"
                                    data-details="{{ $item->details }}"
                                    data-toggle="modal" data-target="#editLookupModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route($routeBase . '.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this {{ strtolower($entityLabel) }}?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No {{ strtolower($entityPlural) }} added yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editLookupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editLookupForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit {{ $entityLabel }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ $entityLabel }} Name</label>
                        <input type="text" name="name" id="edit-lookup-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Details</label>
                        <textarea name="details" id="edit-lookup-details" class="form-control" rows="2"></textarea>
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
$(document).on('click', '.edit-lookup-btn', function () {
    var id = $(this).data('id');
    $('#edit-lookup-name').val($(this).data('name'));
    $('#edit-lookup-details').val($(this).data('details'));
    $('#editLookupForm').attr('action', "{{ route($routeBase . '.update', ['__ID__']) }}".replace('__ID__', id));
});

$(document).on('change', '.toggle-lookup', function () {
    var id = $(this).data('id');
    $.post("{{ route($routeBase . '.toggle', ['__ID__']) }}".replace('__ID__', id), { _token: '{{ csrf_token() }}' });
});
</script>
@endsection