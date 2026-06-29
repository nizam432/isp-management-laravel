@extends('adminlte::page')
@section('title', 'Store Locations')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-warehouse mr-2 text-primary"></i>Store Locations
            </h4>
            <small class="text-muted">Manage inventory store locations</small>
        </div>
        <a href="{{ route('inventory.locations.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> Add Location
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Location List
        </h6>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Quick search..." style="width:220px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="locationTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="text-center" style="width:50px;">#</th>
                        <th>Name / Address</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width:160px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="locationTableBody">
                    @forelse($locations as $location)
                    <tr>
                        <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            <span class="font-weight-bold">{{ $location->name }}</span>
                            @if($location->address)
                                <br><small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i>{{ $location->address }}</small>
                            @endif
                        </td>
                        <td class="text-muted">{{ $location->contact_person ?? '—' }}</td>
                        <td class="text-muted">{{ $location->phone ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $location->is_active ? 'badge-success' : 'badge-secondary' }}">
                                {{ $location->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('inventory.locations.edit', $location) }}"
                               class="btn btn-sm btn-warning px-2" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('inventory.locations.toggle', $location) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit"
                                        class="btn btn-sm px-2 {{ $location->is_active ? 'btn-secondary' : 'btn-success' }}"
                                        title="{{ $location->is_active ? 'Deactivate' : 'Activate' }}"
                                        onclick="return confirm('{{ $location->is_active ? 'Deactivate' : 'Activate' }} this location?')">
                                    <i class="fas {{ $location->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-warehouse fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No locations found. Click <strong>+ Add Location</strong> to add one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($locations->hasPages())
    <div class="card-footer bg-light py-2">
        {{ $locations->links() }}
    </div>
    @endif
</div>

@endsection

@section('css')
<style>
    #locationTable thead th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #555;
        padding: 10px 12px;
    }
    #locationTable tbody td { padding: 10px 12px; vertical-align: middle; }
    #locationTable tbody tr:hover { background:#f0f4ff !important; }
    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color:#1a237e; }
</style>
@stop

@section('js')
@parent
<script>
$(function () {
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#locationTableBody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });
});
</script>
@stop
