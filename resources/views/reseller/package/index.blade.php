@extends('reseller.layouts.app')

@section('title', 'Package')

@section('content')

<div class="card">
    <div class="card-header">
        <i class="fas fa-boxes mr-1"></i> Package
        <small class="text-muted">Prices from Admin — you can only set your own Selling Rate</small>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0">
            <thead class="bg-light">
                <tr>
                    <th>Package Name</th>
                    <th>Server Name</th>
                    <th>Protocol</th>
                    <th>Profile</th>
                    <th>Buying Rate</th>
                    <th>Selling Rate</th>
                    <th>Validity Days</th>
                    <th>Min R. Days</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $pkg)
                <tr>
                    <td>{{ $pkg->package->name ?? '—' }}</td>
                    <td>{{ $pkg->server_name }}</td>
                    <td>{{ strtoupper($pkg->protocol) }}</td>
                    <td>{{ $pkg->profile }}</td>
                    <td>{{ number_format($pkg->rate, 2) }}</td>
                    <td>
                        @if($pkg->my_selling_rate !== null)
                            {{ number_format($pkg->my_selling_rate, 2) }}
                        @else
                            <span class="text-muted">Not set</span>
                        @endif
                    </td>
                    <td>{{ $pkg->validity_days }}</td>
                    <td>{{ $pkg->min_activation_days }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-package-btn"
                            data-id="{{ $pkg->id }}"
                            data-name="{{ $pkg->package->name ?? '' }}"
                            data-server="{{ $pkg->server_name }}"
                            data-protocol="{{ strtoupper($pkg->protocol) }}"
                            data-profile="{{ $pkg->profile }}"
                            data-buying="{{ number_format($pkg->rate, 2) }}"
                            data-selling="{{ $pkg->my_selling_rate }}"
                            data-toggle="modal" data-target="#editPackageModal">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-3">
                        No packages found. Please contact your admin to assign a Tariff / Package.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Edit Package Modal — only Selling Rate is actually editable --}}
<div class="modal fade" id="editPackageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editPackageForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Package</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Package Name</label>
                        <input type="text" id="ep-name" class="form-control bg-light" readonly>
                    </div>
                    <div class="form-group">
                        <label>Server Name</label>
                        <input type="text" id="ep-server" class="form-control bg-light" readonly>
                    </div>
                    <div class="form-group">
                        <label>Protocol</label>
                        <input type="text" id="ep-protocol" class="form-control bg-light" readonly>
                    </div>
                    <div class="form-group">
                        <label>Profile</label>
                        <input type="text" id="ep-profile" class="form-control bg-light" readonly>
                    </div>
                    <div class="form-group">
                        <label>Buying Rate</label>
                        <input type="text" id="ep-buying" class="form-control bg-light" readonly>
                    </div>
                    <div class="form-group">
                        <label>Selling Rate</label>
                        <input type="number" step="0.01" min="0" name="selling_rate" id="ep-selling" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).on('click', '.edit-package-btn', function () {
    var id = $(this).data('id');
    $('#ep-name').val($(this).data('name'));
    $('#ep-server').val($(this).data('server'));
    $('#ep-protocol').val($(this).data('protocol'));
    $('#ep-profile').val($(this).data('profile'));
    $('#ep-buying').val($(this).data('buying'));
    $('#ep-selling').val($(this).data('selling') ?? '');
    $('#editPackageForm').attr('action', "{{ url('reseller/configuration/package/00000000/selling-rate') }}".replace('00000000', id));
});
</script>
@endsection